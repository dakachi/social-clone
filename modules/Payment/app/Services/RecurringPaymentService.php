<?php

namespace Modules\Payment\Services;

use Exception;
use Modules\Payment\Interfaces\RecurringPaymentInterface;
use Modules\AdminPaymentSubscriptions\Models\PaymentSubscription;
use Modules\AdminPaymentHistory\Models\PaymentHistory;
use Illuminate\Support\Facades\Cache;

class RecurringPaymentService
{
    protected $gateway = null;
    protected $gatewayName = null;

    public function __construct(?string $gatewayName = null)
    {
        if ($gatewayName) {
            $this->setGateway($gatewayName);
        }
    }

    public function setGateway(string $gatewayName): static
    {
        $gatewayClass = "Modules\\{$gatewayName}\\Services\\PaymentService";
        if (!class_exists($gatewayClass)) {
            throw new Exception("Payment gateway [{$gatewayName}] is not supported.");
        }
        $this->gatewayName = $gatewayName;
        $this->gateway = app($gatewayClass);
        return $this;
    }

    protected function ensureGateway()
    {
        if (!$this->gateway) {
            throw new Exception("Recurring payment gateway not initialized. Call setGateway() first.");
        }
    }

    public function createSubscription(array $params)
    {
        $this->ensureGateway();
        $result = $this->gateway->createSubscription($params);
        return $result;
    }

    public function saveSubscription(array $data)
    {
        $uid = $data['user_id'] ?? $data['uid'] ?? null;
        $planId = $data['plan_id'] ?? null;

        if (empty($data['subscription_id']) || empty($uid) || empty($planId)) {
            \Log::warning('RecurringPaymentService@saveSubscription missing required fields', $data);
            return false;
        }

        $payload = [
            'id_secure'    => rand_string(),
            'uid'          => $uid,
            'plan_id'      => $planId,
            'service'      => $this->gatewayName,
            'type'         => $data['type'] ?? null,
            'source'       => $data['source'] ?? 'unknown',
            'customer_id'  => $data['customer_id'] ?? null,
            'amount'       => $data['amount'] ?? 0,
            'currency'     => strtoupper($data['currency'] ?? null),
            'status'       => $data['status'] ?? 1,
            'changed'      => time(),
            'created'      => time()
        ];

        $saved = PaymentSubscription::updateOrCreate(
            ['subscription_id' => $data['subscription_id']],
            $payload
        );

        return $saved;
    }

    public function cancelSubscription()
    {
        $userId = auth()->id();

        $subscription = PaymentSubscription::where('uid', $userId)
            ->where('status', 1)
            ->first();

        if (!$subscription) {
            throw new \Exception(__("No active subscription found for the current user."));
        }
        
        if (empty($subscription->service)) {
            throw new \Exception(__("This subscription does not have a valid payment gateway configured."));
        }

        $this->setGateway($subscription->service);

        $result = $this->gateway->cancelSubscription($subscription->subscription_id);

        if ($result && ($result['status'] ?? true)) {
            $subscription->status = 2;
            $subscription->changed = time();
            $subscription->save();
        }

        return $result;
    }

    public function renewSubscription(string $subscriptionId)
    {
        $this->ensureGateway();
        return $this->gateway->renewSubscription($subscriptionId);
    }

    public function syncSubscription(string $subscriptionId)
    {
        $this->ensureGateway();
        return $this->gateway->syncSubscription($subscriptionId);
    }

    public function getSubscriptionInfo(string $subscriptionId)
    {
        $this->ensureGateway();
        return $this->gateway->getSubscriptionInfo($subscriptionId);
    }

    public function handleWebhook($request)
    {
        $this->ensureGateway();
        return $this->gateway->handleWebhook($request);
    }

    public static function updateSubscriptionStatus(string $subscriptionId, $transactionId = null, int $status = 0, int $updatePlan = 0)
    {
        if (!$subscriptionId) {
            return false;
        }

        $updateData = [
            'status'  => $status,
            'changed' => time(),
        ];

        $subscription = PaymentSubscription::where('subscription_id', $subscriptionId)->first();

        if ($subscription) {
            if ($updatePlan && $transactionId) {
                PaymentHistory::firstOrCreate(
                    ['transaction_id' => $transactionId],
                    [
                        'id_secure' => rand_string(),
                        'uid'       => $subscription->uid,
                        'plan_id'   => $subscription->plan_id,
                        'from'      => isset($subscription->source) ? ucfirst($subscription->source) : 'recurring',
                        'currency'  => $subscription->currency ?? 'USD',
                        'by'        => $subscription->type ?? 'recurring',
                        'amount'    => $subscription->amount ?? 0,
                        'status'    => 1,
                        'changed'   => time(),
                        'created'   => time(),
                    ]
                );

                \Plan::updatePlanForTeam($subscription->plan_id, $subscription->uid);
            }
        }

        return PaymentSubscription::where('subscription_id', $subscriptionId)
            ->update($updateData);
    }
}