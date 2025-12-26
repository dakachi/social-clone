<?php

namespace Modules\AppChannelInstagramProfiles\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JanuSoftware\Facebook\Facebook;

class AppChannelInstagramProfilesController extends Controller
{
    public $fb;
    public $scopes;

    public function __construct()
    {
        \Access::check('appchannels.' . module('key'));

        $appId = get_option("instagram_app_id", "");
        $appSecret = get_option("instagram_app_secret", "");
        $appVersion = get_option("instagram_graph_version", "v21.0");
        $appPermissions = get_option(
            "instagram_permissions", 
            "instagram_basic,instagram_content_publish,pages_read_engagement,pages_show_list,instagram_manage_insights,business_management"
        );

        if(!$appId || !$appSecret || !$appVersion || !$appPermissions){
            \Access::deny(__('To use Instagram, you must first configure the app ID, app secret, app permissions and app version.'));
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
                $accessToken = $helper->getAccessToken($callback_url);
                $accessToken = $accessToken->getValue();
                session( ['FB_AccessToken' => $accessToken] );
                return redirect( $callback_url );
            }
            else
            {
                $accessToken = session("FB_AccessToken"); 
            }

            $response = $this->fb->get('/me/accounts?fields=instagram_business_account,id,name,username,fan_count,link,is_verified,picture,access_token,category&limit=10000', $accessToken)->getDecodedBody();
            if(is_string($response))
            {
                $response = $this->fb->get('/me/accounts?fields=instagram_business_account,id,name,username,fan_count,link,is_verified,picture,access_token,category&limit=3', $accessToken)->getDecodedBody();
            }

            $page_ids = [];
            if(isset($response['data']) && !empty($response['data']))
            {
                foreach ($response['data'] as $value) 
                {
                    if(isset($value['instagram_business_account']))
                    {
                        $page_ids[] = $value['instagram_business_account']['id'];
                    }
                }
            }

            if(empty($page_ids))
            {
                $channels = [
                    'status' => 0,
                    'message' => __('No profile to add')
                ];
            }
            else
            {
                if(!empty($page_ids))
                {
                    foreach ($page_ids as $key => $page_id) 
                    {
                        $response = $this->fb->get('/'.$page_id.'?fields=id,name,username,profile_picture_url,ig_id', $accessToken)->getDecodedBody();

                        $result[] = [
                            'id' => $response['id'],
                            'name' => isset($response['name'])?$response['name']:$response['username'],
                            'username' => $response['username'],
                            'avatar' => $response['profile_picture_url']??text2img($response['username'], 'rand'),
                            'desc' => isset($response['name'])?$response['name']:$response['username'],
                            'link' => 'https://www.instagram.com/'.$response['username'],
                            'oauth' => $accessToken,
                            'module' => $request->module['module_name'],
                            'reconnect_url' => $request->module['uri']."/oauth",
                            'social_network' => 'instagram',
                            'category' => 'profile',
                            'login_type' => 1,
                            'can_post' => 1,
                            'data' => "",
                            'proxy' => 0,
                        ];
                    }
                }

                $channels = [
                    'status' => 1,
                    'message' => __('Succeeded')
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

    public function settings(){
        return view('appchannelinstagramprofiles::settings');
    }
}
