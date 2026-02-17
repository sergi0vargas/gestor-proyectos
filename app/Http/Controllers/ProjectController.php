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
}
