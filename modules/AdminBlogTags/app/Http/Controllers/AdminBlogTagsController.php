<?php

namespace Modules\AdminBlogTags\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;

class AdminBlogTagsController extends Controller
{
    public $table;
    public $modules;
    public $Datatable;
    public function __construct()
    {
        $this->table = "article_tags";
        $this->Datatable = [
            "element" => "DataTable",
            "columns" => false,
            "order" => ['created', 'desc'],
            "lengthMenu" => [10, 25, 50, 100, 150, 200],
            "search_field" => ["name", "desc"],
            "columns" => [
                [
                    "data" => 'id_secure',
                    'name' => 'id_secure',
                    'className' => 'w-40'
                ],
                [
                    'data' => 'name',
                    'name' => 'name',
                    'title' => __('Tags'),
                ],
                [
                    'data' => 'desc',
                    'name' => 'desc',
                    'title' => __('Description'),
                ],
                [
                    "data" => 'color',
                    "name" => "color",
                    'title' => __('Color'),
                    "className" => "align-middle"
                ],
                [
                    'data' => 'status',
                    'name' => 'status',
                    'title' => __('Status'),
                    'className' => 'w-80'
                ],
                [
                    'data' => 'changed',
                    'name' => 'id',
                    'title' => __('Action'),
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

public function save(Request $request, $id = null)
{
    $item = DB::table($this->table)->where('id_secure', $request->id_secure)->first();

        $validator_arr = [
            'name'   => 'required|string|max:255',
            'color'  => 'required|string|max:255',
            'status' => 'required|boolean',
            ];

    $validator = Validator::make($request->all(), $validator_arr);

        if ($validator->passes()) {
            $values = [
                'name' => $request->input('name'),
                'desc' => $request->input('desc'),
                'icon' => $request->input('icon'),
                'color' => $request->input('color'),
                'status' => (int)$request->input('status'),
                'changed' => time()
            ];
            if($item){
                DB::table($this->table)->where("id", $item->id)->update($values);
            }else{
                $values['id_secure'] = rand_string();
                $values['created'] = time();
                DB::table($this->table)->insert($values);
            }

            ms(["status" => 1, "message" => "Succeed"]);
        }

        return ms([
            "status" => 0,
            "message" => $validator->errors()->all()[0],
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

        $response = \DBHelper::updateField($this->table, $request->input('id'), 'status', $status_update);
        return response()->json($response);
    }

    public function create()
    {
        return view($this->module->getLowerName().'::update', [
            'module' => $this->module,
            'result' => false
        ]);
    }

    public function edit($id_secure = "")
    {
        $result = DB::table( $this->table )->where('id_secure', $id_secure)->first();
        if(!$result){
            return redirect( url_admin("languages") );
        }

        return view($this->module->getLowerName().'::update', [
            'module' => $this->module,
            'result' => $result
        ]);
    }

    public function destroy(Request $request)
    {
        $response = \DBHelper::destroy($this->table, $request->input('id'));
        return response()->json($response);
    }

}


