<?php

namespace Modules\AdminLanguages\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Cache;
use Modules\AdminLanguages\Models\Languages;
use Modules\AdminLanguages\Models\LanguageItems;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Cookie;
use File;

class Language extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'language';
    }

    public static function getLanguages(){
        $languages = Languages::where("status", 1)->get();
        return $languages;
    }

    public static function setLang($request)
    {
        $lang_default = Languages::where("is_default", 1)->where("status", 1)->first();
        $locale_default = $lang_default->code ?? 'en';
        $supportedLocales = Languages::where('status', 1)->pluck('code')->toArray();

        if (!$request->hasCookie('locale')) {
            if (auth()->check() && in_array(auth()->user()->language, $supportedLocales)) {
                $locale = auth()->user()->language;
            } else {
                $locale = $locale_default;
            }
            Cookie::queue('locale', $locale, 60 * 24 * 365 * 10);
        } else {
            $locale = $request->cookie('locale');
            if (!in_array($locale, $supportedLocales)) {
                $locale = $locale_default;
                Cookie::queue('locale', $locale, 60 * 24 * 365 * 10);
            }
        }

        app()->setLocale($locale);
    }

    public static function getCurrent($field = null, $langCode = null)
    {
        $langCode = $langCode 
            ?? request()->cookie('locale') 
            ?? config('app.locale', 'en');

        static $cache = [];

        if (!isset($cache[$langCode])) {
            $cache[$langCode] = Languages::where("status", 1)
                ->where('code', $langCode)
                ->first();

            if (!$cache[$langCode]) {
                $cache[$langCode] = Languages::where("status", 1)
                    ->orderByRaw("FIELD(code, 'en') DESC")
                    ->first();
            }
        }

        $languageItem = $cache[$langCode];

        if ($languageItem && $field) {
            return $languageItem->{$field} ?? null;
        }
        return $languageItem;
    }

    public static function toJs(?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        $data = LanguageItems::where('code', $locale)
            ->pluck('value', 'name')
            ->toArray();

        \Script::define('Lang', $data);
    }

    protected static function getAllTranslatableDirs()
    {
        $dirs = [];

        // Modules
        $modulesDir = base_path('modules');
        if (is_dir($modulesDir)) {
            foreach (scandir($modulesDir) as $m) {
                if ($m !== '.' && $m !== '..') {
                    $dirs[] = $modulesDir . '/' . $m;
                }
            }
        }

        // Themes
        $themesDir = resource_path('themes');
        foreach (['guest', 'app'] as $themeType) {
            $themeTypeDir = $themesDir . '/' . $themeType;
            if (is_dir($themeTypeDir)) {
                foreach (scandir($themeTypeDir) as $t) {
                    if ($t !== '.' && $t !== '..') {
                        $dirs[] = $themeTypeDir . '/' . $t;
                    }
                }
            }
        }

        // App Laravel
        $appDir = app_path();
        if (is_dir($appDir)) {
            $dirs[] = $appDir;
        }

        return $dirs;
    }

    public static function createLanguageFiles($locale = 'en', bool $autoTranslate = true)
    {
        $allTranslations = [];
        $dirs = self::getAllTranslatableDirs();

        foreach ($dirs as $dir) {
            $translations = self::findTranslationStrings($dir);
            self::updateLangFiles($dir, $translations);

            $langFile = $dir . '/resources/lang/en.json';
            if (!file_exists($langFile)) {
                $langFile = $dir . '/lang/en.json';
            }
            if (file_exists($langFile)) {
                $moduleTranslations = json_decode(file_get_contents($langFile), true);
                $allTranslations = array_merge($allTranslations, $moduleTranslations);
            }
        }

        self::saveTranslations($allTranslations, $locale, $autoTranslate);
    }

    protected static function findTranslationStrings($dir)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $regex = '/__\(\s*([\'"])(.+?)\1/s';
        $translations = [];

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $contents = file_get_contents($file->getRealPath());
                if (preg_match_all($regex, $contents, $matches)) {
                    foreach ($matches[2] as $match) {
                        $translations[$match] = $match;
                    }
                }
            }
        }

        // ... phần đọc module.json giữ nguyên
        $moduleJsonPath = $dir . '/module.json';
        if (file_exists($moduleJsonPath)) {
            $moduleJson = json_decode(file_get_contents($moduleJsonPath), true);
            if (isset($moduleJson['menu'])) {
                $menu = $moduleJson['menu'];
                if (isset($menu['tab_name'])) {
                    $translations[$menu['tab_name']] = $menu['tab_name'];
                }
                if (isset($menu['name'])) {
                    $translations[$menu['name']] = $menu['name'];
                }
                if (isset($menu['sub_menu']) && is_array($menu['sub_menu'])) {
                    foreach ($menu['sub_menu'] as $subMenu) {
                        if (isset($subMenu['name'])) {
                            $translations[$subMenu['name']] = $subMenu['name'];
                        }
                    }
                }
            }
        }

        self::testTranslate($translations);

        return $translations;
    }

    protected static function testTranslate(&$translations)
    {
        if(!empty($translations)){
            foreach ($translations as $key => $value) {
                $translations[$key] = $value;
            }
        }

        return $translations;
    }

    protected static function updateLangFiles($dir, $translations)
    {
        if (realpath($dir) === realpath(app_path())) {
            $langDir = resource_path('lang');
            $langFile = $langDir . '/en.json';
        }
        elseif (file_exists($dir . '/theme.json')) {
            $langDir = $dir . '/lang';
            $langFile = $langDir . '/en.json';
        }
        elseif (file_exists($dir . '/module.json')) {
            $langDir = $dir . '/resources/lang';
            $langFile = $langDir . '/en.json';
        }
        else {
            $langDir = $dir . '/lang';
            $langFile = $langDir . '/en.json';
        }

        if (!file_exists($langDir)) {
            try {
                mkdir($langDir, 0755, true);
            } catch (\Exception $e) {}
        }

        $existingTranslations = file_exists($langFile) ? json_decode(file_get_contents($langFile), true) : [];
        $mergedTranslations = array_merge($existingTranslations, $translations);

        file_put_contents($langFile, json_encode($mergedTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    protected static function saveMergedLangFile($translations, $locale = 'en')
    {
        $langDir = resource_path('lang');
        $langFile = $langDir . '/'.$locale.'.json';

        if (!file_exists($langDir)) {
            mkdir($langDir, 0755, true);
        }

        file_put_contents($langFile, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function updateAllLanguages()
    {
        // Step 1: Get all languages that need translation updates
        $languages = Languages::all();

        foreach ($languages as $language) {
            $locale = $language->code;
            $autoTranslate = $language->auto_translate;

            // Step 2: Call updateLanguageTranslations for each language
            $missingCount = self::updateLanguageTranslations($locale, $autoTranslate);

            // Optional: Log the count of missing translations for each language
            if ($missingCount > 0) {
                \Log::info("Updated $missingCount missing translations for language $locale");
            } else {
                \Log::info("No missing translations found for language $locale");
            }
        }

        return "All languages updated successfully.";
    }

    public static function updateTranslation($id, $value)
    {
        // Update the translation in the database
        $languageItem = LanguageItems::find($id);

        if (!$languageItem) {
            return response()->json([
                'status'  => 0,
                'message' => __("Translation item not found")
            ], 404);
        }

        // Update the translation value in the database
        $languageItem->value = $value;
        $languageItem->save();

        // Update the corresponding language JSON file
        $langCode = $languageItem->code; // Assuming 'code' is the language code
        $languageFilePath = resource_path("lang/{$langCode}.json");

        // Check if the language file exists
        if (!file_exists($languageFilePath)) {
            return response()->json([
                'status'  => 0,
                'message' => __("Language file not found")
            ], 404);
        }

        // Load the existing translations from the file
        $translations = json_decode(file_get_contents($languageFilePath), true);

        // Update the specific translation key with the new value
        $translations[$languageItem->name] = $value;

        // Save the updated translations back to the file
        file_put_contents($languageFilePath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json([
            'status'  => 1,
            'message' => __("Update successfully")
        ]);
    }

    /**
     * Automatically translates a word based on the id of the LanguageItem and the target language from the LanguageItem.
     *
     * @param int $id
     * @return array
     */
    public static function translateWordById($id)
    {
        $languageItem = LanguageItems::find($id);

        if (!$languageItem) {
            return [
                'status' => 0,
                'message' => __('Language Item not found'),
                'text' => '',
            ];
        }

        $locale = $languageItem->code;

        try {
            $trans = new GoogleTranslate();
            $trans->setSource('en');
            $trans->setTarget($locale);
            $translatedText = $trans->translate($languageItem->name);
            $translatedText = self::fixTranslate($translatedText, true);

            $languageItem->value = $translatedText;
            $languageItem->save();

            return response()->json([
                'status' => 1,
                'message' => __('Translation Successfully'),
                'text' => $translatedText,
                'id' => $languageItem->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => __('Translation Failed: ') . $e->getMessage(),
                'text' => '',
                'id' => $languageItem->id,
            ]);
        }
    }

    public static function updateLanguageTranslations($locale = 'en', $autoTranslate = false)
    {
        $allTranslationKeys = [];
        $dirs = self::getAllTranslatableDirs();

        foreach ($dirs as $dir) {
            $translations = self::findTranslationStrings($dir);
            $allTranslationKeys = array_merge($allTranslationKeys, array_keys($translations));
        }

        $existingKeys = LanguageItems::where('code', $locale)->pluck('name')->toArray();
        $missingKeys = array_diff($allTranslationKeys, $existingKeys);

        foreach ($missingKeys as $key) {
            $exists = LanguageItems::where('code', $locale)->where('name', $key)->exists();
            if (!$exists) {
                LanguageItems::create([
                    'code' => $locale,
                    'name' => $key,
                    'value' => $key,
                    'custom' => 0
                ]);
            }
        }

        if ((int)$autoTranslate && $locale !== 'en') {
            $translationsToTranslate = [];
            foreach ($missingKeys as $key) {
                $translationsToTranslate[$key] = $key;
            }
            self::saveTranslations($translationsToTranslate, $locale, true);
        }

        return count($missingKeys);
    }

    public static function updateMissingTranslationKeys()
    {
        // Scan all modules to retrieve translation keys
        $modulesDir = base_path('modules');
        $modules = scandir($modulesDir);

        $allTranslationKeys = [];

        foreach ($modules as $module) {
            if ($module !== '.' && $module !== '..') {
                $moduleDir = $modulesDir . '/' . $module;

                // Find translation keys within the module
                $translations = self::findTranslationStrings($moduleDir);

                // Merge all translation keys into an array
                $allTranslationKeys = array_merge($allTranslationKeys, array_keys($translations));
            }
        }

        // Get the existing keys from the database
        $existingKeys = LanguageItems::where('code', 'en')->pluck('name')->toArray();

        // Find the missing translation keys
        $missingKeys = array_diff($allTranslationKeys, $existingKeys);

        // If there are missing keys, insert them into the database
        foreach ($missingKeys as $key) {
            LanguageItems::updateOrInsert(
                ['code' => 'en', 'name' => $key],
                ['value' => $key, 'custom' => 0]
            );
        }
    }

    /**
     * Save and optionally auto-translate language translations.
     *
     * @param array $translations Key-value pairs of English strings to be translated.
     * @param string $locale Target locale (e.g., 'vi', 'fr'). Default is 'en'.
     * @param bool $autoTranslate Whether to auto-translate using Google Translate. Default is false.
     *
     * This function:
     * - Accepts a batch of translations.
     * - Splits them into chunks to stay within translation API limits.
     * - Automatically translates them if required.
     * - Capitalizes the first letter of each translated sentence.
     * - Handles fallback: if translation is missing or mismatched, it uses the English version.
     * - Saves the merged results into both JSON file and the LanguageItems table.
     */
    public static function saveTranslations(array $translations, string $locale = 'en', bool $autoTranslate = false, int $retryCount = 5)
    {
        $translatedData = $translations;

        // Only auto-translate if needed and locale is not English
        if ($autoTranslate && $locale !== 'en') {
            $maxLength = 4000; // Max characters per translation batch
            $keys = array_keys($translations);
            $values = array_values($translations);

            $batches = [];
            $batchText = '';
            $batchKeys = [];
            $missingResults = [];

            // Break translations into smaller batches
            foreach ($values as $i => $text) {
                $line = "[[I_{$i}]]" . $text; // Tag each line with index to identify later
                $candidate = $batchText === '' ? $line : $batchText . "\n" . $line;

                if (strlen($candidate) > $maxLength) {
                    // Save current batch if it exceeds limit
                    $batches[] = ['keys' => $batchKeys, 'text' => $batchText];
                    $batchText = $line;
                    $batchKeys = [$keys[$i]];
                } else {
                    $batchText = $candidate;
                    $batchKeys[] = $keys[$i];
                }
            }

            if (!empty($batchText)) {
                $batches[] = ['keys' => $batchKeys, 'text' => $batchText];
            }

            $translatedResults = [];

            try {
                // First pass: translate available keys
                foreach ($batches as $batch) {
                    $trans = new GoogleTranslate();
                    $trans->setSource('en');
                    $trans->setTarget($locale);

                    $translatedText = $trans->translate($batch['text']);
                    $lines = self::fixTranslate($translatedText);

                    // Parse each translated line
                    foreach ($lines as $line) {
                        if (preg_match('/^\[\[I_(\d+)\]\](.*)$/', $line, $matches)) {
                            $originalIndex = (int)$matches[1];
                            $translatedValue = trim($matches[2]);

                            // Capitalize the first letter of the sentence
                            $translatedValue = self::capitalizeFirstLetters($translatedValue);

                            if (isset($keys[$originalIndex])) {
                                $translatedResults[$keys[$originalIndex]] = $translatedValue;
                            }
                        }
                    }

                    // Collect missing translations
                    foreach ($batch['keys'] as $key) {
                        if (!isset($translatedResults[$key])) {
                            $originalIndex = array_search($key, $keys);
                            $missingResults[$key] = $values[$originalIndex];
                        }
                    }
                }


                // Retry translating missing results (respect max encoded length)
                $attempts   = 0;
                $retryLimit = $retryCount;

                $maxEncodedLen = 1800;

                $encodedLen = function (string $s): int {
                    return strlen(rawurlencode($s));
                };

                while (!empty($missingResults) && $attempts < $retryLimit) {
                    $attempts++;

                    $missingKeys   = array_keys($missingResults);
                    $missingValues = array_values($missingResults);

                    $batches = [];
                    $buf = '';
                    $bufKeys = [];
                    $tooLarge = [];

                    foreach ($missingValues as $i => $text) {
                        $line = (string) $text;
                        $candidate = ($buf === '') ? $line : ($buf . "\n" . $line);

                        if ($encodedLen($candidate) > $maxEncodedLen) {
                            if ($buf !== '') {
                                $batches[] = ['keys' => $bufKeys, 'text' => $buf];
                            }

                            if ($encodedLen($line) > $maxEncodedLen) {
                                $tooLarge[$missingKeys[$i]] = $line;
                                $buf = '';
                                $bufKeys = [];
                                continue;
                            }

                            $buf = $line;
                            $bufKeys = [$missingKeys[$i]];
                        } else {
                            $buf = $candidate;
                            $bufKeys[] = $missingKeys[$i];
                        }
                    }
                    if ($buf !== '') {
                        $batches[] = ['keys' => $bufKeys, 'text' => $buf];
                    }

                    try {
                        foreach ($batches as $batch) {
                            $trans = new GoogleTranslate();
                            $trans->setSource('en');
                            $trans->setTarget($locale);

                            $translatedText = $trans->translate($batch['text']);
                            $lines = self::fixTranslate($translatedText);

                            foreach ($lines as $idx => $line) {
                                if (isset($batch['keys'][$idx])) {
                                    $translatedResults[$batch['keys'][$idx]] = self::capitalizeFirstLetters(trim($line));
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        $msg = $e->getMessage();
                        if (stripos($msg, '413') !== false || stripos(\get_class($e), 'LargeTextException') !== false) {
                            $maxEncodedLen = max(600, (int) floor($maxEncodedLen * 0.6)); // thu nhỏ mạnh tay
                            \Log::warning("Retry: 413/LargeText -> shrink maxEncodedLen to {$maxEncodedLen}");
                            usleep(300000); // 300ms
                            continue;
                        }
                        \Log::error("Retry failed while translating missing keys: " . $e->getMessage());
                        usleep(300000);
                    }

                    foreach ($tooLarge as $k => $v) {
                        $translatedResults[$k] = $v;
                    }

                    $missingResults = array_diff_key($missingResults, $translatedResults);

                    if (!empty($missingResults)) {
                        usleep(300000);
                    }
                }

                foreach ($missingResults as $key => $value) {
                    $translatedResults[$key] = $value;
                }

                $translatedData = $translatedResults;
            } catch (\Exception $e) {
                dd($e);
                \Log::error("Translation failed: " . $e->getMessage());
                $translatedData = $translations;
            }
        }

        // Save to lang JSON file
        self::saveMergedLangFile($translatedData, $locale);

        // Update or insert each item into the database
        foreach ($translatedData as $key => $value) {
            $item = LanguageItems::where('code', $locale)->where('name', $key)->first();
            if (!$item) {
                LanguageItems::create([
                    'code' => $locale,
                    'name' => $key,
                    'value' => $value,
                    'custom' => 0
                ]);
            }
        }
    }
    
    public static function fixTranslate($translatedText, $noLines = false)
    {
        // Clean common issues
        $translatedText = str_replace(["HH: MM"], "HH:MM", $translatedText);
        $translatedText = str_replace(["© "], "©", $translatedText);
        $translatedText = str_replace(["# "], "#", $translatedText);
        $translatedText = str_replace(["\r"], '', $translatedText);

        // Fix placeholders like %D, %S, %1$S → %d, %s, %1$s
        $translatedText = self::normalizePlaceholders($translatedText);
        if($noLines){
            return trim($translatedText);
        }else{
            return explode("\n", trim($translatedText));
        }
        
    }

    public static function normalizePlaceholders(string $text): string
    {
        return preg_replace_callback('/%(\d+\$)?[A-Z]/', function ($matches) {
            $number = $matches[1] ?? ''; // ví dụ: "1$" nếu có
            $letter = strtolower(substr($matches[0], -1)); // chuyển D → d
            return '%' . $number . $letter;
        }, $text);
    }

    public static function capitalizeFirstLetters($text)
    {
        $firstLetter = mb_substr($text, 0, 1, 'UTF-8');
        $rest = mb_substr($text, 1, null, 'UTF-8');

        return mb_strtoupper($firstLetter, 'UTF-8') . $rest;
    }

    public static function getSupportedLocales(): array
    {
        $langPath = resource_path('lang');
        $directories = scandir($langPath);
        $locales = [];

        foreach ($directories as $dir) {
            if ($dir === '.' || $dir === '..') continue;

            if (is_dir($langPath . '/' . $dir)) {
                $locales[] = $dir;
            }

            if (is_file($langPath . '/' . $dir) && pathinfo($dir, PATHINFO_EXTENSION) === 'json') {
                $locales[] = pathinfo($dir, PATHINFO_FILENAME);
            }
        }

        return array_unique($locales);
    }

    /**
     * Export language translations and metadata as an array.
     *
     * @param string $locale The language code (e.g. 'en', 'vi').
     * @return array Returns metadata and translations.
     */
    public static function export(string $locale): array
    {
        $items = LanguageItems::where('code', $locale)->pluck('value', 'name')->toArray();

        return [
            'meta' => Languages::where('code', $locale)->first(),
            'translations' => $items
        ];
    }

    /**
     * Import language translations and metadata from a JSON file.
     *
     * @param string $filePath Full path to the JSON file.
     * @return bool Returns true on success, throws exception on failure.
     * @throws \Exception
     */
    public static function import(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Language file not found.");
        }

        $json = json_decode(file_get_contents($filePath), true);

        if (!isset($json['meta']) || !isset($json['translations'])) {
            throw new \Exception("Invalid language file structure.");
        }

        $meta = $json['meta'];
        $translations = $json['translations'];

        // Validate essential meta fields
        $requiredFields = ['code', 'name', 'dir', 'icon'];
        foreach ($requiredFields as $field) {
            if (empty($meta[$field])) {
                throw new \Exception("Missing or empty required meta field: {$field}");
            }
        }

        // Insert or update metadata
        Languages::updateOrInsert(
            ['code' => $meta['code']],
            [
                'id_secure'   => $meta['id_secure'] ?? rand_string(),
                'name'        => $meta['name'],
                'code'        => $meta['code'],
                'icon'        => $meta['icon'],
                'dir'         => in_array($meta['dir'], ['ltr', 'rtl']) ? $meta['dir'] : 'ltr',
                'is_default'  => (int)($meta['is_default'] ?? 0),
                'auto_translate' => $meta['auto_translate'] ?? null,
                'status'      => (int)($meta['status'] ?? 1),
                'changed'     => time(),
                'created'     => $meta['created'] ?? time(),
            ]
        );

        // Remove old translations for this locale
        LanguageItems::where('code', $meta['code'])->delete();

        // Insert new translations
        foreach ($translations as $key => $value) {
            LanguageItems::create([
                'code' => $meta['code'],
                'name' => $key,
                'value' => $value,
                'custom' => 0
            ]);
        }

        return true;
    }

}
