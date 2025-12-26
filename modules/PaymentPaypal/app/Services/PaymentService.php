<?php

namespace Modules\PaymentPaypal\Services;

use Modules\Payment\Interfaces\PaymentInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Exception;
use Illuminate\Http\Response;

class PaymentService implements PaymentInterface
{
    protected $clientId;
    protected $secret;
    protected $mode;
    protected $apiBase;
    protected $accessToken;
    protected $httpClient;

    public function __construct()
    {
        $this->clientId    = get_option("paypal_client_id");
        $this->secret      = get_option("paypal_client_secret");
        $this->mode        = (int)get_option("paypal_environment", 0);
        $this->apiBase     = !$this->mode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        $this->httpClient  = new Client();
        $this->accessToken = $this->getAccessToken([]);
    }

    /**
     * Helper for POST requests.
     *
     * @param string $url
     * @param array $options
     * @return array
     * @throws Exception
     */
    private function doPost($url, $options)
    {
        try {
            $response = $this->httpClient->post($url, $options);
        } catch (ClientException $e) {
            throw new Exception($this->parseError($e));
        }
        $httpCode = $response->getStatusCode();
        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception($response->getBody()->getContents());
        }
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get OAuth2 Access Token from PayPal.
     *
     * @param array $data
     * @return string
     */
    public function getAccessToken(array $data)
    {
        try {
            $url = $this->apiBase . "/v1/oauth2/token";
            $options = [
                'auth' => [$this->clientId, $this->secret],
                'form_params' => ['grant_type' => 'client_credentials'],
            ];
            $result = $this->doPost($url, $options);
            if (isset($result['access_token'])) {
                return $result['access_token'];
            }
            throw new Exception( __("Error getting access token: Invalid response.") );
        } catch (Exception $e) {
            return $this->apiError($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * Create an Order using PayPal Orders v2 API.
     * Returns the approval URL.
     *
     * @param array $data
     * @return mixed
     */
    public function pay(array $data)
    {
        try {
            $url = $this->apiBase . "/v2/checkout/orders";
            $orderData = [
                "intent"         => "CAPTURE",
                "purchase_units" => [[
                    "amount"      => [
                        "currency_code" => $data['currency'],
                        "value"         => $data['amount']
                    ],
                    "description" => $data['description'] ?? ''
                ]],
                "application_context" => [
                    "return_url" => $data['return_url'],
                    "cancel_url" => $data['cancel_url']
                ]
            ];
            $options = [
                'headers' => [
                    "Content-Type"  => "application/json",
                    "Authorization" => "Bearer " . $this->accessToken
                ],
                'json' => $orderData
            ];
            $result = $this->doPost($url, $options);
            $approvalLink = null;
            if (isset($result['links']) && is_array($result['links'])) {
                foreach ($result['links'] as $link) {
                    if (isset($link['rel']) && $link['rel'] === "approve") {
                        $approvalLink = $link['href'];
                        break;
                    }
                }
            }
            if (!$approvalLink) {
                throw new Exception( __("Approval link not found.") );
            }
            return $this->apiSuccess($approvalLink);
        } catch (Exception $e) {
            return $this->apiError($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * Process successful payment and return captured transaction data.
     * Expects request() to contain token (order ID) and PayerID.
     *
     * @return mixed
     */
    public function success()
    {
        try {
            $orderId = request()->token;
            $payerId = request()->PayerID;
            if (!$orderId || !$payerId) {
                throw new Exception( __("Missing required parameters: token and/or PayerID.") );
            }
            $url = $this->apiBase . "/v2/checkout/orders/{$orderId}/capture";
            $options = [
                'headers' => [
                    "Content-Type"  => "application/json",
                    "Authorization" => "Bearer " . $this->accessToken,
                ],
                'json' => (object)[]
            ];
            $captureResponse = $this->doPost($url, $options);
            $captureDetails = isset($captureResponse['purchase_units'][0]['payments']['captures'][0])
                ? $captureResponse['purchase_units'][0]['payments']['captures'][0]
                : null;

            $status = $captureResponse['status'] ?? 'Unknown';

            if ($status != "COMPLETED") {
                throw new Exception( __("Payment was not successful. Please try again.") );
            }

            $paymentSuccess = [
                'gateway'             => "Paypal",
                'status'              => 1,
                'transaction_id'      => $orderId,
                'amount'              => $captureDetails['amount']['value'] ?? null,
                'currency'            => $captureDetails['amount']['currency_code'] ?? null,
                'transaction_details' => $captureResponse,
            ];
            return $this->apiSuccess($paymentSuccess);
        } catch (Exception $e) {
            return $this->apiError($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * Refund a captured payment.
     *
     * @param array $data
     * @return mixed
     */
    public function refund(array $data)
    {
        try {
            if (!isset($data['capture_id'])) {
                throw new Exception( __("Missing 'capture_id' in refund data.") );
            }
            $url = $this->apiBase . "/v2/payments/captures/{$data['capture_id']}/refund";
            $refundPayload = [];
            if (isset($data['amount'], $data['currency_code'])) {
                $refundPayload = [
                    "amount" => [
                        "value"         => $data['amount'],
                        "currency_code" => $data['currency_code']
                    ]
                ];
            }
            $options = [
                'headers' => [
                    "Content-Type"  => "application/json",
                    "Authorization" => "Bearer " . $this->accessToken
                ],
                'json' => empty($refundPayload) ? (object)[] : $refundPayload
            ];
            $result = $this->doPost($url, $options);
            return $this->apiSuccess($result);
        } catch (Exception $e) {
            return $this->apiError($e->getMessage(), $e->getCode() ?: 400);
        }
    }


    /**
     * Returns a standardized JSON error response.
     *
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    private function apiError($message, $code = 400)
    {
        throw new Exception("$code - $message");
    }

    /**
     * Returns a standardized JSON success response.
     *
     * @param mixed $data
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    private function apiSuccess($data, $code = 200)
    {
        return $data;
    }

    /**
     * Parse error from ClientException.
     *
     * @param ClientException $e
     * @return string
     */
    private function parseError(ClientException $e)
    {
        $body = $e->getResponse()->getBody()->getContents();
        $errorData = json_decode($body, true);
        return isset($errorData['details'][0]['description'])
            ? $errorData['details'][0]['description']
            : ($errorData['name'] ?? $e->getMessage());
    }

}
