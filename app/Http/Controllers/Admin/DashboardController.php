<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Dashboard\GetDashboardStatsAction;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(GetDashboardStatsAction $getDashboardStatsAction)
    {
        $stats = $getDashboardStatsAction->execute();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }
}
