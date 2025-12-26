<?php

namespace Modules\AppMediaSearch\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppMediaSearchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('appmediasearch::index');
    }

    public function popup_search(Request $request){
        ms([
            "status" => 1,
            "data" => view( module('key') . '::popup_search',[
            ])->render()
        ]);
    }

    public function search(Request $request){
        $keyword = $request->input('keyword');
        $source = $request->input('source');

        if(!$keyword){
            ms([
                "status" => 0,
                "message" => __("Please enter your keyword")
            ]);
        }

        $medias = \SearchMedia::find($keyword, $source);

        ms([
            "status" => 1,
            "data" => view( module('key') . '::result',[
                "medias" => $medias
            ])->render()
        ]);
    }

}
