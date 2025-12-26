<?php

namespace Modules\AppProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\AdminPaymentHistory\Models\PaymentHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;

class AppProfileController extends Controller
{
    public function index(Request $request, $page = '')
    {
        $userId = \Auth::id();

        switch ($page) {
            case 'billing':

                $billings = PaymentHistory::with('plan')->where("uid", $userId)->orderBy('created', 'desc')->paginate(20);

                return view('appprofile::billing', compact('billings'));
                break;

            case 'plan':
                return view('appprofile::plan');

                break;
            case 'settings':
                return view('appprofile::settings');
                break;
            
            default:
                return view('appprofile::index');
                break;
        }
        
    }

    public function showInvoice(Request $request, $id_secure)
    {
        $userId = \Auth::id();
        $invoice = PaymentHistory::with('plan')
            ->where('uid', $userId)
            ->where('id_secure', $id_secure)
            ->firstOrFail();

        return view('appprofile::invoice-detail', compact('invoice'));
    }

    public function downloadInvoice($id_secure)
    {
        $invoice = PaymentHistory::with('plan')
            ->where('id_secure', $id_secure)
            ->firstOrFail();

        $pdf = Pdf::loadView('appprofile::invoice-pdf', compact('invoice'));

        $filename = 'Invoice_'.$invoice->id_secure.'.pdf';

        return $pdf->download($filename);

    }

    public function activateFreePlan($plan_id){
        return \Plan::activateFreePlan($plan_id);
    }

    public function updateProfile(Request $request)
    {
        $user = User::findOrFail(auth()->id());

        if (!get_option('auth_user_change_email_status', 0)) {
            $request->merge(['email' => $user->email]);
        }

        if (!get_option('auth_user_change_username_status', 0)) {
            $request->merge(['username' => $user->username]);
        }

        $validator = \Validator::make($request->all(), [
            'fullname' => 'required|min:3',
            'username' => 'required|min:5|regex:/^\S+$/',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'timezone' => 'required',
            'avatar'   => 'nullable|image',
        ], [
            'fullname.required' => __('Full name is required.'),
            'fullname.min'      => __('Full name must be at least :min characters.'),
            'username.required' => __('Username is required.'),
            'username.min'      => __('Username must be at least :min characters.'),
            'username.regex'    => __('Username must not contain spaces.'),
            'email.required'    => __('Email is required.'),
            'email.email'       => __('Please provide a valid email address.'),
            'email.unique'      => __('This email is already taken.'),
            'timezone.required' => __('Timezone is required.'),
            'avatar.image'      => __('Avatar must be an image.'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => "errors",
                "error_type" => 4,
                "errors" => $validator->errors()
            ]);
        }

        $values = [
            'fullname' => $request->input('fullname'),
            'username' => $request->input('username'),
            'email'    => $request->input('email'),
            'timezone' => $request->input('timezone'),
            'language' => $request->input('language'),
            'changed'  => time()
        ];

        if ($request->hasFile('avatar')) {
            \UploadFile::deleteFileFromServer($user->avatar);
            $avatar = \UploadFile::storeSingleFile($request->file('avatar'), 'avatars', true);
            $values['avatar'] = $avatar;
        }

        $user->update($values);

        return response()->json([
            "status" => 1,
            "message" => __("Profile updated successfully.")
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'current_password'      => ['required'],
            'password'              => ['required', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => __('Current password is required.'),
            'password.required'         => __('New password is required.'),
            'password.min'              => __('New password must be at least :min characters.', ['min' => 6]),
            'password.confirmed'        => __('Password confirmation does not match.'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => __('Validation errors.'),
                'error_type' => 4,
                'errors' => $validator->errors()
            ]);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 0,
                'message' => __('Current password is incorrect.'),
                'error_type' => 1,
                'errors' => [
                    'current_password' => [__('Current password is incorrect.')]
                ]
            ]);
        }

        // Update password
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            'status' => 1,
            'message' => __('Password changed successfully.')
        ]);
    }
}
