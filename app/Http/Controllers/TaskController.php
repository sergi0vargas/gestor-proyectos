<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function show(Task $task)
    {
        $this->authorize('update', $task);
        $task->load('subtasks');
        return response()->json($task);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title'                      => 'required|string|max:255',
            'description'                => 'nullable|string',
            'priority'                   => 'required|in:high,medium,low',
            'estimated_hours'            => 'nullable|numeric|min:0',
            'status'                     => 'required|in:backlog,in_progress,testing,done',
            'subtasks'                   => 'nullable|array',
            'subtasks.*.title'           => 'required|string|max:255',
            'subtasks.*.estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $maxPosition = $project->tasks()->where('status', $validated['status'])->max('position') ?? -1;
        $validated['position'] = $maxPosition + 1;

        $task = $project->tasks()->create(
            collect($validated)->except('subtasks')->toArray()
        );

        foreach ($validated['subtasks'] ?? [] as $subtaskData) {
            $task->subtasks()->create($subtaskData);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Tarea creada.');
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:high,medium,low',
            'estimated_hours' => 'nullable|numeric|min:0',
            'status' => 'required|in:backlog,in_progress,testing,done',
        ]);

        $task->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'task' => $task]);
        }

        return redirect()->route('projects.show', $task->project_id)->with('success', 'Tarea actualizada.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'status' => 'required|in:backlog,in_progress,testing,done',
            'position' => 'nullable|integer|min:0',
        ]);

        $task->update($validated);

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|integer|exists:tasks,id',
            'tasks.*.position' => 'required|integer|min:0',
            'tasks.*.status' => 'required|in:backlog,in_progress,testing,done',
        ]);

        foreach ($validated['tasks'] as $item) {
            $task = Task::find($item['id']);
            if ($task) {
                $this->authorize('update', $task);
                $task->update(['position' => $item['position'], 'status' => $item['status']]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $projectId = $task->project_id;
        $task->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('projects.show', $projectId)->with('success', 'Tarea eliminada.');
    }
}
