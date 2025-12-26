<?php

namespace Modules\Payment\Interfaces;

interface RecurringPaymentInterface
{
    public function createSubscription(array $params);
    public function cancelSubscription(string $subscriptionId);
    public function renewSubscription(string $subscriptionId);
    public function syncSubscription(string $subscriptionId);
    public function handleWebhook(array $payload);
    public function getSubscriptionInfo(string $subscriptionId);
}
