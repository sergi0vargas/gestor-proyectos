<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'active');
        $projects = $request->user()
            ->projects()
            ->where('status', $status)
            ->withCount('tasks')
            ->with('tasks')
            ->latest()
            ->get();

        return view('projects.index', compact('projects', 'status'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
        ]);

        $request->user()->projects()->create($validated);

        return redirect()->route('projects.index')->with('success', 'Proyecto creado.');
    }

    public function show(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['tasks.subtasks', 'tasks.tags', 'tags']);

        $columns = [
            'backlog'     => $project->tasks->where('status', 'backlog')->sortBy('position')->values(),
            'in_progress' => $project->tasks->where('status', 'in_progress')->sortBy('position')->values(),
            'testing'     => $project->tasks->where('status', 'testing')->sortBy('position')->values(),
            'done'        => $project->tasks->where('status', 'done')->sortBy('position')->values(),
        ];

        $projectTags = $project->tags;

        return view('projects.show', compact('project', 'columns', 'projectTags'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'required|in:active,archived',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)->with('success', 'Proyecto actualizado.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Proyecto eliminado.');
    }

    public function export(Request $request, Project $project): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('view', $project);

        $format = $request->query('format', 'json');
        $project->load('tasks.subtasks', 'tasks.tags');

        $slug = \Str::slug($project->name);
        $date = now()->format('Y-m-d');
        $filename = "{$project->id}_{$slug}_{$date}";

        if ($format === 'csv') {
            return $this->exportCsv($project, $filename);
        }

        return $this->exportJson($project, $filename);
    }

    private function exportJson(Project $project, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $data = [
            'exported_at' => now()->toIso8601String(),
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'status' => $project->status,
                'deadline' => $project->deadline?->toDateString(),
                'completion_percentage' => $project->completionPercentage(),
                'tasks' => $project->tasks->map(fn ($task) => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'estimated_hours' => $task->estimated_hours,
                    'position' => $task->position,
                    'tags' => $task->tags->pluck('name')->toArray(),
                    'subtasks' => $task->subtasks->map(fn ($st) => [
                        'id' => $st->id,
                        'title' => $st->title,
                        'is_completed' => $st->is_completed,
                        'estimated_hours' => $st->estimated_hours,
                    ])->values()->toArray(),
                ])->values()->toArray(),
            ],
        ];

        return response()->streamDownload(
            fn () => print(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            "{$filename}.json",
            ['Content-Type' => 'application/json']
        );
    }

    private function exportCsv(Project $project, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'project_id', 'project_name', 'project_description', 'project_status',
            'project_deadline', 'project_completion_pct',
            'task_id', 'task_title', 'task_description', 'task_status',
            'task_priority', 'task_estimated_hours', 'task_position', 'task_tags',
            'subtask_id', 'subtask_title', 'subtask_is_completed', 'subtask_estimated_hours',
        ];

        return response()->streamDownload(function () use ($project, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            $pct = $project->completionPercentage();

            foreach ($project->tasks as $task) {
                $projectRow = [
                    $project->id, $project->name, $project->description,
                    $project->status, $project->deadline?->toDateString(), $pct,
                ];
                $taskRow = [
                    $task->id, $task->title, $task->description, $task->status,
                    $task->priority, $task->estimated_hours, $task->position,
                    $task->tags->pluck('name')->implode(','),
                ];

                if ($task->subtasks->isEmpty()) {
                    fputcsv($handle, array_merge($projectRow, $taskRow, ['', '', '', '']));
                } else {
                    foreach ($task->subtasks as $subtask) {
                        fputcsv($handle, array_merge($projectRow, $taskRow, [
                            $subtask->id, $subtask->title,
                            $subtask->is_completed ? '1' : '0',
                            $subtask->estimated_hours,
                        ]));
                    }
                }
            }
            fclose($handle);
        }, "{$filename}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
