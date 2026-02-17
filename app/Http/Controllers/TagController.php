<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        return response()->json($project->tags);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
        ]);

        $tag = $project->tags()->create($validated);

        return response()->json(['success' => true, 'tag' => $tag]);
    }

    public function destroy(Tag $tag)
    {
        $this->authorize('update', $tag->project);

        $tag->delete();

        return response()->json(['success' => true]);
    }

    public function attach(Task $task, Tag $tag)
    {
        $this->authorize('update', $task);

        $task->tags()->syncWithoutDetaching([$tag->id]);

        return response()->json(['success' => true]);
    }

    public function detach(Task $task, Tag $tag)
    {
        $this->authorize('update', $task);

        $task->tags()->detach($tag->id);

        return response()->json(['success' => true]);
    }
}
