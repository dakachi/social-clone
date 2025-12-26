<?php

namespace Modules\Payment\Interfaces;

interface PaymentInterface
{
    public function pay(array $data);
    public function refund(array $data);
}
