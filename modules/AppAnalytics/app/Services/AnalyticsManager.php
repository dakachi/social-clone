<?php
namespace Modules\AppAnalytics\Services;

use Nwidart\Modules\Facades\Module;
use Modules\AppAnalytics\Contracts\SocialAnalyticsInterface;
use Modules\AppAnalytics\Models\SocialAnalytics;
use Modules\AppAnalytics\Models\SocialAnalyticsSnapshot;
use Modules\AppAnalytics\Models\SocialAnalyticsPost;
use Modules\AppAnalytics\Models\SocialAnalyticsPostInfo;
use Modules\AppAnalytics\Models\SocialAnalyticsLog;

class AnalyticsManager
{
    public function getAnalytics(): array
    {
        $analytics = [];

        foreach (\Nwidart\Modules\Facades\Module::allEnabled() as $module) {
            $moduleName = $module->getName();

            if (!str_starts_with($moduleName, 'AppAnalytics')) continue;

            $socialName = str_replace('AppAnalytics', '', $moduleName);
            $class = "Modules\\{$moduleName}\\Services\\{$socialName}Analytics";

            if (class_exists($class)) {
                try {
                    $instance = app($class);

                    if ($instance instanceof \Modules\AppAnalytics\Contracts\SocialAnalyticsInterface) {
                        $analytics[strtolower($instance->getName())] = $moduleName;
                    }
                } catch (\Exception $e) {
                    $analytics[strtolower($moduleName)] = $moduleName;
                }
            }
        }

        return $analytics;
    }

    public function getAvailableAnalytics(?int $teamId = null): array
    {
        $analytics = [];

        foreach (\Nwidart\Modules\Facades\Module::allEnabled() as $module) {
            $moduleName = $module->getName();

            if (!str_starts_with($moduleName, 'AppAnalytics')) continue;

            $socialName = str_replace('AppAnalytics', '', $moduleName);
            $class = "Modules\\{$moduleName}\\Services\\{$socialName}Analytics";

            if (class_exists($class)) {
                $instance = app($class);
                if( \Gate::allows("appanalytics.".strtolower($instance->getName())) ){
                    if ($instance instanceof \Modules\AppAnalytics\Contracts\SocialAnalyticsInterface) {
                        $analytics[$instance->getName()] = $instance->getAccounts($teamId);
                    }

                }
            }
        }

        return $analytics;
    }

    public function saveInsightsToDatabase(int $accountId, string $social, array $insights, string $modelClass = SocialAnalytics::class): void
    {
        $now = time();

        foreach ($insights as $metric => $dailyValues) {
            foreach ($dailyValues as $date => $value) {
                $finalValue = 0;
                $details = null;

                $hour = 0;
                if (preg_match('/\d{2}:\d{2}:\d{2}$/', $date)) {
                    $dt = \Carbon\Carbon::parse($date);
                    $hour = $dt->hour;
                    $dateOnly = $dt->format('Y-m-d');
                } else {
                    $dateOnly = $date;
                }

                if (is_array($value)) {
                    if (array_key_exists('value', $value)) {
                        $finalValue = $value['value'];
                        $details = json_encode(array_diff_key($value, ['value' => true]));
                    } else {
                        $details = json_encode($value);
                    }
                } elseif (is_numeric($value)) {
                    $finalValue = $value;
                }

                $modelClass::updateOrCreate(
                    [
                        'account_id'     => $accountId,
                        'social_network' => $social,
                        'date'           => $dateOnly,
                        'hour'           => $hour,        // thêm dòng này!
                        'metric'         => $metric,
                    ],
                    [
                        'value'   => $finalValue,
                        'details' => $details,
                        'created' => $now,
                    ]
                );
            }
        }
    }

    public function shouldFetchSocialAnalytics(int $accountId, string $social, string $type = 'page'): bool
    {
        $lastSync = SocialAnalyticsLog::query()
            ->where('account_id', $accountId)
            ->where('social_network', $social)
            ->where('type', $type)
            ->where('date', now()->toDateString())
            ->value('synced_at');

        return !($lastSync && $lastSync >= (time() - 3600));
    }

    public function markSynced(int $accountId, string $social, string $type = 'page'): void
    {
        SocialAnalyticsLog::updateOrCreate(
            [
                'account_id'     => $accountId,
                'social_network' => $social,
                'type'           => $type,
                'date'           => now()->toDateString(),
            ],
            [
                'synced_at' => time(),
            ]
        );
    }
}
