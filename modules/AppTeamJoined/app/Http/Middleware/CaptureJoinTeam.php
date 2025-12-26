<?php

namespace Modules\AppTeamJoined\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\AdminUsers\Models\Teams;
use Modules\AdminUsers\Models\TeamMembers;

class CaptureJoinTeam
{
    public function handle(Request $request, Closure $next)
    {
        
        if (auth()->check() && session()->has('pending_invite_token')) {
            $inviteToken = session('pending_invite_token');
            $member = TeamMembers::where('invite_token', $inviteToken)
                ->where('status', 0)
                ->first();

            if ($member) {
                $user = auth()->user();

                $team = Teams::find($member->team_id);
                $already = TeamMembers::where('team_id', $member->team_id)
                    ->where('uid', $user->id)
                    ->where('status', 1)
                    ->first();

                if ($team && $user->id != $team->owner && !$already) {
                    $member->uid = $user->id;
                    $member->status = 1;
                    $member->pending = null;
                    $member->invite_token = null;
                    $member->save();

                    session()->forget('pending_invite_token');

                    session()->flash('success', __('You have joined the team!'));
                }
            }
        }

        return $next($request);
    }
}
