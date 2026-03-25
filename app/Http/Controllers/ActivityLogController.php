<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    public function forProject(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $logs = ActivityLog::with('user')
            ->forLoggable(Project::class, $project->id)
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($log) => $this->formatLog($log));

        return response()->json($logs);
    }

    public function forTask(Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $logs = ActivityLog::with('user')
            ->forLoggable(Task::class, $task->id)
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($log) => $this->formatLog($log));

        return response()->json($logs);
    }

    private function formatLog(ActivityLog $log): array
    {
        return [
            'id'         => $log->id,
            'event'      => $log->event,
            'old_values' => $log->old_values,
            'new_values' => $log->new_values,
            'created_at' => $log->created_at->format('d/m/Y H:i'),
            'user'       => $log->user?->name ?? 'Sistema',
        ];
    }
}
