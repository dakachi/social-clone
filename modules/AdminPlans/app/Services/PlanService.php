<?php

namespace Modules\AdminPlans\Services;

use Modules\AdminPlans\Models\Plans;
use Modules\AdminUsers\Models\Teams;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\AdminPaymentSubscriptions\Models\PaymentSubscription;

class PlanService
{
    /** @var array */
    protected array $planItems = [];

    public function getTypes(): array
    {
        return [
            1 => __("Monthly"),
            2 => __("Yearly"),
            3 => __("Lifetime"),
        ];
    }

    public function registerPlanItem($item, int $priority = 100, callable $visible = null): void
    {
        $this->planItems[] = [
            'item'     => $item,
            'priority' => $priority,
            'visible'  => $visible,
        ];
    }

    public function getPlanItems(): array
    {
        $items = $this->planItems;
        usort($items, fn($a, $b) => $a['priority'] <=> $b['priority']);
        return $items;
    }

    public function addPermissions($module_name, $plan_permission)
    {
        $module = \Module::find($module_name);
        $menu = $module->get('menu');
        if ($menu) {
            $plan_permission = array_merge([
                'uri'         => $menu['uri'],
                'icon'        => $menu['icon'],
                'color'       => $menu['color'],
                'name'        => $menu['name'],
                'view'        => "permissions",
                'id'          => $module->getName(),
                'key'         => $module->getLowerName(),
                'module_name' => $menu['name'],
            ], $plan_permission);

            $plan_permissions = app()->bound('plan_permissions') ? app('plan_permissions') : [];
            $plan_permissions[] = $plan_permission;
            usort($plan_permissions, fn($a, $b) => $a['sort'] <=> $b['sort']);
            app()->instance('plan_permissions', $plan_permissions);
        }
    }

    public function syncPlanPermissions(array $currentPermissions): array
    {
        $newPermissions = [];
        foreach (\Module::allEnabled() as $module) {
            $moduleLower = $module->getLowerName();
            $modulePermissions = $module->get('permission');
            $menu = $module->get('menu');
            if (!isset($menu['role']) || $menu['role'] !== 'client') continue;

            $alreadyExists = collect($currentPermissions)->contains(fn($perm) =>
                is_array($perm) && ($perm['module_name'] ?? null) === ($menu['name'] ?? null)
            );
            if ($alreadyExists) continue;

            $base = [
                'uri'         => $menu['uri'] ?? '',
                'icon'        => $menu['icon'] ?? '',
                'color'       => $menu['color'] ?? '',
                'name'        => $menu['name'] ?? '',
                'id'          => $module->getName(),
                'key'         => $moduleLower,
                'module_name' => $menu['name'] ?? '',
            ];

            if ($modulePermissions === true) {
                $newPermissions[] = $base + ['key' => $moduleLower];
            } elseif (is_array($modulePermissions)) {
                foreach ($modulePermissions as $perm) {
                    if (!empty($perm['key'])) {
                        $newPermissions[] = $base + [
                            'key'   => $perm['key'],
                            'label' => $perm['label'] ?? strtoupper($perm['key']),
                        ];
                    }
                }
            }
        }
        return array_merge($currentPermissions, $newPermissions);
    }

    public static function updatePlanForTeam($planId, $userId, $paymentInfo = [])
    {
        $plan = Plans::find($planId);
        if (!$plan) return;
        $user = User::find($userId);
        if (!$user) return;
        $team = Teams::where("owner", $user->id)->first();
        if (!$team) return;

        $team->permissions = $plan->permissions;
        $team->save();

        $now = now()->timestamp;
        $curExpiration = $user->expiration_date;
        $remainDays = ($curExpiration > $now && $curExpiration != -1) ? ceil(($curExpiration - $now) / 86400) : 0;
        $oldPlan = Plans::find($user->plan_id);

        if ($plan->free_plan || $plan->type == 3) {
            $expiration_date = -1;
        } elseif ($user->plan_id == $planId && $plan->type != 3) {
            $mainDays = $plan->type == 1 ? 30 : 365;
            $expiration_date = ($curExpiration > $now ? $curExpiration : $now) + 86400 * $mainDays;
        } elseif ($oldPlan && $oldPlan->type == 2 && $plan->type == 1) {
            $oldUnit = $oldPlan->price / 365;
            $remainCredit = $remainDays * $oldUnit;
            $monthlyPrice = $plan->price;
            $months = $monthlyPrice > 0 ? floor($remainCredit / $monthlyPrice) : 0;
            $daysLeft = $monthlyPrice > 0 ? floor(($remainCredit % $monthlyPrice) / $oldUnit) : 0;
            $totalDays = $months * 30 + $daysLeft;
            if ($totalDays < $remainDays) $totalDays = $remainDays;
            $expiration_date = $now + 86400 * $totalDays;
        } elseif ($oldPlan && $oldPlan->type == 1 && $plan->type == 2) {
            $expiration_date = $now + 86400 * 365;
        } else {
            $mainDays = $plan->type == 1 ? 30 : 365;
            $expiration_date = ($curExpiration > $now ? $curExpiration : $now) + 86400 * $mainDays;
        }

        $user->plan_id = $planId;
        $user->expiration_date = $expiration_date;
        $user->save();

        self::resetQuota($team->id);

        $message = $paymentInfo
            ? __("Your payment was successful! \n\nThank you for upgrading your plan.\n\nPayment details:\n- Plan: :plan\n- Amount: :amount :currency\n- Transaction ID: :transaction\n\nYour subscription has been upgraded. Enjoy all the new features included in your :plan plan!\n\nIf you need any support, feel free to contact our team anytime.", [
                'plan' => $plan->name,
                'amount' => $paymentInfo['amount'],
                'currency' => $paymentInfo['currency'],
                'transaction' => $paymentInfo['transaction_id'],
            ])
            : __("Payment successful! Your plan has been upgraded.");

        \Notifier::sendAuto($userId, $message);
    }

