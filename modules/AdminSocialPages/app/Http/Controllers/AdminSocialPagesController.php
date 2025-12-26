<?php

namespace Modules\AdminSocialPages\Http\Controllers;

use App\Http\Controllers\Controller;

class AdminSocialPagesController extends Controller
{
    public function settings()
    {
        return view('adminsocialpages::index');
    }

}
