<?php

namespace Modules\AppAffiliate\Listeners;

use Modules\Payment\Events\PaymentSuccess;
use Modules\AppAffiliate\Services\AffiliateService;

class HandleAffiliateCommission
{
    public function handle(PaymentSuccess $event): void
    {
        $data = $event->paymentData;

        if (isset($data['user_id']) && $data['amount'] > 0 && isset($data['payment_id'])) {
            \Log::info('Affiliate commission triggered', $data);
            \Affiliate::applyCommission($data['user_id'], $data['amount'], $data['payment_id']);
        }
    }
}