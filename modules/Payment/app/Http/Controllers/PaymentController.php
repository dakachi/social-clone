<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AdminPlans\Models\Plans;
use Modules\AdminPaymentSubscriptions\Models\PaymentSubscription;
use Modules\AdminManualPayments\Models\PaymentManual;
use Payment;
use RecurringPayment;
use DB;

class PaymentController extends Controller
{

    public function index(Request $request, $plan_id = "")
    {
        $plan = Plans::where("id_secure", $plan_id)
            ->where("status", 1)
            ->first();

        if (empty($plan)) {
            return redirect()->route('app.dashboard');
        }

        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (\Plan::hasSubscription()) {
            return redirect()->route('app.profile', 'plan')
                ->with('error', __("You already have an active subscription. Please cancel it before registering a new one."));
        }

        if ($plan->free_plan) {
            $currentPlan = Plans::find($user->plan_id);
            $now = time();
            $isPaidPlan = $currentPlan && !$currentPlan->free_plan;
            $unlimited = $user->expiration_date == -1;
            $isStillActive = $unlimited || ($user->expiration_date && $user->expiration_date > $now);

            if ($isPaidPlan && $isStillActive) {
                return redirect()->route('app.profile', 'plan')->with('warning', __('You are currently on a paid plan. If you want to downgrade to the free plan, please cancel your current plan in the plan management section.'));
            }

            \Plan::activateFreePlan($plan_id);
            return redirect()->route('app.profile', 'plan')->with('success', __('Your free plan has been activated successfully!'));
        }

        $coupon = Payment::appliedCoupon();


        /*
        * MANUAL PAYMENT
         */
        $manualTransactionCode     = session('manual_transaction_code');
        $manualTransactionCodeTime = session('manual_transaction_code_time');

        if ($manualTransactionCode && $manualTransactionCodeTime && (time() - $manualTransactionCodeTime < 3600)) {
            $transactionCode = $manualTransactionCode;
        } else {
            $prefix = get_option('payment_manual_prefix', 'PAY-');
            $transactionCode = $prefix . strtoupper(\Str::random(8));

            session([
                'manual_transaction_code'      => $transactionCode,
                'manual_transaction_code_time' => time(),
            ]);
        }
        /*
        * MANUAL PAYMENT END
         */

        return view('payment::index', [
            "plan" => $plan,
            "coupon" => $coupon,
            "transactionCode" => $transactionCode,
        ]);
    }

    public function checkout(Request $request, $payment_gateway)
    {
        $plan_id = $request->plan;

        $plan = Plans::where("id_secure", $plan_id)
            ->where("free_plan", 0)
            ->where("status", 1)
            ->first();

        if (!$plan) {
            return redirect()->route('app.dashboard')->with('error', __("The selected plan is not available."));
        }

        $user = auth()->user();

        if (\Plan::hasSubscription()) {
            return redirect()->route('app.dashboard')
                ->with('error', __("You already have an active subscription. Please cancel it before purchasing a new one."));
        }

        $payments = app()->bound('payments') ? app('payments') : [];
        $payment_item = collect($payments)->firstWhere('uri', $payment_gateway);

        if (!$payment_item) {
            return redirect()->route('app.dashboard');
        }

        $calculatePayment = Payment::calculatePayment($plan->price, $plan->id);

        $gateway_id = $payment_item['id'];
        $gateway_name = $payment_item['uri'];

        $data = [
            'user_id'    => $user->id,
            'fullname'   => $user->fullname,
            'email'      => $user->email,
            'username'   => $user->username,
            'plan_id'    => $plan->id,
            'plan_type'  => $plan->type,
            'plan_name'  => $plan->name,
            'plan_desc'  => $plan->desc,
            'amount'     => $calculatePayment['total'],
            'currency'   => get_option("currency", "USD"),
            'title'      => $plan->name,
            'description'=> $plan->desc,
            'return_url' => route('payment.success', ['gateway' => $gateway_name]),
            'cancel_url' => route('payment.cancel', ['gateway' => $gateway_name])
        ];

        try {
            if ($payment_item['type'] == 1) {
                Payment::setGateway($gateway_id, $plan);
                $response = Payment::pay($data);
            } elseif ($payment_item['type'] == 2) {
                RecurringPayment::setGateway($gateway_id);
                $response = RecurringPayment::createSubscription($data);
            } else {
                return redirect()->route('app.dashboard')->with('error', __("Invalid payment type."));
            }

            if (isset($response['payment_link'])) {
                return redirect($response['payment_link']);
            }
            if (isset($response['redirect_url'])) {
                return redirect($response['redirect_url']);
            }

            return redirect()->route('app.dashboard')->with('error', __("Payment initiation failed."));

        } catch (\Exception $e) {
            return redirect()->route('app.dashboard')->with('error', $e->getMessage());
        }
    }

