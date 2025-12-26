<?php

namespace Modules\AppGroups\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\Paginator;

class AppGroupsController extends Controller
{
    protected $table = 'groups';

    public function index(Request $request)
    {
        $total = DB::table($this->table)
            ->where('team_id', $request->team_id)
            ->count();

        return view('appgroups::index', [
            'total'  => $total,
            'module' => $request->module,
        ]);
    }

    public function list(Request $request)
    {
        $search = $request->input('keyword');
        $module_name = $request->input('module_name');
        $current_page = $request->input('page') + 1;

        Paginator::currentPageResolver(fn() => $current_page);

        $query = DB::table($this->table)
            ->where('team_id', $request->team_id);

        if ($search) {
            $query->whereAny(['name'], 'like', "%$search%");
        }

        $items = $query->orderByDesc('changed')->paginate(30);

        if ($items->total() === 0 && $current_page > 1) {
            return ms(['status' => 0]);
        }

        return ms([
            'status' => 1,
            'data' => view('appgroups::list', [
                'captions' => $items
            ])->render(),
        ]);
    }

    public function update(Request $request)
    {
        $result = DB::table($this->table)
            ->where('id_secure', $request->id)
            ->first();

        $accounts = DB::table('accounts')
            ->where('team_id', $request->team_id)
            ->get();

        return ms([
            'status' => 1,
            'data' => view('appgroups::update', compact('accounts', 'result'))->render(),
        ]);
    }

    public function save(Request $request)
    {
        $item = DB::table($this->table)
            ->where('id_secure', $request->id_secure)
            ->first();

        $validatorRules = [
            'name' => ['required', Rule::unique($this->table, 'name')],
            'color' => ['required'],
            'accounts' => ['required'],
        ];

        if ($item) {
            $validatorRules['name'] = [
                'required',
                Rule::unique($this->table, 'name')->ignore($item->id),
            ];
        }

        $validator = Validator::make($request->all(), $validatorRules);

        $accountIds = (array) $request->accounts;
        $validAccounts = DB::table('accounts')
            ->whereIn('id_secure', $accountIds)
            ->where('team_id', $request->team_id)
            ->pluck('pid')
            ->toArray();

        $validator->after(function ($validator) use ($validAccounts) {
            if (empty($validAccounts)) {
                $validator->errors()->add('accounts', __('Please select at least one channel'));
            }
        });

        if (!$validator->passes()) {
            return ms([
                'status' => 0,
                'message' => $validator->errors()->first(),
            ]);
        }

        $values = [
            'team_id'  => $request->team_id,
            'name'     => $request->input('name'),
            'color'    => $request->input('color'),
            'accounts' => json_encode($validAccounts),
            'changed'  => time(),
        ];

        if ($item) {
            DB::table($this->table)->where('id', $item->id)->update($values);
        } else {
            $values['id_secure'] = rand_string();
            $values['created'] = time();
            DB::table($this->table)->insert($values);
        }

        return ms(['status' => 1, 'message' => 'Succeed']);
    }

    public function status(Request $request, $status = 'active')
    {
        $ids = Arr::wrap($request->input('id'));
        $id_arr = array_filter($ids);

        if (empty($id_arr)) {
            return ms([
                'status' => 0,
                'message' => __('Please select at least one item'),
            ]);
        }

        DB::table($this->table)
            ->whereIn('id_secure', $id_arr)
            ->update(['status' => $status === 'enable' ? 1 : 0]);

        return ms(['status' => 1, 'message' => 'Succeed']);
    }

    public function destroy(Request $request)
    {
        $id_arr = id_arr($request->input('id'));

        if (empty($id_arr)) {
            return ms(['status' => 0, 'message' => __('Please select at least one item')]);
        }

        DB::table($this->table)->whereIn('id_secure', $id_arr)->delete();

        return ms(['status' => 1, 'message' => __('Succeed')]);
    }
}
