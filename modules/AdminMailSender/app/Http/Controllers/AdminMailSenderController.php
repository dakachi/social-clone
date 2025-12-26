<?php

namespace Modules\AdminMailSender\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminMailSenderController extends Controller
{
    public function index()
    {
         $users = \App\Models\User::all();
        return view(module("key").'::index', [
             "users" => $users,
        ]);
    }

    public function save(Request $request)
    {
        // Validate the form data
        $validated = $request->validate([
            'user_ids'   => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'subject'    => 'required|string|max:255',
            'content'    => 'required|string',
        ]);

        // Fetch recipients
        $users = User::whereIn('id', $validated['user_ids'])->get();

        foreach ($users as $user) {

            $userId = $user->id;
            $subject = $request->input('subject');
            $content = $request->input('content');

            \MailSender::sendMail($userId, $subject, $content);
        }

        return response()->json([
            'status' => 1,
            'message' => __('Your email has been sent successfully'),
            'redirect' => '',
        ]) ;
    }

}
