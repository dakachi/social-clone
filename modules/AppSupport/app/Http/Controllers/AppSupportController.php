<?php

namespace Modules\AppSupport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Modules\AppSupport\Models\Support;
use Modules\AppSupport\Models\SupportCategories;
use Modules\AppSupport\Models\SupportLabels;
use Modules\AppSupport\Models\SupportMapLabels;
use Modules\AppSupport\Models\SupportTypes;
use Modules\AppSupport\Models\SupportComments;
use Modules\AppSupport\Events\SupportEvents;
use App\Models\User;
use DB;
use Auth;

class AppSupportController extends Controller
{
    public $modules;
    public $Datatable = [];

    public function __construct()
    {
        $this->table = "support_tickets";
        $this->category_table = "support_categories";
        $this->Datatable = [
            "element" => "DataTable",
            "columns" => false,
            "order" => ['changed:name', 'desc'],
            "lengthMenu" => [25, 50, 100, 150, 200],
            "search_field" => ["content", "title"],
            "columns" => [
                [
                    "data" => 'id_secure',
                    "name" => "id_secure",
                    "className" => "text-truncate py-3 w-40"
                ],
                [
                    "data" => 'title',
                    "title" => __('Tickets'),
                    "name" => "title",
                    "className" => "align-middle min-w-450 py-3 text-break"
                ],
                [
                    "data" => 'user_read',
                    "title" => __('Read'),
                    "name" => "user_read",
                    "className" => "text-truncate py-3"],
                [
                    "data" => 'category_name',
                    "title" => __('Category'),
                    "name" => "category_name",
                    "className" => "text-truncate py-3"
                ],
                [
                    "data" => 'category_color',
                    "title" => __('Category Color'),
                    "name" => "category_color",
                    "className" => "text-truncate py-3"
                ],
                [
                    "data" => 'type_name',
                    "title" => __('Type Name'),
                    "name" => "type_name",
                    "className" => "text-truncate py-3"
                ],
                [
                    "data" => 'type_color',
                    "title" => __('Type Color'),
                    "name" => "type_color",
                    "className" => "text-truncate py-3"
                ],
                [
                    "data" => 'type_icon',
                    "title" => __('Type Icon'),
                    "name" => "type_icon",
                    "className" => "text-truncate py-3"
                ],
                [
                    "data" => 'label_names',
                    "title" => __('Label Names'),
                    "name" => "label_names",
                    "className" => "text-truncate py-3"
                ],
                [
                    "data" => 'label_colors',
                    "title" => __('Label Colors'),
                    "name" => "label_colors",
                    "className" => "text-truncate py-3"
                ],
                [
                    "data" => 'label_icons',
                    "title" => __('Label Icons'),
                    "name" => "label_icons",
                    "className" => "text-truncate py-3"
                ],
                [
                    "data" => 'status',
                    "title" => __('Status'),
                    "name" => "status",
                    "className" => "py-3 w-80"
                ],
                [
                    "data" => 'changed',
                    "title" => __('Last replied'),
                    "name" => "changed",
                    "type" => "time_elapsed",
                    "className" => "text-truncate fs-12 py-3 w-80"
                ],
                [
                    "data" => 'created',
                    "title" => __('Created at'),
                    "name" => "created",
                    "type" => "datetime",
                    "className" => "text-truncate fs-12 py-3 w-80"
                ],
                [
                    "data" => 'created',
                    "title" => '',
                    "name" => "created",
                    "type" => "datetime",
                    "className" => "py-3 w-80"
                ],
            ],
        ];
    }

    public function index()
    {
        $total = Support::where("user_id", Auth::id())->count();
        $categories = SupportCategories::where('status',1)->get();
        $labels = SupportLabels::where('status', 1)->get();
        $types = SupportTypes::where('status',1)->get();
        return view(module("key").'::index', [
            'total' => $total,
            'Datatable' => $this->Datatable,
            'categories' => $categories,
            'labels' => $labels
        ]);
    }

