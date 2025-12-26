<?php

namespace Modules\AppURLShorteners\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use URLShortener;

class AppURLShortenersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function shorten(Request $request)
    {
        $caption = $request->caption;
        $link = $request->link;
        $type = $request->type;

        try {
            if($type == "link" && filter_var($link, FILTER_VALIDATE_URL)){
                $link =  URLShortener::shorten($link);
            }
        } catch (\Exception $e) {}
        $caption = URLShortener::shortenUrlsInContent($caption);

        ms([
            "status" => 1,
            "data" => [
                "caption" => $caption,
                "link" => $link,
            ]
        ]);
    }

}
