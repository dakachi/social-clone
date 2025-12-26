<?php

namespace Modules\AppChannelFacebookPages\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JanuSoftware\Facebook\Facebook;

class AppChannelFacebookPagesController extends Controller
{
    public $fb;
    public function __construct()
    {
        \Access::check('appchannels.' . module('key'));

        $appId  = get_option("facebook_app_id", "");
        $appSecret  = get_option("facebook_app_secret", "");
        $appVersion  = get_option("facebook_graph_version", "v22.0");
        $appPermissions  = get_option("facebook_page_permissions", "pages_read_engagement,pages_manage_posts,pages_show_list,business_management");
        if(!$appId || !$appSecret || !$appVersion || !$appPermissions){
            \Access::deny( __('To use Facebook Pages, you must first configure the app ID, app secret, app permissions and app version.') );
        }

        try {
            $this->fb = new Facebook([
                'app_id' => $appId,
                'app_secret' => $appSecret,
                'default_graph_version' => $appVersion,
            ]);
        } catch (\Exception $e) {}

        $this->scopes = $appPermissions;
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
                
                try {
                    $accessToken = $helper->getAccessToken($callback_url);
                    if ($accessToken) {
                        $accessTokenValue = $accessToken->getValue();
                        session( ['FB_AccessToken' => $accessTokenValue] );
                        return redirect( $callback_url );
                    } else {
                        throw new \Exception('Failed to get access token');
                    }
                } catch (\Exception $e) {
                    throw $e;
                }
            }
            else
            {
                $accessToken = session("FB_AccessToken");
                
                // Verify token is still valid by checking user info first
                try {
                    $me = $this->fb->get('/me', $accessToken)->getDecodedBody();
                } catch (\Exception $e) {
                    // Token is invalid, clear it and force re-auth
                    $request->session()->forget('FB_AccessToken');
                    return redirect( module_url("oauth") );
                }
            }
            
            try {
                $response = $this->fb->get('/me/accounts?fields=id,name,username,fan_count,link,is_verified,picture,access_token,category&limit=10000', $accessToken)->getDecodedBody();
                
                if(is_string($response))
                {
                    $response = $this->fb->get('/me/accounts?fields=id,name,username,fan_count,link,is_verified,picture,access_token,category&limit=3', $accessToken)->getDecodedBody();
                }

                if(!is_string($response))
                {
                    if(!empty($response))
                    {
                        if(isset($response['data']) && !empty($response['data']))
                        {
                            foreach ($response['data'] as $value) 
                            {
                                $result[] = [
                                    'id' => $value['id'],
                                    'name' => $value['name'],
                                    'avatar' => $value['picture']['data']['url']??text2img($response['name'], 'rand'),
                                    'desc' => $value['category'],
                                    'link' => $value['link'],
                                    'oauth' => $value['access_token'],
                                    'module' => $request->module['module_name'],
                                    'reconnect_url' => $request->module['uri']."/oauth",
                                    'social_network' => 'facebook',
                                    'category' => 'page',
                                    'login_type' => 1,
                                    'can_post' => 1,
                                    'data' => "",
                                    'proxy' => 0
                                ];
                            }

                            $channels = [
                                'status' => 1,
                                'message' => __("Succeeded")
                            ];
                        }
                        else
                        {
                            // Check if this is due to revoked permissions
                            // If we have a token but empty data, likely permissions were revoked
                            if (session()->has('FB_AccessToken')) {
                                $request->session()->forget('FB_AccessToken');
                                
                                $channels = [
                                    'status' => 0,
                                    'message' => __('Facebook permissions were revoked. Please reconnect your Facebook account.'),
                                ];
                                
                                // Set session and redirect to add page with error, which will show reconnect button
                                $channels = array_merge($channels, [
                                    'channels' => [],
                                    'module' => $request->module,
                                    'save_url' => url_app('channels/save'),
                                    'reconnect_url' => module_url('oauth'),
                                ]);
                                session( ['channels' => $channels] );
                                return redirect( url_app("channels/add") );
                            }
                            
                            $channels = [
                                'status' => 0,
                                'message' => __('No profile to add'),
                            ];
                        }
                    }
                    else
                    {
                        // Same check for revoked permissions
                        if (session()->has('FB_AccessToken')) {
                            $request->session()->forget('FB_AccessToken');
                            
                            $channels = [
                                'status' => 0,
                                'message' => __('Facebook permissions were revoked. Please reconnect your Facebook account.'),
                            ];
                            
                            $channels = array_merge($channels, [
                                'channels' => [],
                                'module' => $request->module,
                                'save_url' => url_app('channels/save'),
                                'reconnect_url' => module_url('oauth'),
                            ]);
                            session( ['channels' => $channels] );
                            return redirect( url_app("channels/add") );
                        }
                        
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
            } catch (\Exception $apiException) {
                throw $apiException;
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

    public function settings(){
        return view('appchannelfacebookpages::settings');
    }
}