    public function list(Request $request)
    {
        $params = [
            'start'   => $request->input('start'),
            'length'  => $request->input('length'),
            'order'   => $request->input('order'),
            'label_id' => $request->input('label_id'),
            'cate_id' => $request->input('cate_id'),
            'status'  => $request->input('status'),
            'search'  => $request->input('search'),
        ];

        $result = Support::getTicketsList($params);
        $data = [];

        if (!empty($result['data']))
        {
            foreach ($result['data'] as $key => $value)
            {
                $data_item = [];

                if (!empty($this->Datatable['columns']))
                {
                    foreach ($this->Datatable['columns'] as $column)
                    {
                        if ($column['name'] != null)
                        {
                            if (isset($column['type']))
                            {
                                $data_item[$column['name']] = FormatData($column['type'], $value[$column['name']]);
                            }
                            else
                            {
                                $data_item[$column['name']] = $value[$column['name']];
                            }
                        }
                        else
                        {
                            $data_item[$column['name']] = $value->{$column['name']};
                        }
                    }
                }

                $data[] = $data_item;
            }
        }

        $result['data'] = $data;

        return response()->json($result);
    }

    /**
     * Show a specific ticket with its details, recent tickets, and comments.
     */
    public function ticket(Request $request, $ticket_id)
    {
        $ticket = Support::getTicketDetail($ticket_id);
        if (empty($ticket) || !isset($ticket->user_id)) {
            return redirect()->route('app.support.index');
        }

        // Get recent tickets excluding the current ticket.
        $recent_tickets = Support::getRecentTickets($ticket_id);

        // Mark the ticket as read by updating app_read
        Support::where("id", $ticket->id)->update([ "user_read" => 0 ]);

        // Retrieve comments for the ticket
        $comments = Support::getCommentsByTicket($ticket->id);

        if($ticket->user_id != $ticket->open_by){
            $open_user = User::where("id", $ticket->open_by)->first();

            if($open_user){
                $ticket->user_fullname = $open_user->fullname;
                $ticket->user_avatar = $open_user->avatar;
            }
        }

        return view(module("key").'::view_ticket', [
            "ticket" => $ticket,
            "comments" => $comments,
            "recent_tickets" => $recent_tickets,
        ]);
    }

    public function new_ticket(Request $request)
    {
        $result = Support::where("id_secure", $request->id)->first();
        $categories = SupportCategories::where('status',1)->get();
        $labels = SupportLabels::where('status', 1)->get();
        $types = SupportTypes::where('status',1)->get();

        return view(module('key').'::new_ticket', [
            "result" => $result,
            "categories" => $categories,
            "labels" => $labels,
            "types" => $types,
        ]);

    }

    public function save(Request $request)
    {
        $category = SupportCategories::where("id_secure", $request->cate_id)->first();
        $type = SupportTypes::where("id_secure", $request->type_id)->first();
        $labels = $request->labels;

        $validator_arr = [
            'cate_id' => 'required',
            'subject' => "required",
            'content' => "required"
        ];

        $validator = Validator::make($request->all(), $validator_arr);

        if (empty($category)) {
            return ms([
                "status" => 0,
                "message" => __("The selected category is invalid. Please choose a valid category.")
            ]);
        }

        $type_id = !empty($type)?$type->id:0;

        if ($validator->passes()) {
            $values = [
                'id_secure' => rand_string(),
                'user_read' => 0,
                'admin_read' => 1,
                'cate_id' => $category->id,
                'type_id' => $type_id,
                'team_id' => $request->team_id,
                'user_id' => Auth::id(),
                'open_by' => Auth::id(),
                'pin' => 0,
                'title' => $request->input('subject'),
                'content' => $request->input('content'),
                'status' => 1,
                'changed' => time(),
                'created' => time(),
            ];

            $ticket_id = Support::insertGetId($values);

            if( is_array($labels) && !empty($labels) ){
                $label_arr = [];
                foreach ($labels as $label)
                {
                    $id_key = $label;
                    if($id_key != 0){
                        $label_arr[] = $id_key;
                    }
                }

                $support_lables = SupportLabels::whereIn('id_secure', $label_arr)->get();

                if(!empty($support_lables)){
                    foreach ($support_lables as $support_lable) {
                        SupportMapLabels::insert([
                            "ticket_id" => $ticket_id,
                            "label_id" => $support_lable->id,
                        ]);
                    }
                }
            }

            ms(["status" => 1, "message" => "Succeed", "redirect" => module_url()]);
        }

        return ms([
            "status" => 0,
            "message" => $validator->errors()->all()[0],
        ]);
    }

