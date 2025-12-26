<?php

namespace Modules\AppWatermark\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AppChannels\Models\Accounts;
use Modules\AdminUsers\Facades\UserInfo;

class AppWatermarkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('appwatermark::index');
    }

    public function load(Request $request)
    {
        $team_id = $request->input('team_id');
        $account_id = $request->input('account_id');
        $data = [];

        if ($account_id && $account_id != 0) {
            $data = \Channels::getDataAccount($account_id, 'watermark') ?? [];
        } elseif ($team_id) {
            $data = UserInfo::getDataTeam('watermark', [], $team_id) ?? [];
        }

        return response()->json([
            "status" => 1,
            "data" => view('appwatermark::load', [
                'result' => $data,
                'account_id' => $account_id
            ])->render()
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|integer',
            'position'   => 'required|string',
            'size'       => 'required|integer|min:0|max:100',
            'opacity'    => 'required|integer|min:0|max:100',
            'image'      => 'nullable|file|mimes:jpeg,jpg,png,gif,svg|max:2048',
        ]);
        
        $account_id = $validated['account_id'];
        $team_id = $request->team_id;

        $data = [
            'position' => $validated['position'],
            'size'     => $validated['size'],
            'opacity'  => $validated['opacity'],
        ];

        if ($account_id == 0 && $team_id) {
            $old = UserInfo::getDataTeam('watermark', [], $team_id);
            if (!empty($old['mark']) && !$request->hasFile('image')) {
                $data['mark'] = $old['mark'];
            }
        } elseif ($account_id != 0) {
            $old = \Channels::getDataAccount($account_id, 'watermark', []);
            if (!empty($old['mark']) && !$request->hasFile('image')) {
                $data['mark'] = $old['mark'];
            }
        }

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $mark = \UploadFile::storeSingleFile($file, 'watermarks', false, '1:1');
            if ($mark) $data['mark'] = $mark;
        }

        if ($account_id == 0 && $team_id) {
            UserInfo::setDataTeam('watermark', $data, $team_id);
            return response()->json([
                'status'    => 1,
                'message'   => __('Default watermark saved for team!'),
                'data'      => $data,
                'image_url' => !empty($data['mark']) ? asset($data['mark']) : null,
            ]);
        }

        if ($account_id != 0) {
            $account = Accounts::find($account_id);
            if ($account) {
                \Channels::setDataAccount($account_id, 'watermark', $data);
                return response()->json([
                    'status'    => 1,
                    'message'   => __('Watermark saved for account!'),
                    'data'      => $data,
                    'image_url' => !empty($data['mark']) ? asset($data['mark']) : null,
                ]);
            } else {
                return response()->json([
                    'status'  => 0,
                    'message' => __('Account not found!'),
                ], 422);
            }
        }

        return response()->json([
            'status'  => 0,
            'message' => __('Missing team or account to save watermark!'),
        ], 422);
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|integer',
            'team_id'    => 'nullable|integer',
        ]);

        $account_id = $request->input("account_id");
        $team_id = $request->team_id;
        $data = [];

        // --- Delete for Team ---
        if ($account_id == 0 && $team_id) {
            $old = UserInfo::getDataTeam('watermark', [], $team_id);
            // Remove only the 'mark' field, keep other watermark settings
            if (!empty($old)) {
                // Optionally, delete the file physically
                if (!empty($old['mark']) && file_exists(public_path($old['mark']))) {
                    @unlink(public_path($old['mark']));
                }
                unset($old['mark']);
                UserInfo::setDataTeam('watermark', $old, $team_id);
            }
            return response()->json([
                'status'  => 1,
                'message' => __('Watermark image deleted for team!'),
                'data'    => $old,
            ]);
        }

        // --- Delete for Account ---
        if ($account_id != 0) {
            $old = \Channels::getDataAccount($account_id, 'watermark', []);
            if (!empty($old)) {
                // Optionally, delete the file physically
                if (!empty($old['mark']) && file_exists(public_path($old['mark']))) {
                    @unlink(public_path($old['mark']));
                }
                unset($old['mark']);
                \Channels::setDataAccount($account_id, 'watermark', $old);
            }
            return response()->json([
                'status'  => 1,
                'message' => __('Watermark image deleted for account!'),
                'data'    => $old,
            ]);
        }

        // Error: No team or account found
        return response()->json([
            'status'  => 0,
            'message' => __('Missing team or account to delete watermark!'),
        ], 422);
    }

}
