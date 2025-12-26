<?php

namespace Modules\AdminEmbedCode\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminEmbedCodeController extends Controller
{
    public function settings()
    {
        return view('adminembedcode::index');
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
}