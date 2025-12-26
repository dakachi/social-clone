<?php

namespace Modules\AdminCaptcha\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CaptchaService
{
    public function render()
    {
        $type = get_option('captcha_type', 'disable');
        if ($type === 'recaptcha' && get_option('auth_google_recaptcha_status', 1)) {
            $siteKey = trim(get_option('auth_google_recaptcha_site_key'));
            $secretKey = trim(get_option('auth_google_recaptcha_secret_key'));

            if (!$siteKey || !$secretKey) return '';

            return '<div class="g-recaptcha" data-sitekey="' . $siteKey . '"></div>
                <script src="https://www.google.com/recaptcha/api.js" async defer></script>';
        }

        if ($type === 'turnstile' && get_option('auth_cloudflare_turnstile_status', 1)) {
            $siteKey = trim(get_option('auth_cloudflare_turnstile_site_key'));
            $secretKey = trim(get_option('auth_cloudflare_turnstile_secret_key'));

            if (!$siteKey || !$secretKey) return '';

            return '<div class="cf-turnstile" data-sitekey="' . $siteKey . '"></div>
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
        }
        return '';
    }

    public function verify($request, $type = null)
    {
        $captcha_type = get_option('captcha_type', 'disable');
        $message = __('It looks like the captcha was incorrect. Please try again.');

        if ($captcha_type === 'disable') return true;

        if ($captcha_type === 'recaptcha' && get_option('auth_google_recaptcha_status', 1)) {
            $token = $request->input('g-recaptcha-response');
            $secret = trim(get_option('auth_google_recaptcha_secret_key'));

            if (!$secret) return true;
            if (!$token)  return $this->returnFail($type, $message);

            $res = \Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);
            if (!($res->json('success') === true)) {
                return $this->returnFail($type, $message);
            }
            return true;
        }

        if ($captcha_type === 'turnstile' && get_option('auth_cloudflare_turnstile_status', 1)) {
            $token = $request->input('cf-turnstile-response');
            $secret = trim(get_option('auth_cloudflare_turnstile_secret_key'));

            if (!$secret) return true;
            if (!$token)  return $this->returnFail($type, $message);

            $res = \Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);
            if (!($res->json('success') === true)) {
                return $this->returnFail($type, $message);
            }
            return true;
        }

        return true;
    }

    protected function returnFail($type, $message)
    {
        if ($type == 1) {
            abort(response()->json([
                'status' => 0,
                'message' => $message,
            ]));
        } elseif ($type == 4) {
            abort(response()->json([
                'status' => 0,
                'error_type' => 4,
                'class' => 'text-danger text-error',
                'message' => $message,
            ]));
        } elseif ($type == 2) {
            return back()->with('error', $message);
        } else {
            return false;
        }
    }

}