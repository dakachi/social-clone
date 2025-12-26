<?php

namespace Modules\AppAnalytics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\AppAnalytics\Services\AnalyticsManager;
use Modules\AppChannels\Models\Accounts;

class AppAnalyticsController extends Controller
{
    public function index(Request $request)
    {

        $teamId = $request->team_id;
        $analytics = (new AnalyticsManager())->getAvailableAnalytics($teamId);

        return view('appanalytics::index', compact('analytics'));
    }

    public function show(Request $request, $social, $id_secure)
    {
        $account = Accounts::where('id_secure', $id_secure)
            ->where('social_network', $social)
            ->where('team_id', $request->team_id)
            ->firstOrFail();

        $module = "AppAnalytics" . ucfirst($social);
        $class  = "Modules\\{$module}\\Services\\" . ucfirst($social) . "Analytics";

        if (!class_exists($class)) {
            abort(404, "Analytics service not found for {$social}");
        }

        \Access::check("appanalytics.".strtolower($social));

        $service = app($class);

        [$since, $until] = \Core::parseDateRange($request);
        $analytics = $service->getAnalyticsData($account->team_id, $account->id_secure, $since, $until);

        if ( isset($analytics['status']) && $analytics['status'] == "error") {
            return view(module('key')."::error", compact('account', 'analytics'));
        }

        $view = strtolower("{$module}::show");

        if (!view()->exists($view)) {
            abort(404, "View [{$view}] not found.");
        }

        return view($view, compact('account', 'analytics'));
    }

    public function exportPdf(Request $request, $social, $id_secure)
    {
        $account = Accounts::where('id_secure', $id_secure)
            ->where('social_network', $social)
            ->firstOrFail();

        $module = "AppAnalytics" . ucfirst($social);
        $class  = "Modules\\{$module}\\Services\\" . ucfirst($social) . "Analytics";

        if (!class_exists($class)) {
            abort(404, "Analytics service not found for {$social}");
        }

        $service = app($class);
        [$since, $until] = \Core::parseDateRange($request);

        $analytics = $service->getAnalyticsData($account->team_id, $account->id_secure, $since, $until);
        $charts    = $request->input('charts', []);

        $pdf = Pdf::loadView(strtolower("{$module}::export_pdf"), [
            'account'   => $account,
            'analytics' => $analytics,
            'charts'    => $charts,
            'startDate' => $since,
            'endDate'   => $until,
        ])->setPaper('a4', 'portrait');

        return $pdf->download(ucfirst($social) . '_analytics_' . now()->format('Ymd_His') . '.pdf');
    }
}
