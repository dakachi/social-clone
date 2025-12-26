<?php

namespace Modules\AdminMailServer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminMailServerController extends Controller
{
    public function index()
    {
        return view('adminmailserver::index');
    }

    public function testSendEmail(Request $request)
    {
        $request->validate(['test_email' => 'required|email']);

        $ok = \MailSender::sendTestMail($request->test_email);

        return response()->json([
            'status' => $ok ? 1 : 0,
            'message' => $ok ? __('Email sent!') : __('Send failed! Check your mail config.')
        ]);
    }
}
