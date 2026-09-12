<?php

declare(strict_types=1);

namespace StoneSamin\Core;

use StoneSamin\Core\Contracts\ModuleInterface;
use StoneSamin\Core\Modules\Catalog\ProductTaxonomyModule;
use StoneSamin\Core\Modules\Catalog\ProductTaxonomyThumbnailModule;

defined('ABSPATH') || exit;

final class Plugin
{
    private bool $booted = false;

    /**
     * @var array<int, ModuleInterface>
     */
    private array $registeredModules = [];

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        $this->registerModules();

        do_action('ss_stonesamin_core_loaded', $this);
    }

    private function registerModules(): void
    {
        foreach ($this->getModules() as $module) {
            $this->registerModule($module);
        }
    }

    private function registerModule(ModuleInterface $module): void
    {
        $module->register();

        $this->registeredModules[] = $module;
    }

    /**
     * @return array<int, ModuleInterface>
     */
    private function getModules(): array
    {
        return [
            new ProductTaxonomyModule(),
            new ProductTaxonomyThumbnailModule(),
        ];
    }
}

