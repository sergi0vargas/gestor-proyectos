<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $projects = $user->projects()->with(['tasks'])->where('status', 'active')->get();

        $totalProjects = $user->projects()->count();
        $activeProjects = $user->projects()->where('status', 'active')->count();

        $taskStats = $user->projects()
            ->with('tasks')
            ->get()
            ->flatMap(fn($p) => $p->tasks);

        $pendingTasks = $taskStats->whereNotIn('status', ['done'])->count();
        $totalEstimatedHours = $taskStats->sum('estimated_hours');

        return view('dashboard', compact(
            'projects',
            'totalProjects',
            'activeProjects',
            'pendingTasks',
            'totalEstimatedHours'
        ));
    }
}
