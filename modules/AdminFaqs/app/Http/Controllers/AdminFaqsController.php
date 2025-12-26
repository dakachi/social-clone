<?php


namespace Modules\AdminFaqs\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Articles;
use DB;


class AdminFaqsController extends Controller
{
    public $table = "articles";
    public $type = "faqs";
    public $modules;
    public $Datatable;

    public function __construct()
    {
        $this->table = "articles";
        $this->Datatable = [
            // The HTML element id or class for the datatable container.
            "element" => "DataTable",

            // Default sorting order: sort by 'price' in descending order.
            "order" => ['created', 'desc'],

            // Options for the number of records to display per page.
            "lengthMenu" => [10, 25, 50, 100, 150, 200],

            // Default search fields; for instance, the datatable may search by 'name' and 'desc'.
            "search_field" => ["title", "desc", "content"],

            // Columns configuration: each array element corresponds to a column.
            "columns" => [
                [
                    'data' => 'id_secure',
                    'name' => 'id_secure',
                    'className' => 'w-40'
                ],
                [
                    'data' => 'title',
                    'name' => 'title',
                    'title' => __('Title'),
                    "className" => "max-w-250",
                ],
                [
                    'data' => 'desc',
                    'name' => 'desc',
                    'title' => __('Description'),
                ],
                 [
                    'data' => 'content',
                    'name' => 'content',
                    'title' => __('Content'),
                     "className" => "text-truncate w-40"
                ],
                [
                    'data' => 'thumbnail',
                    'name' => 'thumbnail',
                    'title' => __('Thumbnail'),
                ],
                [
                    'data' => 'status',
                    'name' => 'status',
                    'title' => __('Status'),
                    'className' => 'align-middle w-80'
                ],
                [
                    'data' => 'changed',
                    'name' => 'changed',
                    'title' => __('Action'),
                ],
            ],
            'status_filter' => [
                [
                    'value' => '-1',
                    'label' => __('All')
                ],

                [
                    'value' => '1',
                    'name' => 'enable',
                    'icon' => 'fa-light fa-eye',
                    'color' => 'success',
                    'label' => __('Enable')
                ],

                [
                    'value' => '0',
                    'name' => 'disable',
                    'icon' => 'fa-light fa-eye-slash',
                    'color' => 'light',
                    'label' => __('Disable')
                ],
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
            ],

        ];
    }

    public function index()
    {
        $total = Articles::where("type", $this->type)->count();
        return view(module("key").'::index', [
            'total' => $total,
            'Datatable' => $this->Datatable,
        ]);
    }

    public function list(Request $request)
    {
        $joins = [];
        $whereConditions['type'] = $this->type;
        $dataTableService = \DataTable::make($this->table, $this->Datatable, $whereConditions, $joins);
        $data = $dataTableService->getData($request);
        return response()->json($data);
    }

    public function update(Request $request, $id = ""){
        $id = $request->id;
        $result = DB::table($this->table)->where("id_secure", $id)->where("type", $this->type)->first();
        $categories = DB::table($this->table)->where("status", 1)->where("type", $this->type)->get();

        return view(module("key").'::update', [
            "result" => $result,
            "categories" => $categories,
        ]);
    }

    public function save(Request $request)
    {
        $rules = [
            'title'     => 'required',
            'content'   => 'required',
            'status'    => 'required',
        ];

        $title = $request->input('title');
        $data = [
            'id_secure' => rand_string(),
            'slug'      => Str::slug($title, '-'),
            'title'     => $title,
            'content'   => $request->input('content'),
            'status'    => (int)$request->input('status'),
            'type'      => $this->type,
            'cate_id'   => $request->input('cate_id'),
            'changed'   => time(),
            'created'   => time(),
        ];

        if ($request->has('id')) {
            $data['id_secure'] = $request->input('id');
        }

        $response = \DBHelper::saveData(Articles::class, $rules, $data, ['id_secure', 'type', 'created']);
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
        $response = \DBHelper::destroy(Articles::class, $request->input('id'), [ "type" => $this->type ]);
        return response()->json($response);
    }

}
