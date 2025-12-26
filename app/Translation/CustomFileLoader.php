<?php

namespace App\Translation;

use Illuminate\Translation\FileLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;

class CustomFileLoader extends FileLoader
{
    protected $mergedTranslations;

    public function __construct(Filesystem $files, $path)
    {
        parent::__construct($files, $path);

        // Attempt to load cached translations
        $this->mergedTranslations = Cache::get('merged_translations', function () {
            $langFile = resource_path('lang/en.json');
            if (file_exists($langFile)) {
                $translations = json_decode(file_get_contents($langFile), true);
                Cache::put('merged_translations', $translations, now()->addDay()); // Cache for 1 day
                return $translations;
            }
            return [];
        });
    }

    protected function loadJsonPaths($locale)
    {
        // Return the merged translations for the given locale
        return $this->mergedTranslations;
    }
}
