<?php

namespace Modules\AdminManualPayments\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\AdminManualPayments\Models\PaymentManual;

class AdminManualPaymentsController extends Controller
{
    public $table = "payment_manual";
    public $modules;
    public $Datatable;

    public function __construct()
    {
        $this->Datatable = [
            "element" => "DataTable",
            "order" => ['created', 'desc'],
            "lengthMenu" => [10, 25, 50, 100, 150, 200],
            "search_field" => ["users.email", "users.fullname", "payment_id", "notes"],
            "columns" => [
                ['data' => 'id_secure', 'name' => 'id_secure', 'className' => 'w-40'],
                ['data' => 'uid', 'name' => 'uid', 'title' => __('User')],
                ['data' => 'user_fullname', 'name' => 'users.fullname', 'alias' => 'user_fullname', 'title' => __('User Fullname')],
                ['data' => 'user_email', 'name' => 'users.email', 'alias' => 'user_email', 'title' => __('User Email')],
                ['data' => 'user_avatar', 'name' => 'users.avatar', 'alias' => 'user_avatar', 'title' => __('User Avatar')],
                ['data' => 'plan_name', 'name' => 'plans.name', 'alias' => 'plan_name', 'title' => __('Plan')],
                ['data' => 'payment_id', 'name' => 'payment_id', 'title' => __('Payment ID')],
                ['data' => 'payment_info', 'name' => 'payment_info', 'title' => __('Payment Info')],
                ['data' => 'amount', 'name' => 'amount', 'title' => __('Amount'), 'className' => 'text-center'],
                ['data' => 'currency', 'name' => 'currency', 'title' => __('Currency'), 'className' => 'text-center'],
                ['data' => 'notes', 'name' => 'notes', 'title' => __('Notes'), 'className' => 'text-center'],
                ['data' => 'created', 'name' => 'created', 'type' => 'datetime', 'title' => __('Created at'), 'className' => 'text-center'],
                ['data' => 'status', 'name' => 'status', 'title' => __('Status'), 'className' => 'w-80'],
                ['title' => __('Action'), 'className' => 'text-center'],
            ],
            'status_filter' => [
                ['value' => '-1', 'name' => 'all', 'label' => __('All')],
                ['value' => '0', 'name' => 'pending', 'icon' => 'fa-light fa-hourglass-half', 'color' => 'primary', 'label' => __('Pending')],
                ['value' => '1', 'name' => 'approve', 'icon' => 'fa-light fa-check-circle', 'color' => 'success', 'label' => __('Approved')],
                ['value' => '2', 'name' => 'cancel', 'icon' => 'fa-light fa-times-circle', 'color' => 'danger', 'label' => __('Cancel')],
            ],
            'actions' => [
                ['url' => module_url("status/pending"), 'icon' => 'fa-light fa-hourglass-half', 'label' => __('Pending'), 'call_success' => "Main.DataTable_Reload('#DataTable')"],
                ['url' => module_url("status/approve"), 'icon' => 'fa-light fa-check-circle', 'label' => __('Approved'), 'call_success' => "Main.DataTable_Reload('#DataTable')" , 'confirm' => __("Once you approve, the system will move the user to the selected customer segment. This action cannot be undone. Are you sure you want to proceed?") ],
                ['url' => module_url("status/cancel"), 'icon' => 'fa-light fa-times-circle', 'label' => __('Cancel'), 'call_success' => "Main.DataTable_Reload('#DataTable')"],
                ['divider' => true],
                ['url' => module_url("destroy"), 'icon' => 'fa-light fa-trash-can-list', 'label' => __('Delete'), 'confirm' => __("Are you sure you want to delete this item?"), 'call_success' => "Main.DataTable_Reload('#DataTable')"],
            ],
        ];
    }

    public function index()
    {
        $total = PaymentManual::count();
        return view(module("key").'::index', [
            'total' => $total,
            'Datatable' => $this->Datatable
        ]);
    }

    public function list(Request $request)
    {
        $whereConditions = [];
        $joins = [
            ["table" => "plans", "first" => "plans.id", "second" => $this->table.".plan_id", "type" => "left"],
            ["table" => "users", "first" => "users.id", "second" => $this->table.".uid", "type" => "left"]
        ];
        $dataTableService = \DataTable::make($this->table, $this->Datatable, $whereConditions, $joins);
        $data = $dataTableService->getData($request);
        return response()->json($data);
    }

    public function update(Request $request)
    {
        $result = PaymentManual::where('id_secure', $request->id)->first();

        return ms([
            "status" => 1,
            "data" => view(module('key').'::update', [
                "result" => $result,
            ])->render()
        ]);
    }

    public function save(Request $request)
    {
        $validator_arr = [
            'uid'        => "required|exists:users,id",
            'plan_id'    => "required|exists:plans,id",
            'payment_id' => "required",
            'amount'     => 'required|numeric|min:0',
            'currency'   => 'required|string|max:10',
        ];

        $validator = Validator::make($request->all(), $validator_arr);

        if ($validator->fails()) {
            return ms([
                "status" => 0,
                "message" => $validator->errors()->all()[0],
            ]);
        }

        $item = PaymentManual::where('id_secure', $request->id_secure)->first();

        $values = [
            'uid'        => $request->input('uid'),
            'plan_id'    => $request->input('plan_id'),
            'payment_id' => $request->input('payment_id'),
            'amount'     => $request->input('amount'),
            'currency'   => $request->input('currency'),
            'notes'      => $request->input('notes'),
        ];

        if ($item) {
            $item->fill($values);
            $item->save();
        } else {
            $values['id_secure'] = rand_string();
            $values['created']   = time();
            $values['status']    = 0;
            PaymentManual::create($values);
        }

        return ms(["status" => 1, "message" => __("Succeed")]);
    }

    public function status1(Request $request, $status = "enable")
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

    public function status(Request $request, $status = "active")
    {
        $ids = $request->input('id');
        $id_arr = [];

        if (empty($ids)) {
            return ms([
                "status" => 0,
                "message" => __("Please select at least one item"),
            ]);
        }

        if (is_string($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $value) {
            if ($value != 0) {
                $id_arr[] = $value;
            }
        }

        switch ($status) {
            case 'approve':
                $status = 1;
                break;
            case 'cancel':
                $status = 2;
                break;
            default:
                $status = 0;
                break;
        }

        foreach ($id_arr as $key => $id_secure) {
            $item = PaymentManual::where("id_secure", $id_secure)->first();
            if($item && $item->status != 1 && $status == 1){
                \Plan::updatePlanForTeam($item->plan_id, $item->uid);
            }
            PaymentManual::whereIn('id_secure', $id_arr)->update(['status' => $status]);
        }

        ms(["status" => 1, "message" => __("Succeeded")]);
    }

    public function destroy(Request $request)
    {
        $item = PaymentManual::where('id_secure', $request->input('id'))->first();
        if ($item) {
            $item->delete();
            return response()->json(['status' => 1, 'message' => __('Deleted!')]);
        } else {
            return response()->json(['status' => 0, 'message' => __('Item not found!')]);
        }
    }
}