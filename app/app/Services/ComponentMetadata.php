<?php

namespace App\Services;

use Symfony\Component\Yaml\Yaml;

class ComponentMetadata
{
    private string $basePath;

    public function __construct()
    {
        $packagesPath = base_path('packages/katanaui/katana/resources/views/components/katana');
        $vendorPath = base_path('vendor/katanaui/katana/resources/views/components/katana');
        $this->basePath = is_dir($packagesPath) ? $packagesPath : $vendorPath;
    }

    public function getAllComponents(): array
    {
        $yamlPath = $this->basePath . '/katana.yml';

        if (!file_exists($yamlPath)) {
            return [];
        }

        $data = Yaml::parse(file_get_contents($yamlPath));

        return $data['components'] ?? [];
    }

    public function getComponentData(string $component): array
    {
        $ymlFile = $this->resolveYamlPath($component);

        if (!$ymlFile || !file_exists($ymlFile)) {
            return ['component' => ucfirst(str_replace('-', ' ', $component))];
        }

        return Yaml::parse(file_get_contents($ymlFile)) ?? [];
    }

    private function resolveYamlPath(string $component): ?string
    {
        // Try dotted path: "accordion.item" -> "accordion/item.yml"
        $slashedPath = str_replace('.', '/', $component);

        $candidates = [
            $this->basePath . '/' . $slashedPath . '.yml',
            $this->basePath . '/' . $component . '.yml',
            $this->basePath . '/' . $component . '/' . $component . '.yml',
            $this->basePath . '/' . $slashedPath . '/' . basename($slashedPath) . '.yml',
        ];

        // Also handle nested components like "accordion" -> "accordion/accordion.yml"
        $candidates[] = $this->basePath . '/' . $component . '/' . $component . '.yml';

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
