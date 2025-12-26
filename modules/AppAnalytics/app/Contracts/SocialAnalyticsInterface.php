<?php
namespace Modules\AppAnalytics\Contracts;

interface SocialAnalyticsInterface
{
    public function getName(): string;
    public function getAnalyticsData(int $teamId): array;
}
