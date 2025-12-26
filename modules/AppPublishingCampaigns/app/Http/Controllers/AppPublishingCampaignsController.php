<?php

namespace Modules\AppPublishingCampaigns\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Nwidart\Modules\Facades\Module;
use Modules\AppPublishing\Models\PostStat;
use Modules\AppPublishingCampaigns\Models\PostCampaign;
use DB;

class AppPublishingCampaignsController extends Controller
{
    public $table;
    public $modules;
    public $Datatable = [
        "element" => "DataTable",
        "columns" => false,
        "order" => [3, 'desc'],
        "lengthMenu" => [10, 25, 50, 100, 150, 200],
        "search_field" => ["name", "desc"]
    ];
    
    public function __construct()
    {
        $this->table = "post_campaigns";
        $this->module = Module::find( ex_str(__NAMESPACE__) );

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
        $total = PostCampaign::where("team_id", $request->team_id)->count();
        return view($this->module->getLowerName().'::index', [
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

        if (!empty($order)) {
            $order_index = $order[0]['column'];
            $order_sort = $order[0]['dir'] == "desc" ? "desc" : "asc";
            $order_field = isset($this->Datatable['columns'][$order_index]) ? $this->Datatable['columns'][$order_index]['name'] : "id";
        } else {
            $order_index = $this->Datatable['order'][0];
            $order_sort = $this->Datatable['order'][1];
            $order_field = $this->Datatable['columns'][$order_index]['name'];
        }

        $order_field = $order_field == "succeed" || $order_field == "failed" ? "id" : $order_field;

        Paginator::currentPageResolver(function () use ($current_page) {
            return $current_page;
        });

        // Lấy danh sách campaign đang phân trang
        $pagination = PostCampaign::where("team_id", $request->team_id);
        $pagination = $pagination->orderBy($order_field, $order_sort);

        if ($status != -1) {
            $pagination->where('status', '=', $status);
        }

        if ($search != "" && isset($this->Datatable['search_field']) && !empty($this->Datatable['search_field'])) {
            $pagination->whereAny($this->Datatable['search_field'], 'like', '%' . $search . '%');
        }

        $pagination = $pagination->paginate($per_page);

        $campaign_ids = $pagination->pluck('id')->toArray();

        $post_stats = PostStat::where('team_id', $request->team_id)
            ->whereIn('campaign', $campaign_ids)
            ->whereIn('status', [4, 5])
            ->selectRaw('campaign, status, COUNT(*) as total')
            ->groupBy('campaign', 'status')
            ->get();

        $campaign_stats = [];
        foreach ($campaign_ids as $cid) {
            $campaign_stats[$cid] = ['success' => 0, 'failed' => 0];
        }
        foreach ($post_stats as $row) {
            if (!isset($campaign_stats[$row->campaign])) continue;
            if ($row->status == 4) $campaign_stats[$row->campaign]['success'] = $row->total;
            if ($row->status == 5) $campaign_stats[$row->campaign]['failed'] = $row->total;
        }

        if (!empty($pagination)) {
            foreach ($pagination as $key => $value) {
                $data_item = [];
                $cid = $value->id;

                if (!empty($this->Datatable['columns'])) {
                    foreach ($this->Datatable['columns'] as $column) {
                        if ($column['data'] != null) {
                            if (isset($column['type'])) {
                                $data_item[$column['data']] = FormatData($column['type'], $value->{$column['name']});
                            } else {
                                // Nếu là cột succeed/failed thì gán đúng giá trị từ thống kê
                                if ($column['name'] == "succeed") {
                                    $data_item[$column['data']] = isset($campaign_stats[$cid]) ? $campaign_stats[$cid]['success'] : 0;
                                } elseif ($column['name'] == "failed") {
                                    $data_item[$column['data']] = isset($campaign_stats[$cid]) ? $campaign_stats[$cid]['failed'] : 0;
                                } else {
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
        return view($this->module->getLowerName().'::update', [
            'module' => $this->module,
            'result' => false
        ]);
    }

    public function edit(Request $request, $id_secure = "")
    {
        $result = PostCampaign::where("team_id", $request->team_id)->where('id_secure', $id_secure)->first();
        if(!$result){
            return redirect( url_admin("languages") );
        }

        return view($this->module->getLowerName().'::update', [
            'module' => $this->module,
            'result' => $result
        ]);
    }

    public function save(Request $request)
    {

        $item = PostCampaign::where("team_id", $request->team_id)->where('id_secure', $request->id_secure)->first();

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
                'name' => $request->input('name'),
                'desc' => $request->input('desc'),
                'color' => $request->input('color'),
                'team_id' => $request->team_id,
                'status' => (int)$request->input('status'),
                'changed' => time()
            ];

            if($item){
                PostCampaign::where("team_id", $request->team_id)->where("id", $item->id)->update($values);
            }else{
                $values['id_secure'] = rand_string();
                $values['created'] = time();
                PostCampaign::insert($values);
            }
            
            ms(["status" => 1, "message" => "Succeed"]);
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

        PostCampaign::where("team_id", $request->team_id)
            ->whereIn('id_secure', $id_arr)
            ->update(['status' => $status]);

        ms(["status" => 1, "message" => "Succeed"]);

    }

    public function destroy(Request $request)
    {
        $id_arr = id_arr( $request->input('id') );
        if(empty($id_arr))
              ms(["status" => 0, "message" => __("Please select at least one item")]);

        PostCampaign::where("team_id", $request->team_id)->whereIn('id_secure', $id_arr)->delete();
        ms(["status" => 1, "message" => __("Succeed")]);
    }

}
