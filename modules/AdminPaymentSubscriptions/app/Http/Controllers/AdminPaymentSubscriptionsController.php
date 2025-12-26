<?php

namespace Modules\AdminPaymentSubscriptions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Nwidart\Modules\Facades\Module;
use DB;

class AdminPaymentSubscriptionsController extends Controller
{
    public $table = "payment_subscriptions";
    public $modules;
    public $Datatable;
    
    public function __construct()
    {
        $this->Datatable = [
            // The HTML element id or class for the datatable container.
            "element" => "DataTable",

            // Default sorting order: sort by 'price' in descending order.
            "order" => ['created', 'desc'],

            // Options for the number of records to display per page.
            "lengthMenu" => [10, 25, 50, 100, 150, 200],

            // Default search fields; for instance, the datatable may search by 'name' and 'desc'.
            "search_field" => ["users.fullname", "users.email", "source", "subscription_id"],

            // Columns configuration: each array element corresponds to a column.
            "columns" => [
                [
                    'data' => 'id_secure',
                    'name' => 'id_secure',
                    'className' => 'align-middle w-40'
                ],
                [
                    'data' => 'status',
                    'name' => 'status',
                    'title' => __('Status'),
                    'className' => 'align-middle w-80'
                ],
                [ 
                    'data' => 'uid',
                    'name' => 'users.id',
                    'name' => 'uid',
                    'title' => __('User'),
                    'className' => 'align-middle' 
                ],                
                [
                    'data' => 'user_fullname',
                    'name' => 'users.fullname',
                    'alias' => 'user_fullname',
                    'title' => __('User Fullname'),
                    'className' => 'align-middle'
                ],
                [
                    'data' => 'user_email',
                    'name' => 'users.email',
                    'alias' => 'user_email',
                    'title' => __('User Email'),
                    'className' => 'align-middle'
                ],
                [
                    'data' => 'user_avatar',
                    'name' => 'users.avatar',
                    'alias' => 'user_avatar',
                    'title' => __('User Avatar'),
                    'className' => 'align-middle'
                ],
                [
                    'data' => 'plan_name',
                    'name' => 'plans.name',
                    'alias' => 'plan_name',
                    'title' => __('Plan'),
                    'className' => 'align-middle'
                ],
                [
                    'data' => 'source',
                    'name' => 'source',
                    'title' => __('From'),
                    'className' => 'align-middle'
                ],
                [
                    'data' => 'subscription_id',
                    'name' => 'subscription_id',
                    'title' => __('Subscription ID'),
                    'className' => 'align-middle'
                ],
                [
                    'data' => 'customer_id',
                    'name' => 'customer_id',
                    'title' => __('Customer ID'),
                    'className' => 'align-middle'
                ],                
                [
                    'data' => 'amount',
                    'name' => 'amount',
                    'title' => __('Amount'),
                    'className' => 'align-middle text-center'
                ],
                [
                    'data' => 'currency',
                    'name' => 'currency',
                    'title' => __('Currency'),
                    'className' => 'align-middle text-center'
                ],
                [
                    'data' => 'created',
                    'name' => 'created',
                    'type' => 'datetime',
                    'title' => __('Created at'),
                    'className' => 'align-middle text-center text-nowrap'
                ],
            ],
            'actions' => [
                [
                    'url'           => module_url("destroy"),
                    'icon'          => 'fa-light fa-trash-can-list',
                    'label'         => __('Delete'),
                    'confirm'       => __("Are you sure you want to delete this item?"),
                    'call_success'  => "Main.DataTable_Reload('#DataTable')"
                ],
            ],
        ];
    }
    public function index()
    {
        $total = DB::table( $this->table )->count();
        return view(module("key").'::index', [
            'total' => $total,
            'Datatable' => $this->Datatable
        ]);
    }

    public function list(Request $request)
    {
        //Conditions
        $whereConditions = [];
        //Join Tables
        $joins = [
            [
                "table" => "plans",
                "first" => "plans.id",
                "second" => $this->table.".plan_id",
                "type" => "left"
            ],
            [
                "table" => "users",
                "first" => "users.id",
                "second" => $this->table.".uid",
                "type" => "left"
            ]
        ];

        $dataTableService = \DataTable::make($this->table, $this->Datatable, $whereConditions, $joins);
        $data = $dataTableService->getData($request);
        return response()->json($data);
    }


    public function update(Request $request)
    {
        $result = DB::table($this->table)->where("id_secure", $request->id)->first();

        return ms([
            "status" => 1,
            "data" => view(module('key').'::update', [
                "result" => $result,
            ])->render()
        ]);
    }

    public function destroy(Request $request)
    {
        $response = \DBHelper::destroy($this->table, $request->input('id'));
        return response()->json($response);
    }
}
