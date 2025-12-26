<?php

namespace Modules\PaymentPayPalRecurring\Services;

use Modules\Payment\Interfaces\RecurringPaymentInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Models\User;
use Exception;

class PaymentService implements RecurringPaymentInterface
{
    protected $clientId;
    protected $secret;
    protected $mode;
    protected $apiBase;
    protected $accessToken;
    protected $httpClient;

    public function __construct()
    {
        $this->clientId     = get_option("paypal_recurring_client_id");
        $this->secret       = get_option("paypal_recurring_client_secret");
        $this->mode         = (int)get_option("paypal_recurring_environment", 0);
        $this->apiBase      = !$this->mode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        $this->httpClient   = new Client();
        $this->accessToken  = $this->getAccessToken();
    }

    public function createSubscription(array $params)
    {
        try {
            // Validate params
            $email     = $params['email'] ?? null;
            $userId    = $params['user_id'] ?? '';
            $planId    = $params['plan_id'] ?? null;
            $planType  = $params['plan_type'] ?? '';
            $fullname  = $params['fullname'] ?? '';
            $planName  = $params['plan_name'] ?? null;
            $planDesc  = $params['plan_desc'] ?? null;
            $amount    = $params['amount'] ?? null;
            $currency  = $params['currency'] ?? 'USD';
            $returnUrl = $params['return_url'] ?? null;
            $cancelUrl = $params['cancel_url'] ?? null;

            if (!$email || !$returnUrl || !$cancelUrl) {
                throw new Exception("Missing required params: email, return_url, cancel_url");
            }

            $planPaypalId = $planId;
            if (!$planPaypalId || strlen($planPaypalId) < 10) {
                if (!$planName || !$planDesc || !$amount) {
                    throw new Exception("Missing plan info for auto-create: plan_name, plan_desc, amount");
                }
                $productId = $this->createProduct($planName, $planDesc);

                $interval      = 'MONTH';
                $intervalCount = 1;
                if ($planType == 2) {
                    $interval = 'YEAR';
                } elseif ($planType == 3) {
                    $interval = 'YEAR'; $intervalCount = 100;
                }
                $planPaypalId = $this->createPlan($productId, $planName, $planDesc, $currency, $amount, $interval, $intervalCount);
            }

            $customId = substr("{$userId}-{$planId}-{$planType}", 0, 127);

            // Subscriber info
            $subscriber = ['email_address' => $email];
            if ($fullname) {
                $subscriber['name'] = ['given_name' => $fullname];
            }

            $payload = [
                'plan_id'   => (string)$planPaypalId,
                'custom_id' => $customId,
                'subscriber' => $subscriber,
                'application_context' => [
                    'brand_name'          => get_option('website_title', config('site.title', 'Stackposts')),
                    'locale'              => 'en-US',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action'         => 'SUBSCRIBE_NOW',
                    'return_url'          => $returnUrl,
                    'cancel_url'          => $cancelUrl,
                ]
            ];

            $options = [
                'headers' => [
                    "Content-Type"  => "application/json",
                    "Authorization" => "Bearer " . $this->accessToken,
                ],
                'json' => $payload
            ];

            $result = $this->doPost($this->apiBase . "/v1/billing/subscriptions", $options);

            if (empty($result['id'])) {
                throw new Exception('PayPal did not return a subscription id.');
            }

            $approvalUrl = null;
            foreach ($result['links'] ?? [] as $link) {
                if (($link['rel'] ?? '') === 'approve') {
                    $approvalUrl = $link['href'];
                    break;
                }
            }

            if (!$approvalUrl) {
                throw new Exception('Approval URL not found in PayPal response.');
            }

            return [
                'status'          => 1,
                'payment_link'    => $approvalUrl,
                'subscription_id' => $result['id'],
                'plan_id'         => $planPaypalId,
            ];
        } catch (\Throwable $e) {
            \Log::error('PayPal createSubscription error: ' . $e->getMessage());
            return [
                'status'  => 0,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function createProduct($name, $description = '')
    {
        $url = $this->apiBase . "/v1/catalogs/products";
        $payload = [
            'name'        => substr($name, 0, 127),
            'type'        => 'SERVICE',
            'description' => substr($description, 0, 256),
        ];
        $options = [
            'headers' => [
                "Content-Type"  => "application/json",
                "Authorization" => "Bearer " . $this->accessToken,
            ],
            'json' => $payload,
        ];
        $result = $this->doPost($url, $options);

        if (empty($result['id'])) {
            throw new Exception('Failed to create PayPal Product.');
        }
        return $result['id'];
    }

    public function createPlan($productId, $planName, $planDesc, $currency, $amount, $interval = 'MONTH', $intervalCount = 1)
    {
        $url = $this->apiBase . "/v1/billing/plans";
        $payload = [
            'product_id' => $productId,
            'name'       => substr($planName, 0, 127),
            'description'=> substr($planDesc, 0, 256),
            'status'     => 'ACTIVE',
            'billing_cycles' => [[
                'frequency' => [
                    'interval_unit'  => strtoupper($interval),
                    'interval_count' => (int)$intervalCount,
                ],
                'tenure_type'     => 'REGULAR',
                'sequence'        => 1,
                'total_cycles'    => 0,
                'pricing_scheme'  => [
                    'fixed_price' => [
                        'value'         => (string)round($amount, 2),
                        'currency_code' => strtoupper($currency)
                    ]
                ],
            ]],
            'payment_preferences' => [
                'auto_bill_outstanding'     => true,
                'setup_fee'                 => [
                    'value'         => '0',
                    'currency_code' => strtoupper($currency)
                ],
                'setup_fee_failure_action'  => 'CONTINUE',
                'payment_failure_threshold' => 1,
            ],
        ];
        $options = [
            'headers' => [
                "Content-Type"  => "application/json",
                "Authorization" => "Bearer " . $this->accessToken,
            ],
            'json' => $payload,
        ];
        $result = $this->doPost($url, $options);

        if (empty($result['id'])) {
            throw new Exception('Failed to create PayPal Plan.');
        }
        return $result['id'];
    }

    public function cancelSubscription(string $subscriptionId)
    {
        try {
            if (empty($subscriptionId)) {
                throw new Exception("Missing subscription id.");
            }

            $urlInfo = $this->apiBase . "/v1/billing/subscriptions/{$subscriptionId}";
            $options = [
                'headers' => [
                    "Content-Type" => "application/json",
                    "Authorization" => "Bearer " . $this->accessToken,
                ]
            ];

            try {
                $resp = $this->httpClient->get($urlInfo, $options);
                $data = json_decode($resp->getBody()->getContents(), true);
                $status = $data['status'] ?? null;
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                if ($e->getResponse()->getStatusCode() === 404) {
                    return [
                        'status'          => 1,
                        'message'         => __('Subscription does not exist or already canceled/deleted.'),
                        'subscription_id' => $subscriptionId,
                    ];
                }
                throw $e;
            }

            if ($status === 'CANCELLED' || $status === 'EXPIRED') {
                return [
                    'status'          => 1,
                    'message'         => __('Subscription canceled successfully.'),
                    'subscription_id' => $subscriptionId,
                ];
            }

            if (in_array($status, ['ACTIVE', 'APPROVAL_PENDING', 'SUSPENDED'])) {
                $url = $this->apiBase . "/v1/billing/subscriptions/{$subscriptionId}/cancel";
                $options['json'] = [ "reason" => "Canceled by user request" ];
                $response = $this->httpClient->post($url, $options);
                $httpCode = $response->getStatusCode();

                if ($httpCode == 204) {
                    return [
                        'status'          => 1,
                        'message'         => __('Subscription canceled successfully.'),
                        'subscription_id' => $subscriptionId,
                    ];
                } else {
                    throw new Exception("PayPal API did not confirm cancellation. HTTP code: {$httpCode}");
                }
            }

            return [
                'status'  => 0,
                'message' => "Subscription is not in a cancelable state: {$status}"
            ];
        } catch (ClientException $e) {
            $msg = $this->parseError($e);
            \Log::error('PayPal cancelSubscription error: ' . $msg);
            return [
                'status'  => 0,
                'message' => $msg
            ];
        } catch (\Exception $e) {
            \Log::error('PayPal cancelSubscription error: ' . $e->getMessage());
            return [
                'status'  => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    public function renewSubscription(string $subscriptionId){}

    public function syncSubscription(string $subscriptionId)
    {
        try {
            if (empty($subscriptionId)) {
                throw new Exception("Missing subscription id.");
            }

            $url = $this->apiBase . "/v1/billing/subscriptions/{$subscriptionId}";
            $options = [
                'headers' => [
                    "Content-Type" => "application/json",
                    "Authorization" => "Bearer " . $this->accessToken,
                ]
            ];

            $response = $this->httpClient->get($url, $options);
            $result = json_decode($response->getBody()->getContents(), true);

            if (!isset($result['id'])) {
                throw new Exception('PayPal did not return a subscription.');
            }

            $statusMap = [
                'ACTIVE'    => 1,
                'APPROVAL_PENDING' => 0,
                'SUSPENDED' => 0,
                'CANCELLED' => 2,
                'EXPIRED'   => 2,
            ];
            $statusNum = $statusMap[$result['status']] ?? 0;

            return [
                'status'              => 1,
                'gateway'             => 'paypal',
                'subscription_id'     => $result['id'],
                'customer_id'         => $result['subscriber']['payer_id'] ?? null,
                'status_code'         => $statusNum,
                'status_text'         => $result['status'] ?? null,
                'plan_id'             => $result['plan_id'] ?? null,
                'plan_nickname'       => $result['plan_id'] ?? null,
                'current_period_start'=> isset($result['billing_info']['last_payment']['time']) ? strtotime($result['billing_info']['last_payment']['time']) : null,
                'current_period_end'  => isset($result['billing_info']['next_billing_time']) ? strtotime($result['billing_info']['next_billing_time']) : null,
                'cancel_at_period_end'=> ($result['status'] ?? '') == 'CANCELLED' ? true : false,
                'cancel_at'           => isset($result['billing_info']['next_billing_time']) ? strtotime($result['billing_info']['next_billing_time']) : null,
                'canceled_at'         => isset($result['status']) && $result['status'] == 'CANCELLED' ? time() : null,
                'trial_end'           => null, // PayPal không trả về trial_end
                'created'             => isset($result['start_time']) ? strtotime($result['start_time']) : null,
                'raw'                 => $result,
            ];
        } catch (ClientException $e) {
            $msg = $this->parseError($e);
            \Log::error('PayPal syncSubscription error: ' . $msg);
            return [
                'status'  => 0,
                'message' => $msg
            ];
        } catch (\Exception $e) {
            \Log::error('PayPal syncSubscription error: ' . $e->getMessage());
            return [
                'status'  => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getSubscriptionInfo(string $subscriptionId)
    {
        try {
            if (empty($subscriptionId)) {
                throw new Exception("Missing subscription id.");
            }

            $url = $this->apiBase . "/v1/billing/subscriptions/{$subscriptionId}";
            $options = [
                'headers' => [
                    "Content-Type" => "application/json",
                    "Authorization" => "Bearer " . $this->accessToken,
                ]
            ];

            $response = $this->httpClient->get($url, $options);
            $result = json_decode($response->getBody()->getContents(), true);

            if (!isset($result['id'])) {
                throw new Exception('PayPal did not return a subscription.');
            }

            $statusMap = [
                'ACTIVE'    => 1,
                'APPROVAL_PENDING' => 0,
                'SUSPENDED' => 0,
                'CANCELLED' => 2,
                'EXPIRED'   => 2,
            ];
            $statusNum = $statusMap[$result['status']] ?? 0;

            return [
                'status'              => 1,
                'gateway'             => 'paypal',
                'subscription_id'     => $result['id'],
                'customer_id'         => $result['subscriber']['payer_id'] ?? null,
                'status_code'         => $statusNum,
                'status_text'         => $result['status'] ?? null,
                'plan_id'             => $result['plan_id'] ?? null,
                'plan_nickname'       => $result['plan_id'] ?? null,
                'start_date'          => isset($result['start_time']) ? strtotime($result['start_time']) : null,
                'current_period_start'=> isset($result['billing_info']['last_payment']['time']) ? strtotime($result['billing_info']['last_payment']['time']) : null,
                'current_period_end'  => isset($result['billing_info']['next_billing_time']) ? strtotime($result['billing_info']['next_billing_time']) : null,
                'cancel_at_period_end'=> ($result['status'] ?? '') == 'CANCELLED' ? true : false,
                'cancel_at'           => isset($result['billing_info']['next_billing_time']) ? strtotime($result['billing_info']['next_billing_time']) : null,
                'canceled_at'         => isset($result['status']) && $result['status'] == 'CANCELLED' ? time() : null,
                'trial_end'           => null, // PayPal không trả về trial_end
                'created'             => isset($result['start_time']) ? strtotime($result['start_time']) : null,
                'raw'                 => $result,
            ];
        } catch (ClientException $e) {
            $msg = $this->parseError($e);
            \Log::error('PayPal getSubscriptionInfo error: ' . $msg);
            return [
                'status'  => 0,
                'message' => $msg
            ];
        } catch (\Exception $e) {
            \Log::error('PayPal getSubscriptionInfo error: ' . $e->getMessage());
            return [
                'status'  => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    public function handleWebhook($request)
    {
        $payload = $request->getContent();
        $event = json_decode($payload, true);

        $allowedEvents = [
            'BILLING.SUBSCRIPTION.CREATED',
            'BILLING.SUBSCRIPTION.ACTIVATED',
            'BILLING.SUBSCRIPTION.CANCELLED',
            'BILLING.SUBSCRIPTION.EXPIRED',
            'PAYMENT.SALE.COMPLETED',
            'PAYMENT.SALE.DENIED',
        ];

        $eventType = $event['event_type'] ?? null;
        $resource = $event['resource'] ?? [];

        if (!$eventType || !in_array($eventType, $allowedEvents)) {
            return response()->json(['status' => 'ignored', 'message' => 'Event not handled'], 200);
        }

        $subscriptionId = null;
        if (str_starts_with($eventType, 'BILLING.SUBSCRIPTION')) {
            $subscriptionId = $resource['id'] ?? null;
        } elseif (str_starts_with($eventType, 'PAYMENT.SALE')) {
            $subscriptionId = $resource['billing_agreement_id'] ?? null;
        }

        $transactionId = $resource['id'] ?? null;
        $customId = $resource['custom_id'] ?? $resource['custom'] ?? null;
        $userId = $planId = $planType = null;
        if ($customId && preg_match('/^(\d+)-(\w+)-(\d+)$/', $customId, $matches)) {
            $userId   = $matches[1] ?? null;
            $planId   = $matches[2] ?? null;
            $planType = $matches[3] ?? null;
        } elseif ($customId) {
            $parts = explode('-', $customId);
            $userId = $parts[0] ?? null;
            $planId = $parts[1] ?? null;
            $planType = $parts[2] ?? null;
        }

        $email = null;
        if ($userId) {
            $user   = User::find($userId);
            $email = $user ? $user->email : null;
        }

        $amount   = $resource['amount']['total']    ?? null;
        $currency = $resource['amount']['currency'] ?? null;

        $data = [
            'subscription_id' => $subscriptionId,
            'user_id'         => $userId,
            'plan_id'         => $planId,
            'type'            => $planType,
            'source'          => 'paypal',
            'customer_id'     => $email,
            'amount'          => $amount,
            'currency'        => $currency,
            'raw_data'        => json_encode($resource),
        ];

        try {
            switch ($eventType) {
                case 'BILLING.SUBSCRIPTION.CREATED':
                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                    \RecurringPayment::updateSubscriptionStatus($subscriptionId, false, 1, 0);
                    break;
                case 'BILLING.SUBSCRIPTION.CANCELLED':
                case 'BILLING.SUBSCRIPTION.EXPIRED':
                    \RecurringPayment::updateSubscriptionStatus($subscriptionId, false, 2, 0);
                    break;
                case 'PAYMENT.SALE.COMPLETED':
                    \RecurringPayment::saveSubscription($data);
                    \RecurringPayment::updateSubscriptionStatus($subscriptionId, $transactionId, 1, 1);
                    break;
                case 'PAYMENT.SALE.DENIED':
                    \RecurringPayment::updateSubscriptionStatus($subscriptionId, false, 2, 0);
                    break;
            }
            return response()->json(['status' => 'success'], 200);
        } catch (\Throwable $e) {
            \Log::error("PayPal webhook error: " . $e->getMessage(), [
                'event' => $event,
                'resource' => $resource,
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    protected function getAccessToken()
    {
        $url = $this->apiBase . "/v1/oauth2/token";
        try {
            $response = $this->httpClient->post($url, [
                'auth' => [$this->clientId, $this->secret],
                'form_params' => ['grant_type' => 'client_credentials'],
            ]);
            $body = json_decode($response->getBody()->getContents(), true);
            return $body['access_token'] ?? null;
        } catch (Exception $e) {
            \Log::error('PayPal getAccessToken error: ' . $e->getMessage());
            throw new Exception("Could not get PayPal access token.");
        }
    }

    private function doPost($url, $options)
    {
        try {
            $response = $this->httpClient->post($url, $options);
            $httpCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            if ($httpCode < 200 || $httpCode >= 300) {
                throw new Exception($body);
            }
            return json_decode($body, true);
        } catch (ClientException $e) {
            throw new Exception($this->parseError($e));
        } catch (\Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected function parseError(ClientException $e)
    {
        $body = $e->getResponse()->getBody()->getContents();
        $errorData = json_decode($body, true);
        return $errorData['message'] ?? $e->getMessage();
    }
}
