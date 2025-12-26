<?php

namespace Modules\AdminURLShorteners\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminURLShortenersController extends Controller
{
    public function index()
    {
        return view('adminurlshorteners::index');
    }
}
