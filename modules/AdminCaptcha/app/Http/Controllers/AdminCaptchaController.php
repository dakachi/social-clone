<?php

namespace Modules\AdminCaptcha\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminCaptchaController extends Controller
{
    public function index()
    {
        return view('admincaptcha::index');
    }
}
