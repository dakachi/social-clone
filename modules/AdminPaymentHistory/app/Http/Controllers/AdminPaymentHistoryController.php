<?php

namespace Modules\AdminPaymentHistory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\AdminPaymentHistory\Models\PaymentHistory;
use Illuminate\Support\Facades\DB;

class AdminPaymentHistoryController extends Controller
{
    public $table = "payment_history";
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
            "search_field" => ["users.fullname", "users.email", "transaction_id", "from"],

            // Columns configuration: each array element corresponds to a column.
            "columns" => [
                [
                    'data' => 'id_secure',
                    'name' => 'id_secure',
                    'className' => 'w-40'
                ],
                [
                    'data' => 'uid',
                    'name' => 'uid',
                    'title' => __('User'),
                ],
                [
                    'data' => 'user_fullname',
                    'name' => 'users.fullname',
                    'alias' => 'user_fullname',
                    'title' => __('User Fullname'),
                ],
                [
                    'data' => 'user_email',
                    'name' => 'users.email',
                    'alias' => 'user_email',
                    'title' => __('User Email'),
                ],
                [
                    'data' => 'user_avatar',
                    'name' => 'users.avatar',
                    'alias' => 'user_avatar',
                    'title' => __('User Avatar'),
                ],
                [
                    'data' => 'plan_name',
                    'name' => 'plans.name',
                    'alias' => 'plan_name',
                    'title' => __('Plan'),
                ],
                [
                    'data' => 'from',
                    'name' => 'from',
                    'title' => __('From'),
                ],
                [
                    'data' => 'transaction_id',
                    'name' => 'transaction_id',
                    'title' => __('Transaction ID'),
                ],
                [
                    'data' => 'amount',
                    'name' => 'amount',
                    'title' => __('Amount'),
                    'className' => 'text-center'
                ],
                [
                    'data' => 'currency',
                    'name' => 'currency',
                    'title' => __('Currency'),
                    'className' => 'text-center'
                ],
                [
                    'data' => 'created',
                    'name' => 'created',
                    'type' => 'datetime',
                    'title' => __('Created at'),
                    'className' => 'text-center'
                ],
                [
                    'data' => 'status',
                    'name' => 'status',
                    'title' => __('Status'),
                    'className' => 'w-80'
                ],
                [
                    'title' => __('Action'),
                    'className' => 'text-center'
                ],
            ],
            'status_filter' => [
                ['value' => '-1', 'label' => __('All')],
                ['value' => '1', 'name' => 'enable', 'icon' => 'fal fa-check-circle', 'color' => 'success', 'label' => __('Success')],
                ['value' => '0', 'name' => 'disable', 'icon' => 'fal fa-sync-alt', 'color' => 'danger', 'label' => __('Refund')],
            ],
            'actions' => [
                [
                    'url'           => module_url("status/enable"),
                    'icon'          => 'fal fa-check-circle',
                    'label'         => __('Success'),
                    'call_success'  => "Main.DataTable_Reload('#DataTable')"
                ],
                [
                    'url'           => module_url("status/disable"),
                    'icon'          => 'fal fa-sync-alt',
                    'label'         => __('Refund'),
                    'call_success'  => "Main.DataTable_Reload('#DataTable')"
                ],
                [
                    'divider'       => true
                ],
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

        $dataTableService = \DataTable::make(PaymentHistory::class, $this->Datatable, $whereConditions, $joins);
        $data = $dataTableService->getData($request);
        return response()->json($data);
    }

    public function status(Request $request, $status = "enable")
    {
        $status_update = $status;
        if(isset($this->Datatable['status_filter'])){
            foreach ($this->Datatable['status_filter'] as $value) {
                if (isset($value['name']) && $value['value'] != -1 && $value['name'] == $status) {
                    $status_update = $value['value'];
                    break;
                }
            }
        }

        $response = \DBHelper::updateField($this->table, $request->input('id'), 'status', $status_update);
        return response()->json($response);
    }

    public function destroy(Request $request)
    {
        $response = \DBHelper::destroy($this->table, $request->input('id'));
        return response()->json($response);
    }

}
