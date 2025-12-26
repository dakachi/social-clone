<?php

namespace Modules\AppAffiliate\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Modules\AdminAffiliate\Models\AffiliateInfo;
use Illuminate\Support\Str;

class CaptureReferral
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('ref')) {
            $refCode = trim($request->query('ref'));
            $affiliateUserId = User::where('id_secure', $refCode)->value('id');

            if ($affiliateUserId) {
                if (Auth::check() && Auth::id() == $affiliateUserId) {
                    return $next($request);
                }

                session([
                    'ref'     => $refCode,
                    'ref_uid' => $affiliateUserId,
                ]);

                $cookieName = "ref_seen_{$affiliateUserId}";

                if (! $request->hasCookie($cookieName)) {
                    $affiliate_info = AffiliateInfo::firstOrCreate(
                        ['affiliate_uid' => $affiliateUserId],
                        [
                            'id_secure'        => Str::random(10),
                            'clicks'           => 0,
                            'conversions'      => 0,
                            'total_withdrawal' => 0,
                            'total_approved'   => 0,
                            'total_balance'    => 0,
                        ]
                    );

                    $affiliate_info->increment('clicks');
                    Cookie::queue($cookieName, 1, 60 * 24);
                }
            }
        }

        return $next($request);
    }
}
