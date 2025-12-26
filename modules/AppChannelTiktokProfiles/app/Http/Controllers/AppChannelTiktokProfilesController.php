<?php

namespace Modules\AppChannelTiktokProfiles\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use gimucco\TikTokLoginKit;

class AppChannelTiktokProfilesController extends Controller
{
    public $tiktok;
    protected $app_id;
    protected $app_secret;
    protected $callback_url;
    protected $scopes;

    public function __construct()
    {
        \Access::check('appchannels.' . module('key'));
        
        $this->app_id = get_option("tiktok_app_id", "");
        $this->app_secret = get_option("tiktok_app_secret", "");
        $this->callback_url = module_url();
        $this->scopes = get_option("tiktok_scopes", "user.info.basic,user.info.profile,user.info.stats,video.list,video.publish");

        if (!$this->app_id || !$this->app_secret) {
            \Access::deny(__('To use TikTok, you must first configure the app ID and app secret.'));
        }

        // Log check for TikTok app id
        if (empty($this->app_id)) {
            \Log::warning('TikTok App ID is empty during controller init.');
        } else {
            \Log::info('TikTok App ID detected in config: ' . $this->app_id);
        }

        try {
            $this->tiktok = new TikTokLoginKit\Connector($this->app_id, $this->app_secret, $this->callback_url);
        } catch (\Exception $e) {
            \Log::error('TikTok SDK init error', ['error' => $e->getMessage()]);
            \Access::deny(__('Could not connect to TikTok API: ') . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $result = [];
        try 
        {
            if( !session("Tiktok_AccessToken") )
            {
                if(!$request->code)
                {
                    return redirect( module_url("oauth") );
                }

                $token = $this->tiktok->verifyCode( $request->code );

                if ( $token->getAccessToken() ) 
                {
                    session( ['Tiktok_AccessToken' => 
                        [
                            "access_token" => $token->getAccessToken(),
                            "refresh_token" => $token->getRefreshToken(),
                            "expires_in" => $token->getExpiresIn(),
                            "refresh_expires_in" => $token->getRefreshExpiresIn(),
                            "scope" => implode(",", $token->getScope()),
                            "token_type" => $token->getTokenType(),
                        ] 
                    ] );
                }
                return redirect( $this->callback_url );
            }
            else
            {
                $accessToken = session("Tiktok_AccessToken"); 
            }

            $this->tiktok->setToken($accessToken['access_token']);

            $response = $this->tiktok->getUser();

            if(!empty($response) && $response->getDisplayName() != "")
            {
                $result[] = [
                    'id' => $response->getOpenID(),
                    'name' => $response->getDisplayName(),
                    'avatar' => $response->getBestAvatar(),
                    'desc' => __("Profile"),
                    'link' => "https://www.tiktok.com/@" . $response->getDisplayName(),
                    'oauth' => $accessToken,
                    'module' => $request->module['module_name'],
                    'reconnect_url' => $request->module['uri']."/oauth",
                    'social_network' => 'tiktok',
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
            else
            {
                $channels = [
                    'status' => 0,
                    'message' => __('No profile to add'),
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
            'oauth' => session("Tiktok_AccessToken")
        ]);

        session( ['channels' => $channels] );
        return redirect( url_app("channels/add") );
    }

    public function oauth(Request $request)
    {
        $request->session()->forget('Tiktok_AccessToken');
        $login_url = $this->tiktok->getRedirect(explode(',', $this->scopes));
        return redirect($login_url);
    }

    public function settings(){
        return view('appchanneltiktokprofiles::settings');
    }
}
