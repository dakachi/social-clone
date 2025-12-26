<?php

namespace Modules\AppChannelXProfiles\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AppChannelXProfiles\Classes\XOAuth;
use Smolblog\OAuth2\Client\Provider\Twitter;

class AppChannelXProfilesController extends Controller
{
    public $x;
    protected $callback_url;

    public function __construct()
    {
        \Access::check('appchannels.' . module('key'));
        
        $this->callback_url = module_url();
        $clientId = get_option("x_client_id", "");
        $clientSecret = get_option("x_client_secret", "");

        // Kiểm tra thiếu cấu hình
        if (!$clientId || !$clientSecret) {
            \Access::deny(__('To use X (Twitter), you must first configure the client ID and client secret.'));
        }

        try {
            $this->x = new Twitter([
                'clientId'     => $clientId,
                'clientSecret' => $clientSecret,
                'redirectUri'  => $this->callback_url,
            ]);
        } catch (\Exception $e) {
            \Log::error('Twitter (X) SDK init error', ['error' => $e->getMessage()]);
            \Access::deny(__('Could not connect to X (Twitter) API: ') . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $result = [];
        try 
        {
            if( !session("X_AccessToken") )
            {
                if (!$request->state || ( $request->state != session("X_OAuth2State") ) ) 
                {
                    $request->session()->forget('X_OAuth2State');
                    return redirect( module_url("oauth") );
                }

                if(!$request->code || !session("X_OAuth2Verifier"))
                {
                    return redirect( module_url("oauth") );
                }

                $token = $this->x->getAccessToken('authorization_code', [
                    'code' => $request->code,
                    'code_verifier' => session("X_OAuth2Verifier"),
                ]);

                session( ['X_AccessToken' => $token ] );
                return redirect( $this->callback_url );
            }
            else
            {
                $accessToken = session('X_AccessToken'); 
            }

            $response = $this->x->getResourceOwner($accessToken);

            if($response->getImageUrl())
            {
                $avatar = $response->getImageUrl();
            }
            else
            {
                $avatar = text2img( $response->getName() );
            }

            $result[] = [
                'id' => $response->getId(),
                'name' => $response->getName(),
                'username' => $response->getUsername(),
                'avatar' => $avatar,
                'desc' => __("Profile"),
                'link' => 'https://x.com/'.$response->getUsername(),
                'oauth' => $accessToken,
                'module' => $request->module['module_name'],
                'reconnect_url' => $request->module['uri']."/oauth",
                'social_network' => 'x',
                'category' => 'profile',
                'login_type' => 1,
                'can_post' => 1,
                'data' => "",
                'proxy' => 0,
            ];

            $channels = [
                'status' => 1,
                'message' => __('Succeeded')
            ];
        } 
        catch (\Exception $e) 
        {
            $channels = [
                'status' => 0,
                'message' => $e->getMessage()
            ];
        }

        $channels = array_merge($channels, [
            'channels' => $result,
            'module' => $request->module,
            'save_url' => url_app('channels/save'),
            'reconnect_url' => module_url('oauth'),
            'oauth' => session("FB_AccessToken")
        ]);

        session( ['channels' => $channels] );
        return redirect( url_app("channels/add") );
    }

    public function oauth(Request $request)
    {   
        $request->session()->forget('X_AccessToken');
        $options = [
            'scope' => [
                'tweet.read',
                'tweet.write',
                'tweet.moderate.write',
                'users.read',
                'follows.read',
                'follows.write',
                'offline.access',
                'space.read',
                'mute.read',
                'mute.write',
                'like.read',
                'like.write',
                'list.read',
                'list.write',
                'block.read',
                'block.write',
                'bookmark.read',
                'bookmark.write',
                'media.write',
                'users.email'
            ],
        ];

        $authUrl = $this->x->getAuthorizationUrl($options);
        session([ "X_OAuth2State" => $this->x->getState() ]);
        session([ "X_OAuth2Verifier" => $this->x->getPkceVerifier() ]);
        return redirect($authUrl);
    }

    public function settings(){
        return view('appchannelxprofiles::settings');
    }
}
