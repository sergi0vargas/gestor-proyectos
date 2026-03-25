<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Handles the dashboard summary for the authenticated user.
 *
 * @author Sergio Vargas
 */
class DashboardController extends Controller
{
    /**
     * Display the dashboard metrics, project summary, and progress charts.
     *
     * @author Sergio Vargas
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $projects = $user->projects()
            ->with(['tasks'])
            ->where('status', 'active')
            ->get();

        $activeProjects = $user->projects()->where('status', 'active')->count();

        $tasksByStatus = $user->projects()
            ->join('tasks', 'projects.id', '=', 'tasks.project_id')
            ->selectRaw('tasks.status, COUNT(*) as count, COALESCE(SUM(tasks.estimated_hours), 0) as estimated_hours')
            ->groupBy('tasks.status')
            ->get()
            ->keyBy('status');

        $statusConfig = [
            'backlog' => [
                'label' => 'Backlog',
                'stroke_color' => '#94a3b8',
            ],
            'in_progress' => [
                'label' => 'En progreso',
                'stroke_color' => '#6366f1',
            ],
            'testing' => [
                'label' => 'Testing',
                'stroke_color' => '#f59e0b',
            ],
            'done' => [
                'label' => 'Terminadas',
                'stroke_color' => '#10b981',
            ],
        ];

        $statusData = [];
        foreach ($statusConfig as $status => $config) {
            $statusData[$status] = [
                'count' => (int) ($tasksByStatus[$status]->count ?? 0),
                'hours' => (float) ($tasksByStatus[$status]->estimated_hours ?? 0),
            ];
        }

        $totalTasks = array_sum(array_column($statusData, 'count'));
        $doneTasks = $statusData['done']['count'];
        $pendingTasks = $totalTasks - $doneTasks;
        $overallProgress = $totalTasks > 0 ? (int) round($doneTasks / $totalTasks * 100) : 0;
        $totalEstimatedHours = array_sum(array_column($statusData, 'hours'));
        $completedHours = $statusData['done']['hours'];
        $remainingHours = max($totalEstimatedHours - $completedHours, 0);
        $hoursPct = $totalEstimatedHours > 0 ? (int) round($completedHours / $totalEstimatedHours * 100) : 0;

        $hoursProgressClasses = match (true) {
            $hoursPct >= 75 => [
                'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                'text' => 'text-emerald-600 dark:text-emerald-400',
                'bar' => 'bg-emerald-500',
            ],
            $hoursPct >= 40 => [
                'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                'text' => 'text-amber-600 dark:text-amber-400',
                'bar' => 'bg-amber-500',
            ],
            $hoursPct > 0 => [
                'badge' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
                'text' => 'text-rose-600 dark:text-rose-400',
                'bar' => 'bg-rose-500',
            ],
            default => [
                'badge' => 'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-300',
                'text' => 'text-slate-600 dark:text-slate-400',
                'bar' => 'bg-slate-400',
            ],
        };

        $circumference = 251.2;
        $offset = 0;
        $taskSegments = [];
        foreach ($statusConfig as $status => $config) {
            $count = $statusData[$status]['count'];
            $hours = $statusData[$status]['hours'];
            $percentage = $totalTasks > 0 ? (int) round(($count / $totalTasks) * 100) : 0;
            $dash = round(($percentage / 100) * $circumference, 2);

            $taskSegments[] = [
                'key' => $status,
                'label' => $config['label'],
                'count' => $count,
                'hours' => $hours,
                'percentage' => $percentage,
                'stroke_color' => $config['stroke_color'],
                'dash' => $dash,
                'gap' => round(max($circumference - $dash, 0), 2),
                'offset' => round($circumference - $offset, 2),
            ];

            $offset += $dash;
        }

        $hoursBreakdown = array_values(array_filter(
            $taskSegments,
            static fn (array $segment): bool => $segment['hours'] > 0
        ));

        return view('dashboard', compact(
            'projects',
            'activeProjects',
            'pendingTasks',
            'totalEstimatedHours',
            'totalTasks',
            'overallProgress',
            'completedHours',
            'remainingHours',
            'hoursPct',
            'hoursProgressClasses',
            'taskSegments',
            'hoursBreakdown'
        ));
    }
}
