<?php

namespace Modules\AppChannels\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Modules\AppChannels\Models\Accounts;
use Modules\AppChannels\Events\ChannelEvent;
use DB;

class AppChannelsController extends Controller
{
    public function __construct(Request $request)
    {
        $this->maxChannels = \Access::permission('max_channels');
        $this->totalAccounts = Accounts::where('team_id',  $request->team_id)->where('status', '!=', 0)->count();
    }

    public function index(Request $request)
    {
        $total = Accounts::where("team_id", $request->team_id)->count();
        
        return view('appchannels::index', [
            'total' => $total,
            'module' => $request->module,
        ]);
    }

    public function list(Request $request){
        $search = $request->input("keyword");
        $status = $request->input("status");
        $module_name = $request->input("module_name");
        $current_page = $request->input("page") + 1;
        $per_page = 30;

        $wheres = ["team_id" => $request->team_id ];

        if($module_name != "" && Module::find($module_name))
        {
            $wheres['module'] = $module_name;
        }

        switch ($status) {
            case 0:
                $wheres['status'] = 0;
                break;

            case 1:
                $wheres['status'] = 1;
                break;

            case 2:
                $wheres['status'] = 2;
                break;
        }

        Paginator::currentPageResolver(function () use ($current_page){
            return $current_page;
        });

        $query = Accounts::where($wheres);

        if($search){
            $query->whereAny(['name', 'username', 'social_network'], 'like', '%'.$search.'%');
        }

        $channels = $query->orderByDesc('created')->paginate(30);

        if( $channels->Total() == 0 && $current_page > 1 ){
            ms([
                "status" => 0
            ]);
        }

        $module_data = [];
        foreach ($channels as $key => $channel) {
            if( !isset( $module_data[$channel->social_network] ) )
            {
                if($module = Module::find($channel->module))
                {
                    $module_data[$channel->social_network] = $module->get("menu");
                }
            }

            $channels[$key]->module_item = $module_data[$channel->social_network];
        }

        ms([
            "status" => 1,
            "data" => view('appchannels::list',[
                "channels" => $channels
            ])->render()
        ]);
    }

    public function status(Request $request, $status = "active")
    {
        $ids = $request->input('id');
        $id_arr = [];

        if(empty($ids)){
            return ms([ 
                "status" => 0,
                "message" => __("Please select at least one item"),
            ]);
        }

        if(is_string($ids)){
            $ids = [$ids];
        }

        foreach ($ids as $value) 
        {
            $id_key = $value;
            if($id_key != 0){
                $id_arr[] = $id_key;
            }
        }

        switch ($status) 
        {
            case 'active':
                $status = 1;
                break;

            case 'pause':
                $status = 2;
                break;
            
            default:
                $status = 0;
                break;
        }

        DB::table('accounts')
            ->whereIn('id_secure', $id_arr)
            ->update(['status' => $status]);

        ms(["status" => 1, "message" => "Succeeded"]);
    }

    public function add(Request $request)
    {
        $result = session("channels"); 

        if (!$result) 
        {
            return redirect( module_url() );
        }

        if( !\Channels::checkCanAddAccounts( $result['module']['module_name'] ) ){
            $result = array_merge($result, [
                'status' => 0,
                'message' => __("You have added the maximum number of allowed channels.")
            ]);
        }

        return view('appchannels::add', [
            'result' => $result
        ]);
    }

    public function save(Request $request)
    {
        $result = session('channels'); 
        $channels = $request->input("channels");
        $team_id = $request->team_id;

        if (!$result || !isset($result['channels']) || !isset($result['module'])) 
        {
            ms([
                'status' => 0,
                'message' => __("Unknown error")
            ]);
        }

        if(!$channels)
        {
            ms([
                'status' => 0,
                'message' => __("Please select at least 1 channel.")
            ]);
        }

        $module = $result['module'];
        $accept_channels = Arr::keyBy($result['channels'], 'id');
        $accept_channel_id_arr = array_column($accept_channels,'id');

        foreach ($channels as $key => $channel_id) 
        {
            if( in_array( $channel_id, $accept_channel_id_arr) )
            {
                $channel = $accept_channels[$channel_id];
                $channel_item = Accounts::where([
                    "pid" => $channel_id, 
                    "login_type" => $channel['login_type'], 
                    "team_id" => $team_id
                ])->get()->first();

                $avatar_url = 'channel_avatars/'.rand_string(10).'.png';


                try 
                {
                    $avatar_url = \UploadFile::storeSingleFileFromURL($channel['avatar'], 'channel_avatars');
                } 
                catch (\Exception $e) 
                {
                    $avatarRandom = text2img($channel['name'], 'rand');
                    $avatar_url = \UploadFile::storeSingleFileFromURL($avatarRandom, 'channel_avatars');
                }

                $oauth = $channel['oauth'];
                if(is_array($channel['oauth']) || is_object($channel['oauth'])){
                    $oauth = json_encode($channel['oauth']);
                }

                $data = [
                    'module' => $channel['module'],
                    'social_network' => $channel['social_network'],
                    'category' => $channel['category'],
                    'reconnect_url' => $channel['reconnect_url'],
                    'login_type' => $channel['login_type'],
                    'can_post' => $channel['can_post'],
                    'team_id' => $team_id,
                    'pid' => $channel['id'],
                    'name' => $channel['name'],
                    'username' => $channel['username']??$channel['name'],
                    'token' => $oauth,
                    'avatar' => $avatar_url,
                    'url' => $channel['link'],
                    'data' => isset($channel['data'])?$channel['data']:"",
                    'proxy' => isset($channel['proxy'])?(int)$channel['proxy']:0,
                    'tmp' => isset($channel['tmp'])?$channel['tmp']:"",
                    'status' => 1,
                    'changed' => time()
                ];

                if( !empty($channel_item) )
                {
                    \UploadFile::deleteFileFromServer($channel_item->avatar);
                    Accounts::where("id", $channel_item->id)->update($data);
                }
                else
                {
                    if( !\Channels::checkCanAddAccounts( $module ) ){
                        ms([
                            'status' => 0,
                            'message' => __("You have added the maximum number of allowed channels.")
                        ]);
                    }

                    $data = array_merge($data, [
                        "id_secure" => rand_string(),
                        "created" => time()
                    ]);
                    Accounts::create($data);
                }

                event(new ChannelEvent("add", $data));
            }
        }

        $request->session()->forget('channels');

        ms([
            'status' => 1,
            'message' => __("Succeeded"),
            'redirect' => module_url()
        ]);
    }

    public function destroy(Request $request)
    {
        $id_arr = id_arr( $request->input('id') );
        if(empty($id_arr))
              ms(["status" => 0, "message" => __("Please select at least one item")]);

        foreach ($id_arr as $key => $id) {
            $channel_item = Accounts::where([
                "id_secure" => $id, 
                "team_id" => $request->team_id
            ])->get()->first();

            \UploadFile::deleteFileFromServer($channel_item->avatar);
        }

        Accounts::whereIn('id_secure', $id_arr)->delete();
        ms(["status" => 1, "message" => __("Succeeded")]);
    }
}
