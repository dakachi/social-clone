<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Artisan;

class HandleModuleNotFound
{
    public function handle($request, Closure $next)
    {
        try {
            return $next($request);
        } catch (NotFoundHttpException $e) {
            if ($this->isClassNotFoundError($e)) {
                $this->updateModuleCache();
                Log::info('Updated module cache due to class not found error.');
                return redirect()->back()->with('error', 'Module cache was updated. Please try again.');
            }

            throw $e;
        }
    }

    protected function isClassNotFoundError($exception)
    {
        $previous = $exception->getPrevious();
        return $previous && strpos($previous->getMessage(), 'Class') !== false && strpos($previous->getMessage(), 'not found') !== false;
    }

    protected function updateModuleCache()
    {
        $filePath = base_path('bootstrap/cache/modules.php');

        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('module:cache');
    }
}
