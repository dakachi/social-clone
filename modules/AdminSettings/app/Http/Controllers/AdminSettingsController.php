<?php

namespace Modules\AdminSettings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class AdminSettingsController extends Controller
{

    public function index()
    {
        return view('adminsettings::index');
    }

    public function save(Request $request)
    {
        $posts = $request->all();

        foreach ($posts as $name => $value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            $exists = \DB::table('options')->where('name', $name)->exists();
            if ($exists) {
                \DB::table('options')->where('name', $name)->update(['value' => $value]);
            }
        }

        ms([
            "status" => 1,
            "message" => __("Succeed"),
            'redirect' => '',
        ]);
    }
}
