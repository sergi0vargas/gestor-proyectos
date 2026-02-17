<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $subtask = $task->subtasks()->create($validated);

        return response()->json(['success' => true, 'subtask' => $subtask]);
    }

    public function update(Request $request, Subtask $subtask)
    {
        $this->authorize('update', $subtask);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $subtask->update($validated);

        return response()->json(['success' => true, 'subtask' => $subtask]);
    }

    public function destroy(Subtask $subtask)
    {
        $this->authorize('delete', $subtask);
        $subtask->delete();

        return response()->json(['success' => true]);
    }

    public function toggle(Subtask $subtask)
    {
        $this->authorize('update', $subtask);

        $subtask->update(['is_completed' => ! $subtask->is_completed]);

        return response()->json(['success' => true, 'is_completed' => $subtask->is_completed]);
    }

    public function storeChild(Request $request, Subtask $subtask)
    {
        $this->authorize('update', $subtask);

        if ($subtask->parent_id !== null) {
            return response()->json(['error' => 'No se permiten más de 2 niveles de subtareas.'], 422);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $child = Subtask::create([
            'task_id' => $subtask->task_id,
            'parent_id' => $subtask->id,
            'title' => $validated['title'],
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'is_completed' => false,
        ]);

        return response()->json(['success' => true, 'subtask' => $child]);
    }
}
