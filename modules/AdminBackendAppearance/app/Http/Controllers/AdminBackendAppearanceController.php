<?php

namespace Modules\AdminBackendAppearance\Http\Controllers;

use App\Http\Controllers\Controller;

class AdminBackendAppearanceController extends Controller
{
    public function index()
    {
        return view('adminbackendappearance::index');
    }

}
