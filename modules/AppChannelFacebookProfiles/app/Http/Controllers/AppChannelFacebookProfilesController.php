<?php

namespace Modules\AppChannelFacebookProfiles\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JanuSoftware\Facebook\Facebook;

class AppChannelFacebookProfilesController extends Controller
{
    public $fb;
    public function __construct()
    {
        \Access::check('appchannels.' . module('key'));
        
        $appId  = get_option("facebook_app_id", "");
        $appSecret  = get_option("facebook_app_secret", "");
        $appVersion  = get_option("facebook_graph_version", "v22.0");

        if(!$appId || !$appSecret || !$appVersion){
            \Access::deny( __('To use Facebook Pages, you must first configure the app ID, app secret, and app version.') );
        }

        try {
            $this->fb = new Facebook([
                'app_id' => $appId,
                'app_secret' => $appSecret,
                'default_graph_version' => $appVersion,
            ]);
        } catch (\Exception $e) {}

        $this->scopes = get_option("facebook_profile_permissions", "public_profile,publish_video");
    }

    public function index(Request $request)
    {
        $result = [];
        try 
        {
            if( !session("FB_AccessToken") )
            {
                if(!$request->code)
                {
                    return redirect( module_url("oauth") );
                }

                $callback_url = module_url();
                $helper = $this->fb->getRedirectLoginHelper();
                if ( $request->state ) 
                {
                    $helper->getPersistentDataHandler()->set('state', $request->state);
                }
                $accessToken = $helper->getAccessToken($callback_url);
                $accessToken = $accessToken->getValue();
                session( ['FB_AccessToken' => $accessToken] );
                return redirect( $callback_url );
            }
            else
            {
                $accessToken = session("FB_AccessToken"); 
            }

            $response = $this->fb->get('/me?fields=id,name,picture', $accessToken)->getDecodedBody();

            if(!is_string($response))
            {
                if(!empty($response))
                {
                    $result[] = [
                        'id' => $response['id'],
                        'name' => $response['name'],
                        'avatar' => $response['picture']['data']['url'],
                        'desc' => __("Profile"),
                        'link' => "https://fb.com/",
                        'oauth' => $accessToken,
                        'module' => $request->module['module_name'],
                        'reconnect_url' => $request->module['uri']."/oauth",
                        'social_network' => 'facebook',
                        'category' => 'profile',
                        'login_type' => 1,
                        'can_post' => 0,
                        'data' => "",
                        'proxy' => 0,
                    ];

                    $channels = [
                        'status' => 1,
                        'message' => __('Succeeded')
                    ];
                }
                else
                {
                    $channels = [
                        'status' => 0,
                        'message' => __('No profile to add'),
                    ];
                }
            }
            else
            {
                $channels = [
                    'status' => 0,
                    'message' => $response,
                ];
            }
        } 
        catch (\Exception $e) 
        {
            $channels = [
                'status' => 0,
                'message' => $e->getMessage(),
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
        $request->session()->forget('FB_AccessToken');
        $helper = $this->fb->getRedirectLoginHelper();
        $permissions = [ $this->scopes ];
        $login_url = $helper->getLoginUrl( module_url() , $permissions);
        return redirect($login_url);
    }
}
