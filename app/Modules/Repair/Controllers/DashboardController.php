<?php

namespace App\Modules\Repair\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Repair\Models\DamageReport;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total' => DamageReport::count(),
            'waiting' => DamageReport::where('status', DamageReport::STATUS_WAITING)->count(),
            'in_progress' => DamageReport::where('status', DamageReport::STATUS_IN_PROGRESS)->count(),
            'done' => DamageReport::where('status', DamageReport::STATUS_DONE)->count(),
        ];

        $recentReports = DamageReport::with(['machine', 'reporter', 'assignedTechnician'])
            ->orderByDesc('reported_at')
            ->limit(5)
            ->get();

        return view('repair::dashboard', compact('stats', 'recentReports'));
    }
}
