<?php

namespace Modules\AppCaptions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use DB;

class AppCaptionsController extends Controller
{
    public function __construct()
    {
        $this->table = "captions";
    }

    public function index(Request $request)
    {
        $total = DB::table( $this->table )->where("team_id", $request->team_id)->count();
        return view( module('key') . '::index', [
            'total' => $total,
            'module' => $request->module,
        ]);
    }

    public function list(Request $request){
        $search = $request->input("keyword");
        $status = $request->input("status");
        $module_name = $request->input("module_name");
        $current_page = $request->input("page") + 1;
        $per_page = 10;

        $wheres = ["team_id" => $request->team_id ];

        Paginator::currentPageResolver(function () use ($current_page){
            return $current_page;
        });

        $query = DB::table( $this->table )->where($wheres);

        if($search){
            $query->whereAny(['name', 'content'], 'like', '%'.$search.'%');
        }

        $captions = $query->orderByDesc('changed')->paginate($per_page);

        if( $captions->Total() == 0 && $current_page > 1 ){
            ms([
                "status" => 0
            ]);
        }

        switch ( $request->segment(4) ) {
            case 'popup':
                $view = 'popup_list';
                break;
            
            default:
                $view = 'list';
                break;
        }

        ms([
            "status" => 1,
            "data" => view( module('key') . '::' . $view ,[
                "captions" => $captions
            ])->render()
        ]);
    }

    public function update(Request $request){

        $id = $request->id;

        $result = DB::table( $this->table )->where("id_secure", $id)->first();

        ms([
            "status" => 1,
            "data" => view( module('key') . '::update',[
                "result" => $result
            ])->render()
        ]);
    }

    public function save(Request $request)
    {
        $item = DB::table( $this->table )->where('id_secure', $request->id_secure)->first();

        $validator_arr = [
            'name' => "required",
            'content' => 'required'
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
                'content' => $request->input('content'),
                'changed' => time()
            ];

            if($item){
                DB::table( $this->table )->where("id", $item->id)->update($values);
            }else{
                $values['type'] = (int)$request->type;
                $values['id_secure'] = rand_string();
                $values['created'] = time();
                DB::table( $this->table )->insert($values);
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

        DB::table( $this->table )
            ->whereIn('id_secure', $id_arr)
            ->update(['status' => $status]);

        ms(["status" => 1, "message" => "Succeed"]);

    }

    public function destroy(Request $request)
    {
        $id_arr = id_arr( $request->input('id') );
        if(empty($id_arr))
              ms(["status" => 0, "message" => __("Please select at least one item")]);

        DB::table( $this->table )->whereIn('id_secure', $id_arr)->delete();
        ms(["status" => 1, "message" => __("Succeed")]);
    }

    public function saveCation(Request $request){

        $id = $request->id;

        $result = DB::table( $this->table )->where("id_secure", $id)->first();

        ms([
            "status" => 1,
            "data" => view( module('key') . '::popup_save',[
                "result" => $result
            ])->render()
        ]);
    }

    public function getCation(){
        ms([
            "status" => 1,
            "data" => view( module('key') .'::get_caption', [
            ])->render()
        ]);
    }
}
