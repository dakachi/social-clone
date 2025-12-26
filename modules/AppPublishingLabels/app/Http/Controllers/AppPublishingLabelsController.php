<?php

namespace Modules\AppPublishingLabels\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\AppPublishing\Models\PostStat;
use Modules\AppPublishingLabels\Models\PostLabel;

class AppPublishingLabelsController extends Controller
{
    public $table;
    public $modules;
    public $Datatable = [
        "element" => "DataTable",
        "columns" => false,
        "order" => [2, 'desc'],
        "lengthMenu" => [10, 25, 50, 100, 150, 200],
        "search_field" => ["name"]
    ];
    
    public function __construct()
    {
        $this->table = "post_labels";

        $this->Datatable['columns'] = [
            [ "data" => 'RecordID', "name" => "id_secure", "className" => "align-middle w-40" ],
            [ "data" => __('Name'), "name" => "name", "className" => "align-middle" ],
            [ "data" => __('Color'), "name" => "color", "className" => "align-middle" ],
            [ "data" => __('Post Succeed'), "name" => "succeed", "className" => "align-middle" ],
            [ "data" => __('Post Failed'), "name" => "failed", "className" => "align-middle" ],
            [ "data" => __('Status'), "name" => "status", "className" => "align-middle w-80" ],
            [ "data" => __('Changed'), "name" => "changed", "className" => "align-middle" ],
        ];
    }

    public function index(Request $request)
    {
        $total = PostLabel::where("team_id", $request->team_id)->count();
        return view(module("key").'::index', [
            'total' => $total,
            'Datatable' => $this->Datatable,
        ]);
    }

    public function list(Request $request)
    {
        $data = [];
        $current_page = (int)$request->input('start') + 1;
        $per_page = (int)$request->input('length');
        $order = $request->input('order');
        $status = (int)$request->input('status');
        $search = $request->input('search');

        if(!empty($order)) {
            $order_index = $order[0]['column'];
            $order_sort = $order[0]['dir']=="desc"?"desc":"asc";
            $order_field = isset($this->Datatable['columns'][$order_index])?$this->Datatable['columns'][$order_index]['name']:"id";

        } else {
            $order_index = $this->Datatable['order'][0];
            $order_sort = $this->Datatable['order'][1];
            $order_field = $this->Datatable['columns'][$order_index]['name'];
        }

        $order_field = $order_field == "succeed" || $order_field == "failed" ? "id" : $order_field;

        Paginator::currentPageResolver(function () use ($current_page){
            return $current_page;
        });

        $pagination = PostLabel::orderBy($order_field ?? 'id', $order_sort ?? 'desc')
            ->where("team_id", $request->team_id);

        if ($status != -1) {
            $pagination->where('status', '=', $status);
        }
        if ($search != "" && isset($this->Datatable['search_field']) && !empty($this->Datatable['search_field'])) {
            $pagination->whereAny($this->Datatable['search_field'], 'like', '%'.$search.'%');
        }

        $pagination = $pagination->paginate($per_page);
        $label_ids = $pagination->pluck('id')->toArray();

        $stats = PostStat::where('team_id', $request->team_id)
            ->where(function($q) use ($label_ids) {
                foreach ($label_ids as $lid) {
                    $q->orWhereJsonContains('labels', (int)$lid);
                }
            })
            ->whereIn('status', [4, 5])
            ->get(['labels', 'status']);

        $labels_stats = [];
        foreach ($label_ids as $label_id) {
            $labels_stats[$label_id] = ['success' => 0, 'failed' => 0];
        }
        foreach ($stats as $row) {
            $labels = is_array($row->labels) ? $row->labels : json_decode($row->labels, true);
            if (!is_array($labels)) continue;
            foreach ($labels as $label_id) {
                if (!isset($labels_stats[$label_id])) continue;
                if ($row->status == 4) {
                    $labels_stats[$label_id]['success']++;
                } else if ($row->status == 5) {
                    $labels_stats[$label_id]['failed']++;
                }
            }
        }

        if (!empty($pagination)) {
            foreach ($pagination as $key => $value) {
                $data_item = [];
                if (!empty($this->Datatable['columns'])) {
                    foreach ($this->Datatable['columns'] as $column) {
                        if ($column['data'] != null) {
                            if (isset($column['type'])) {
                                $data_item[$column['data']] = FormatData($column['type'], $value->{$column['name']});
                            } else {
                                $label_id = $value->id;
                                if($column['name'] == "succeed"){
                                    $data_item[$column['data']] = isset($labels_stats[$label_id]) ? $labels_stats[$label_id]['success'] : 0;
                                }elseif($column['name'] == "failed"){
                                    $data_item[$column['data']] = isset($labels_stats[$label_id]) ? $labels_stats[$label_id]['failed'] : 0;
                                }else{
                                    $data_item[$column['data']] = $value->{$column['name']};
                                }
                            }
                        } else {
                            $data_item[$column['name']] = $value->{$column['name']};
                        }
                    }
                }
                $data[] = $data_item;
            }
        }

        $return = [
            "recordsTotal" => $pagination->total(),
            "recordsFiltered" => $pagination->total(),
            "data" => $data
        ];

        return json_encode($return);
    }

