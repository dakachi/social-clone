<?php

namespace Modules\AdminPlans\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\AdminPlans\Models\Plans;

class CheckUserPlan
{
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $plan = $user->plan_id ? \Modules\AdminPlans\Models\Plans::find($user->plan_id) : null;

            if (!$plan) {
                $freePlan = \Modules\AdminPlans\Models\Plans::where('free_plan', 1)->where('status', 1)->first();

                $action = optional(\Route::current())->getActionName();
                preg_match('/Modules\\\\([^\\\\]+)/', $action, $matches);
                $currentModule = $matches[1] ?? null;

                $excludedModules = [
                    'Guest',
                    'Auth',
                    'Payment',
                    'AppProfile',
                ];

                if (in_array($currentModule, $excludedModules) || $user->role != 1) {
                    return $next($request);
                }

                if ($freePlan) {
                    \Plan::activateFreePlan($freePlan->id_secure);
                    return redirect()->route('app.profile', 'plan')->with('warning', __('You have been switched to the free plan.'));
                } else {
                    return redirect()->route('app.profile', 'plan')->with('warning', __('Your subscription plan is invalid or has been deleted. Please select a new plan.'));
                }
            }
        }
        return $next($request);
    }
}
