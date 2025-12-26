<?php

namespace Modules\AppProxies\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AppProxies\Models\ProxyModel;
use DB;
use Proxy;

class AppProxiesController extends Controller
{
    public $modules; 
    public function __construct()
    {               
       
        $this->Datatable = [
            "element" => "DataTable",            
            "order" => ['created', 'desc'],
            "lengthMenu" => [10, 25, 50, 100, 150, 200],
            "search_field" => ["description", "proxy", "location"],
            "columns" => [
                [   
                    "name" => "id_secure",
                    "alias" => "id_secure",
                    "data"  => "id_secure",
                    "className" => "align-middle w-40"
                ],
                [   
                    "data" => "proxy",
                    "name" => "proxy",
                    "title" => __('Proxy'), 
                    "className" => "align-middle" 
                ],
                [   
                    "data" => "location", 
                    "name" => "location", 
                    "title" => __('Location'), 
                    "className" => "align-middle" 
                ],

                [
                    "data" => "description",
                    "name" => "description",
                    "title" => __('Description'),
                    "className" => "align-middle"
                ],
                [   
                    "data" => "status", 
                    "name" => "status", 
                    "title" => __('Status'), 
                    "className" => "align-middle" 
                ],
                [   
                    "data" => "changed", 
                    "name" => "changed", 
                    "className" => "align-middle" 
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

    public function index(Request $request)
    {
        $total = ProxyModel::where("team_id", $request->team_id)->count();
        return view(module("key").'::index', [
            'total' => $total,
            'Datatable' => $this->Datatable,
        ]);
    }

    public function list(Request $request)
    {   
        $joins = [];
        $whereConditions = [
            "team_id" => $request->team_id
        ];
        $dataTableService = \DataTable::make(ProxyModel::class, $this->Datatable, $whereConditions, $joins);
        $data = $dataTableService->getData($request);
        return response()->json($data);
    }
    
    public function update(Request $request, $id = null){
        $result = ProxyModel::where("id_secure", $request->id)->where("team_id", $request->team_id)->first();
        return response()->json([
            "status" => 1,
            "data" => view(module("key").'::update', [
                "result" => $result,                
            ])->render()
        ]);       
    }
    
    public function save(Request $request, $id = null)
    {
        $rules = [
            'proxy'    => 'required|string|max:255',
            'status'   => 'required|in:0,1',
            'limit'    => 'required|integer',
        ];

        $proxy = $request->input('proxy');
        $teamId = $request->input('team_id');

        if (!Proxy::validateProxyFormat($proxy)) {
            return response()->json([
                'status' => 0,
                'message' => __("Invalid proxy format. Use ip:port or username:password@ip:port.")
            ]);
        }

        $location = Proxy::getProxyLocation($proxy);

        $isUpdate = false;
        if ($request->filled('id')) {
            $existing = ProxyModel::where('id_secure', $request->input('id'))
                ->where('team_id', $teamId)
                ->first();

            if ($existing) {
                $isUpdate = true;
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => __("Invalid ID or unauthorized access.")
                ]);
            }
        }

        $data = [
            'id_secure'   => $isUpdate ? $request->input('id') : rand_string(),
            'proxy'       => $proxy,
            'location'    => $location,
            'limit'       => (int) $request->input('limit'),
            'description' => $request->input('description'),
            'status'      => (int) $request->input('status'),
            'is_system'   => false,
            'team_id'     => $teamId,
            'changed'     => time(),
        ];

        if (!$isUpdate) {
            $data['active'] = 1;
            $data['created'] = time();
        }

        $response = \DBHelper::saveData(ProxyModel::class, $rules, $data, ['id_secure', 'created']);
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

        $response = \DBHelper::updateField(ProxyModel::class, $request->input('id'), 'status', $status_update);
        return response()->json($response);
    }

    public function destroy(Request $request)
    {
        $id_arr = id_arr($request->input('id'));
        $teamId = $request->input('team_id');

        if (empty($id_arr)) {
            return response()->json([
                "status" => 0,
                "message" => __("Please select at least one item")
            ]);
        }

        ProxyModel::whereIn('id_secure', $id_arr)->where("team_id", $teamId)->delete();

        return response()->json([
            "status" => 1,
            "message" => __("Succeed")
        ]);
    }
}
