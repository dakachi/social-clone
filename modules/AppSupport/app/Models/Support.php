<?php

namespace Modules\AppSupport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Auth;

class Support extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'support_tickets';

    public static function getTicketsList(array $params)
    {
        $start        = isset($params['start']) ? (int)$params['start'] : 0;
        $per_page     = isset($params['length']) ? (int)$params['length'] : 10;
        $current_page = intval($start / $per_page) + 1;

        $order_field = "t.id";
        $order_sort  = "desc";

        if (isset($params['order']) && is_array($params['order'])) {
            $order_arr = $params['order'][0] ?? null;
            if ($order_arr) {
                $order_index = $order_arr['column'] ?? 0;
                $order_sort  = (($order_arr['dir'] ?? 'asc') === "desc") ? "desc" : "asc";
                $columnsMapping = [
                    0 => 't.id_secure',
                    1 => 't.content',
                    2 => 'c.name',
                ];
                if (isset($columnsMapping[$order_index])) {
                    $order_field = $columnsMapping[$order_index];
                }
            }
        }

        Paginator::currentPageResolver(function () use ($current_page) {
            return $current_page;
        });

        $labelsSub = DB::table('support_map_labels as sml')
            ->join('support_labels as l', 'sml.label_id', '=', 'l.id')
            ->select(
                'sml.ticket_id',
                DB::raw("GROUP_CONCAT(DISTINCT l.name  ORDER BY l.name  SEPARATOR ',') as label_names"),
                DB::raw("GROUP_CONCAT(DISTINCT l.color ORDER BY l.color SEPARATOR ',') as label_colors"),
                DB::raw("GROUP_CONCAT(DISTINCT l.icon  ORDER BY l.icon  SEPARATOR ',') as label_icons")
            )
            ->groupBy('sml.ticket_id');

        $query = self::query()
            ->from('support_tickets as t')
            ->leftJoin('support_categories as c', 't.cate_id', '=', 'c.id')
            ->leftJoin('support_types as s', 't.type_id', '=', 's.id')
            ->leftJoinSub($labelsSub, 'lbl', function ($join) {
                $join->on('lbl.ticket_id', '=', 't.id');
            })
            ->select(
                't.id',
                't.id_secure',
                't.title',
                't.content',
                't.status',
                't.changed',
                't.created',
                't.user_read',
                't.admin_read',
                'c.name  as category_name',
                'c.color as category_color',
                's.name  as type_name',
                's.color as type_color',
                's.icon  as type_icon',
                'lbl.label_names',
                'lbl.label_colors',
                'lbl.label_icons'
            )
            ->where('t.user_id', Auth::id());

        if (isset($params['cate_id']) && $params['cate_id'] != -1) {
            $query->where('t.cate_id', $params['cate_id']);
        }

        if (isset($params['label_id']) && $params['label_id'] != -1) {
            $labelId = $params['label_id'];
            $query->whereExists(function ($q) use ($labelId) {
                $q->from('support_map_labels as _sml')
                  ->whereColumn('_sml.ticket_id', 't.id')
                  ->where('_sml.label_id', $labelId);
            });
        }

        if (isset($params['status']) && $params['status'] != -1) {
            $query->where('t.status', $params['status']);
        }

        if (isset($params['search']) && trim($params['search']) !== "") {
            $search = trim($params['search']);
            $query->where(function ($q) use ($search) {
                $q->where('t.content', 'like', "%{$search}%")
                  ->orWhere('t.title', 'like', "%{$search}%")
                  ->orWhere('c.name', 'like', "%{$search}%");
            });
        }

        $query->orderBy($order_field, $order_sort);

        $pagination = $query->paginate($per_page);

        $data = $pagination->getCollection()->map(function ($record) {
            $record->label_names  = !empty($record->label_names)  ? explode(',', $record->label_names)  : [];
            $record->label_colors = !empty($record->label_colors) ? explode(',', $record->label_colors) : [];
            $record->label_icons  = !empty($record->label_icons)  ? explode(',', $record->label_icons)  : [];
            return $record;
        })->toArray();

        return [
            'recordsTotal'    => $pagination->total(),
            'recordsFiltered' => $pagination->total(),
            'data'            => $data,
        ];
    }

    public static function getTicketDetail($ticketId)
    {
        if (!$ticketId) return false;

        $labelsSub = DB::table('support_map_labels as sml')
            ->join('support_labels as l', 'sml.label_id', '=', 'l.id')
            ->select(
                'sml.ticket_id',
                DB::raw("GROUP_CONCAT(DISTINCT l.id    ORDER BY l.id    SEPARATOR ',') as label_ids"),
                DB::raw("GROUP_CONCAT(DISTINCT l.name  ORDER BY l.name  SEPARATOR ',') as label_names"),
                DB::raw("GROUP_CONCAT(DISTINCT l.color ORDER BY l.color SEPARATOR ',') as label_colors"),
                DB::raw("GROUP_CONCAT(DISTINCT l.icon  ORDER BY l.icon  SEPARATOR ',') as label_icons")
            )
            ->groupBy('sml.ticket_id');

        $commentsSub = DB::table('support_comments')
            ->select('ticket_id', DB::raw('COUNT(*) as total_comment'))
            ->groupBy('ticket_id');

        $ticket = DB::table('support_tickets as t')
            ->leftJoin('support_categories as c', 't.cate_id', '=', 'c.id')
            ->leftJoin('users as u', 't.user_id', '=', 'u.id')
            ->leftJoin('support_types as s', 't.type_id', '=', 's.id')
            ->leftJoinSub($labelsSub, 'lbl', function ($join) {
                $join->on('lbl.ticket_id', '=', 't.id');
            })
            ->leftJoinSub($commentsSub, 'com', function ($join) {
                $join->on('com.ticket_id', '=', 't.id');
            })
            ->select(
                't.id',
                't.id_secure',
                't.user_id',
                't.title',
                't.content',
                't.status',
                't.changed',
                't.created',
                't.user_read',
                't.admin_read',
                't.open_by',
                'c.name  as category_name',
                'c.color as category_color',
                's.name  as type_name',
                's.color as type_color',
                's.icon  as type_icon',
                'u.fullname as user_fullname',
                'u.avatar   as user_avatar',
                'lbl.label_ids',
                'lbl.label_names',
                'lbl.label_colors',
                'lbl.label_icons',
                DB::raw('COALESCE(com.total_comment, 0) as total_comment')
            )
            ->where('t.id_secure', $ticketId)
            ->where('t.user_id', Auth::id())
            ->first();

        if (!$ticket) return null;

        $ticket->label_ids    = !empty($ticket->label_ids)    ? explode(',', $ticket->label_ids)    : [];
        $ticket->label_names  = !empty($ticket->label_names)  ? explode(',', $ticket->label_names)  : [];
        $ticket->label_colors = !empty($ticket->label_colors) ? explode(',', $ticket->label_colors) : [];
        $ticket->label_icons  = !empty($ticket->label_icons)  ? explode(',', $ticket->label_icons)  : [];

        return $ticket;
    }

    public static function getCommentsByTicket($ticketId, $page = 1)
    {
        $perPage = 50;
        $page = max(1, (int)$page);

        $total = DB::table('support_comments')->where('ticket_id', $ticketId)->count();
        $lastPage = max(1, (int)ceil($total / $perPage));
        if ($page > $lastPage) $page = $lastPage;

        $offset = ($page - 1) * $perPage;

        $descPage = DB::table('support_comments as c')
            ->leftJoin('users as u', 'c.user_id', '=', 'u.id')
            ->where('c.ticket_id', $ticketId)
            ->select(
                'c.id',
                'c.ticket_id',
                'c.user_id',
                'c.comment',
                'c.changed',
                'c.created',
                'u.avatar as user_avatar',
                'u.fullname as user_fullname'
            )
            ->orderBy('c.created', 'desc')
            ->orderBy('c.id', 'desc')
            ->offset($offset)
            ->limit($perPage);

        $comments = DB::query()
            ->fromSub($descPage, 'x')
            ->orderBy('created', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->values();

        return [
            'comments' => $comments,
            'pagination' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => $lastPage,
            ],
        ];
    }

    public static function getRecentTickets($excludeTicketId)
    {
        $commentsSub = DB::table('support_comments')
            ->select('ticket_id', DB::raw('COUNT(*) as total_comment'))
            ->groupBy('ticket_id');

        $tickets = DB::table('support_tickets as t')
            ->leftJoin('support_categories as c', 't.cate_id', '=', 'c.id')
            ->leftJoinSub($commentsSub, 'com', function ($join) {
                $join->on('t.id', '=', 'com.ticket_id');
            })
            ->select(
                't.id',
                't.id_secure',
                't.title',
                't.status',
                't.created',
                'c.name as category_name',
                'c.color as category_color',
                DB::raw('COALESCE(com.total_comment, 0) as total_comment')
            )
            ->where('t.id_secure', '!=', $excludeTicketId)
            ->where('t.user_id', Auth::id())
            ->orderBy('t.created', 'desc')
            ->limit(10)
            ->get();

        return $tickets;
    }

}
