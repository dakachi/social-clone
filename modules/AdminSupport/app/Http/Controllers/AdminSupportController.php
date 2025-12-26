<?php

namespace Modules\AdminSupport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Modules\AdminSupport\Models\Support;
use Modules\AdminSupport\Models\SupportCategories;
use Modules\AdminSupport\Models\SupportLabels;
use Modules\AdminSupport\Models\SupportTypes;
use Modules\AdminSupport\Models\SupportMapLabels;
use Modules\AdminSupport\Models\SupportComments;
use Modules\AdminSupport\Events\SupportEvents;
use App\Models\User;
use DB;
use Auth;

class AdminSupportController extends Controller
{
    public $table = "support_tickets";
    public $category_table = "support_categories";
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
            "search_field" => ["content", "title", "id_secure"],

            // Columns configuration: each array element corresponds to a column.
            "columns" => [

                [
                    "data" => 'id_secure', 
                    "name" => "id_secure", 
                    "className" => "align-middle text-truncate py-3 w-40"
                ],
                [
                    "data" => 'title', 
                    "title" => __('Tickets'), 
                    "name" => "title", 
                    "className" => "align-middle min-w-450 py-3"
                ],
                [
                    "data" => 'admin_read', 
                    "title" => __('Read'), 
                    "name" => "admin_read", 
                    "className" => "align-middle text-truncate py-3"
                ],
                [
                    "data" => 'category_name', 
                    "title" => __('Category'), 
                    "name" => "category_name", 
                    "className" => "align-middle text-truncate py-3"
                ],
                [
                    "data" => 'category_color', 
                    "title" => __('Category Color'), 
                    "name" => "category_color", 
                    "className" => "align-middle text-truncate py-3"
                ],
                [
                    "data" => 'type_name', 
                    "title" => __('Status Name'), 
                    "name" => "type_name", 
                    "className" => "align-middle text-truncate py-3"
                ],
                [
                    "data" => 'type_color', 
                    "title" => __('Status Color'), 
                    "name" => "type_color", 
                    "className" => "align-middle text-truncate py-3"
                ],
                [
                    "data" => 'type_icon', 
                    "title" => __('Status Icon'), 
                    "name" => "type_icon", 
                    "className" => "align-middle text-truncate py-3"
                ],
                [
                    "data" => 'user_username', 
                    "title" => __('Username'), 
                    "name" => "user_username", 
                    "className" => "align-middle text-truncate py-3"
                ],
                [
                    "data" => 'user_account_id', 
                    "title" => __('User Account ID'), 
                    "name" => "user_account_id", 
                    "className" => "align-middle text-truncate py-3"
                ],
                [
                    "data" => 'user_fullname', 
                    "title" => __('User'), 
                    "name" => "user_fullname", 
                    "className" => "align-middle text-truncate py-3"
                ],
                [
                    "data" => 'user_avatar', 
                    "title" => __('Avatar'), 
                    "name" => "user_avatar", 
                    "className" => "align-middle text-truncate py-3"
                ],
                [
                    "data" => 'user_email', 
                    "title" => __('Email'), 
                    "name" => "user_email", 
                    "className" => "align-middle text-truncate py-3"
                ],
                [
                    "data" => 'label_names', 
                    "title" => __('Label Names'), 
                    "name" => "label_names", 
                    "className" => "align-middle text-truncate py-3"
                ],
                [
                    "data" => 'label_colors', 
                    "title" => __('Label Colors'), 
                    "name" => "label_colors", 
                    "className" => "align-middle text-truncate py-3"
                ],
                [
                    "data" => 'label_icons', 
                    "title" => __('Label Icons'), 
                    "name" => "label_icons", 
                    "className" => "align-middle text-truncate py-3"
                ],
                [
                    "data" => 'status', 
                    "title" => __('Status'), 
                    "name" => "status", 
                    "className" => "align-middle py-3 w-80"
                ],
                [
                    "data" => 'changed', 
                    "title" => __('Last replied'), 
                    "name" => "changed", 
                    "type" => "time_elapsed", 
                    "className" => "align-middle text-truncate fs-12 py-3 w-80"
                ],
                [
                    "data" => 'created', 
                    "title" => __('Created at'), 
                    "name" => "created", "type" => "datetime", 
                    "className" => "align-middle text-truncate fs-12 py-3 w-80"
                ],
                [
                    "data" => 'created', 
                    "title" => __('Action'), 
                    "name" => "created", 
                    "type" => "datetime", 
                    "className" => "align-middle py-3 w-80"
                ],
            ],
            'status_filter' => [
                [
                    'value' => '-1',
                    'name' => 'all',
                    'label' => __('All')
                ],
                [
                    'value' => '0', 
                    'name' => 'disable', 
                    'icon' => 'a-light fa-circle-check', 
                    'color' => 'light', 
                    'label' => __('Closed')
                ],                    
                [
                    'value' => '1', 
                    'name' => 'enable', 
                    'icon' => 'fa-light fa-door-open', 
                    'color' => 'success', 
                    'label' => __('Open')
                ],
                [
                    'value' => '2', 
                    'name' => 'disable', 
                    'icon' => 'a-light fa-circle-check', 
                    'color' => 'light', 
                    'label' => __('Resolved')
                ],
            ],   
        ];
    }

    /**
     * Index page: display the list of support tickets.
     */

    public function index()
    {
        $total = DB::table($this->table)->count();
        $categories = SupportCategories::all();
        $labels = SupportLabels::all();
        return view(module("key").'::index', [
            'total' => $total,
            'Datatable' => $this->Datatable,
            'categories' => $categories,
            'labels' => $labels
        ]);
    }

    /**
     * Retrieve filtered list of support tickets for DataTable.
     */
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

        if (!empty($result['data'])) {
            foreach ($result['data'] as $value) {
                $data_item = [];
                if (!empty($this->Datatable['columns'])) {
                    foreach ($this->Datatable['columns'] as $column) {
                        if (isset($column['name'])) {
                            // If data has a type, format it accordingly. Otherwise, simply assign.
                            $data_item[$column['name']] = isset($column['type']) ? FormatData($column['type'], $value[$column['name']]) : $value[$column['name']];
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
        if (empty($ticket)) {
            return redirect()->route('admin.support.index');
        }

        // Get recent tickets excluding the current ticket.
        $recent_tickets = Support::getRecentTickets($ticket_id);

        // Mark the ticket as read by updating admin_read
        Support::where("id", $ticket->id)->update([ "admin_read" => 0 ]);

        // Retrieve comments for the ticket
        $comments = Support::getCommentsByTicket($ticket->id);

        return view(module("key").'::view_ticket', [
            "ticket" => $ticket,
            "comments" => $comments,
            "recent_tickets" => $recent_tickets,
        ]);
    }

    /**
     * Return update view for a specific ticket.
     */
    public function update(Request $request)
    {
        $result = DB::table($this->table)->where("id_secure", $request->id)->first();
        $categories = DB::table($this->category_table)->get();

        return ms([
            "status" => 1,
            "data" => view(module('key').'::update', [
                "result" => $result,
                "categories" => $categories,
            ])->render()
        ]);
    }

    /**
     * Update status (or pin, unread) for selected tickets.
     */


    public function status(Request $request, $status = "open")
    {
        $ids = $request->input('id');
        if(empty($ids)){
            return ms([
                "status" => 0,
                "message" => __("Please select at least one item")
            ]);
        }

        if (is_string($ids)) {
            $ids = [$ids];
        }

        // Filter to get only non-zero IDs.
        $id_arr = array_filter($ids, function ($value) {
            return $value != 0;
        });

        $updates = [];

        switch ($status) {
            case 'open':
                $updates["status"] = 1;
                break;
            case 'resolved':
                $updates["status"] = 2;
                break;
            case 'close':
                $updates["status"] = 0;
                break;
            case 'pin':
                $updates["pin"] = 1;
                break;
            case 'unpin':
                $updates["pin"] = 0;
                break;
            case 'unread':
                $updates["admin_read"] = 1;
                break;
        }

        DB::table($this->table)
            ->whereIn('id_secure', $id_arr)
            ->update($updates);

        return ms(["status" => 1, "message" => __("Succeed")]);
    }


    /**
     * Delete selected tickets and their associated comments and map labels.
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('id');
        if(empty($ids)){
            return ms([
                "status" => 0,
                "message" => __("Please select at least one item")
            ]);
        }

        if(is_string($ids)){
            $ids = [$ids];
        }

        // Filter non-zero IDs.
        $id_arr = array_filter($ids, function($value) {
            return $value != 0;
        });

        $tickets = Support::whereIn('id_secure', $id_arr)->get();

        if(!empty($tickets)){
            foreach ($tickets as $ticket) {
                Support::where('id', $ticket->id)->delete();
                SupportComments::where('ticket_id', $ticket->id)->delete();
                SupportMapLabels::where('ticket_id', $ticket->id)->delete();
            }
        }
        
        return ms([
            "status" => 1,
            "message" => __("Succeed")
        ]);
    }

    /**
     * Return view for updating a ticket.
     */
    public function update_ticket(Request $request, $id = false)
    {
        $ticket = Support::getTicketDetail($id);
        $categories = SupportCategories::all();
        $labels = SupportLabels::all();
        $types = SupportTypes::all();

        return view(module('key').'::update_ticket', [
            "ticket" => $ticket,
            "categories" => $categories,
            "labels" => $labels,
            "types" => $types,
        ]);
    }

    /**
     * Save or update a ticket.
     */
    public function save(Request $request)
    {
        $ticket = Support::where("id_secure", $request->id)->first();
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

        $type_id = !empty($type) ? $type->id : 0;

        if ($validator->passes()) {
            $values = [
                'cate_id' => $category->id,
                'type_id' => $type_id,
                'title' => $request->input('subject'),
                'content' => $request->input('content'),
                'changed' => time(),
            ];

            if(empty($ticket)){


                $user = User::where("id_secure", $request->user_id)->firstOrFail();
                if (empty($user)) {
                    return ms([
                        "status" => 0,
                        "message" => __("Please select a recipient for the ticket.")
                    ]);
                }

                $values["team_id"] = $request->team_id;
                $values["pin"] = 0;
                $values["status"] = 1;
                $values["open_by"] = Auth::id();
                $values["user_id"] = $user->id;
                $values["user_read"] = 1;
                $values["admin_read"] = 0;
                $values["id_secure"] = rand_string();
                $values["created"] = time();
                $ticket_id = Support::insertGetId($values);
            } else {
                $ticket_id = $ticket->id;
                Support::where("id", $ticket->id)->update($values);
                SupportMapLabels::where('ticket_id', $ticket_id)->delete();
            }

            if(is_array($labels) && !empty($labels)) {
                $label_arr = [];
                foreach ($labels as $label) {
                    if($label != 0) {
                        $label_arr[] = $label;
                    }
                }
                $support_labels = SupportLabels::whereIn('id_secure', $label_arr)->get();
                if(!empty($support_labels)){
                    foreach ($support_labels as $support_label) {
                        SupportMapLabels::insert([
                            "ticket_id" => $ticket_id,
                            "label_id" => $support_label->id,
                        ]);
                    }
                }
            }

            return ms(["status" => 1, "message" => "Succeed", "redirect" => module_url()]);
        }

        return ms([
            "status" => 0,
            "message" => $validator->errors()->all()[0],
        ]);
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
                Support::where("id", $ticket->id)->update([ "user_read" => 1 ]);
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
            return redirect()->route('admin.support.index');
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

    /**
     * Return edit view for a specific comment.
     */
    public function edit_comment(Request $request)
    {
        $comment_id = $request->input('id');
        $comment = SupportComments::where("id_secure", $comment_id)->first();
        if(empty($comment)) {
            return ms([
                "status" => 0,
                "message" => __("The comment does not exist"),
            ]);
        }
        $ticket = Support::where("id", $comment->ticket_id)->first();
        if(empty($ticket)) {
            return ms([
                "status" => 0,
                "message" => __("The ticket does not exist")
            ]);
        }
        return response()->json([
            "status" => 1,
            "data" => view(module("key").'::edit_comment', [
                "result" => $comment,
                "ticket_id" => $ticket->id_secure
            ])->render()
        ]);
    }

    /**
     * Delete a specific comment.
     */
    public function delete_comment(Request $request)
    {
        $comment_id = $request->input('id');
        $comment = SupportComments::where("id_secure", $comment_id)->first();
        if(empty($comment)) {
            return ms([
                "status" => 0,
                "message" => __("The comment does not exist"),
            ]);
        }
        SupportComments::where('id', $comment->id)->delete();
        return ms([
            "status" => 1
        ]);
    }
}
