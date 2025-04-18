<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('Admin');
        $isManager = $user->hasRole('Manager');
        
        // Get counts based on user role
        $query = function ($model) use ($user, $isAdmin, $isManager) {
            if (!$isAdmin && !$isManager) {
                return $model::where('owner_id', $user->id);
            }
            return $model::query();
        };
        
        // Summary counts
        $counts = [
            'contacts' => $query(Contact::class)->count(),
            'leads' => $query(Lead::class)->whereNull('converted_at')->count(),
            'deals' => $query(Deal::class)->whereNull('won')->whereNull('lost_reason')->count(),
            'deals_won' => $query(Deal::class)->where('won', true)->count(),
        ];
        
        // Sales pipeline data
        $pipelineData = DB::table('deals')
            ->select('pipeline_stage', DB::raw('count(*) as count'), DB::raw('sum(amount) as value'))
            ->when(!$isAdmin && !$isManager, function ($query) use ($user) {
                return $query->where('owner_id', $user->id);
            })
            ->whereNull('won')
            ->whereNull('lost_reason')
            ->whereNull('deleted_at')
            ->groupBy('pipeline_stage')
            ->get();
        
        // Sales performance by month (current year)
        $startDate = Carbon::now()->startOfYear();
        $endDate = Carbon::now()->endOfMonth();
        
        $salesPerformance = DB::table('deals')
            ->select(
                DB::raw('MONTH(actual_close_date) as month'),
                DB::raw('sum(amount) as value'),
                DB::raw('count(*) as count')
            )
            ->when(!$isAdmin && !$isManager, function ($query) use ($user) {
                return $query->where('owner_id', $user->id);
            })
            ->whereBetween('actual_close_date', [$startDate, $endDate])
            ->where('won', true)
            ->whereNull('deleted_at')
            ->groupBy('month')
            ->get();
        
        // Format sales performance for chart
        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[$m] = [
                'month' => Carbon::create()->month($m)->format('F'),
                'value' => 0,
                'count' => 0,
            ];
        }
        
        foreach ($salesPerformance as $data) {
            $monthlyData[$data->month]['value'] = $data->value;
            $monthlyData[$data->month]['count'] = $data->count; 
        }
        
        // Get upcoming tasks
        $upcomingTasks = Task::with(['taskable', 'assignee'])
            ->when(!$isAdmin && !$isManager, function ($query) use ($user) {
                return $query->where('assignee_id', $user->id);
            })
            ->where('status', '!=', 'Completed')
            ->where('due_date', '>=', now())
            ->orderBy('due_date')
            ->limit(5)
            ->get();
        
        // Recent leads
        $recentLeads = Lead::with('owner')
            ->when(!$isAdmin && !$isManager, function ($query) use ($user) {
                return $query->where('owner_id', $user->id);
            })
            ->whereNull('converted_at')
            ->latest()
            ->limit(5)
            ->get();
        
        return Inertia::render('Dashboard', [
            'counts' => $counts,
            'pipelineData' => $pipelineData,
            'monthlyData' => array_values($monthlyData),
            'upcomingTasks' => $upcomingTasks,
            'recentLeads' => $recentLeads,
        ]);
    }
}