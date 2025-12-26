<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class ThemeAllBuildCommand extends Command
{
    protected $signature = 'theme:all:build';
    protected $description = 'Build Vite assets for all available themes';

    public function handle()
    {
        $themePath = base_path('resources/themes');

        if (!File::exists($themePath)) {
            $this->error('Themes directory does not exist.');
            return Command::FAILURE;
        }

        $themes = collect(File::directories($themePath))
            ->flatMap(function ($section) {
                return File::directories($section);
            })
            ->map(function ($themeDir) use ($themePath) {
                return str_replace($themePath . DIRECTORY_SEPARATOR, '', $themeDir);
            })
            ->values()
            ->toArray();

        if (empty($themes)) {
            $this->warn('No themes found to build.');
            return Command::SUCCESS;
        }

        foreach ($themes as $theme) {
            $this->info("Building theme: {$theme}");

            $process = new Process([
                'npm', 'run', 'build',
                '--theme=' . $theme
            ], base_path());

            $process->setTimeout(null);
            $process->setTty(Process::isTtySupported());

            $process->run(function ($type, $buffer) {
                echo $buffer;
            });

            if (!$process->isSuccessful()) {
                $this->error("Failed to build theme: {$theme}");
                return Command::FAILURE;
            }
        }

        $this->info('✅ All themes built successfully.');
        return Command::SUCCESS;
    }
}
