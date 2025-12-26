<?php

namespace Modules\AdminFrontendThemes\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FrontendThemeService
{
    protected $themesPath = 'resources/themes/guest';

    public function all()
    {
        $fullPath = base_path($this->themesPath);

        if (!is_dir($fullPath)) return [];

        $themes = [];

        foreach (scandir($fullPath) as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            $themePath = $fullPath . '/' . $dir;

            if (is_dir($themePath)) {
                $jsonPath = $themePath . '/theme.json';
                if (file_exists($jsonPath)) {
                    $data = json_decode(file_get_contents($jsonPath), true);
                    if (is_array($data)) {
                        $data['id'] = $dir;
                        $data['path'] = $themePath;
                        $themes[] = $data;
                    }
                }
            }
        }

        usort($themes, function ($a, $b) {
            return ($b['sort'] ?? 0) <=> ($a['sort'] ?? 0);
        });

        return $themes;
    }

    public function get($themeId)
    {
        $fullPath = base_path($this->themesPath . '/' . $themeId . '/theme.json');
        if (!file_exists($fullPath)) return null;
        $data = json_decode(file_get_contents($fullPath), true);
        if (!is_array($data)) return null;
        $data['id'] = $themeId;
        return $data;
    }
}
