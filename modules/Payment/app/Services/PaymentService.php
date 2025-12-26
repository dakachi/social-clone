<?php

namespace Modules\Payment\Services;

use Modules\AdminPaymentHistory\Models\PaymentHistory;
use Modules\Payment\Interfaces\PaymentInterface;
use Modules\Payment\Events\PaymentSuccess;
use Modules\AdminPlans\Models\Plans;
use App\Models\User;
use Exception;
use DB;

class PaymentService
{
    protected $gateway;

    /**
     * Calculate discount amount and total.
     *
     * @param float $subtotal The original subtotal.
     * @param float $discount Discount value (either percentage if type 1 or fixed amount if type 2).
     * @param int   $discountType Numeric value: 1 for percent, 2 for fixed price.
     * @param bool  $formatted If true, returns formatted numbers (as strings with 2 decimals), otherwise returns raw numbers.
     *
     * @return array {
     *      @type float|string $subtotal Original subtotal.
     *      @type float|string $discount Calculated discount amount.
     *      @type float|string $total    Total after discount.
     * }
     */
    public function calculatePayment($subtotal, $plan_id, $formatted = false)
    {
        $discount = 0;
        $discountType = 0;

        $coupon = $this->appliedCoupon();
        if ($coupon) {
            $coupon_plans = json_decode($coupon->plans);

            if (
                $coupon->start_date < time() &&
                $coupon->end_date > time() &&
                $coupon->usage_limit > $coupon->usage_count &&
                in_array($plan_id, $coupon_plans)
            ) {
                $discountType = $coupon->type;
                $discount = $coupon->discount;
            }
        }

        // Tính discount theo loại
        if ($discountType == 1) {
            // % discount
            $discountAmount = ($subtotal * $discount) / 100;
        } else {
            // fixed discount
            $discountAmount = $discount;
        }

        // Tổng sau khi giảm
        $total = $subtotal - $discountAmount;

        if ($total <= 0) {
            $total = 0;
        }

        if ($formatted) {
            return [
                'subtotal' => \Core::currency($subtotal),
                'discount' => \Core::currency($discountAmount * -1),
                'total'    => \Core::currency($total),
            ];
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discountAmount * -1,
            'total'    => $total,
        ];
    }

    public function updatedCoupon()
    {
        if(session("coupon")){
            $coupon_id = session("coupon");
            $coupon = DB::table("coupons")->where("id", $coupon_id)->first();
            session()->forget('coupon');

            if(!empty($coupon)){
                if($coupon->usage_count < $coupon->usage_limit){
                    $usage_count = $coupon->usage_count + 1;
                }elseif($coupon->usage_limit < 0){
                    $usage_count = $coupon->usage_count + 1;
                }else{
                    $usage_count = $coupon->usage_limit;
                }

                DB::table("coupons")->where("id", $coupon_id)->update([
                    "usage_count" => $usage_count
                ]);

                return true;
            }
        }

        return false;
    }

    public function appliedCoupon()
    {
        $coupon = [];
        if(session("coupon")){
            $coupon_id = session("coupon");
            $coupon = DB::table("coupons")->where("id", $coupon_id)->first();
        }

        return $coupon;
    }

    public function getPaymentsByType($type = null)
    {
        $payments = app()->bound('payments') ? app('payments') : [];

        if ($type === null) {
            $result = [];
            foreach ($payments as $item) {
                if (isset($item['type'])) {
                    $t = $item['type'];
                    $result[$t][] = $item;
                }
            }
            // Đảm bảo luôn trả về đầy đủ type có trong danh sách (kể cả khi không có payment nào type=1,2)
            foreach ($result as &$list) {
                usort($list, fn($a, $b) => $a['type'] <=> $b['type']);
            }
            return $result;
        }

        $filtered = array_filter($payments, function($item) use ($type) {
            return isset($item['type']) && $item['type'] == $type;
        });

        usort($filtered, fn($a, $b) => $a['type'] <=> $b['type']);

        return array_values($filtered);
    }

    public function addPaymentGateway($module_name, $payment, $status = 1)
    {
        if ($status == 0) {
            return; 
        }

        $module = \Module::find($module_name);
        $menu = $module->get('menu');

        if ($menu) {
            $payment['logo'] = \Module::asset($module->getName().':'.$payment['logo_path']);
            $payment = array_merge($payment, [
                'uri'         => $menu['uri'],
                'icon'        => $menu['icon'],
                'color'       => $menu['color'],
                'name'        => $menu['name'],
                'id'          => $module->getName(),
                'key'         => $module->getLowerName(),
                'module_name' => $menu['name'],
            ]);

            $payments = app()->bound('payments') ? app('payments') : [];
            $payments[] = $payment;
            app()->instance('payments', $payments);
        }
    }

