<?php

namespace Modules\AppChannelLinkedinProfiles\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AppChannelLinkedinProfiles\Classes\LinkedinAPI;
use DB;

class AppChannelLinkedinProfilesController extends Controller
{
    public $linkedin;
    protected $app_id;
    protected $app_secret;
    protected $callback_url;
    protected $scopes;

    public function __construct()
    {
        \Access::check('appchannels.' . module('key'));

        $this->app_id = get_option("linkedin_app_id", "");
        $this->app_secret = get_option("linkedin_app_secret", "");
        $this->callback_url = module_url();
        $this->scopes = 'email profile w_member_social openid';

        if (!$this->app_id || !$this->app_secret) {
            \Access::deny(__('To use LinkedIn, you must first configure the app ID and app secret.'));
        }

        try {
            $this->linkedin = new LinkedinAPI(
                $this->app_id,
                $this->app_secret,
                $this->callback_url,
                $this->scopes,
                false 
            );
        } catch (\Exception $e) {
            \Log::error('LinkedIn SDK init error', ['error' => $e->getMessage()]);
            \Access::deny(__('Could not connect to LinkedIn API: ') . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $result = [];
        try 
        {
            if( !session("Linkedin_AccessToken") )
            {
                if(!$request->code)
                {
                    return redirect( module_url("oauth") );
                }

                $response = $this->linkedin->getAccessToken($request->code);

                if ( isset($response['accessToken']) ) 
                {
                    session(["Linkedin_AccessToken" => $response['accessToken']]);

                }

                return redirect( $this->callback_url );
            }
            else
            {
                $accessToken = session('Linkedin_AccessToken'); 
            }

            $response = $this->linkedin->getPerson($accessToken);

            if ( isset($response['sub']) ) 
            {
                
                if(isset($response['picture']))
                {
                    $avatar = $response['picture'];
                }
                else
                {
                    $avatar = text2img( $response['name'] );
                }

                $result[] = [
                    'id' => $response['sub'],
                    'name' => $response['name'],
                    'avatar' => $avatar,
                    'desc' => __("Profile"),
                    'link' => 'https://www.linkedin.com/',
                    'oauth' => $accessToken,
                    'module' => $request->module['module_name'],
                    'reconnect_url' => $request->module['uri']."/oauth",
                    'social_network' => 'linkedin',
                    'category' => 'profile',
                    'login_type' => 1,
                    'can_post' => 1,
                    'data' => "",
                    'proxy' => 0,
                    'tmp' => $response['sub'],
                ];

                DB::table("accounts")->where( [
                    "tmp" => $response['sub'], 
                    "social_network" => "linkedin"
                ] )->update([
                    "token" => $accessToken
                ]);

                $channels = [
                    'status' => 1,
                    'message' => __('Succeeded')
                ];
            }
            else
            {
                $channels = [
                    'status' => 0,
                    'message' => $response['message']
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
            'oauth' => session("Linkedin_AccessToken")
        ]);

        session( ['channels' => $channels] );
        return redirect( url_app("channels/add") );
    }

    public function oauth(Request $request)
    {   
        $request->session()->forget('Linkedin_AccessToken');
        $login_url = $this->linkedin->getAuthUrl();
        return redirect($login_url);
    }

    public function settings(){
        return view('appchannellinkedinprofiles::settings');
    }
}