    public function create()
    {
        return view(module("key").'::update', [
            'result' => false
        ]);
    }

    public function edit(Request $request, $id_secure = "")
    {
        $result = PostLabel::where('id_secure', $id_secure)->where("team_id", $request->team_id)->first();
        return view(module("key").'::update', [
            'result' => $result
        ]);
    }

    public function save(Request $request)
    {

        $item = PostLabel::where('id_secure', $request->id_secure)->where("team_id", $request->team_id)->first();

        $validator_arr = [
            'name' => "required|unique:{$this->table},name",
            'color' => 'required',
            'status' => 'required'
        ];

        if($item){
            $validator_arr['name'] = [
                "required",
                Rule::unique($this->table)->ignore($item->id),
            ];
        }

        $validator = Validator::make($request->all(), $validator_arr);

        if ($validator->passes()) {
            $values = [
                'team_id' => $request->team_id,
                'name' => $request->input('name'),
                'color' => $request->input('color'),
                'status' => (int)$request->input('status'),
                'changed' => time()
            ];

            if($item){
                PostLabel::where("id", $item->id)->update($values);
            }else{
                $values['id_secure'] = rand_string();
                $values['created'] = time();
                PostLabel::insert($values);
            }
            
            ms(["status" => 1, "message" => __('Succeed')]);
        }

        return ms([ 
            "status" => 0, 
            "message" => $validator->errors()->all()[0], 
        ]);
    }

    public function status(Request $request, $status = "active")
    {
        $ids = $request->input('id');
        $id_arr = [];

        if(empty($ids)){
            return ms([ 
                "status" => 0,
                "message" => __("Please select at least one item"),
            ]);
        }

        if(is_string($ids)){
            $ids = [$ids];
        }

        foreach ($ids as $value) 
        {
            $id_key = $value;
            if($id_key != 0){
                $id_arr[] = $id_key;
            }
        }

        switch ($status) 
        {
            case 'enable':
                $status = 1;
                break;
            
            default:
                $status = 0;
                break;
        }

        PostLabel::whereIn('id_secure', $id_arr)
            ->where("team_id", $request->team_id)
            ->update(['status' => $status]);

        ms(["status" => 1, "message" => __('Succeed')]);

    }

    public function destroy(Request $request)
    {
        $id_arr = id_arr( $request->input('id') );
        if(empty($id_arr))
              ms(["status" => 0, "message" => __("Please select at least one item")]);

        PostLabel::whereIn('id_secure', $id_arr)->where("team_id", $request->team_id)->delete();
        ms(["status" => 1, "message" => __("Succeed")]);
    }
}