    public function success(Request $request, $payment_gateway)
    {
        $payments = app()->bound('payments') ? app('payments') : [];
        $payment_item = collect($payments)->firstWhere('uri', $payment_gateway);

        if (!$payment_item) {
            return redirect()->route('app.dashboard');
        }

        $gateway_id = $payment_item['id'];
        $isRecurring = ($payment_item['type'] ?? 1) == 2;
        $msg = null;
        $result = null;

        try {
            if ($request->isMethod('post')) {
                $queryData = collect($request->post())
                    ->except(['module', 'team', 'team_id'])
                    ->toArray();

                $queryString = http_build_query($queryData);
                $url = route('payment.success', ['gateway' => $payment_gateway]) . '?' . $queryString;

                header("Location: {$url}");
                exit(0);
            }

            if ($isRecurring) {
                RecurringPayment::setGateway($gateway_id);
                $msg = __("Your subscription is being processed. It will be activated as soon as we receive confirmation from the payment gateway.");
            } else {
                Payment::setGateway($gateway_id);
                $result = Payment::success();

                $result["type"] = 1;

                if (!$result) {
                    return redirect()->route("app.dashboard.index");
                }

                if (isset($result['status']) && $result['status'] !== 'success') {
                    $msg = __("Your order is being processed and will be activated shortly. We appreciate your trust and are excited to have you onboard. Enjoy all the benefits coming your way.");
                }
            }

            return view('payment::payment_success', [
                "result" => $result,
                "msg"    => $msg,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('app.dashboard.index')->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, $payment_gateway)
    {
        if ($request->isMethod('post')) {
            $queryData = collect($request->post())
                ->except(['module', 'team', 'team_id'])
                ->toArray();

            $queryString = http_build_query($queryData);
            $url = route('payment.cancel', ['gateway' => $payment_gateway]) . '?' . $queryString;

            header("Location: {$url}");
            exit(0);
        }

        return view('payment::payment_failed', []);
    }

    public function webhook(Request $request, $gateway)
    {
        $payments = app()->bound('payments') ? app('payments') : [];
        $payment_item = collect($payments)->firstWhere('uri', $gateway);

        if (!$payment_item) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Payment gateway not found.'
            ], 404);
        }

        $gateway_id = $payment_item['id'];
        $type       = $payment_item['type'] ?? 1; 

        try {
            if ($type == 2) {
                RecurringPayment::setGateway($gateway_id);
                $result = RecurringPayment::handleWebhook($request);
                return response()->json([
                    'status' => 'ok',
                    'type'   => 'recurring',
                    'result' => $result,
                ]);
            } else {
                Payment::setGateway($gateway_id);
                $result = Payment::handleWebhook($request);
                return response()->json([
                    'status' => 'ok',
                    'type'   => 'onetime',
                    'result' => $result,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error("Webhook payment failed: $gateway", [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Webhook processing error: ' . $e->getMessage()
            ], 400);
        }
    }

    public function cancelSubscription(Request $request)
    {
        try {
            $response = RecurringPayment::cancelSubscription();

            if (isset($response['status']) && $response['status']) {
                return response()->json([
                    'status'  => 1,
                    'message' => __('Subscription canceled successfully.')
                ]);
            } else {
                $error = $response['error'] ?? __('Failed to cancel subscription.');
                return response()->json([
                    'status'  => 0,
                    'message' => $error
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'status'  => 0,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function manualPayment(Request $request)
    {
        if (!auth()->check()) {
            return redirect()
                ->route('login')
                ->with('error', __('You must be logged in to perform this action!'));
        }

        $validated = $request->validate([
            'plan_id'          => 'required|string|max:32',
            'transaction_code' => 'required|string|max:255',
            'payment_info'     => 'required|string|max:1000',
        ]);

        $plan = Plans::where('id_secure', $request->plan_id)->first();
        if (!$plan) {
            return redirect()
                ->back()
                ->with('error', __('The selected plan does not exist or has been removed!'));
        }

        $response = \Captcha::verify($request, 2);
        if ($response !== true) {
            return $response;
        }

        $userId = auth()->id();

        $calculatePayment = Payment::calculatePayment($plan->price, $plan->id, false);

        $payment = new PaymentManual();
        $payment->id_secure      = rand_string();
        $payment->uid            = $userId;
        $payment->plan_id        = $plan->id;
        $payment->payment_id     = $request->transaction_code;
        $payment->payment_info   = $request->payment_info;
        $payment->amount         = $calculatePayment['total'];
        $payment->currency       = get_option("currency", "USD");
        $payment->status         = 0;
        $payment->changed        = time();
        $payment->created        = time();
        $payment->save();

        session()->forget(['manual_transaction_code', 'manual_transaction_code_time']);

        return redirect()
            ->route('app.dashboard')
            ->with('success', __('Your payment information has been submitted. We will verify it as soon as possible!'));
    }
}
