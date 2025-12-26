<?php
namespace Modules\AppMediaSearch\Services\Providers;

interface ProviderInterface
{
    public function search(string $query, string $type = 'photo'): array;
}