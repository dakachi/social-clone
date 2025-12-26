<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ExportTheme extends Command
{
    protected $signature = 'theme:export {theme}';
    protected $description = 'Export a theme with views + built assets into a zip file';

    public function handle()
    {
        $theme = $this->argument('theme');
        $themePath = base_path("resources/themes/{$theme}");
        $buildPath = public_path("build/{$theme}");
        $exportPath = storage_path("app/exports/{$theme}");
        $zipPath = storage_path("app/exports/{$theme}.zip");

        if (!is_dir($themePath)) {
            $this->error("❌ Theme folder not found: {$themePath}");
            return;
        }

        if (!is_dir($buildPath)) {
            $this->error("❌ Build folder not found: {$buildPath}");
            return;
        }

        File::deleteDirectory($exportPath);
        File::ensureDirectoryExists("{$exportPath}/resources/themes/{$theme}");
        File::ensureDirectoryExists("{$exportPath}/public/build/{$theme}");

        File::copyDirectory($themePath, "{$exportPath}/resources/themes/{$theme}");
        File::copyDirectory($buildPath, "{$exportPath}/public/build/{$theme}");

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($exportPath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = str_replace("{$exportPath}/", '', $filePath);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
            $this->info("✅ Exported to: storage/app/exports/{$theme}.zip");
        } else {
            $this->error("❌ Failed to create zip");
        }
    }
}
