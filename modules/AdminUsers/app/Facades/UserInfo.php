<?php
namespace Modules\AdminUsers\Facades;

use Carbon\Carbon;
use Illuminate\Support\Facades\Facade;
use App\Models\User;
use Modules\AdminUsers\Models\Teams;

class UserInfo extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'UserInfo';
    }

    public static function getTeamPermission($key, $default = null, $teamId = null)
    {
        $teamId = $teamId ?? request()->team_id;
        $team = \Modules\AdminUsers\Models\Teams::find($teamId);
        if (!$team) return $default;

        $permissions = $team->permissions ?: [];

        foreach ($permissions as $item) {
            if (isset($item['key']) && $item['key'] == $key) {
                return $item['value'];
            }
        }
        return $default;
    }

    public static function setDataTeam($key, $value, $teamId = null)
    {
        $teamId = $teamId ?? request()->team_id;
        $team = Teams::find($teamId);
        if (!$team) return false;
        $data = is_array($team->data) ? $team->data : (json_decode($team->data, true) ?: []);
        $data[$key] = $value;
        $team->data = $data;
        $team->save();
        return true;
    }

    public static function getDataTeam($key = null, $default = null, $teamId = null)
    {
        $teamId = $teamId ?? request()->team_id;
        $team = Teams::find($teamId);
        if (!$team) return $default;
        $data = is_array($team->data) ? $team->data : (json_decode($team->data, true) ?: []);
        if ($key === null) return $data;
        return $data[$key] ?? $default;
    }

    public static function setDataUser($key, $value, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        if (!$userId) return false;
        $user = User::find($userId);
        if (!$user) return false;
        $data = is_array($user->data) ? $user->data : (json_decode($user->data, true) ?: []);
        $data[$key] = $value;
        $user->data = $data;
        $user->save();
        return true;
    }

    public static function getDataUser($key = null, $default = null, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        if (!$userId) return false;
        $user = User::find($userId);
        if (!$user) return false;
        $data = is_array($user->data) ? $user->data : (json_decode($user->data, true) ?: []);
        if ($key === null) return $data;
        return $data[$key] ?? $default;
    }

    public static function getJoinedTeams($userId = null)
    {
        $userId = $userId ?? auth()->id();
        if (!$userId) return collect();

        $user = User::find($userId);
        if (!$user) return collect();

        $teams = $user->teams()
            ->wherePivot('status', 1)
            ->orderByDesc('teams.id')
            ->get();

        $teams->load('ownerUser');

        return $teams->map(function($team) {
            return [
                'name'        => $team->name ?? optional($team->ownerUser)->fullname,
                'id'          => $team->id,
                'id_secure'   => $team->id_secure,
                'owner_id'    => $team->owner,
            ];
        });
    }
}
