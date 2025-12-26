<?php

namespace Modules\AdminSupport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;

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
                    1 => 't.title',
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
            ->leftJoin('users as u', 't.user_id', '=', 'u.id')
            ->leftJoinSub($labelsSub, 'lbl', function ($join) {
                $join->on('lbl.ticket_id', '=', 't.id');
            })
            ->select(
                't.id',
                't.id_secure',
                't.title',
                't.status',
                't.changed',
                't.created',
                't.pin',
                't.user_read',
                't.admin_read',
                't.open_by',
                'c.name  as category_name',
                'c.color as category_color',
                's.name  as type_name',
                's.color as type_color',
                's.icon  as type_icon',
                'u.username       as user_username',
                'u.id             as user_account_id',
                'u.fullname       as user_fullname',
                'u.avatar         as user_avatar',
                'u.email          as user_email',
                'lbl.label_names',
                'lbl.label_colors',
                'lbl.label_icons'
            );

        if (isset($params['cate_id']) && $params['cate_id'] != -1) {
            $query->where('t.cate_id', $params['cate_id']);
        }

        if (isset($params['label_id']) && $params['label_id'] != -1) {
            $query->whereExists(function ($q) use ($params) {
                $q->from('support_map_labels as _sml')
                  ->whereColumn('_sml.ticket_id', 't.id')
                  ->where('_sml.label_id', $params['label_id']);
            });
        }

        if (isset($params['status']) && $params['status'] != -1) {
            $query->where('t.status', $params['status']);
        }

        if (!empty($params['search']) && trim($params['search']) !== "") {
            $search = trim($params['search']);
            $query->where(function ($q) use ($search) {
                $q->where('t.content', 'like', "%{$search}%")
                  ->orWhere('t.title', 'like', "%{$search}%")
                  ->orWhere('c.name', 'like', "%{$search}%")
                  ->orWhere('u.username', 'like', "%{$search}%")
                  ->orWhere('u.fullname', 'like', "%{$search}%")
                  ->orWhere('u.email', 'like', "%{$search}%");
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
        if (!$ticketId) {
            return false;
        }

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

        $ticket = DB::table('support_tickets as t')
            ->leftJoin('support_categories as c', 't.cate_id', '=', 'c.id')
            ->leftJoin('users as u', 't.user_id', '=', 'u.id')
            ->leftJoin('support_types as s', 't.type_id', '=', 's.id')
            ->leftJoinSub($labelsSub, 'lbl', function ($join) {
                $join->on('lbl.ticket_id', '=', 't.id');
            })
            ->select(
                't.*',
                'c.name as category_name',
                'c.color as category_color',
                's.name as type_name',
                's.color as type_color',
                's.icon as type_icon',
                'u.fullname as user_fullname',
                'u.avatar as user_avatar',
                'lbl.label_ids',
                'lbl.label_names',
                'lbl.label_colors',
                'lbl.label_icons'
            )
            ->where('t.id_secure', $ticketId)
            ->first();

        if (!$ticket) {
            return null;
        }

        $ticket->label_ids    = $ticket->label_ids    ? explode(',', $ticket->label_ids)    : [];
        $ticket->label_names  = $ticket->label_names  ? explode(',', $ticket->label_names)  : [];
        $ticket->label_colors = $ticket->label_colors ? explode(',', $ticket->label_colors) : [];
        $ticket->label_icons  = $ticket->label_icons  ? explode(',', $ticket->label_icons)  : [];

        $ticket->total_comment = DB::table('support_comments')
            ->where('ticket_id', $ticket->id)
            ->count();

        return $ticket;
    }

    public static function getCommentsByTicket($ticketId, $page = 1)
    {
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $comments = DB::table('support_comments as c')
            ->leftJoin('users as u', 'c.user_id', '=', 'u.id')
            ->select(
                'c.*',
                'u.avatar as user_avatar',
                'u.fullname as user_fullname'
            )
            ->where('c.ticket_id', $ticketId)
            ->orderBy('c.created', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $comments = $comments->reverse()->values();

        // Count the total number of comments for the ticket to set pagination info.
        $totalComments = DB::table('support_comments')
            ->where('ticket_id', $ticketId)
            ->count();

        $lastPage = ceil($totalComments / $perPage);

        return [
            'comments' => $comments,
            'pagination' => [
                'total' => $totalComments,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
            ]
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
                DB::raw("IFNULL(com.total_comment, 0) as total_comment")
            )
            ->where('t.id_secure', '!=', $excludeTicketId)
            ->orderBy('t.created', 'desc')
            ->limit(10)
            ->get();

        return $tickets;
    }
}
