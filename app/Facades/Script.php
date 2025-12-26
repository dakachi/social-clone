<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Script extends Facade
{
    protected static array $variables = [];
    protected static array $defines = [];
    protected static array $rawScripts = [];

    protected static function getFacadeAccessor()
    { 
        return 'Script';
    }

    protected static function addVariable(string $key, mixed $value): void
    {
        self::$variables[$key] = $value;
    }

    protected static function define(string $name, mixed $value): void
    {
        self::$defines[$name] = $value;
    }


    protected static function meta(): string
    {
        return '<meta name="csrf-token" content="' . csrf_token() . '">';
    }

    protected static function globals(): string
    {
        $globals = collect(self::$defines)->map(function ($value, $name) {
            $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            return "<script>var {$name} = {$json};</script>";
        })->implode("\n\r");

        if(get_option("embed_code_status", 0)){
            $globals .= "\r".get_option("embed_code", "");
        }
        
        return $globals;
    }

    protected static function fromView(string $view, array $data = []): void
    {
        self::raw(view($view, $data)->render());
    }

    protected static function raw(string $js): void
    {
        if (stripos($js, '<script') !== false) {
            $cleaned = trim(strip_tags($js));
            if ($cleaned !== '') {
                self::$rawScripts[] = $js;
            }
        } else {
            self::$rawScripts[] = "<script>\n{$js}\n</script>";
        }
    }


    protected static function renderRaw(): string
    {
        return implode("\n", self::$rawScripts);
    }

    protected static function renderCss(): string
    {
        $assets = Core::loadModuleAssets()['css'] ?? [];

        return collect($assets)
            ->filter() // loại null/empty
            ->unique() // loại trùng lặp
            ->map(function ($css) {
                $version = file_exists(public_path($css))
                    ? filemtime(public_path($css))
                    : config('app.asset_version', time());

                return sprintf(
                    '<link rel="stylesheet" href="%s?v=%s">',
                    e($css),
                    $version
                );
            })
            ->implode("\n");
    }

    protected static function renderJs(bool $defer = true, bool $async = false): string
    {
        $assets = Core::loadModuleAssets()['js'] ?? [];

        return collect($assets)
            ->filter()
            ->unique()
            ->map(function ($js) use ($defer, $async) {
                $version = file_exists(public_path($js))
                    ? filemtime(public_path($js))
                    : config('app.asset_version', time());

                $attrs = [];
                if ($defer) $attrs[] = 'defer';
                if ($async) $attrs[] = 'async';

                return sprintf(
                    '<script src="%s?v=%s" %s></script>',
                    e($js),
                    $version,
                    implode(' ', $attrs)
                );
            })
            ->implode("\n");
    }
}
