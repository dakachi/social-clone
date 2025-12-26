<?php

namespace Modules\AdminPlans\Listeners;

use Modules\Payment\Events\PaymentSuccess;

class HandlePlan
{
    public function handle(PaymentSuccess $event): void
    {
        $data = $event->paymentData;
        if (isset($data['user_id']) && $data['amount'] > 0 && isset($data['payment_id'])) {
            \Plan::updatePlanForTeam($data['plan_id'], $data['user_id'], $data);
        }
    }
}