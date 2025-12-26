<?php

namespace Modules\AdminPlans\Listeners;

use Modules\Auth\Events\AuthEvent;

class HandleAccessPlan
{
    public function handle(AuthEvent $event): void
    {
        $type = $event->type;
        $user_id = $event->data;
        $planIdSecure = session('start_plan');
        
        if($type == "signup"){
            \Plan::assignPlanOnRegister($user_id, $planIdSecure);
        }
        
    }
}