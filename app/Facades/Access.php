<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Gate;
use Modules\AdminUsers\Models\Teams;
use Auth;
use User;

class Access extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return 'access';
    }

    protected static function deny($message = null)
    {
        if(module('role') != "admin"){
            abort(403, $message ?? __('You do not have permission to access this feature.'));
        }
    }

    protected static function check($permission, $withExpire = true, $redirectToDashboard = false, $message = null)
    {
        if ($withExpire && self::isExpired()) {
            $msg = __('Your account or plan has expired. Please upgrade to continue.');

            if (request()->expectsJson()) {
                response()->json([
                    'status' => 0,
                    'message' => $msg,
                    'code' => 403
                ], 403)->send();
                exit;
            } elseif ($redirectToDashboard) {
                session()->flash('error', $msg);
                session()->save();
                redirect()->route('app.dashboard')->send();
                exit;
            } else {
                self::deny($msg);
            }
        }

        if (!self::canAccess($permission)) {
            $msg = $message ?? __('You do not have permission to access this feature.');

            if (request()->expectsJson()) {
                response()->json([
                    'status' => 0,
                    'message' => $msg,
                    'code' => 403
                ], 403)->send();
                exit;
            } elseif ($redirectToDashboard) {
                session()->flash('error', $msg);
                session()->save();
                redirect()->route('app.dashboard')->send();
                exit;
            } else {
                self::deny($msg);
            }
        }
    }

    protected static function canAccess($permission, $withExpire = true)
    {
        if ($withExpire && self::isExpired()) {
            return false;
        }

        return \Gate::allows($permission);
    }

    protected static function getPermissions()
    {
        if (!app()->bound('permissions')) {
            return $default;
        }

        return app('permissions');
    }

    protected static function permission($key, $default = null)
    {
        if (!app()->bound('permissions')) {
            return $default;
        }

        $permissions = app('permissions');

        if (is_array($permissions) && array_key_exists($key, $permissions)) {
            $value = $permissions[$key];
        } else {
            $value = data_get($permissions, $key, $default);
        }

        if (is_string($value) && is_numeric($value)) {
            return strpos($value, '.') !== false ? (float) $value : (int) $value;
        }

        return $value;
    }

    protected static function isExpired($user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) return true;
        return $user->expiration_date > 0 && $user->expiration_date < time();
    }
}


