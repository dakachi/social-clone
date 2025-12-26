<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ThemeDevCommand extends Command
{
    protected $signature = 'theme:dev {theme}';
    protected $description = 'Run Vite Dev Server for a specific theme';

    public function handle()
    {
        $theme = $this->argument('theme');

        $this->info("Starting Vite Dev Server for theme: {$theme}");

        $process = new Process([
            'npm', 'run', 'dev',
            '--theme=' . $theme
        ], base_path());

        $process->setTimeout(null); // No timeout for dev server
        $process->setTty(Process::isTtySupported()); // Forward output nicely to terminal

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        return Command::SUCCESS;
    }
}
