<?php

namespace Modules\AdminPrivacyPolicy\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminPrivacyPolicyController extends Controller
{
    public function settings()
    {
        return view(module("key").'::index');
    }
    public function save(Request $request)
    {
        $posts = $request->all();
        foreach ($posts as $name => $value)
        {
            if(is_string($value) || $value==""){
                DB::table('options')->updateOrInsert(
                    ['name' => $name],
                    fn ($exists) => $exists ? ['value' => $value] : ['value' => $value],
                );
            }
        }

        ms([
            "status" => 1,
            "message" => __("Succeed")
        ]);
    }

    public function pusher(){
        return view('adminloginauth::pusher');
    }

}
