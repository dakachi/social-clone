<?php


namespace Modules\AdminAffiliateSettings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminAffiliateSettingsController extends Controller
{

    public function index()
    {
        return view('adminaffiliatesettings::index');
    }
}

