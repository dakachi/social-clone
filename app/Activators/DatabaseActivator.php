<?php

namespace App\Activators;

use Illuminate\Container\Container;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Module;
use Illuminate\Support\Facades\File;

class DatabaseActivator implements ActivatorInterface
{
    private $config;
    private $modulesStatuses;

    public function __construct(Container $app)
    {
        $this->config = $app['config'];
        $this->modulesStatuses = $this->readJson();
    }

    public function reset(): void
    {
        $this->modulesStatuses = [];
        $this->writeJson();
    }

    public function enable(Module $module): void
    {
        $this->setActiveByName($module->getName(), true);
    }

    public function disable(Module $module): void
    {
        $this->setActiveByName($module->getName(), false);
    }

    public function hasStatus(Module|string $module, bool $status): bool
    {
        $name = $module instanceof Module ? $module->getName() : $module;
        return isset($this->modulesStatuses[$name]) ? $this->modulesStatuses[$name] === $status : !$status;
    }

    public function setActive(Module $module, bool $active): void
    {
        $this->setActiveByName($module->getName(), $active);
    }

    public function setActiveByName(string $name, bool $status): void
    {
        $this->modulesStatuses[$name] = $status;
        $this->writeJson();
    }

    public function delete(Module $module): void
    {
        unset($this->modulesStatuses[$module->getName()]);
        $this->writeJson();
    }

    private function writeJson(): void
    {
        $filePath = base_path('resources/modules_statuses.json');
        File::put($filePath, json_encode($this->modulesStatuses, JSON_PRETTY_PRINT));
    }

    private function readJson(): array
    {
        $filePath = base_path('resources/modules_statuses.json');
        if (!File::exists($filePath)) {
            return [];
        }
        return json_decode(File::get($filePath), true);
    }
}