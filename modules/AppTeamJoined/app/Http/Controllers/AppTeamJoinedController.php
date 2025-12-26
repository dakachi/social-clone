<?php

namespace Modules\AppTeamJoined\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\AdminUsers\Models\Teams;
use Modules\AdminUsers\Models\TeamMembers;
use App\Models\User;

class AppTeamJoinedController extends Controller
{
    /**
     * Show list of teams the current user has joined (not owner)
     */
    public function index(Request $request)
    {
        $teams = \UserInfo::getJoinedTeams();
        $total = $teams->count();
        return view('appteamjoined::joined_teams', [
            'total' => $total
        ]);
    }

    public function list(Request $request)
    {
        $search       = $request->input("keyword");
        $status       = $request->input("status");
        $current_page = (int)$request->input("page", 0) + 1;
        $per_page     = 10;

        $user = auth()->user();

        $query = TeamMembers::where('uid', $user->id)
            ->where('status', 1);

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        Paginator::currentPageResolver(function () use ($current_page) {
            return $current_page;
        });

        $team_member_rows = $query->get();

        $teamIds = $team_member_rows->pluck('team_id')->toArray();

        $teamsQuery = Teams::whereIn('id', $teamIds)
            ->where('owner', '!=', $user->id);

        if ($search) {
            $teamsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        $teams = $teamsQuery->orderByDesc('id')->paginate($per_page);

        if ($teams->total() == 0 && $current_page > 1) {
            return ms([
                "status" => 0
            ]);
        }

        return response()->json([
            "status" => 1,
            "data"   => view('appteamjoined::list', [
                "teams" => $teams
            ])->render()
        ]);
    }

   /**
     * Leave a team you joined (using id_secure)
     */
    public function leaveTeam(Request $request)
    {
        $team_id_secure = $request->input("id");

        $user = auth()->user();

        $team = Teams::where('id_secure', $team_id_secure)->firstOrFail();

        if ($team->owner == $user->id) {
            return response()->json([
                "status" => 0,
                "message" => __("You are the owner and cannot leave this team.")
            ]);
        }

        $member = TeamMembers::where('team_id', $team->id)
            ->where('uid', $user->id)
            ->first();

        if (!$member) {
            return response()->json([
                "status" => 0,
                "message" => __("You are not a member of this team.")
            ]);
        }

        $member->delete();

        return response()->json([
            "status" => 1,
            "message" => __("You have left the team.")
        ]);
    }

    public function openTeam(Request $request)
    {
        $id_secure = $request->input('value') ?? $request->value;
        $id_secure = $id_secure ?? $request->id;

        $user = auth()->user();

        $team = Teams::where('id_secure', $id_secure)->first();

        if (!$team) {
            $team = Teams::where('owner', $user->id)->first();
            if (!$team) {
                return response()->json([
                    "status" => 0,
                    "message" => __("Team does not exist and you are not the owner of any team.")
                ]);
            }
        }

        $isOwner = $team->owner == $user->id;
        if (!$isOwner) {
            $member = TeamMembers::where('team_id', $team->id)
                ->where('uid', $user->id)
                ->where('status', 1)
                ->first();

            if (!$member) {
                return response()->json([
                    "status" => 0,
                    "message" => __("You do not have permission to access this team.")
                ]);
            }

            session(['current_team_id' => $team->id]);
            session(['current_team_secure' => $team->id_secure]);
            session(['current_team_name' => $team->name]);
        }else{
            session()->forget('current_team_id');
            session()->forget('current_team_secure');
            session()->forget('current_team_name');
        }

        return response()->json([
            "status" => 1,
            "message" => __("Switched to this team."),
            "redirect" => url("app/dashboard")
        ]);
    }

    public function logoutTeam(Request $request)
    {
        session()->forget(['current_team_id', 'current_team_secure', 'current_team_name']);

        return response()->json([
            "status" => 1,
            "message" => __("You have logged out from the team."),
            "redirect" => url('app/teams/joined')
        ]);
    }

    public function joinTeam(Request $request)
    {
        $inviteToken = $request->query('token');
        if (!$inviteToken) {
            return redirect( url('') .'?error=1&message=' . urlencode(__('Invalid invite link.')));
        }

        $member = TeamMembers::where('invite_token', $inviteToken)
            ->where('status', 0)
            ->first();

        if (!$member) {
            return redirect( url('') .'?error=1&message=' . urlencode(__('This invite is invalid or has expired.')));
        }

        if (!auth()->check()) {
            session(['pending_invite_token' => $inviteToken]);
            return redirect( url('auth/login'). '?info=1&message=' . urlencode(__('Please log in to join the team.')));
        }

        $user = auth()->user();
        $team = Teams::find($member->team_id);

        // Check if user is the owner
        if ($team && $user->id == $team->owner) {
            return redirect()->route("app.dashboard")->with('warning', __('You are already the owner of this team.'));
        }

        // Check if user is already a member
        $already = TeamMembers::where('team_id', $member->team_id)
            ->where('uid', $user->id)
            ->where('status', 1)
            ->first();
        if ($already) {
            return redirect()->route("app.dashboard")->with('success', __('You are already a member of this team.'));
        }

        // Accept invite
        $member->uid = $user->id;
        $member->status = 1;
        $member->pending = null;
        $member->invite_token = null;
        $member->save();

        return redirect()->route("app.teams.joined")->with('success', __('You have joined the team!'));
    }

}
