<?php

namespace Modules\AppPublishingDraft\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AppPublishing\Models\Posts;
use Illuminate\Pagination\Paginator;

class AppPublishingDraftController extends Controller
{
    public function index()
    {
        return view('apppublishingdraft::index');
    }

    public function list(Request $request)
    {
        $search = $request->input('keyword');
        $status = $request->input('status');
        $current_page = (int) $request->input('page', 0) + 1;
        $per_page = 30;

        $teamId = $request->input('team_id') ?? (auth()->user()->team_id ?? null);

        Paginator::currentPageResolver(function () use ($current_page) {
            return $current_page;
        });

        $query = Posts::where('team_id', $teamId)->where('status', 1);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('url', 'like', '%' . $search . '%')
                  ->orWhere('title', 'like', '%' . $search . '%')
                  ->orWhere('desc', 'like', '%' . $search . '%');
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        $schedules = $query->orderByDesc('changed')->paginate($per_page);

        if ($schedules->total() == 0 && $current_page > 1) {
            return response()->json([
                "status" => 0,
                "message" => __("No data found."),
            ]);
        }

        return response()->json([
            "status" => 1,
            "data" => view(module('key') . '::list', [
                "schedules" => $schedules
            ])->render()
        ]);
    } 
}
