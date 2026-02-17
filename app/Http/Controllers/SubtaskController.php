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
}