    /**
     * Set the payment gateway dynamically.
     *
     * @param string $gatewayName
     * @throws Exception
     */
    public function setGateway(string $gatewayName)
    {
        $gatewayClass = "Modules\\{$gatewayName}\\Services\\PaymentService";
        if (!class_exists($gatewayClass)) {
            throw new Exception("Payment gateway {$gatewayName} is not supported.");
        }
        $this->gateway = app($gatewayClass);
    }

    /**
     * Standardize error response.
     *
     * @param string $message
     * @param int $code
     * @return array
     */
    private function apiError($message, $code = 400)
    {
        throw new Exception("$code - $message");
    }

    /**
     * Process a payment request.
     *
     * @param array $data
     * @return array
     */
    public function pay(array $data)
    {
        try {
            if (!$this->gateway instanceof PaymentInterface) {
                throw new Exception( __("Invalid payment gateway.") );
            }

            session([
                "checkout_plan_id" => $data['plan_id'],
                "checkout_plan_type" => $data['plan_type']
            ]);

            $payment_link = $this->gateway->pay($data);

            return [
                "status"       => 1,
                "payment_link" => $payment_link
            ];
        } catch (Exception $e) {
            return $this->apiError($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * Process the successful payment callback.
     * Triggers a PaymentSuccess event.
     *
     * @return array
     */
    public function success()
    {

        try {
            if (!$this->gateway instanceof PaymentInterface) {
                throw new Exception( __("Invalid payment gateway.") );
            }

            if(session()->has('checkout_plan_id') && session()->has('checkout_plan_type')){
                $plan_type = session("checkout_plan_type");
                $plan_id = session("checkout_plan_id");
                $plan = Plans::find($plan_id);
                $user_id = \Auth::id();
                $invoice_id = rand_string();

                session()->forget(['checkout_plan_id', 'checkout_plan_type']);

                $this->updatedCoupon();
                $response = $this->gateway->success();

                $dataPaymentHistory = [
                    'uid'            => $user_id,
                    'plan_id'        => $plan_id,
                    'from'           => $response['gateway'],
                    'transaction_id' => $response['transaction_id'],
                    'currency'       => $response['currency'],
                    'by'             => $plan_type,
                    'amount'         => $response['amount'],
                    'status'         => 1,
                    'changed'        => time(),
                    'created'        => time(),
                ];

                // Only insert if transaction_id does not exist
                $paymentHistory = PaymentHistory::firstOrCreate(
                    ['transaction_id' => $response['transaction_id']],
                    array_merge($dataPaymentHistory, ['id_secure' => $invoice_id])
                );
                
                $response['user_id'] = $user_id;
                $response['payment_id'] = $paymentHistory->id;
                $response['plan_id'] = $plan_id;
                event(new PaymentSuccess($response));

                $user = Auth()->user();

                if($user){
                    \MailSender::sendByTemplate('payment_success', $user->email, [
                        'fullname'      => $user->fullname,
                        'order_id'      => $invoice_id,
                        'plan_name'     => $plan->name,
                        'order_amount'  => $response['amount'],
                        'order_currency'=> get_option("currency", "USD"),
                        'order_date'    => \Carbon\Carbon::now()->format('d M Y'),
                        'login_url'     => route("login")
                    ]);
                }

                session()->flash('payment_response', $response);

                return $response;
            }else{
                if(session()->has('payment_response')){
                    $response = session('payment_response');
                    return $response;
                } 

                return false;
            }

        } catch (Exception $e) {
            return $this->apiError($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function notify(array $response)
    {
        try {
            if (!$this->gateway instanceof PaymentInterface) {
                throw new Exception(__("Invalid payment gateway."));
            }

            $userId   = $response['uid']       ?? (\Auth::id() ?? 0);
            $planId   = $response['plan_id']   ?? session("checkout_plan_id");
            $planType = $response['plan_type'] ?? session("checkout_plan_type");

            if (!$planId) {
                throw new Exception("Missing plan_id for payment.");
            }

            $plan      = Plans::find($planId);
            $invoiceId = rand_string();
            $paymentHistory = PaymentHistory::firstOrCreate(
                ['transaction_id' => $response['transaction_id']],
                [
                    'uid'            => $userId,
                    'plan_id'        => $planId,
                    'from'           => $response['gateway'],
                    'transaction_id' => $response['transaction_id'],
                    'currency'       => $response['currency'],
                    'by'             => $planType,
                    'amount'         => $response['amount'],
                    'status'         => $response['status'] ?? 1,
                    'changed'        => time(),
                    'created'        => time(),
                    'id_secure'      => $invoiceId,
                ]
            );

            \Log::info('PaymentHistory result', [
                'id'   => $paymentHistory->id,
                'new'  => $paymentHistory->wasRecentlyCreated,
                'exists' => $paymentHistory->exists,
            ]);

            $response['user_id']    = $userId;
            $response['payment_id'] = $paymentHistory->id;
            $response['plan_id']    = $planId;

            event(new PaymentSuccess($response));

            $user = User::find($userId);
            if ($user && $plan) {
                \MailSender::sendByTemplate('payment_success', $user->email, [
                    'fullname'       => $user->fullname ?? $user->username,
                    'order_id'       => $invoiceId,
                    'plan_name'      => $plan->name,
                    'order_amount'   => $response['amount'],
                    'order_currency' => $response['currency'] ?? get_option("currency", "USD"),
                    'order_date'     => \Carbon\Carbon::now()->format('d M Y'),
                    'login_url'      => route("login")
                ]);
            }

            return response('OK', 200);

        } catch (Exception $e) {
            \Log::info('PaymentHistory result', [
                'id'   => $response['gateway'],
                'new'  => $e->getMessage()
            ]);
            return response('Invalid: '.$e->getMessage(), 400);
        }
    }

    /**
     * Process a refund request.
     *
     * @param array $data
     * @return array
     */
    public function refund(array $data)
    {
        try {
            if (!$this->gateway instanceof PaymentInterface) {
                throw new Exception( __("Invalid payment gateway.") );
            }

            return $this->gateway->refund($data);
        } catch (Exception $e) {
            return $this->apiError($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * Process a webhook request.
     *
     * @param array $data
     * @return array
     */
    public function webhook($request)
    {
        try {
            if (!$this->gateway instanceof PaymentInterface) {
                throw new Exception( __("Invalid payment gateway.") );
            }

            if (method_exists($this->gateway, 'webhook')) {
                return $this->gateway->webhook($request);
            } else {
                return $this->apiError( __("Webhook functionality is not supported for this payment service."), 400);
            }
        } catch (Exception $e) {
            return $this->apiError($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function price($price, $withSymbol = true)
    {
        $currency  = get_option("currency", "USD");
        $symbol    = get_option("currency_symbol", "$");
        $position  = get_option("currency_symbol_postion", "1");

        $price = is_numeric($price) ? number_format($price, 0) : $price;

        if (!$withSymbol) {
            return $price;
        }

        return $position == "1"
            ? $symbol . $price
            : $price . ' ' . $symbol;
    }

    public function listCurrency(){
        return [
            'ALL' => 'Albania Lek',
            'AFN' => 'Afghanistan Afghani',
            'ARS' => 'Argentina Peso',
            'AWG' => 'Aruba Guilder',
            'AUD' => 'Australia Dollar',
            'AZN' => 'Azerbaijan New Manat',
            'BSD' => 'Bahamas Dollar',
            'BBD' => 'Barbados Dollar',
            'BDT' => 'Bangladeshi taka',
            'BYR' => 'Belarus Ruble',
            'BZD' => 'Belize Dollar',
            'BMD' => 'Bermuda Dollar',
            'BOB' => 'Bolivia Boliviano',
            'BAM' => 'Bosnia and Herzegovina Convertible Marka',
            'BWP' => 'Botswana Pula',
            'BGN' => 'Bulgaria Lev',
            'BRL' => 'Brazil Real',
            'BND' => 'Brunei Darussalam Dollar',
            'KHR' => 'Cambodia Riel',
            'CAD' => 'Canada Dollar',
            'KYD' => 'Cayman Islands Dollar',
            'CLP' => 'Chile Peso',
            'CNY' => 'China Yuan Renminbi',
            'COP' => 'Colombia Peso',
            'CRC' => 'Costa Rica Colon',
            'HRK' => 'Croatia Kuna',
            'CUP' => 'Cuba Peso',
            'CZK' => 'Czech Republic Koruna',
            'DKK' => 'Denmark Krone',
            'DOP' => 'Dominican Republic Peso',
            'XCD' => 'East Caribbean Dollar',
            'EGP' => 'Egypt Pound',
            'SVC' => 'El Salvador Colon',
            'EEK' => 'Estonia Kroon',
            'EUR' => 'Euro Member Countries',
            'FKP' => 'Falkland Islands (Malvinas) Pound',
            'FJD' => 'Fiji Dollar',
            'GHC' => 'Ghana Cedis',
            'GIP' => 'Gibraltar Pound',
            'GTQ' => 'Guatemala Quetzal',
            'GGP' => 'Guernsey Pound',
            'GYD' => 'Guyana Dollar',
            'HNL' => 'Honduras Lempira',
            'HKD' => 'Hong Kong Dollar',
            'HUF' => 'Hungary Forint',
            'ISK' => 'Iceland Krona',
            'INR' => 'India Rupee',
            'IDR' => 'Indonesia Rupiah',
            'IRR' => 'Iran Rial',
            'IMP' => 'Isle of Man Pound',
            'ILS' => 'Israel Shekel',
            'JMD' => 'Jamaica Dollar',
            'JPY' => 'Japan Yen',
            'JEP' => 'Jersey Pound',
            'KZT' => 'Kazakhstan Tenge',
            'KPW' => 'Korea (North) Won',
            'KRW' => 'Korea (South) Won',
            'KGS' => 'Kyrgyzstan Som',
            'LAK' => 'Laos Kip',
            'LVL' => 'Latvia Lat',
            'LBP' => 'Lebanon Pound',
            'LRD' => 'Liberia Dollar',
            'LTL' => 'Lithuania Litas',
            'MKD' => 'Macedonia Denar',
            'MYR' => 'Malaysia Ringgit',
            'MUR' => 'Mauritius Rupee',
            'MXN' => 'Mexico Peso',
            'MNT' => 'Mongolia Tughrik',
            'MZN' => 'Mozambique Metical',
            'NAD' => 'Namibia Dollar',
            'NPR' => 'Nepal Rupee',
            'ANG' => 'Netherlands Antilles Guilder',
            'NZD' => 'New Zealand Dollar',
            'NIO' => 'Nicaragua Cordoba',
            'NGN' => 'Nigeria Naira',
            'NOK' => 'Norway Krone',
            'OMR' => 'Oman Rial',
            'PKR' => 'Pakistan Rupee',
            'PAB' => 'Panama Balboa',
            'PYG' => 'Paraguay Guarani',
            'PEN' => 'Peru Nuevo Sol',
            'PHP' => 'Philippines Peso',
            'PLN' => 'Poland Zloty',
            'QAR' => 'Qatar Riyal',
            'RON' => 'Romania New Leu',
            'RUB' => 'Russia Ruble',
            'SHP' => 'Saint Helena Pound',
            'SAR' => 'Saudi Arabia Riyal',
            'RSD' => 'Serbia Dinar',
            'SCR' => 'Seychelles Rupee',
            'SGD' => 'Singapore Dollar',
            'SBD' => 'Solomon Islands Dollar',
            'SOS' => 'Somalia Shilling',
            'ZAR' => 'South Africa Rand',
            'LKR' => 'Sri Lanka Rupee',
            'SEK' => 'Sweden Krona',
            'CHF' => 'Switzerland Franc',
            'SRD' => 'Suriname Dollar',
            'SYP' => 'Syria Pound',
            'TWD' => 'Taiwan New Dollar',
            'THB' => 'Thailand Baht',
            'TTD' => 'Trinidad and Tobago Dollar',
            'TRY' => 'Turkey Lira',
            'TRL' => 'Turkey Lira',
            'TVD' => 'Tuvalu Dollar',
            'UAH' => 'Ukraine Hryvna',
            'GBP' => 'United Kingdom Pound',
            'USD' => 'United States Dollar',
            'UYU' => 'Uruguay Peso',
            'UZS' => 'Uzbekistan Som',
            'VEF' => 'Venezuela Bolivar',
            'VND' => 'Viet Nam Dong',
            'YER' => 'Yemen Rial',
            'ZWD' => 'Zimbabwe Dollar'
        ];
    }
}
