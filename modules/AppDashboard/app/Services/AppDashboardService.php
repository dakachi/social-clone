<?php

namespace Modules\AppDashboard\Services;

class AppDashboardService
{
    /**
     * Holds all registered admin dashboard items.
     *
     * @var array
     */
    protected $dashboardItems = [];

    /**
     * Register a new admin dashboard item.
     *
     * @param string|callable $item HTML string or a closure returning HTML.
     * @param int $priority Display priority (lower = earlier).
     * @return void
     */
    public function registerDashboardItem($item, int $priority = 100, callable $visible = null)
    {
        $this->dashboardItems[] = [
            'item'     => $item,
            'priority' => $priority,
            'visible'  => $visible,
        ];
    }

    /**
     * Get all admin dashboard items for a specific position.
     *
     * @param string|null $position If specified, returns only items for that position.
     * @return array Sorted list of admin dashboard items.
     */
    public function getDashboardItems(): array
    {
        $items = $this->dashboardItems;

        // Sort items by priority
        usort($items, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $items;
    }
}