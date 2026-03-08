<?php

namespace App\Http\Controllers;

use App\Services\ComponentMetadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

class ComponentController extends Controller
{
    public function index()
    {
        $metadata = new ComponentMetadata();
        $components = $metadata->getAllComponents();

        return view('studio.index', compact('components'));
    }

    public function show(string $component)
    {
        $metadata = new ComponentMetadata();
        $components = $metadata->getAllComponents();
        $yaml = $metadata->getComponentData($component);

        $props = $yaml['props'] ?? [];
        $slots = $yaml['slots'] ?? [];
        $componentName = $yaml['component'] ?? ucfirst(str_replace('-', ' ', $component));

        // Read current values from query string, falling back to YAML defaults
        $attrs = [];
        foreach ($props as $prop) {
            $name = $prop['name'];
            $default = $prop['default'] ?? '';
            $attrs[$name] = request()->input("attrs.{$name}", $default);
        }

        $slotValues = [];
        foreach ($slots as $slot) {
            $name = $slot['name'];
            $default = $slot['default'] ?? '';
            $slotValues[$name] = request()->input("slots.{$name}", $default);
        }

        // Ensure default slot exists
        if (!isset($slotValues['slot'])) {
            $slotValues['slot'] = request()->input('slots.slot', $componentName);
        }

        $componentId = $component;
        $code = $this->generateCode($component, $attrs, $slotValues, $props);

        // Server-side render the initial preview as a full HTML document (srcdoc for nested iframe)
        $renderedHtml = '';
        try {
            $renderedHtml = Blade::render($code);
        } catch (\Throwable $e) {
            $renderedHtml = '<div style="padding:1.5rem;font-family:system-ui;"><p style="color:#ef4444;font-weight:600;margin:0 0 0.5rem;">Render Error</p><pre style="background:#fef2f2;color:#991b1b;padding:1rem;border-radius:0.5rem;font-size:0.8rem;overflow:auto;white-space:pre-wrap;">' . e($e->getMessage()) . '</pre></div>';
        }
        $previewDoc = view('studio.render', ['rendered' => $renderedHtml])->render();

        return view('studio.show', compact(
            'components',
            'componentId',
            'componentName',
            'props',
            'slots',
            'attrs',
            'slotValues',
            'code',
            'previewDoc'
        ));
    }

    public function preview(string $component)
    {
        $metadata = new ComponentMetadata();
        $yaml = $metadata->getComponentData($component);

        $props = $yaml['props'] ?? [];
        $slots = $yaml['slots'] ?? [];
        $componentName = $yaml['component'] ?? ucfirst(str_replace('-', ' ', $component));

        $attrs = [];
        foreach ($props as $prop) {
            $name = $prop['name'];
            $default = $prop['default'] ?? '';
            $attrs[$name] = request()->input("attrs.{$name}", $default);
        }

        $slotValues = [];
        foreach ($slots as $slot) {
            $name = $slot['name'];
            $default = $slot['default'] ?? '';
            $slotValues[$name] = request()->input("slots.{$name}", $default);
        }

        if (!isset($slotValues['slot'])) {
            $slotValues['slot'] = request()->input('slots.slot', $componentName);
        }

        $code = $this->generateCode($component, $attrs, $slotValues, $props);

        try {
            $rendered = Blade::render($code);
        } catch (\Throwable $e) {
            $rendered = '<div style="padding:1.5rem;font-family:system-ui;"><p style="color:#ef4444;font-weight:600;margin:0 0 0.5rem;">Render Error</p><pre style="background:#fef2f2;color:#991b1b;padding:1rem;border-radius:0.5rem;font-size:0.8rem;overflow:auto;white-space:pre-wrap;">' . e($e->getMessage()) . '</pre></div>';
        }

        return view('studio.render', ['rendered' => $rendered]);
    }

    public function render(Request $request)
    {
        $code = $request->input('code', '');

        if (empty($code)) {
            return response('<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#a1a1aa;font-family:system-ui;">No component to preview</div>');
        }

        try {
            $rendered = Blade::render($code);
        } catch (\Throwable $e) {
            $rendered = '<div style="padding:1.5rem;font-family:system-ui;"><p style="color:#ef4444;font-weight:600;margin:0 0 0.5rem;">Render Error</p><pre style="background:#fef2f2;color:#991b1b;padding:1rem;border-radius:0.5rem;font-size:0.8rem;overflow:auto;white-space:pre-wrap;">' . e($e->getMessage()) . '</pre></div>';
        }

        return view('studio.render', ['rendered' => $rendered]);
    }

    private function generateCode(string $component, array $attrs, array $slotValues, array $props): string
    {
        $tagName = "katana.{$component}";
        $lines = ["<x-{$tagName}"];

        $attrLines = [];
        foreach ($attrs as $key => $value) {
            if ($value === '' || $value === null) continue;
            if ($value === false || $value === 'false') continue;
            if ($value === '0' && $key !== 'open') continue;

            if ($value === true || $value === 'true' || $value === '1') {
                $attrLines[] = "    {$key}";
            } else {
                $attrLines[] = "    {$key}=\"{$value}\"";
            }
        }

        if (!empty($attrLines)) {
            $lines[0] .= "\n" . implode("\n", $attrLines);
        }

        $defaultSlot = $slotValues['slot'] ?? '';
        $namedSlots = array_filter($slotValues, function ($v, $k) {
            return $k !== 'slot' && $v !== '' && $v !== null;
        }, ARRAY_FILTER_USE_BOTH);

        if (empty($defaultSlot) && empty($namedSlots)) {
            $lines[] = "/>";
        } else {
            $lines[] = ">";

            if (!empty($defaultSlot)) {
                $lines[] = "    {$defaultSlot}";
            }

            foreach ($namedSlots as $name => $content) {
                $lines[] = "    <x-slot:{$name}>{$content}</x-slot:{$name}>";
            }

            $lines[] = "</x-{$tagName}>";
        }

        return implode("\n", $lines);
    }
}