    public static function assignPlanOnRegister($userId, $planIdSecure = null)
    {
        $user = User::find($userId);
        if (!$user) return ['status' => 'user_not_found', 'redirect' => route('register')];

        if ($planIdSecure) {
            $plan = Plans::where('id_secure', $planIdSecure)->first();
            if (!$plan) return self::assignDefaultPlan($user);
            if ($plan->free_plan) {
                self::updateUserPlan($user, $plan, 'free');
                return ['status' => 'free', 'redirect' => url_app("dashboard")];
            }
            if ($plan->trial_day) {
                $trialDays = (int) ($plan->trial_days ?? 7);
                self::updateUserPlan($user, $plan, 'trial', $trialDays);
                return [
                    'status' => 'trial',
                    'trial_ends_at' => $user->trial_ends_at,
                    'redirect' => url_app("dashboard")
                ];
            }
            if ($plan->is_paid) {
                return ['status' => 'require_payment', 'redirect' => route('payment.index', $plan->id_secure)];
            }
        }
        return self::assignDefaultPlan($user);
    }

    public static function assignDefaultPlan(User $user)
    {
        $plan = Plans::where('free_plan', 1)->orderByDesc('id')->first();
        if ($plan) {
            self::updateUserPlan($user, $plan, 'free');
            return ['status' => 'free', 'redirect' => url_app("dashboard")];
        }
        return ['status' => 'choose_plan', 'redirect' => url("pricing")];
    }

    public static function updateUserPlan(User $user, Plans $plan, $type = 'free', $trialDays = null)
    {
        DB::transaction(function () use ($user, $plan, $type, $trialDays) {
            $user->plan_id = $plan->id;
            $team = Teams::where("owner", $user->id)->first();
            if ($team) {
                $team->permissions = $plan->permissions;
                $team->save();

                if ($type == 'free') {
                    $user->expiration_date = -1;
                } elseif ($type === 'trial' && $trialDays) {
                    $user->expiration_date = Carbon::now()->addDays($trialDays)->timestamp;
                } elseif ($plan->type == 1) {
                    $user->expiration_date = Carbon::now()->addMonth()->timestamp;
                } elseif ($plan->type == 2) {
                    $user->expiration_date = Carbon::now()->addYear()->timestamp;
                } elseif ($plan->type == 3) {
                    $user->expiration_date = -1;
                } else {
                    $user->expiration_date = null;
                }
                $user->save();
                self::resetQuota($team->id);
            }
        });
    }

    public static function updateUserPlanByID($userId, $planId, $type = 'free', $trialDays = null)
    {
        $user = User::find($userId);
        $plan = Plans::find($planId);

        if (!$user || !$plan) return false;

        return self::updateUserPlan($user, $plan, $type, $trialDays);
    }

    public static function hasSubscription()
    {
        if (PaymentSubscription::userHasActive(auth()->id())) {
            return true;
        }

        return false;
    }

    public static function activateFreePlan($planIdSecure)
    {
        $user = auth()->user();
        $plan = Plans::where('id_secure', $planIdSecure)->where('free_plan', 1)->first();
        
        if (PaymentSubscription::userHasActive($user->id)) {
            return response()->json([
                'status' => 0,
                'message' => __("You already have an active subscription. Please cancel it before registering a new one.")
            ], 404);
        }
        
        if (!$plan) {
            return response()->json([
                'status' => 0,
                'message' => __("Invalid or unavailable free plan.")
            ], 404);
        }

        $user->plan_id = $plan->id;
        $user->expiration_date = -1;
        $user->save();

        $team = Teams::where('owner', $user->id)->first();
        if ($team) {
            $team->permissions = $plan->permissions;
            $team->save();
        }

        return response()->json([
            'status' => 1,
            'message' => __("Your free plan has been activated successfully!"),
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'type' => $plan->type,
            ],
        ]);
    }

    public static function resetQuota($teamId)
    {
        $startOfDay = Carbon::now()->startOfDay()->timestamp;
        $nextReset = $startOfDay + 30*24*60*60;
        \UserInfo::setDataTeam("quota_reset_at", $startOfDay, $teamId);
        \UserInfo::setDataTeam("next_quota_reset_at", $nextReset, $teamId);
    }

    public static function doResetQuota($teamId)
    {
        $now = Carbon::now();
        $quotaResetAt = \UserInfo::getDataTeam("quota_reset_at", $teamId);
        $nextQuotaResetAt = \UserInfo::getDataTeam("next_quota_reset_at", $teamId);

        if (
            empty($quotaResetAt) ||
            empty($nextQuotaResetAt) ||
            $now->timestamp > $nextQuotaResetAt
        ) {
            self::resetQuota($teamId);
        }
    }
}
