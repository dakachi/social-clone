<?php

namespace Modules\AppChannels\Facades;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Facade;
use Modules\AppChannels\Models\Accounts;
use DB;

class Channels extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return 'channels';
    }

    protected static function checkCanAddAccounts($module)
    { 
        $maxChannels = \Access::permission('max_channels');

        $channelCalculateBy = \Access::permission('channel_calculate_by');

        if($channelCalculateBy == 1){
            $totalAccounts = Accounts::where('team_id', request()->team_id)->where('status', '!=', 0)->where('module', $module)->count();
        }else{
            $totalAccounts = Accounts::where('team_id', request()->team_id)->where('status', '!=', 0)->count();
        }

        if($maxChannels <= $totalAccounts && $maxChannels != -1){
            return false;
        }

        return true;
    }

    protected static function list($data, $wheres = [], $field = "id_secure")
    { 
        if(empty($data)) return false;

        $Query = DB::table("accounts");

        if($data){
            $Query->whereIn($field, $data);
        }

        if($wheres){
            $Query->where($wheres);
        }

        $Query->where("team_id", request()->team_id);

        return $Query->get();
        
    }

    protected static function all($wheres = [])
    { 
        $Query = DB::table("accounts");

        if($wheres){
            $Query->where($wheres);
        }

        $Query->where("team_id", request()->team_id);

        return $Query->get();
        
    }

    protected static function addChannel($module_name, $channel) 
    {
        $module = \Module::find($module_name);
        $menu = $module->get('menu');

        if ($menu) {
            $channel = array_merge($channel, [
                'uri' => $menu['uri'] . '/oauth',
                'icon' => $menu['icon'],
                'color' => $menu['color'],
                'id' => $module->getName(),
                'key' => $module->getLowerName(),
                'module_name' => $menu['name'],
            ]);

            // Get current instance channels or fallback to empty array
            $channels = app()->bound('channels') ? app('channels') : [];

            $channels[] = $channel;

            // Bind updated channels array back into the container
            app()->instance('channels', $channels);
        }
    }

    protected static function channels($permission = 'appchannels') 
    {
        try {
            $channels = app('channels');
            $channels_group = [];
            if ($channels) 
            {
                $channels = array_values(\Arr::sort($channels, function (array $value) {
                    return $value['position'];
                }));

                foreach ($channels as $key => $channel) 
                {
                    if (Gate::allows($permission. '.' . $channel['key'])) {
                        if( !isset( $channels_group[$channel['social_network']] ) )
                        {
                            $channel_parent = $channel;
                            \Arr::pull($channel_parent, 'uri');
                            \Arr::pull($channel_parent, 'category');
                            \Arr::pull($channel_parent, 'id');

                            $channels_group[$channel['social_network']] = $channel_parent;
                        }

                        $channel_child = $channel;
                        \Arr::pull($channel_child, 'name');
                        \Arr::pull($channel_child, 'social_network');
                        \Arr::pull($channel_child, 'position');
                        \Arr::pull($channel_child, 'icon');
                        \Arr::pull($channel_child, 'color');
                        $channels_group[$channel['social_network']]['items'][] = $channel_child;
                    }
                }
            }   

            return $channels_group;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function setDataAccount($accountId, $key, $value)
    {
        $account = Accounts::find($accountId);
        if (!$account) return false;
        $data = is_array($account->data) ? $account->data : (json_decode($account->data, true) ?: []);
        $data[$key] = $value;
        $account->data = $data;
        $account->save();
        return true;
    }

    public static function getDataAccount($accountId, $key = null, $default = null)
    {
        $account = Accounts::find($accountId);
        if (!$account) return $default;
        $data = is_array($account->data) ? $account->data : (json_decode($account->data, true) ?: []);
        if ($key === null) return $data;
        return $data[$key] ?? $default;
    }
}


