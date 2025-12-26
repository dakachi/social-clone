<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeTailwindTheme extends Command
{
    protected $signature = 'theme:make:tailwind {name} {--with-preline} {--with-daisyui}';
    protected $description = 'Create a new Tailwind theme with optional Preline or DaisyUI support';

    public function handle()
    {
        $name = $this->argument('name');
        $withPreline = $this->option('with-preline');
        $withDaisyui = $this->option('with-daisyui');

        $themeParts = explode('/', $name);

        if (count($themeParts) !== 2) {
            $this->error('Theme name must follow format: type/name (e.g., guest/landing)');
            return;
        }

        [$type, $theme] = $themeParts;
        $themePath = resource_path("themes/{$type}/{$theme}");

        // Create base directories
        File::ensureDirectoryExists("{$themePath}/assets/js");
        File::ensureDirectoryExists("{$themePath}/assets/css");
        File::ensureDirectoryExists("{$themePath}/public/js");
        File::ensureDirectoryExists("{$themePath}/public/css");
        File::ensureDirectoryExists("{$themePath}/resources/views/layouts");

        // Add .gitkeep to empty folders
        collect([
            "{$themePath}/public/js/.gitkeep",
            "{$themePath}/public/css/.gitkeep",
            "{$themePath}/assets/js/.gitkeep",
            "{$themePath}/assets/css/.gitkeep",
        ])->each(fn($path) => File::put($path, ''));

        // JS file
        $jsContent = "import '../css/app.css';\n";
        if ($withPreline) {
            $jsContent .= "import 'preline';\n\ndocument.addEventListener('DOMContentLoaded', () => {\n  window.HSStaticMethods?.autoInit();\n});\n";
        } else {
            $jsContent .= "\nconsole.log('App JS loaded');\n";
        }
        File::put("{$themePath}/assets/js/app.js", $jsContent);

        // CSS file
        $cssContent = "@import \"tailwindcss\";\n";
        if ($withPreline) {
            $cssContent .= "\n/* Preline variants */\n@import 'preline/variants.css';";
        }
        if ($withDaisyui) {
            $cssContent .= "\n\n@plugin \"daisyui\" {\n  themes: all;\n}";
        }
        File::put("{$themePath}/assets/css/app.css", $cssContent);

        // Blade layout
        File::put("{$themePath}/resources/views/layouts/app.blade.php", <<<BLADE
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ \$title ?? 'Untitled' }}</title>
    {!! theme_vite('{$type}/{$theme}') !!}
</head>
<body>
    @include('partials.header')  
    @yield('content')
    @include('partials.footer')
</body>
</html>
BLADE);

        File::put("{$themePath}/resources/views/layouts/auth.blade.php", <<<BLADE
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ \$title ?? 'Untitled' }}</title>
    {!! theme_vite('{$type}/{$theme}') !!}
</head>
<body>
    @yield('content')
</body>
</html>
BLADE);

        // composer.json
        File::put("{$themePath}/composer.json", json_encode([
            'name' => "$type/$theme",
            'description' => ucfirst($theme) . " theme for {$type}",
            'type' => 'laravel-theme',
            'extra' => [
                'laravel-theme' => [
                    'name' => "$type/$theme"
                ]
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Blade view structure
        $viewPath = "{$themePath}/resources/views";

        $views = [
            'auth' => [
                'activation.blade.php',
                'forgot_password.blade.php',
                'login.blade.php',
                'recovery_password.blade.php',
                'resend_activation.blade.php',
                'signup.blade.php',
            ],
            'pages' => [
                'about.blade.php',
                'blogs.blade.php',
                'blog_detail.blade.php',
                'contact.blade.php',
                'faqs.blade.php',
                'home.blade.php',
                'page_not_found.blade.php',
                'pricing.blade.php',
                'privacy_policy.blade.php',
                'terms_of_service.blade.php',
            ],
            'partials' => [
                'footer.blade.php',
                'header.blade.php',
                'pricing-comparison.blade.php',
                'pricing.blade.php',
            ],
        ];

        foreach ($views as $folder => $files) {
            $folderPath = "{$viewPath}/{$folder}";
            File::ensureDirectoryExists($folderPath);

            foreach ($files as $file) {
                $filePath = "{$folderPath}/{$file}";
                File::put($filePath, "<!-- {$file} -->\n");
            }
        }

        $this->info("🎉 Tailwind theme '{$type}/{$theme}' created" . 
            ($withPreline ? ' with Preline' : '') . 
            ($withDaisyui ? ' with DaisyUI' : '') . 
            ' successfully.');
    }
}
