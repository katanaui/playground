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

        // Read current values from query string, falling back to studio defaults then YAML defaults
        $studioDefaults = $yaml['studio']['defaults'] ?? [];
        $attrs = [];
        foreach ($props as $prop) {
            $name = $prop['name'];
            $default = $studioDefaults[$name] ?? $prop['default'] ?? '';
            $attrs[$name] = request()->input("attrs.{$name}", $default);
        }

        $slotValues = [];
        foreach ($slots as $slot) {
            $name = $slot['name'];
            $default = $slot['default'] ?? '';
            $slotValues[$name] = request()->input("slots.{$name}", $default);
        }

        // Ensure default slot exists — only use component name if slots are defined in YAML
        if (!isset($slotValues['slot'])) {
            $fallback = !empty($slots) ? $componentName : '';
            $slotValues['slot'] = request()->input('slots.slot', $fallback);
        }

        $componentId = $component;
        $container = $yaml['studio']['container'] ?? null;
        $studioDefault = $yaml['studio']['default'] ?? null;

        // Use studio.default as initial code if set and no user overrides via query string
        if ($studioDefault && !request()->has('attrs') && !request()->has('slots')) {
            $code = $studioDefault;
        } else {
            $code = $this->generateCode($component, $attrs, $slotValues, $props);
        }

        // Wrap in studio container for preview rendering (but keep $code clean for display)
        $renderCode = $container ? str_replace('{{component}}', $code, $container) : $code;

        // Server-side render the initial preview as a full HTML document (srcdoc for nested iframe)
        $renderedHtml = '';
        try {
            $renderedHtml = Blade::render($renderCode);
        } catch (\Throwable $e) {
            $renderedHtml = '<div style="padding:1.5rem;font-family:system-ui;"><p style="color:#ef4444;font-weight:600;margin:0 0 0.5rem;">Render Error</p><pre style="background:#fef2f2;color:#991b1b;padding:1rem;border-radius:0.5rem;font-size:0.8rem;overflow:auto;white-space:pre-wrap;">' . e($e->getMessage()) . '</pre></div>';
        }
        $studioScripts = $yaml['studio']['scripts'] ?? [];
        $studioHead = $yaml['studio']['head'] ?? '';
        $previewDoc = view('studio.render', ['rendered' => $renderedHtml, 'studioScripts' => $studioScripts, 'studioHead' => $studioHead])->render();

        return view('studio.show', compact(
            'components',
            'componentId',
            'componentName',
            'props',
            'slots',
            'attrs',
            'slotValues',
            'code',
            'container',
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

        $studioDefaults = $yaml['studio']['defaults'] ?? [];
        $attrs = [];
        foreach ($props as $prop) {
            $name = $prop['name'];
            $default = $studioDefaults[$name] ?? $prop['default'] ?? '';
            $attrs[$name] = request()->input("attrs.{$name}", $default);
        }

        $slotValues = [];
        foreach ($slots as $slot) {
            $name = $slot['name'];
            $default = $slot['default'] ?? '';
            $slotValues[$name] = request()->input("slots.{$name}", $default);
        }

        if (!isset($slotValues['slot'])) {
            $fallback = !empty($slots) ? $componentName : '';
            $slotValues['slot'] = request()->input('slots.slot', $fallback);
        }

        $container = $yaml['studio']['container'] ?? null;
        $studioDefault = $yaml['studio']['default'] ?? null;

        if ($studioDefault && !request()->has('attrs') && !request()->has('slots')) {
            $code = $studioDefault;
        } else {
            $code = $this->generateCode($component, $attrs, $slotValues, $props);
        }
        $renderCode = $container ? str_replace('{{component}}', $code, $container) : $code;

        try {
            $rendered = Blade::render($renderCode);
        } catch (\Throwable $e) {
            $rendered = '<div style="padding:1.5rem;font-family:system-ui;"><p style="color:#ef4444;font-weight:600;margin:0 0 0.5rem;">Render Error</p><pre style="background:#fef2f2;color:#991b1b;padding:1rem;border-radius:0.5rem;font-size:0.8rem;overflow:auto;white-space:pre-wrap;">' . e($e->getMessage()) . '</pre></div>';
        }

        $studioScripts = $yaml['studio']['scripts'] ?? [];
        $studioHead = $yaml['studio']['head'] ?? '';

        return view('studio.render', ['rendered' => $rendered, 'studioScripts' => $studioScripts, 'studioHead' => $studioHead]);
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

        return view('studio.render', ['rendered' => $rendered, 'studioScripts' => [], 'studioHead' => '']);
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
            } elseif (is_array($value)) {
                $phpExpr = $this->phpArrayLiteral($value);
                $attrLines[] = "    :{$key}=\"{$phpExpr}\"";
            } elseif (is_string($value) && (str_starts_with($value, '[') || str_starts_with($value, '{')) && json_decode($value) !== null) {
                $decoded = json_decode($value, true);
                $attrLines[] = "    :{$key}=\"" . $this->phpArrayLiteral($decoded) . "\"";
            } else {
                $attrLines[] = "    {$key}=\"" . e((string) $value) . "\"";
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

    private function phpArrayLiteral($value): string
    {
        if (is_array($value)) {
            if (array_is_list($value)) {
                $items = array_map(fn($v) => $this->phpArrayLiteral($v), $value);
                return '[' . implode(', ', $items) . ']';
            }
            $items = [];
            foreach ($value as $k => $v) {
                $items[] = "'" . addslashes((string) $k) . "' => " . $this->phpArrayLiteral($v);
            }
            return '[' . implode(', ', $items) . ']';
        }
        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_null($value)) {
            return 'null';
        }
        return (string) $value;
    }
}
