<?php
namespace Modules\AdminSupportCategories\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class AdminSupportCategoriesController extends Controller
{
    public $table = "support_categories";
    public $modules;
    public $Datatable;
    public function __construct()
    {
        $this->Datatable = [
            "element" => "DataTable",
            "order" => ['created', 'desc'],
            "lengthMenu" => [10, 25, 50, 100, 150, 200],
            "search_field" => ["name"],
        ];
    }

    public function index(Request $request)
    {
        $total = DB::table($this->table)->count();
        return view(module("key").'::index', [
            'total' => $total,
            'module' => $request->module,
            'Datatable' => $this->Datatable
        ]);
    }

    public function list(Request $request)
    {
        $search = $request->input("keyword");
        $current_page = (int)$request->input("page") + 1;
        $per_page = 30;

        $query = DB::table($this->table);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('icon', 'like', '%'.$search.'%');
            });
        }

        $results = $query->orderByDesc('changed')->paginate($per_page, ['*'], 'page', $current_page);

        if ($results->total() == 0 && $current_page > 1) {
            return ms(["status" => 0]);
        }

        return ms([
            "status" => 1,
            "data" => view(module("key").'::list', [
                "results" => $results
            ])->render()
        ]);
    }


    public function update(Request $request)
    {
        $result = DB::table($this->table)->where("id_secure", $request->id)->first();

        return ms([
            "status" => 1,
           "data" => view(module("key").'::update', [
                "result" => $result
            ])->render()
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $rules = [
            'name'         => 'required|string|max:255',
            'icon'         => 'required|string|max:255',
            'color'        => 'required|string|max:255',
            'status'       => 'required|boolean',
        ];

        $data = [
            'id_secure'     => rand_string(),
            'name'          => $request->input('name'),
            'icon'          => $request->input('icon'),
            'color'         => $request->input('color'),
            'name'          => $request->input('name'),
            'status'        => (int)$request->input('status'),
            'changed'       => time(),
            'created'       => time(),
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
