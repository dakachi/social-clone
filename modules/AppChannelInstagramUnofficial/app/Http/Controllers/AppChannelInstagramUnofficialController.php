<?php

namespace Modules\AppChannelInstagramUnofficial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppChannelInstagramUnofficialController extends Controller
{
    public function oauth(Request $request)
    {
        return view( module('key') . '::oauth', [] );
    }

    public function proccess(Request $request)
    {
        $username = trim($request->input('ig_username'));
        $password = trim($request->input('ig_password'));
        $type     = $request->input('ig_type');
        $ig_verification_code = trim($request->input('ig_verification_code'));
        $ig_security_code     = trim($request->input('ig_security_code'));
        $options = $request->input('ig_options');
        $teamId   = $request->input('team_id');
        $options = $options ? json_decode($options, true) : [];

        if (empty($username) || empty($password)) {
            return response()->json([
                'status' => 0,
                'message' => __('Username and password are required.')
            ]);
        }

        $proxyModel = \Proxy::assignProxy('instagram', 2);
        if ($proxyModel) {
            \IGUnofficial::setProxy($proxyModel->proxy);
        }

        if ($options && $type != 1) {
            switch ($type) {
                case 2:
                    if (empty($ig_verification_code) || empty($options)) {
                        return response()->json([
                            'status' => 0,
                            'message' => __('Verification code required.')
                        ]);
                    }

                    $auth_data =  $options['auth_data'] ?? '';
                    $two_factor_identifier = $options['two_factor_identifier'] ?? '';
                    $verification_method = $options['verification_method'] ?? '';

                    \IGUnofficial::setAuthData($auth_data);
                    if ($proxyModel) {
                        \IGUnofficial::setProxy($proxyModel->proxy);
                    }

                    $response = \IGUnofficial::verifyTwoFactorCode($two_factor_identifier, $ig_verification_code, $verification_method);
                    return $this->addAccount($request, $response);
                case 3:
                    if (empty($ig_security_code)) {
                        return response()->json([
                            'status' => 0,
                            'message' => __('Security code required.')
                        ]);
                    }
                    return response()->json([
                        'status' => 0,
                        'message' => __('Challenge handling not implemented.')
                    ]);
            }
        }

        $response = \IGUnofficial::authenticate($username, $password);
        return $this->addAccount($request, $response);
    }


    public function addAccount($request, $response, $proxyModel = null)
    {
        $authStatus = $response['status'] ?? false;

        if ($authStatus) {
            $data = $response['data'] ?? [];

            if (!empty($data['needs_challenge']) && !empty($data['options']['two_factor_identifier'])) {
                return response()->json([
                    'status'  => 0,
                    'type'    => "2FA",
                    'message' => __("Two-factor authentication required"),
                    'options' => $data['options'],
                ]);
            }

            if (!empty($data['needs_challenge']) && !empty($data['options']['api_path'])) {
                return response()->json([
                    'status'  => 0,
                    'type'    => "challenge",
                    'message' => __("Challenge required"),
                    'options' => $data['options'],
                ]);
            }

            $authData = $data['options']['auth_data'] ?? [];
            $proxyId  = $proxyModel ? $proxyModel->id : 0;

            $result[] = [
                'id'        => $data['profile_id'] ?? null,
                'name'      => $data['name'] ?? '',
                'avatar'    => !empty($data['profile_pic'])
                    ? \Media::url($data['profile_pic'])
                    : text2img($data['username'] ?? 'IG'),
                'desc'      => $data['username'] ?? $data['name'] ?? '',
                'link'      => !empty($data['username'])
                    ? 'https://www.instagram.com/' . $data['username']
                    : '',
                'oauth'     => json_encode($authData),
                'module'    => module('module_name'),
                'reconnect_url' => module('uri'),
                'social_network' => 'instagram',
                'category'  => 'profile',
                'login_type'=> 2,
                'can_post'  => 1,
                'data'      => "",
                'proxy'     => $proxyId,
            ];

            $channels = [
                'status'  => 1,
                'message' => __('Succeeded'),
                'channels'=> $result,
                'module'  => $request->module ?? '',
                'save_url'=> url_app('channels/save'),
                'reconnect_url' => module_url('oauth'),
                'oauth'   => json_encode($authData),
            ];
            session(['channels' => $channels]);

            return response()->json([
                "status"   => 1,
                "message"  => __("Login succeeded"),
                "redirect" => url_app("channels/add"),
            ]);
        } else {
            return response()->json([
                "status"  => 0,
                "message" => $response['error'] ?? __('Login failed. Please try again.'),
            ]);
        }
    }

    public function settings(){
        return view('appchannelinstagramprofiles::settings');
    }
}
