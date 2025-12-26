<?php

namespace Modules\PaymentStripe\Services;

use Modules\Payment\Interfaces\PaymentInterface;
use Exception;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Refund;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class PaymentService implements PaymentInterface
{
    /**
     * Constructor: Initialize Stripe configuration.
     */
    public function __construct()
    {
        $stripe_publishable_key = get_option("stripe_secret_key");
        if (!$stripe_publishable_key) {
            throw new Exception("Stripe API key is not configured.");
        }
        Stripe::setApiKey($stripe_publishable_key);
    }

    /**
     * Create a Stripe Checkout Session for payment.
     *
     * Expects $data array to contain:
     *   - amount: Payment amount in major units (e.g., "10.00" dollars)
     *   - currency: Currency code (e.g., "USD")
     *   - description: (Optional) Payment description
     *   - return_url: URL to redirect upon successful payment
     *   - cancel_url: URL to redirect if payment is cancelled
     *
     * @param array $data
     * @return array Standardized response including session ID and redirect URL.
     */
    public function pay(array $data)
    {
        try {
            // Determine the currency and convert the amount appropriately.
            $currency = strtoupper($data['currency']);
            $unitAmount = (float)$data['amount']; // amount in major unit provided by your system

            // Multiply by 100 if the currency has decimals.
            if (!isZeroDecimalCurrency($currency)) {
                $unitAmount = intval($unitAmount * 100);
            } else {
                // For zero-decimal currencies, ensure it's an integer.
                $unitAmount = intval($unitAmount);
            }

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'mode'                 => 'payment',
                'line_items'           => [[
                    'price_data' => [
                        'currency'     => $currency,
                        'product_data' => [
                            'name' => $data['description'] ?? 'Stripe Payment',
                        ],
                        'unit_amount'  => $unitAmount,
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => $data['return_url'] . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => $data['cancel_url']
            ]);

            session(['stripe_session_id' => $session->id]);
            return $this->apiSuccess($session->url);
        } catch (Exception $e) {
            return $this->apiError($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * Process successful payment by retrieving a Stripe Checkout Session.
     *
     * Expects that the request (e.g., via query string) contains:
     *   - session_id: The Checkout Session ID returned during payment
     *
     * @return array Standardized response including transaction details.
     */
    public function success()
    {
        try {
            $session_id = request()->session_id;
            if (!$session_id) {
                throw new Exception("Missing session_id in request.");
            }
            // Retrieve the Checkout Session with the expansion on payment_intent
            $session = \Stripe\Checkout\Session::retrieve(
                $session_id,
                ['expand' => ['payment_intent']]
            );

            if ($session->payment_status !== 'paid') {
                throw new Exception("Payment not successful.");
            }

            // Retrieve PaymentIntent (using latest_charge for simplicity)
            if (is_string($session->payment_intent)) {
                $paymentIntent = \Stripe\PaymentIntent::retrieve($session->payment_intent);
            } else {
                $paymentIntent = $session->payment_intent;
            }

            if (empty($paymentIntent->latest_charge)) {
                throw new Exception("No charge information found. PaymentIntent latest_charge is empty.");
            }

            $transaction_id = $paymentIntent->latest_charge;
            
            // Kiểm tra chuyển đổi số tiền dựa trên đơn vị tiền tệ
            $currency = strtoupper($session->currency);
            $amount = $session->amount_total;
            if (!isZeroDecimalCurrency($currency)) {
                $amount = $amount / 100;
            }

            return $this->apiSuccess([
                'gateway'             => "Stripe",
                'status'              => 1,
                'transaction_id'      => $transaction_id,
                'amount'              => $amount,
                'currency'            => $currency,
                'transaction_details' => $paymentIntent
            ]);
        } catch (Exception $e) {
            return $this->apiError($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * Process a refund request for a given payment.
     *
     * Expects $data array to contain:
     *   - payment_intent: The Stripe PaymentIntent ID
     *   - amount (optional): Amount to refund (for partial refund) in major units (e.g., "5.00")
     *
     * @param array $data
     * @return array Standardized response containing refund details.
     */
    public function refund(array $data)
    {
        try {
            if (!isset($data['payment_intent'])) {
                throw new Exception("Missing payment_intent in refund data.");
            }

            $currency = strtoupper($data['currency'] ?? 'USD'); // default to USD or retrieve from your data
            $refundData = [
                'payment_intent' => $data['payment_intent'],
            ];

            if (isset($data['amount'])) {
                $amount = (float)$data['amount'];
                if (!isZeroDecimalCurrency($currency)) {
                    $amount = intval($amount * 100);
                } else {
                    $amount = intval($amount);
                }
                $refundData['amount'] = $amount;
            }
            $refund = \Stripe\Refund::create($refundData);
            return $this->apiSuccess($refund);
        } catch (Exception $e) {
            return $this->apiError($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * Handle Stripe Webhook.
     *
     * This function verifies the webhook signature using the webhook secret,
     * then processes events such as: checkout.session.completed (payment success),
     * payment_intent.payment_failed (payment failure), and charge.refunded (refund event).
     *
     * @param Request $request
     * @return array Standardized API response.
     */
    public function webhook($request)
    {
        try {
            $payload = $request->getContent();
            $sig_header = $request->header('Stripe-Signature');
            $webhookSecret = get_option("stripe_webhook_secret"); // hoặc config('services.stripe.webhook_secret')
            
            // Xây dựng và xác thực event từ Stripe
            $event = Webhook::constructEvent($payload, $sig_header, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            // Payload không hợp lệ.
            return $this->apiError("Invalid payload", 400);
        } catch (SignatureVerificationException $e) {
            // Chữ ký không hợp lệ.
            return $this->apiError("Invalid signature", 400);
        } catch (Exception $e) {
            return $this->apiError($e->getMessage(), $e->getCode() ?: 400);
        }

        // Xử lý event dựa theo type
        switch ($event->type) {
            case 'checkout.session.completed':
                // Thanh toán thành công.
                $session = $event->data->object;  // Đây là đối tượng Checkout Session từ Stripe.
                // TODO: Cập nhật đơn hàng, trạng thái thanh toán, lưu thông tin giao dịch,...
                break;
                
            case 'payment_intent.payment_failed':
                // Thanh toán thất bại.
                $paymentIntent = $event->data->object; // Đây là đối tượng PaymentIntent
                // TODO: Lưu thông báo thất bại, cập nhật trạng thái đơn hàng, cảnh báo cho người dùng,...
                break;
                
            case 'charge.refunded':
                // Hoàn tiền thành công.
                $charge = $event->data->object;  // Đây là đối tượng Charge đã được refund.
                // TODO: Cập nhật trạng thái hoàn tiền cho đơn hàng, ghi nhận thông tin hoàn tiền,...
                break;
                
            default:
                // Xử lý các sự kiện khác hoặc bỏ qua.
                \Log::info("Received unknown event type: " . $event->type);
                break;
        }

        return $this->apiSuccess("Webhook handled", 200);
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
}
