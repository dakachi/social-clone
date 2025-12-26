<?php

namespace Modules\AdminCreditRates\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminCreditRatesController extends Controller
{
    public function index()
    {
        $rates = json_decode(get_option('credit_rates', '{}'), true);
        $creditRates = app()->bound('creditRates') ? app('creditRates') : [];
        return view('admincreditrates::index', [
            'creditRates' => $creditRates,
            'rates' => $rates,
        ]);
    }
}
