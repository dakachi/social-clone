<?php

namespace Modules\AppChannelLinkedinPages\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AppChannelLinkedinProfiles\Classes\LinkedinAPI;
use DB;

class AppChannelLinkedinPagesController extends Controller
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

        $this->scopes = get_option(
            "linkedin_scopes",
            'email profile w_member_social openid w_organization_social r_organization_social rw_organization_admin'
        );

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

            $person = $this->linkedin->getPerson($accessToken);
            $response = $this->linkedin->getCompanyPages($accessToken);

            if(isset($response['elements']))
            {
                foreach ($response['elements'] as $value)
                {
                    $value = $value['organizationalTarget~'];
                    if(isset($value['logoV2'])){
                        $avatar = (array)$value['logoV2'];
                        $avatar = $avatar['original~'];
                        $avatar = $avatar['elements'][0]['identifiers'][0]['identifier'];
                    }else{
                        $avatar = text2img( $value['localizedName'] );
                    }
                    
                    $result[] = [
                        'id' => $value['id'],
                        'name' => $value['localizedName'],
                        'avatar' => $avatar,
                        'desc' => $value['vanityName'],
                        'link' => 'https://linkedin.com/company/'.$value['id'],
                        'oauth' => $accessToken,
                        'module' => $request->module['module_name'],
                        'reconnect_url' => $request->module['uri']."/oauth",
                        'social_network' => 'linkedin',
                        'category' => 'page',
                        'login_type' => 1,
                        'can_post' => 1,
                        'data' => "",
                        'proxy' => 0,
                        'tmp' => $person['sub'],
                    ];
                }

                DB::table("accounts")->where( [
                    "tmp" => $person['sub'], 
                    "social_network" => "linkedin"
                ] )->update([
                    "token" => $accessToken
                ]);

                $channels = [
                    'status' => 1,
                    'message' => __('Succeeded')
                ];
            }else{
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
}
