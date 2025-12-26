<?php


namespace Modules\AdminAffiliateWithdrawal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminAffiliateWithdrawalController extends Controller
{
    public $table = "affiliate_withdrawal";
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
            "search_field" => ["users.id", "users.fullname", "users.email", "bank", "notes"],

            // Columns configuration: each array element corresponds to a column.
            "columns" => [
                [ 
                    'data' => 'id_secure',
                    'name' => 'id_secure',
                    'className' => 'w-40' 
                ],
                [ 
                    'data' => 'affiliate_uid',
                    'name' => 'users.id',
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
                    'data' => 'amount',
                    'name' => 'amount',
                    'title' => __('Amount'),
                ], 
                [ 
                    'data' => 'bank',
                    'name' => 'bank',
                    'title' => __('Bank'),
                ],              
                [ 
                    'data' => 'notes',
                    'name' => 'notes',
                    'title' => __('Notes'),
                ], 
                [ 
                    'data' => 'created',
                    'name' => 'created',
                    'type' => 'datetime',
                    'title' => __('Created at'),
                    'className' => 'align-middle text-center'
                ],   
                [ 
                    'data' => 'status',
                    'name' => 'status',
                    'title' => __('Status'),
                ],         
                [ 
                    'data' => 'id',
                    'name' => 'id',
                    'title' => __('Action'),
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
            'Datatable' => $this->Datatable
        ]);
    }

    public function list(Request $request)
    {   
        //Conditions
        $whereConditions = []; 
        $joins = [
            [
                "table" => "users",
                "first" => "users.id",
                "second" => $this->table.".affiliate_uid",
                "type" => "left"
            ]        
        ];

        $dataTableService = \DataTable::make($this->table, $this->Datatable, $whereConditions, $joins);
        $data = $dataTableService->getData($request);
        return response()->json($data);
    }



    public function update(Request $request){
        $id = $request->input("id");
        $result = DB::table($this->table)->where("id_secure", $id)->first();
        
        return response()->json([
            "status" => 1,
            "data" => view(module("key").'::update', [
                "result" => $result
            ])->render()
        ]);
    }

    public function updateNote(Request $request){
        $note = $request->input("note");
        $idSecure = $request->input("id_secure");

        DB::table($this->table)->where("id_secure", $idSecure)->update([
            "notes" => $note
        ]);

        return response()->json([
            "status" => 1,
            "message" => __("Updated successfully")
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
        $response = \Affiliate::updateWithdrawalStatus($id_arr, $status_update);

        return response()->json($response);
    }

    public function destroy(Request $request)
    {
        $response = \DBHelper::destroy($this->table, $request->input('id'));
        return response()->json($response);
    }

}
