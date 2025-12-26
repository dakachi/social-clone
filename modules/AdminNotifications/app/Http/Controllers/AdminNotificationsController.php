<?php

namespace Modules\AdminNotifications\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AdminNotifications\Models\Notification;
use Modules\AdminNotifications\Models\NotificationManual;
use App\Models\User;

class AdminNotificationsController extends Controller
{
    public $table = "ai_templates";
    public $category_table = "ai_categories";
    public $modules;
    public $Datatable;

    public function __construct()
    {
        $this->Datatable = [
            "element" => "DataTable",
            "order" => ['created_at', 'desc'],
            "lengthMenu" => [10, 25, 50, 100, 150, 200],
            "search_field" => ["title", "message"],

            "columns" => [
                [
                    "name" => "id_secure",
                    "alias" => "id_secure",
                    "data"  => "id_secure",
                    "className" => "w-40"
                ],
                [
                    "name"      => "title",
                    "alias"     => "title",
                    "data"      => "title",
                    "className" => "text-start",
                    "title"     => __("Title")
                ],
                [
                    "name"      => "message",
                    "alias"     => "message",
                    "data"      => "message",
                    "className" => "text-start",
                    "title"     => __("Message")
                ],
                [
                    "name"      => "url",
                    "alias"     => "url",
                    "data"      => "url",
                    "className" => "text-break",
                    "title"     => __("Link")
                ],
                [
                    "name"      => "created_by",
                    "alias"     => "created_by",
                    "data"      => "created_by",
                    "title"     => __("Created by"),
                    "className" => "text-center"
                ],
                [
                    "name"      => "created_at",
                    "alias"     => "created_at",
                    "data"      => "created_at",
                    'type'      => 'datetime',
                    "title"     => __("Created At"),
                    "className" => "text-nowrap"
                ],
                [
                    "name"      => "id",
                    "alias"     => "id",
                    "data"      => "id",
                    "className" => "text-center",
                    "title"     => __("Action")
                ],
            ],

            "status_filter" => [], // Không cần nếu không có trường status

            "actions" => [
                [
                    'url'          => module_url("destroy"),
                    'icon'         => 'fa-light fa-trash-can',
                    'label'        => __('Delete'),
                    'confirm'      => __("Are you sure you want to delete this notification?"),
                    'call_success' => "Main.DataTable_Reload('#DataTable')"
                ]
            ],
        ];
    }

    public function index()
    {
        $total = NotificationManual::count();

        return view(module("key").'::index', [
            'total' => $total,
            'Datatable' => $this->Datatable
        ]);
    }

    public function list(Request $request)
    {
        $joins = [];
        $whereConditions = [];
        $dataTableService = \DataTable::make(NotificationManual::class, $this->Datatable, $whereConditions, $joins);
        $data = $dataTableService->getData($request);
        return response()->json($data);
    }

    public function update(Request $request, $id = null){
        $result = NotificationManual::where("id_secure", $id)->first();
        $users = User::all();

        return response()->json([
            "status" => 1,
            "data" => view(module("key").'::update', [
                "result" => $result,
                "users" => $users,
            ])->render()
        ]);
    }

    public function save(Request $request)
    {
        $id = $request->input("id");

        $rules = [
            'title'   => 'nullable|string|max:255',
            'message' => 'required|string|max:1000',
            'url'     => 'nullable|url|max:255',
        ];

        if (!$id) {
            $rules['user_ids'] = 'required|array';
        }

        $request->validate($rules);

        if ($id) {
            // Update existing manual notification only
            $manual = NotificationManual::findOrFail($id);

            $manual->update([
                'title'      => $request->title,
                'message'    => $request->message,
                'url'        => $request->url,
                'type'       => 'news',
                'created_by' => auth()->id() ?? 1,
            ]);

            return response()->json([
                'status'  => 1,
                'message' => __('Manual notification updated successfully.'),
            ]);
        } else {
            // Create new manual + push to users
            $manual = NotificationManual::create([
                'id_secure'  => rand_string(),
                'title'      => $request->title,
                'message'    => $request->message,
                'url'        => $request->url,
                'type'       => 'news',
                'created_by' => auth()->id() ?? 1,
            ]);

            $notifications = [];
            foreach ($request->user_ids as $userId) {
                $notifications[] = [
                    'id_secure'  => rand_string(),
                    'user_id'    => $userId,
                    'source'     => 'manual',
                    'mid'        => $manual->id,
                    'type'       => 'news',
                    'url'        => $request->url,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Notification::insert($notifications);

            return response()->json([
                'status'  => 1,
                'message' => __('Manual notification sent successfully.'),
            ]);
        }
    }

    public function destroy(Request $request)
    {
        $response = \DBHelper::destroy(NotificationManual::class, $request->input('id'));
        return response()->json($response);
    }
  
}
