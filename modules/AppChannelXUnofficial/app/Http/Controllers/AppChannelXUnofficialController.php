<?php

namespace Modules\AppChannelXUnofficial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppChannelXUnofficialController extends Controller
{
    public function index(Request $request)
    {
        return redirect( module_url("oauth") );
    }

    public function oauth(Request $request)
    {
        return view( module('key') . '::oauth', [] );
    }

    public function proccess(Request $request){

        $x_csrf_token = trim($request->input('x_csrf_token'));
        $x_auth_token = trim($request->input('x_auth_token'));
        $x_screen_name = trim($request->input('x_screen_name'));
       

        if (empty($x_csrf_token) || empty($x_auth_token) || empty($x_screen_name)) {
            return response()->json([
                'status' => 0,
                'message' => __('CSRF Token, Auth Token and Screen name are required.')
            ]);
        }

        \XUnofficial::setCredentials($x_csrf_token, $x_auth_token, $x_screen_name);
        $response = \XUnofficial::getProfile();

        $authData = [
            'x_csrf_token' => $x_csrf_token,
            'x_auth_token' => $x_auth_token,
            'x_screen_name' => $x_screen_name
        ];
        $authStatus = $response['status'] ?? false;
        $data = $response['data'] ?? [];

        if ($authStatus && !empty($data)) {
            $result[] = [
                'id'        => $data['id'], // Twitter user id
                'name'      => $data['name'],
                'avatar'    => $data['avatar'] ?: text2img($data['screen_name']),
                'desc'      => $data['bio'] ?? $data['screen_name'] ?? $data['name'],
                'link'      => 'https://x.com/' . $data['screen_name'],
                'oauth'     => json_encode($authData ?? []),
                'module'    => module('module_name'),
                'reconnect_url' => module("uri"),
                'social_network' => 'x',
                'category'  => 'profile',
                'login_type'=> 2,
                'can_post'  => 1,
                'data'      => "",
                'proxy'     => $request->proxy ?? 0,
            ];

            $channels = [
                'status'  => 1,
                'message' => __('Succeeded'),
                'channels' => $result,
                'module'   => $request->module ?? '',
                'save_url' => url_app('channels/save'),
                'reconnect_url' => module_url('oauth'),
                'oauth'    => json_encode($authData ?? [])
            ];
            session(['channels' => $channels]);

            return response()->json([
                "status" => 1,
                "message" => __("Login succeeded"),
                "redirect" => url_app("channels/add")
            ]);
        } else {
            return response()->json([
                "status" => 0,
                "message" => $response['error'] ?? __('Login failed. Please try again.'),
            ]);
        }

    }

    public function settings(){
        return view('appchannelxprofiles::settings');
    }
}
