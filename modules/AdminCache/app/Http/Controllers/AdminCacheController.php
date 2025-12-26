<?php

namespace Modules\AdminCache\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class AdminCacheController extends Controller
{
    public function index()
    {
        return view('admincache::index');
    }

    public function clear(Request $request)
    {
        $type = $request->input('type');

        try {
            switch ($type) {
                case 'app':
                    Artisan::call('cache:clear');
                    $msg = __('Application cache cleared successfully.');
                    break;

                case 'config':
                    Artisan::call('config:clear');
                    $msg = __('Config cache cleared successfully.');
                    break;

                case 'route':
                    Artisan::call('route:clear');
                    $msg = __('Route cache cleared successfully.');
                    break;

                case 'view':
                    Artisan::call('view:clear');
                    $msg = __('View cache cleared successfully.');
                    break;

                case 'optimize':
                    Artisan::call('optimize');
                    $msg = __('Application optimized successfully.');
                    break;

                case 'session':
                    $driver = config('session.driver');
                    switch ($driver) {
                        case 'file':
                            \File::cleanDirectory(storage_path('framework/sessions'));
                            break;

                        case 'database':
                            \DB::table(config('session.table', 'sessions'))->truncate();
                            break;

                        case 'redis':
                            \Redis::connection(config('session.connection'))->flushdb();
                            break;

                        default:
                            throw new \Exception("Session clear not supported for driver: {$driver}");
                    }
                    $msg = __('All sessions cleared successfully. All users have been logged out.');
                    break;

                default:
                    return response()->json(['status' => false, 'message' => __('Invalid cache type')], 400);
            }

            return response()->json(['status' => 1, 'message' => $msg]);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 500);
        }
    }
}
