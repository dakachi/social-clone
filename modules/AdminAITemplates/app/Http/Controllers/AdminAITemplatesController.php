<?php

namespace Modules\AdminAITemplates\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class AdminAITemplatesController extends Controller
{
    public $table = "ai_templates";
    public $category_table = "ai_categories";
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
            "search_field" => ["content", "ai_categories.name"],

            // Columns configuration: each array element corresponds to a column.
            "columns" => [
                [
                    "name" => "id_secure",
                    "alias" => "id_secure",
                    "data"  => "id_secure",
                    "className" => "w-40"
                ],

                [
                    "name" => "content",
                    "alias" => "content",
                    "data"  => "content",
                    "className" => "text-start",
                    "title"     => __('Content')
                ],

                [
                    "name" => "ai_categories.name",
                    "data"  => "ai_categories_name",
                    "alias" => "ai_categories_name",
                    "title"     => __('Category')
                ],
                [
                    "name" => "ai_categories.icon",
                    "data"  => "ai_categories_icon",
                    "alias" => "ai_categories_icon",
                    "title"     => __('Category icon')
                ],
                [
                    "name" => "ai_categories.color",
                    "data"  => "ai_categories_color",
                    "alias" => "ai_categories_color",
                    "title"     => __('Category color')
                ],

                [
                    "name" => "status",
                    "alias" => "status",
                    "data"  => "status",
                    "className" => "align-middle w-80",
                    "title"     => __('Status')
                ],
                [
                    "name" => "id",
                    "alias" => "id",
                    "data"  => "id",
                    "className" => "align-middle text-center",
                    "title"     => __('Action')
                ],
            ],

            'status_filter' => [
                ['value' => '-1', 'label' => __('All')],
                ['value' => '1', 'name' => 'enable', 'icon' => 'fa-light fa-eye', 'color' => 'success', 'label' => __('Enable')],
                ['value' => '0', 'name' => 'disable', 'icon' => 'fa-light fa-eye-slash', 'color' => 'light', 'label' => __('Disable')],
            ],

            // Actions configuration: define actions that can be applied to selected rows.
            'actions' => [
                [
                    'url'           => module_url("status/enable"),
                    'icon'          => 'fa-eye',
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
        $joins = [
            [
                "table" => "ai_categories",
                 "first" => "ai_categories.id",
                 "second" => $this->table.".cate_id",
                 "type" => "left"
            ]
        ];

        $whereConditions = [];
        $dataTableService = \DataTable::make($this->table, $this->Datatable, $whereConditions, $joins);
        $data = $dataTableService->getData($request);
        return response()->json($data);
    }

    public function update(Request $request, $id = null) {
    $result = DB::table($this->table)->where("id_secure", $request->id)->first();
    $categories = DB::table($this->category_table)->get();

        return response()->json([
            "status" => 1,
            "data" => view(module("key").'::update', [
                "result" => $result,
                "categories" => $categories
            ])->render()
        ]);
    }


    public function save(Request $request, $id = null)
    {
        $rules = [
            'cate_id'         => 'required|string|max:255',
            'content'         => 'required|string|max:255',
            'status'          => 'required|boolean',
        ];

        $data = [
            'id_secure'   => rand_string(),
            'cate_id'     => $request->input('cate_id'),
            'content'     => $request->input('content'),
            'status'      => (int)$request->input('status'),
            'changed'     => time(),
            'created'     => time(),
        ];

        if ($request->has('id')) {
            $data['id_secure'] = $request->input('id');
        }

        $response = \DBHelper::saveData($this->table, $rules, $data, ['id_secure', 'created']);
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
