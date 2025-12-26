<?php

namespace Modules\AdminPaymentConfigurations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminPaymentConfigurationsController extends Controller
{
    private $table;

    public function __construct()
    {
        $this->table = "payment_getways";
    }

    public function index()
    {
        return view('adminpaymentconfigurations::index');
    }

    public function list(Request $request)
    {
        $search = $request->input("keyword");
        $current_page = $request->input("page", 1);
        $per_page = 30;

        $query = DB::table($this->table);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('icon', 'like', '%'.$search.'%');
            });
        }

        $results = $query->orderByDesc('changed')->paginate($per_page, ['*'], 'page', $current_page);

        if ($results->total() == 0 && $current_page > 1) {
            return ms(["status" => 0]);
        }

        return ms([
            "status" => 1,
            "data" => view('adminpaymentconfigurations::list', [
                "results" => $results
            ])->render()
        ]);
    }
}