    public function resolved(Request $request)
    {
        $id = $request->input('id');
        $ticket = Support::where("id_secure", $id)->where("status", 1)->first();

        if(!$ticket){
            return ms([
                "status" => 0,
                "message" => __("Please select at least one item"),
            ]);
        }

        Support::where('id', $ticket->id)->update(['status' => 2]);

        ms(["status" => 1, "message" => "Succeed"]);
    }

    /**
     * Save or update a comment for a ticket.
     */
    public function save_comment(Request $request)
    {
        $ticket = Support::where('id_secure', $request->ticket_id)->first();
        $comment = SupportComments::where('id_secure', $request->comment_id)->first();

        $validator_arr = [
            'comment' => "required",
        ];

        $validator = Validator::make($request->all(), $validator_arr);

        if(empty($ticket)) {
            return ms([
                "status" => 0,
                "message" => __("The ticket does not exist"),
            ]);
        }

        if($ticket->status != 1) {
            return ms([
                "status" => 0,
                "message" => __("This ticket is closed. You cannot comment on it"),
            ]);
        }

        if ($validator->passes()) {
            $values = [
                'ticket_id' => $ticket->id,
                'user_id' => Auth::user()->id,
                'comment' => $request->input('comment'),
                'changed' => time()
            ];

            if($comment) {
                SupportComments::where("id", $comment->id)->update($values);
            } else {
                $values['id_secure'] = rand_string();
                $values['created'] = time();
                SupportComments::insert($values);

                // Mark the ticket as read by updating user read
                Support::where("id", $ticket->id)->update([ "admin_read" => 1 ]);
            }

            // Broadcast the event with minimal info via SupportEvents event
            if(get_option("broadcast_driver", 0)){
                try {
                    broadcast(new SupportEvents([
                        'ticket_id' => $ticket->id_secure,
                        'user_token' => session("user_token"),
                        'user_id' => Auth::user()->id_secure,
                    ]))->toOthers();
                } catch (\Exception $e) {}
            }

            Support::where("id", $ticket->id)->update([
                "changed" => time()
            ]);

            return ms(["status" => 1, "message1" => "Succeed"]);
        }

        return ms([
            "status" => 0,
            "message" => $validator->errors()->all()[0],
        ]);
    }

    /**
     * Load additional comments for a ticket with pagination.
     */
    public function load_comment(Request $request)
    {
        $page = (int)$request->page + 1;
        $ticket = Support::getTicketDetail($request->ticket_id);
        if(empty($ticket)){
            return redirect()->route('app.support.index');
        }

        if($ticket->user_id != $ticket->open_by){
            $open_user = User::where("id", $ticket->open_by)->first();


            if($open_user){
                $ticket->user_fullname = $open_user->fullname;
                $ticket->user_avatar = $open_user->avatar;
                $ticket->user_id = $ticket->open_by;
            }
        }

        $comments = Support::getCommentsByTicket($ticket->id, $page);
        return response()->json([
            "status" => 1,
            "data" => view(module("key").'::comments', [
                "comments" => $comments,
                "ticket" => $ticket,
                "page" => $page,
            ])->render()
        ]);
    }


}
