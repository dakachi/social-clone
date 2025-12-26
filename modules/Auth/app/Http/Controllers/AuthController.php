<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Modules\AdminUsers\Models\Teams;
use Laravel\Socialite\Facades\Socialite;
use Modules\Auth\Events\AuthEvent;
use App\Models\User;
use Core;
use Auth;
use Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        return view('auth::login');
    }

    public function signup(Request $request)
    {
        if(!get_option("auth_signup_page_status", 1)){
            return redirect()->route('home');
        }

        session(['start_plan' => request("plan")]);

        return view('auth::signup');
    }

    public function forgotPassword(Request $request)
    {
        return view('auth::forgot_password');
    }

    public function recoveryPassword(Request $request)
    {
        return view('auth::recovery_password');
    }

    public function resendActivation(Request $request)
    {
        return view('auth::resend_activation');
    }

    public function activation(Request $request)
    {
        $email = $request->query('email');
        $token = $request->query('token');
        $status = false;
        $message = '';

        if (!$email || !$token) {
            $message = __("Invalid activation link.");
            return view('auth::activation', compact('status', 'message'));
        }

        $user = User::where('email', $email)->where('secret_key', $token)->first();

        if (!$user) {
            $message = __("Activation link is invalid or has expired.");
            return view('auth::activation', compact('status', 'message'));
        }

        if ($user->status == 2) {
            $message = __("Your account has already been activated. Please login!");
            $status = true;
            return view('auth::activation', compact('status', 'message'));
        }

        $user->status = 2;
        $user->secret_key = rand_string(32);
        $user->save();

        if (get_option('auth_welcome_email_new_user_status', 0)) {
            \MailSender::sendByTemplate('welcome', $user->email, [
                'fullname'  => $user->fullname,
                'login_url' => url('auth/login'),
            ]);
        }

        $status = true;
        $message = __("Your account has been activated successfully! You can now login.");

        return view('auth::activation', compact('status', 'message'));
    }

    public function loginAsAdmin(Request $request)
    {
        $user = \Auth::user();
        if($user->role != 2)
        {
            return redirect()->route('app.dashboard.index');
        }

        session(["login_as" => "admin"]);
        return redirect()->route('admin.dashboard.index');
    }

    public function loginAsUser(Request $request)
    {
        session(["login_as" => "client"]);
        return redirect()->route('app.dashboard.index');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('auth/login');
    }   

    public function settings(Request $request)
    {
        return view('auth::settings');
    }

    /*
    * AJAX REQUESTS
     */
    public function doSignup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname'  => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'username'  => [
                'required',
                'string',
                'min:5',
                'max:64',
                'regex:/^\S+$/',
                'unique:users,username',
            ],
            'password'  => 'required|string|min:6|confirmed',
            'timezone'  => 'required|in:' . implode(',', timezone_identifiers_list()),
        ], [
            'username.regex'    => __('Username must not contain any whitespace.'),
            'username.min'      => __('Username must be at least 5 characters.'),
            'timezone.required' => __('Please select your timezone.'),
            'timezone.in'       => __('Invalid timezone.'),
        ]);

        if ($validator->fails()) {
            return ms([
                "status" => 0,
                "message" => "errors",
                "class" => "text-error",
                "error_type" => 2,
                "errors" => $validator->errors()
            ]);
        }

        \Captcha::verify($request, 4);

        $user = User::create([
            'id_secure'     => rand_string(),
            'role'          => 1,
            'login_type'    => 'direct',
            'fullname'      => $request->fullname,
            'email'         => $request->email,
            'username'      => $request->username,
            'password'      => Hash::make($request->password),
            'timezone'      => $request->timezone,
            'avatar'        => text2img($request->fullname),
            'secret_key'    => rand_string(32),
            'status'        => get_option('auth_activation_email_new_user_status', 0) ? 1 : 2,
            'changed'       => time(),
            'created'       => time()
        ]);

        Teams::create([
            'id_secure'   => rand_string(),
            'owner'       => $user->id
        ]);

        event(new AuthEvent("signup", $user->id));

        if (get_option('auth_activation_email_new_user_status', 0)) {
            $verify_url = url('auth/activation?email=' . urlencode($user->email) . '&token=' . $user->secret_key);

            \MailSender::sendByTemplate('activation', $user->email, [
                'fullname'   => $user->fullname,
                'verify_url' => $verify_url,
            ]);

            $return = [
                "status" => 1,
                "error_type" => 4,
                "class" => "text-success",
                "message" => __("Signup successful! Please check your email and click the activation link to activate your account."),
                "redirect" => url('auth/login'),
            ];
        } else {
            if (get_option('auth_welcome_email_new_user_status', 0)) {
                \MailSender::sendByTemplate('welcome', $user->email, [
                    'fullname'  => $user->fullname,
                    'login_url' => url('auth/login'),
                ]);
            }

            $user->last_login = time();
            $user->save();
            Auth::login($user);

            $return = [
                "status" => 1,
                "error_type" => 4,
                "class" => "text-success",
                "message" => __("Signup successful! You can now log in to your account."),
                "redirect" => Core::startPage(),
            ];
        }

        return ms($return, true);
    }

    public function doLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|max:255',
            'password' => 'required|string|min:6',
            'remember' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return ms([
                "status" => 0,
                "message" => "errors",
                "class" => "alert alert-danger alert-error",
                "error_type" => 2,
                "errors" => $validator->errors()
            ]);
        }

        $loginValue = $request->username;
        $fieldType = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($fieldType, $loginValue)->first();
        if (!$user) {
            return ms([
                "status" => 1,
                "error_type" => 4,
                "class" => "text-danger text-error",
                "message" => __("Username or password incorrect. Please try again."),
            ]);
        }

        \Captcha::verify($request, 4);

        switch ($user->status) {
            case 0:
                return ms([
                    "status" => 0,
                    "error_type" => 4,
                    "class" => "text-danger text-error",
                    "message" => __("Your account has been banned or locked. Please contact support."),
                ]);
            case 1:
                return ms([
                    "status" => 0,
                    "error_type" => 4,
                    "class" => "text-danger text-error",
                    "message" => __("Your account is not active. Please verify your email or contact support."),
                ]);
            case 2:
                if (Auth::attempt([$fieldType => $loginValue, 'password' => $request->password], $request->boolean('remember', false))) {
                    $request->session()->regenerate();
                    $user = Auth::user();
                    $user->last_login = time();
                    $user->save();

                    if($user->language){
                        \Cookie::queue('locale', $user->language, 60 * 24 * 365 * 10);
                    }

                    $team = Teams::where("owner", $user->id)->first();
                    if (empty($team)) {
                        $plan = $user->plan ?? null;
                        $planPermissions = $plan->permissions ?? [];

                        $team = new Teams();
                        $team->owner = $user->id;
                        $team->id_secure = rand_string();
                        $team->permissions = $planPermissions;
                        $team->save();
                    }

                    return ms([
                        "status" => 1,
                        "error_type" => 4,
                        "class" => "text-success",
                        "message" => __("Login successfully"),
                        "redirect" => Core::startPage()
                    ], true);

                } else {
                    return ms([
                        "status" => 0,
                        "error_type" => 4,
                        "class" => "text-danger text-error",
                        "message" => __("Username or password incorrect. Please try again."),
                    ]);
                }
            default:
                return ms([
                    "status" => 0,
                    "error_type" => 4,
                    "class" => "text-danger text-error",
                    "message" => __("Your account is not active. Please verify your email or contact support."),
                ]);
        }
    }

    public function doForgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255|exists:users,email',
        ], [
            'email.exists' => __('This email does not exist in our records.'),
        ]);

        if ($validator->fails()) {
            return ms([
                "status" => 0,
                "message" => "errors",
                "class" => "alert alert-danger alert-error",
                "error_type" => 2,
                "errors" => $validator->errors()
            ]);
        }

        \Captcha::verify($request, 4);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return ms([
                "status" => 1,
                "message" => __("A password reset link has been sent to your email address."),
                "class" => "text-success",
                "error_type" => 4,
            ]);
        } else {
            return ms([
                "status" => 0,
                "message" => __("Unable to send reset link. Please try again later."),
                "class" => "text-error",
                "error_type" => 4,
            ]);
        }
    }

    public function doRecoveryPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return ms([
                "status" => 0,
                "message" => "errors",
                "class" => "text-error",
                "error_type" => 2,
                "errors" => $validator->errors()
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return ms([
                "status" => 0,
                "message" => __("Account does not exist."),
                "class" => "text-error",
                "error_type" => 4,
            ]);
        }

        $reset = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return ms([
                "status" => 0,
                "message" => __("Reset link is invalid or has expired."),
                "class" => "text-error",
                "error_type" => 4,
            ]);
        }

        \Captcha::verify($request, 4);

        $user->password = Hash::make($request->password);
        $user->save();

        \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return ms([
            "status" => 1,
            "message" => __("Your password has been reset successfully. Please login!"),
            "class" => "text-success",
            "error_type" => 4,
            "redirect" => route('login'),
        ]);
    }

    public function doResendActivation(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return ms([
                "status" => 0,
                "message" => __("You are not logged in."),
                "error_type" => 4,
            ]);
        }

        if ($user->status == 2) {
            return ms([
                "status" => 0,
                "message" => __("Your account is already activated. Please login."),
                "error_type" => 4,
                "class" => "text-success",
            ]);
        }

        \Captcha::verify($request, 4);

        $user->secret_key = \Str::random(48);
        $user->save();

        $verify_url = url('auth/activation?email=' . urlencode($user->email) . '&token=' . $user->secret_key);

        \MailSender::sendByTemplate('activation', $user->email, [
            'fullname'   => $user->fullname ?? $user->username ?? $user->email,
            'verify_url' => $verify_url,
        ]);

        return ms([
            "status" => 1,
            "error_type" => 4,
            "class" => "text-success",
            "message" => __("Activation email resent! Please check your email to activate your account."),
        ]);
    }

    public function viewAsUser($id_secure)
    {
        $user = User::where('id_secure', $id_secure)->firstOrFail();
        session([
            'impersonate_by' => auth()->id(), 
            'login_as' => 'client'
        ]);
        auth()->login($user);

        return redirect()->route('app.dashboard')->with('success', __('You are now viewing as user: ') . $user->fullname);
    }

    public function leaveImpersonate()
    {
        $impersonateBy = session('impersonate_by');
        if ($impersonateBy) {
            $admin = User::where('id', $impersonateBy)->where("role", "!=", 1)->first();
            if ($admin) {
                auth()->login($admin);
                session()->forget('impersonate_by');
                session(['login_as' => 'admin']);
                return redirect()->route('admin.users.index')->with('success', __('Returned to admin account.'));
            }
            session()->forget('impersonate_by');
            return redirect()->route('login')->with('error', __('Admin user not found.'));
        }
        return redirect()->back()->with('error', __('Not impersonating any user.'));
    }

    public function saveSidebarState(Request $request)
    {
        $value = $request->input('sidebar_small', 0);
        $userId = auth()->id();
        \UserInfo::setDataUser('sidebar-small', $value ? 1 : 0);
        return response()->json(['status' => 1]);
    }

    /*
    * SOCIAL LOGIN
     */
    // Facebook
    public function redirectFacebook()
    {
        if (!get_option('auth_facebook_login_status', 0)) {
            abort(404);
        }
        $config = [
            'client_id'     => get_option('auth_facebook_login_app_id', ''),
            'client_secret' => get_option('auth_facebook_login_app_secret', ''),
            'redirect'      => url('auth/login/facebook/callback'),
            'graph_api_version' => get_option("auth_facebook_login_app_version", "v22.0"),
        ];
        config(['services.facebook' => $config]);
        return Socialite::driver('facebook')->redirect();
    }

    public function callbackFacebook()
    {
        $config = [
            'client_id'     => get_option('auth_facebook_login_app_id', ''),
            'client_secret' => get_option('auth_facebook_login_app_secret', ''),
            'redirect'      => url('auth/login/facebook/callback'),
        ];
        config(['services.facebook' => $config]);

        try {
            $socialUser = Socialite::driver('facebook')->stateless()->user();
            return $this->handleSocialLogin($socialUser, 'facebook');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Login Facebook failed!');
        }
    }

    // Google
    public function redirectGoogle()
    {
        if (!get_option('auth_google_login_status', 0)) {
            abort(404);
        }
        $config = [
            'client_id'     => get_option('auth_google_login_client_id', ''),
            'client_secret' => get_option('auth_google_login_client_secret', ''),
            'redirect'      => url('auth/login/google/callback'),
        ];
        config(['services.google' => $config]);
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle()
    {
        $config = [
            'client_id'     => get_option('auth_google_login_client_id', ''),
            'client_secret' => get_option('auth_google_login_client_secret', ''),
            'redirect'      => url('auth/login/google/callback'),
        ];
        config(['services.google' => $config]);

        try {
            $socialUser = Socialite::driver('google')->stateless()->user();
            return $this->handleSocialLogin($socialUser, 'google');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Login Google failed!');
        }
    }

    // X (Twitter)
    public function redirectX()
    {
        if (!get_option('auth_x_login_status', 0)) {
            abort(404);
        }
        $config = [
            'client_id'     => get_option('auth_x_login_client_id', ''),
            'client_secret' => get_option('auth_x_login_client_secret', ''),
            'redirect'      => url('auth/login/x/callback'),
        ];
        config(['services.twitter-oauth-2' => $config]);
        try {
            return Socialite::driver('twitter-oauth-2')
            ->scopes(['tweet.read','users.read','users.email'])
            ->redirect();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', __("Cannot connect to X (Twitter): ") . $e->getMessage());
        }
    }

    public function callbackX()
    {
        $config = [
            'client_id'     => get_option('auth_x_login_client_id', ''),
            'client_secret' => get_option('auth_x_login_client_secret', ''),
            'redirect'      => url('auth/login/x/callback'),
        ];
        config(['services.twitter-oauth-2' => $config]);
        try {
            $socialUser = Socialite::driver('twitter-oauth-2')->stateless()->user();
            return $this->handleSocialLogin($socialUser, 'twitter');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Login X failed!');
        }
    }


    // -------- HANDLE SOCIAL LOGIN --------
    protected function handleSocialLogin($socialUser, $provider)
    {
        $user = User::where('email', $socialUser->getEmail())->first();
        if (!$user) {
            $user = User::create([
                'id_secure'  => rand_string(),
                'role'       => 1,
                'fullname'   => $socialUser->getName() ?: $socialUser->getNickname(),
                'email'      => $socialUser->getEmail(),
                'username'   => $socialUser->getNickname() ?: $socialUser->getEmail(),
                'avatar'     => $socialUser->getAvatar(),
                'login_type' => $provider,
                'status'     => 2,
                'created'    => time(),
                'changed'    => time(),
            ]);

            Teams::create([
                'id_secure'   => rand_string(),
                'owner'       => $user->id
            ]);

            event(new AuthEvent("signup", $user->id));
        }else{
            $user->last_login = time();
            $user->save();
        }

        Auth::login($user, true);
        return redirect(Core::startPage());
    }

}
