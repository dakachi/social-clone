<?php

namespace Modules\Guest\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function __construct()
    {
        if(!get_option("auth_landing_page_status", 1)){
            if(auth()->id()){
                header("Location: ". route('app.dashboard'));
            }else{
                header("Location: ". route('login'));
            }
            exit;
        }
    }

    public function index(Request $request)
    {
        return view('guest::home');
    }

    public function pricing(Request $request)
    {
        return view('guest::pricing');
    }

    public function faqs(Request $request)
    {
        return view('guest::faqs');
    }
        
    public function contact(Request $request)
    {
        return view('guest::contact');
    }

    public function about(Request $request)
    {
        return view('guest::about');
    }

    public function blogs(Request $request, $cate_slug = null)
    {
        return view('guest::blogs');
    }

    public function blogDetail(Request $request, $id)
    {
        return view('guest::blog_detail');
    }

    public function pageNotFound(Request $request)
    {
        return view('guest::page_not_found');
    }

    public function privacyPolicy(Request $request)
    {
        return view('guest::privacy_policy');
    }

    public function termsOfService(Request $request)
    {
        return view('guest::terms_of_service');
    }
}
