<?php

namespace Modules\AppProxies\Services;

use Illuminate\Support\Facades\Http;
use Modules\AppProxies\Models\ProxyModel;
use Modules\AppProxies\Models\ChannelProxy;
use Modules\AppChannels\Models\Accounts;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProxyService
{
    public function getAllAvailable(int $team_id = null)
    {
        return ProxyModel::query()
            ->where('status', 1)
            ->when($team_id, fn($q) => $q->where(function ($q2) use ($team_id) {
                $q2->where('team_id', $team_id)->orWhere('is_system', 1);
            }))
            ->get();
    }

    public static function getProxyById(int $proxyId): ?ProxyModel
    {
        return ProxyModel::where('status', 1)
            ->where(function ($q) use ($proxyId) {
                $q->where('id', $proxyId);
            })
            ->first();
    }

    public function create(array $data): ProxyModel
    {
        return ProxyModel::create($data);
    }

    public function update(int $id, array $data): ProxyModel
    {
        $proxy = ProxyModel::findOrFail($id);
        $proxy->update($data);
        return $proxy;
    }

    public function delete(int $id): bool
    {
        return ProxyModel::where('id', $id)->delete();
    }

    public function isWorking(ProxyModel $proxy): bool
    {
        try {
            $response = Http::timeout(10)->withOptions([
                'proxy'  => $proxy->toCurlProxy(),
                'verify' => false,
            ])->get('https://httpbin.org/ip');

            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function tryWithFallback(int $account_id, string $module, callable $callback)
    {
        $proxies = $this->getProxiesForAccount($account_id, $module);

        foreach ($proxies as $proxy) {
            try {
                $client = Http::timeout(10)->withOptions([
                    'proxy' => $proxy->toCurlProxy(),
                    'verify' => false,
                ]);

                $response = $callback($client);

                if ($response->successful()) return $response;
            } catch (\Throwable $e) {
                $proxy->update(['status' => 0]);
            }
        }

        throw new \Exception("All proxies failed for account #$account_id on module [$module].");
    }

    public function assignProxyToAccount(int $account_id, int $proxy_id, string $module, int $priority = 1)
    {
        return ChannelProxy::updateOrCreate([
            'account_id' => $account_id,
            'module'     => $module,
            'proxy_id'   => $proxy_id,
        ], [
            'priority'   => $priority,
        ]);
    }

    public function removeProxyFromAccount(int $account_id, string $module)
    {
        return ChannelProxy::where('account_id', $account_id)
            ->where('module', $module)
            ->delete();
    }

    public function getProxiesForAccount(int $account_id, string $module)
    {
        return ChannelProxy::with('proxy')
            ->where('account_id', $account_id)
            ->where('module', $module)
            ->join('proxies', 'channel_proxies.proxy_id', '=', 'proxies.id')
            ->where('proxies.status', 1)
            ->orderBy('priority')
            ->get()
            ->pluck('proxy');
    }

    public function canUse(ProxyModel $proxy, int $team_id): bool
    {
        return $proxy->is_system == 1 || $proxy->team_id == $team_id;
    }

    public function extractIpFromProxy(string $proxy): ?string
    {
        $proxyParse = explode('@', $proxy);
        $ipPort = count($proxyParse) > 1 ? explode(':', $proxyParse[1]) : explode(':', $proxyParse[0]);
        return count($ipPort) === 2 ? $ipPort[0] : null;
    }

    public function getProxyLocation(string $proxy): ?string
    {
        $ip = $this->extractIpFromProxy($proxy);

        if (!$ip) return null;

        try {
            $response = Http::timeout(10)->get("http://ip-api.com/json/{$ip}");
            $data = $response->json();

            return ($data['status'] ?? null) === 'success' ? ($data['countryCode'] ?? null) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function assignProxy(string $social_network, int $login_type = 2): ?ProxyModel
    {
        $team_id = request()->team_id;
        $isFreeUser = true;

        $teamProxies = ProxyModel::withCount(['accounts as usage_count' => function ($query) use ($social_network, $login_type) {
                $query->where('social_network', $social_network)
                      ->where('login_type', $login_type)
                      ->where('status', 1);
            }])
            ->where([
                ['status', 1],
                ['is_system', 0],
                ['team_id', $team_id]
            ])
            ->get()
            ->filter(fn($proxy) => $proxy->limit == -1 || $proxy->usage_count < $proxy->limit);

        if ($teamProxies->isNotEmpty()) {
            return $teamProxies->sortBy('usage_count')->first();
        }

        $systemProxies = ProxyModel::withCount(['accounts as usage_count' => function ($query) use ($social_network, $login_type) {
                $query->where('social_network', $social_network)
                      ->where('login_type', $login_type)
                      ->where('status', 1);
            }])
            ->where([
                ['status', 1],
                ['is_system', 1],
            ])
            ->when($isFreeUser, fn($q) => $q->where('is_free', 1))
            ->get()
            ->filter(fn($proxy) => $proxy->limit == -1 || $proxy->usage_count < $proxy->limit);

        return $systemProxies->sortBy('usage_count')->first();
    }

    public static function autoAssign(int $teamId, bool $isFreeUser = false): ?int
    {
        $query = ProxyModel::where('status', 1)
            ->where(function ($q) use ($teamId) {
                $q->where('team_id', $teamId)
                  ->orWhere('is_system', 1);
            });

        if ($isFreeUser) {
            $query->where('is_free', 1);
        }

        $query->where(function ($q) {
            $q->where('limit', -1)
              ->orWhereRaw('(SELECT COUNT(*) FROM accounts WHERE accounts.proxy = proxies.id) < proxies.limit');
        });

        $proxy = $query
            ->orderByRaw('team_id = ? DESC', [$teamId])
            ->orderBy('is_system', 'asc')
            ->orderByRaw('RAND()')
            ->first();

        return $proxy?->id;
    }

    public function isAssignable(ProxyModel $proxy): bool
    {
        if ($proxy->limit == -1) {
            return true; // unlimited
        }

        return $proxy->accounts()->count() < $proxy->limit;
    }

    public function isAssignableByNetwork(ProxyModel $proxy, string $social_network): bool
    {
        if ($proxy->limit == -1) {
            return true;
        }

        $count = $proxy->accounts()
            ->where('social_network', $social_network)
            ->where('status', 1)
            ->count();

        return $count < $proxy->limit;
    }

    public function resolveProxyString($proxy_id): string|false
    {
        if (is_numeric($proxy_id)) {
            $proxy = ProxyModel::where('id', $proxy_id)
                ->where('status', 1)
                ->first();

            return $proxy?->proxy ?? false;
        }

        return $proxy_id ?: false;
    }

    public function validateProxyFormat(string $proxy): bool
    {
        return preg_match('/^([^\s:@]+:[^\s:@]+@)?([a-zA-Z0-9\-\.]+|\d{1,3}(?:\.\d{1,3}){3}):\d{2,5}$/', $proxy) === 1;
    }

    public function importFromCsv(string $filePath, int $team_id = null, bool $is_system = false): array
    {
        $results = [
            'imported' => 0,
            'skipped' => 0,
            'errors'  => [],
        ];

        if (!file_exists($filePath)) {
            throw new \Exception("CSV file not found: $filePath");
        }

        $rows = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($rows as $index => $row) {
            $proxy = trim($row);
            if (!$proxy || strlen($proxy) < 7) {
                $results['skipped']++;
                continue;
            }

            // Validate: must contain at least 1 :
            if (!Str::contains($proxy, ':')) {
                $results['skipped']++;
                continue;
            }

            // Check if already exists
            if (ProxyModel::where('proxy', $proxy)->exists()) {
                $results['skipped']++;
                continue;
            }

            try {
                ProxyModel::create([
                    'proxy'     => $proxy,
                    'status'    => 1,
                    'team_id'   => $team_id,
                    'is_system' => $is_system ? 1 : 0,
                    'id_secure' => Str::uuid(),
                ]);

                $results['imported']++;
            } catch (\Throwable $e) {
                $results['errors'][] = "Line {$index}: {$e->getMessage()}";
            }
        }

        return $results;
    }
}
