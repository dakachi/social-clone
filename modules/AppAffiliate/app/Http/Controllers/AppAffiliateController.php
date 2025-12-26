<?php

namespace Modules\AppAffiliate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Modules\AdminAffiliate\Models\AffiliateInfo;
use Modules\AdminAffiliate\Models\AffiliateWithdrawal;
use Illuminate\Validation\ValidationException;
use DB;

class AppAffiliateController extends Controller
{
    public $modules;
    public $Datatable = [
        "element" => "DataTable",
        "columns" => false,
        "order" => [5, 'desc'],
        "lengthMenu" => [10, 25, 50, 100, 150, 200],
        "search_field" => ["bank", "amount"]
    ];

    public function __construct()
    {
        $this->table = "affiliate_withdrawal";



        $this->Datatable['columns'] = [
            [ "data" => 'id', "title" => __('Payment ID'), "name" => "id", "className" => "align-middle text-center" ],
            [ "data" => 'amount', "title" => __('Amount'), "name" => "amount", "className" => "align-middle" ],
            [ "data" => 'bank', "title" => __('Payment Method'), "name" => "bank", "className" => "align-middle" ],
            [ "data" => 'status', "title" => __('Status'), "name" => "status", "className" => "align-middle w-80" ],
            [ "data" => 'created', "title" => __('Date'), "name" => "created", "type" => "datetime", "className" => "align-middle w-auto text-center" ],
            [ "data" => 'notes', "title" => __('Notes'), "name" => "notes", "className" => "align-middle w-auto text-center"],
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
        $data = [];
        $current_page = (int)$request->input('start') + 1;
        $per_page = (int)$request->input('length');
        $order = $request->input('order');
        $status = (int)$request->input('status');
        $search = $request->input('search');

        if(!empty($order))
        {
            $order_index = $order[0]['column'];
            $order_sort = $order[0]['dir']=="desc"?"desc":"asc";
            $order_field = isset($this->Datatable['columns'][$order_index])?$this->Datatable['columns'][$order_index]['name']:"id";
        }
        else
        {
            $order_index = $this->Datatable['order'][0];
            $order_sort = $this->Datatable['order'][1];
        }

        Paginator::currentPageResolver(function () use ($current_page){
            return $current_page;
        });

        $pagination = DB::table( $this->table );
        $pagination->where('affiliate_uid', auth()->id());
        
        if($status != -1)
        {
            $pagination->where('status', '=', $status);
        }

        if( $search != "" &&
            isset($this->Datatable['search_field']) &&
            !empty($this->Datatable['search_field'])
        )
        {
            $pagination->whereAny($this->Datatable['search_field'], 'like', '%'.$search.'%');
        }

        $pagination = $pagination->paginate($per_page);
        $data = [];
        foreach ($pagination->items() as $item) {
            $row = [];
            foreach ($this->Datatable['columns'] as $column) {
                if ($column['name'] === "affiliate_uid") {
                    $row[$column['data']] = $item->affiliate_name ?? $item->affiliate_uid;
                } elseif ($column['name'] === "notes") {

                    $row[$column['data']] = '<i class="fa fa-info-circle text-muted" data-bs-toggle="tooltip" title="' . htmlspecialchars($item->notes ?? __('Empty')) . '"></i>';
                } else {
                    $row[$column['data']] = isset($column['type'])
                        ? FormatData($column['type'], $item->{$column['name']})
                        : $item->{$column['name']};
                }
            }
            $data[] = $row;
        }

        $return = [
            "recordsTotal" => $pagination->total(),
            "recordsFiltered" => $pagination->total(),
            "data" => $data
        ];

        return json_encode($return);
    }

    public function update(Request $request){

        $id = $request->id;

        $accounts = DB::table( "accounts" )->where("team_id", $request->team_id)->get();
        $result = DB::table( $this->table )->where("id", $id)->first();

        ms([
            "status" => 1,
            "data" => view('affiliate_withdrawal::update',[
                "accounts" => $accounts,
                "result" => $result,
            ])->render()
        ]);
    }

    public function withdrawalRequest(Request $request)
    {
        try {
            $validated = $request->validate([
                'bank'   => ['required', 'string', 'max:255'],
                'amount' => ['required', 'numeric', 'min:0.01'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $firstError = collect($errors)->flatten()->first();
            return response()->json([
                'status'  => 0,
                'message' => $firstError ?: __("Validation error."),
            ]);
        }

        $user = auth()->user();
        $affiliate_uid = $user->id;

        $info = AffiliateInfo::where('affiliate_uid', $affiliate_uid)->lockForUpdate()->first();
        if (!$info) {
            return response()->json([
                'status' => 0,
                'message' => __('Affiliate information not found.')
            ]);
        }

        $min_withdrawal = (float) get_option("affiliate_minimum_withdrawal", 50);

        if ($validated['amount'] > $info->total_balance) {
            return response()->json([
                'status' => 0,
                'message' => __("Insufficient account balance.")
            ]);
        }
        if ($validated['amount'] < $min_withdrawal) {
            return response()->json([
                'status' => 0,
                'message' => __("The amount must be greater than or equal to :amount.", ['amount' => $min_withdrawal])
            ]);
        }

        \DB::beginTransaction();
        try {
            $info->total_balance -= $validated['amount'];
            $info->save();

            $withdrawal = AffiliateWithdrawal::create([
                'id_secure'     => rand_string(32),
                'affiliate_uid' => $affiliate_uid,
                'amount'        => $validated['amount'],
                'bank'          => $validated['bank'],
                'status'        => 0, // pending
                'created'       => time(),
            ]);

            \DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => __("Withdrawal request submitted successfully. Please wait for admin approval."),
                'data'    => [
                    'withdrawal_id' => $withdrawal->id_secure
                ]
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => __("An error occurred: ") . $e->getMessage(),
            ]);
        }
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
            case 'active':
                $status = 2;
                break;

            case 'inactive':
                $status = 1;
                break;

            case 'pending':
                $status = 3;
                break;

            default:
                $status = 0;
                break;
        }

        DB::table('affiliate_withdrawal')
            ->whereIn('id_secure', $id_arr)
            ->update(['status' => $status]);

        ms(["status" => 1, "message" => "Succeeded"]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $user = auth()->user(); // current user

        $inviteUrl = url("?ref=" . $user->id_secure);
        $inviter_name = $user->fullname ?? $user->username ?? $user->email;

        \MailSender::sendByTemplate('send_affiliate', $email, [
            'inviter_name' => $inviter_name,
            'invite_url' => $inviteUrl,
        ]);

        return response()->json([
            'status' => 1,
            'message' => __('Your invitation email has been sent successfully'),
            'redirect' => '',
        ]);
    }



}
