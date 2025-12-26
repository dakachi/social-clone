<?php

namespace Modules\AppChannelYoutubeChannels\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Client;
use Google\Service\YouTube;
use Google\Service\Oauth2;

class AppChannelYoutubeChannelsController extends Controller
{
    public $youtube;
    protected $client;
    protected $client_id;
    protected $client_secret;
    protected $api_key;
    protected $callback_url;

    public function __construct()
    {
        \Access::check('appchannels.' . module('key'));

        $scopes = [
            'https://www.googleapis.com/auth/youtube', 
            'https://www.googleapis.com/auth/userinfo.email'
        ];

        if (\Module::find('AppAnalyticsYoutube')) {
            $scopes[] = "https://www.googleapis.com/auth/yt-analytics.readonly";
        }
        
        $this->client_id = get_option("youtube_client_id", "");
        $this->client_secret = get_option("youtube_client_secret", "");
        $this->api_key = get_option("youtube_api_key", "");
        $this->callback_url = module_url(); 

        if (!$this->client_id || !$this->client_secret || !$this->api_key) {
            \Access::deny(__('To use YouTube, you must first configure the client ID, client secret, and API key.'));
        }

        try {
            $this->client = new \Google_Client();
            $this->client->setApprovalPrompt("force");
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');
            $this->client->setApplicationName("YouTube");
            $this->client->setClientId($this->client_id);
            $this->client->setClientSecret($this->client_secret);
            $this->client->setRedirectUri($this->callback_url);
            $this->client->setDeveloperKey($this->api_key);
            $this->client->setScopes($scopes);

            $this->client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));

            $this->youtube = new \Google_Service_YouTube($this->client);
        } catch (\Exception $e) {
            \Log::error('YouTube SDK init error', ['error' => $e->getMessage()]);
            \Access::deny(__('Could not connect to YouTube API: ') . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $result = [];

        try 
        {
            if( !session("YT_AccessToken") )
            {
                if(!$request->code)
                {
                    return redirect( module_url("oauth") );
                }

                $accessToken = $this->client->fetchAccessTokenWithAuthCode($request->code);
                session(["YT_AccessToken" => $accessToken]);
                return redirect( $this->callback_url );
            }
            else
            {
                $accessToken = session("YT_AccessToken"); 
            }

            $this->client->setAccessToken($accessToken);

            $part = 'brandingSettings,status,id,snippet,contentDetails,contentOwnerDetails,statistics';
            $optionalParams = array(
                'mine' => true
            );

            $response = $this->youtube->channels->listChannels($part, $optionalParams);

            if(!empty($response))
            {
                if(!empty($response->getItems()))
                {
                    foreach ($response->getItems() as $value) 
                    {
                        $result[] = [
                            'id' => $value->getId(),
                            'name' => $value->getSnippet()->getLocalized()->getTitle(),
                            'avatar' => $value->getSnippet()->getThumbnails()->getDefault()->getUrl(),
                            'desc' => $value->getSnippet()->getLocalized()->getDescription(),
                            'link' => 'https://www.youtube.com/channel/'.$value->getId(),
                            'oauth' => $accessToken,
                            'module' => $request->module['module_name'],
                            'reconnect_url' => $request->module['uri']."/oauth",
                            'social_network' => 'youtube',
                            'category' => 'channel',
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
                    $channels = [
                        'status' => 0,
                        'message' => __('No profile to add'),
                    ];
                }
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
            'oauth' => session("YT_AccessToken")
        ]);

        session( ['channels' => $channels] );
        return redirect( url_app("channels/add") );
    }

    public function oauth(Request $request)
    {
        $request->session()->forget('YT_AccessToken');
        $login_url = $this->client->createAuthUrl();
        return redirect($login_url);
    }

    public function settings(){
        return view('appchannelyoutubechannels::settings');
    }
}
