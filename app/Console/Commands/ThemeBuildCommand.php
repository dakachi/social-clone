<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ThemeBuildCommand extends Command
{
    protected $signature = 'theme:build {theme}';
    protected $description = 'Build Vite assets for a specific theme';

    public function handle()
    {
        $theme = $this->argument('theme');

        $this->info("Building Vite assets for theme: {$theme}");

        $process = new Process([
            'npm', 'run', 'build',
            '--theme=' . $theme
        ], base_path());

        $process->setTimeout(null); // No timeout
        $process->setTty(Process::isTtySupported()); // Forward output nicely

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (!$process->isSuccessful()) {
            $this->error("Build failed!");
            return Command::FAILURE;
        }

        $this->info("Build completed successfully.");
        return Command::SUCCESS;
    }
}
