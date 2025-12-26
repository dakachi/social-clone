<?php

namespace App\Services;

class HeaderManager
{
    /**
     * Holds all registered header items.
     *
     * @var array
     */
    protected $headerItems = [];

    /**
     * Register a new header item.
     *
     * @param string|callable $item HTML string or a closure returning HTML.
     * @param string $position Position to display the item: 'left', 'center', 'right'.
     * @param int $priority Display priority (lower = earlier).
     * @return void
     */
    public function registerHeaderItem($item, string $position = 'right', int $priority = 100, callable $visible = null)
    {
        $this->headerItems[] = [
            'item'     => $item,
            'position' => $position,
            'priority' => $priority,
            'visible'  => $visible,
        ];
    }

    /**
     * Get all header items for a specific position.
     *
     * @param string|null $position If specified, returns only items for that position.
     * @return array Sorted list of header items.
     */
    public function getHeaderItems(string $position = null): array
    {
        $items = $this->headerItems;

        // Filter by position if specified
        if ($position) {
            $items = array_filter($items, function ($headerItem) use ($position) {
                return $headerItem['position'] === $position;
            });
        }

        // Sort items by priority
        usort($items, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $items;
    }
}