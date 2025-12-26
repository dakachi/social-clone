<?php

namespace Modules\AdminBroadcast\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminBroadcastController extends Controller
{
    public function settings()
    {
        return view('adminbroadcast::settings');
    }
}
