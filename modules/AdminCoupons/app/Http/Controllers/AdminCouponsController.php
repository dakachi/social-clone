<?php

namespace Modules\AdminCoupons\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AdminPlans\Models\Plans;
use DB;

class AdminCouponsController extends Controller
{

    public $table;
    public $Datatable;
    public function __construct()
    {
        $this->table = "coupons";
        $this->Datatable = [
            "element" => "DataTable",
            "columns" => false,
            "order" => ['created', 'desc'],
            "lengthMenu" => [10, 25, 50, 100, 150, 200],
            "search_field" => ["name", "code", "discount"],
            "columns" => [
                [
                    "data" => 'id_secure',
                    "name" => "id_secure",
                    "className" => "w-40"
                ],
                [
                    "data" => "name",
                    "name" => "name",
                    "title" => __('Name'),
                ],
                [
                    "data" => "code",
                    "name" => "code",
                    "title" => __('Code'),
                ],
                [
                    "data" => "discount",
                    "name" => "discount",
                    "title" => __('Discount'),
                ],
                [
                    "data" => "usage_count",
                    "name" => "usage_count",
                    "title" => __('Usage count'),
                ],
                [
                    "data" => "usage_limit",
                    "name" => "usage_limit",
                    "title" => __('Usage limit'),
                    "className" => "align-middle text-end"
                ],
                [
                    "data" => "start_date",
                    "name" => "start_date",
                    "data" => __('Start date'),
                    "type" => "datetime",
                ],
                [
                    "data" => "end_date",
                    "name" => "end_date",
                    "data" => __('End date'),
                    "type" => "datetime",
                ],
                [
                    "data" => "status",
                    "name" => "status",
                    "title" => __('Status'),
                ],
                [
                    "data" => "changed",
                    "name" => "changed",
                ],
            ],
            'status_filter' => [
                ['value' => '-1', 'label' => __('All')],
                ['value' => '1', 'name' => 'enable', 'icon' => 'fa-light fa-eye', 'color' => 'success', 'label' => __('Enable')],
                ['value' => '0', 'name' => 'disable', 'icon' => 'fa-light fa-eye-slash', 'color' => 'light', 'label' => __('Disable')],
            ],
            'actions' => [
                [
                    'url'           => module_url("status/enable"),
                    'icon'          => 'fa-light fa-eye',
                    'label'         => __('Enable'),
                    'call_success'  => "Main.DataTable_Reload('#DataTable')"
                ],
                [
                    'url'           => module_url("status/disable"),
                    'icon'          => 'fa-light fa-eye-slash',
                    'label'         => __('Disable'),
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
            ]
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
        $joins = [];
        $whereConditions = [];
        $dataTableService = \DataTable::make($this->table, $this->Datatable, $whereConditions, $joins);
        $data = $dataTableService->getData($request);
        return response()->json($data);
    }

    public function update(Request $request, $id = ""){
        $result = DB::table($this->table)->where("id_secure", $id)->first();
        $plans = Plans::where("free_plan", 0)->get();
        return view(module("key").'::update', [
            "result" => $result,
            "plans" => $plans
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $rules = [
            'name'             => 'required|string|max:255',
            'code'             => 'required|string|max:255|unique:coupons,code,'.$request->id.",id_secure",
            'discount'         => 'required|numeric',
            'start_date'       => 'required',
            'end_date'         => 'required|after_or_equal:start_date',
            'plans'            => 'required|array',
            'type'             => 'required|in:1,2',
            'usage_limit'      => 'required|numeric',
            'status'           => 'required|in:0,1'
        ];

        $start_date = $request->input('end_date');
        if($start_date != -1)
            $start_date = datetime_sql( $start_date );

        $plan_items = [];
        $plan_ids = $request->input('plans');
        if(!empty($plan_ids)){
            $plan_items = Plans::whereIn("id_secure", $plan_ids)->pluck('id')->toArray();
        }

        if(!$plan_items)
            return response()->json([
                "status" => 0,
                "message" => __("The plan you chose is unavailable.")
            ]);

        $data = [
            'id_secure'         => rand_string(),
            'name'              => $request->input('name'),
            'code'              => $request->input('code'),
            'discount'          => $request->input('discount'),
            'start_date'        => timestamp_sql( $request->input('start_date') ),
            'end_date'          => timestamp_sql( $request->input('end_date') ),
            'plans'             => $plan_items,
            'usage_limit'       => $request->input('usage_limit'),
            'usage_count'       => 0,
            'type'              => $request->input('type'),
            'status'            => (int)$request->input('status'),
            'changed'           => time(),
            'created'           => time(),
        ];

        if ($request->has('id')) {
            $data['id_secure'] = $request->input('id');
        }

        $response = \DBHelper::saveData($this->table, $rules, $data, ['id_secure', 'usage_count', 'created']);
        return response()->json($response);
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
