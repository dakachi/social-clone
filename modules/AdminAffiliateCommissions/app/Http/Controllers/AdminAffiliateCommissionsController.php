<?php

namespace Modules\AdminAffiliateCommissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AdminAffiliate\Models\Affiliate as AffiliateModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class AdminAffiliateCommissionsController extends Controller
{
    public $table;
    public $modules;
    public $Datatable;
    public function __construct()
    {
        $this->table = "affiliate";
        $this->Datatable = [
            "element" => "DataTable",
            "order" => ['created', 'desc'],
            "lengthMenu" => [10, 25, 50, 100, 150, 200],
            "search_field" => ["payment_history.transaction_id", "users.fullname", "users.email"],
            "columns" => [
                [ 
                    'data' => 'id_secure',
                    'name' => 'id_secure',
                    'className' => 'w-40' 
                ],
                [ 
                    'data' => 'affiliate_uid',
                    'name' => 'user.id',
                    'name' => 'affiliate_uid',
                    'title' => __('Affiliater'),
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
                    'data' => 'transaction_id',
                    'name' => 'payment_history.transaction_id',
                    'alias' => 'transaction_id',
                    'title' => __('Transaction ID'),
                ],            
                [ 
                    'data' => 'amount',
                    'name' => 'amount',
                    'title' => __('Amount'),
                ], 
                [ 
                    'data' => 'commission_rate',
                    'name' => 'commission_rate',
                    'title' => __('Commission Rate'),
                ], 
                [ 
                    'data' => 'commission',
                    'name' => 'commission',
                    'title' => __('Commission'),
                ], 
                [ 
                    'data' => 'payment_status',
                    'name' => 'payment_history.status',
                    'alias' => 'payment_status',
                    'title' => __('Transaction Status'),
                ],  

                [ 
                    'data' => 'status',
                    'name' => 'affiliate.status',
                    'alias' => 'status',
                    'title' => __('Status'),
                ],  
                [ 
                    'data' => 'created',
                    'name' => 'created',
                    'type' => 'datetime',
                    'title' => __('Created at'),
                ],                  
            ],
            'status_filter' => [
                [
                    'value' => '-1', 
                    'name' => 'all', 
                    'label' => __('All')],
                [
                    'value' => '0', 
                    'name' => 'pending', 
                    'icon' => 'fa-light fa-hourglass-half', 
                    'color' => 'primary', 
                    'label' => __('Pending')],
                [
                    'value' => '1', 
                    'name' => 'approve', 
                    'icon' => 'fa-light fa-check-circle', 
                    'color' => 'success', 
                    'label' => __('Approved')],
                [
                    'value' => '2', 
                    'name' => 'reject', 
                    'icon' => 'fa-light fa-times-circle', 
                    'color' => 'danger', 
                    'label' => __('Reject')],               
            ],
              
            // Actions configuration: define actions that can be applied to selected rows.
            'actions' => [
                [
                    'url'           => module_url("status/pending"),
                    'icon'          => 'fa-light fa-hourglass-half',
                    'label'         => __('Pending'),
                    'call_success'  => "Main.DataTable_Reload('#DataTable')"
                ],
                [
                    'url'           => module_url("status/approve"),
                    'icon'          => 'fa-light fa-check-circle',
                    'label'         => __('Approved'),
                    'call_success'  => "Main.DataTable_Reload('#DataTable')"
                ],
                [
                    'url'           => module_url("status/reject"),
                    'icon'          => 'fa-light fa-times-circle',
                    'label'         => __('Reject'),
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
        $total = DB::table($this->table)->count();
        return view(module("key").'::index', [
            'total' => $total,
            'Datatable' => $this->Datatable,

        ]);
    }

    public function list(Request $request)
    {   
        //Join Tables
        $joins = [
            [
                "table" => "payment_history",
                "first" => "payment_history.id",
                "second" => $this->table.".payment_id",
                "type" => "left"
            ],
            [
                "table" => "users",
                "first" => "users.id",
                "second" => $this->table.".affiliate_uid",
                "type" => "left"
            ]
        ];

        $whereConditions = [];
        $dataTableService = \DataTable::make(AffiliateModel::class, $this->Datatable, $whereConditions, $joins);
        $data = $dataTableService->getData($request);
        return response()->json($data);
    }

    public function update(Request $request){

        $id = $request->id;
        $result = DB::table($this->table)->where("id_secure", $id)->first();

        ms([
            "status" => 1,
            "data" => view(module("key").'::update', [
                "result" => $result,
            ])->render()
        ]);
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

        $id_arr = id_arr($request->input('id'));
        $response = \Affiliate::updateStatus($id_arr, $status_update);

        return response()->json($response);
    }

    public function destroy(Request $request)
    {
        $response = \DBHelper::destroy(AffiliateModel::class, $request->input('id'));
        return response()->json($response);
    }

}


