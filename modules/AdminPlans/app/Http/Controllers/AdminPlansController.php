<?php

namespace Modules\AdminPlans\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AdminPlans\Models\Plans;
use Modules\AdminUsers\Models\Teams;
use App\Models\User;

class AdminPlansController extends Controller
{
    public $table = "support_categories";
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
            "search_field" => ["name"],

            // Columns configuration: each array element corresponds to a column.
            "columns" => [
                [
                    "name" => "id_secure",
                    "data"  => "id_secure",
                    "className" => "w-40"
                ],
                [
                    "name" => "name",
                    "data"  => "name",
                    "title"     => __('Name')
                ],
                [
                    "name" => "price",
                    "data"  => "price",
                    "className" => "text-start",
                    "title"     => __('Price')
                ],
                [
                    "name" => "type",
                    "data"  => "type",
                    "className" => "text-center",
                    "title"     => __('Type')
                ],
                [
                    "name" => "featured",
                    "data"  => "featured",
                    "className" => "text-center",
                    "title"     => __('Featured')
                ],
                [
                    "name" => "free_plan",
                    "data"  => "free_plan",
                    "className" => "text-center",
                    "title"     => __('Free Plan')
                ],
                [
                    "name" => "status",
                    "data"  => "status",
                    "className" => "w-80",
                    "title"     => __('Status')
                ],
                [
                    "className" => "text-center",
                    "title"     => __('Action')
                ],
            ],

            'status_filter' => [
                ['value' => '-1', 'label' => __('All')],
                ['value' => '1', 'name' => 'enable', 'icon' => 'fa-light fa-eye', 'color' => 'success', 'label' => __('Enable')],
                ['value' => '0', 'name' => 'disable', 'icon' => 'fa-light fa-eye-slash', 'color' => 'light', 'label' => __('Disable')],
            ],

            // Filters configuration: define dropdown filters to narrow the data.
            'filters' => [
                [
                    'name'  => 'datatable_filter[type]',
                    'label' => __('Plan Type'),
                    'options' => [
                        ['value' => '-1', 'label' => __('All')],
                        ['value' => '1',  'label' => __('Monthly')],
                        ['value' => '2',  'label' => __('Yearly')],
                        ['value' => '3',  'label' => __('Lifetime')],
                    ]
                ],
                [
                    'name'  => 'datatable_filter[free_plan]',
                    'label' => __('Free Plan'),
                    'options' => [
                        ['value' => '-1', 'label' => __('All')],
                        ['value' => '1',  'label' => __('Yes')],
                        ['value' => '0',  'label' => __('No')],
                    ]
                ],
                [
                    'name'  => 'datatable_filter[featured]',
                    'label' => __('Featured'),
                    'options' => [
                        ['value' => '-1', 'label' => __('All')],
                        ['value' => '1',  'label' => __('Yes')],
                        ['value' => '0',  'label' => __('No')],
                    ]
                ],
            ],

            // Actions configuration: define actions that can be applied to selected rows.
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
        $total = Plans::count();
        return view(module("key").'::index', [
            'total' => $total,
            'Datatable' => $this->Datatable
        ]);
    }

    public function list(Request $request)
    {
        $joins = [];
        $whereConditions = [];
        $dataTableService = \DataTable::make(Plans::class, $this->Datatable, $whereConditions, $joins);
        $data = $dataTableService->getData($request);
        return response()->json($data);
    }

    public function update(Request $request, $id = ""){
        $result = Plans::where("id_secure", $id)->first();
        return view(module("key").'::update', [
            "result" => $result
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $rules = [
            'name'         => 'required|string|max:255',
            'desc'         => 'required|string|max:255',
            'status'       => 'required|boolean',
            'featured'     => 'nullable',
            'position'     => 'nullable|integer',
            'permissions'  => 'required|array',
            'price'        => 'required|numeric',
            'type'         => 'required|in:1,2,3',
            'free_plan'    => 'required|in:0,1',
            'trial_day'    => 'required|integer|min:0',
        ];

        $permissions = $request->input('permissions', []);
        $labels = $request->input('labels', []);

        $permission_saved = [];

        foreach ($permissions as $key => $value) {
            $labelByKey = \Str::of($key)
            ->replace('_', ' ')
            ->title()
            ->value();

            $permission_saved[] = [
                'key' => $key,
                'label' => $labels[$key] ?? $labelByKey,
                'value' => $value
            ];
        }

        $data = [
            'id_secure'   => rand_string(),
            'name'        => $request->input('name'),
            'desc'        => $request->input('desc'),
            'featured'    => $request->input('featured'),
            'position'    => $request->input('position'),
            'permissions' => $permission_saved,
            'price'       => $request->input('price'),
            'type'        => $request->input('type'),
            'free_plan'   => $request->input('free_plan'),
            'trial_day'   => $request->input('trial_day'),
            'status'      => (int)$request->input('status'),
            'changed'     => time(),
            'created'     => time(),
        ];

        if ($request->has('id')) {
            $data['id_secure'] = $request->input('id');
        }

        $response = \DBHelper::saveData(Plans::class, $rules, $data, ['id_secure', 'created']);

        $userIds = User::where('plan_id', $response['id'] ?? 0)->pluck('id')->toArray();

        if (!empty($userIds)) {
            Teams::whereIn('owner', $userIds)->update([
                'permissions' => json_encode($permission_saved)
            ]);
        }

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

        $response = \DBHelper::updateField(Plans::class, $request->input('id'), 'status', $status_update);
        return response()->json($response);
    }

    public function destroy(Request $request)
    {
        $response = \DBHelper::destroy(Plans::class, $request->input('id'));
        return response()->json($response);
    }

}
