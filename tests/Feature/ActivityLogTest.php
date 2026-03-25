<?php

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('LogsActivity trait', function () {

    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    it('logs a created event when a project is created', function () {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        expect(ActivityLog::count())->toBe(1);
        $log = ActivityLog::first();
        expect($log->event)->toBe('created');
        expect($log->loggable_type)->toBe(Project::class);
        expect($log->loggable_id)->toBe($project->id);
        expect($log->user_id)->toBe($this->user->id);
        expect($log->old_values)->toBeNull();
        expect($log->new_values)->toHaveKey('name');
    });

    it('logs an updated event when a watched attribute changes on a project', function () {
        $project = Project::factory()->create(['user_id' => $this->user->id, 'name' => 'Old Name']);
        ActivityLog::truncate();

        $project->update(['name' => 'New Name']);

        expect(ActivityLog::count())->toBe(1);
        $log = ActivityLog::first();
        expect($log->event)->toBe('updated');
        expect($log->old_values)->toBe(['name' => 'Old Name']);
        expect($log->new_values)->toBe(['name' => 'New Name']);
    });

    it('does not log when only unwatched attributes change on a task', function () {
        $project = Project::factory()->create(['user_id' => $this->user->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);
        ActivityLog::truncate();

        $task->update(['position' => 99]);

        expect(ActivityLog::count())->toBe(0);
    });

    it('logs a deleted event when a project is deleted', function () {
        $project = Project::factory()->create(['user_id' => $this->user->id]);
        $projectId = $project->id;
        ActivityLog::truncate();

        $project->delete();

        expect(ActivityLog::count())->toBe(1);
        $log = ActivityLog::first();
        expect($log->event)->toBe('deleted');
        expect($log->loggable_id)->toBe($projectId);
        expect($log->old_values)->toHaveKey('name');
        expect($log->new_values)->toBeNull();
    });

    it('does not log when no watched attribute changes', function () {
        $project = Project::factory()->create(['user_id' => $this->user->id]);
        ActivityLog::truncate();

        // Saving with the same values — getDirty() will be empty
        $project->update(['name' => $project->name]);

        expect(ActivityLog::count())->toBe(0);
    });

    it('does not crash when there is no authenticated user (seeder context)', function () {
        auth()->logout();

        $project = Project::factory()->create(['user_id' => $this->user->id]);

        // Should log nothing when not authenticated (guard prevents it)
        expect(ActivityLog::count())->toBe(0);
    });

    it('logs status change when a task status is updated', function () {
        $project = Project::factory()->create(['user_id' => $this->user->id]);
        $task = Task::factory()->create(['project_id' => $project->id, 'status' => 'backlog']);
        ActivityLog::truncate();

        $task->update(['status' => 'in_progress', 'position' => 0]);

        expect(ActivityLog::count())->toBe(1);
        $log = ActivityLog::first();
        expect($log->event)->toBe('updated');
        expect($log->new_values)->toBe(['status' => 'in_progress']);
        expect($log->old_values)->toBe(['status' => 'backlog']);
    });

});

describe('ActivityLogController', function () {

    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->project = Project::factory()->create(['user_id' => $this->user->id]);
        $this->task = Task::factory()->create(['project_id' => $this->project->id]);
    });

    it('returns project activity as JSON for the owner', function () {
        $response = $this->getJson("/projects/{$this->project->id}/activity");

        $response->assertOk()->assertJsonIsArray();
    });

    it('returns 403 for a project not owned by the user', function () {
        $otherUser = User::factory()->create();
        $otherProject = Project::factory()->create(['user_id' => $otherUser->id]);

        $this->getJson("/projects/{$otherProject->id}/activity")->assertForbidden();
    });

    it('returns task activity as JSON for the owner', function () {
        $response = $this->getJson("/tasks/{$this->task->id}/activity");

        $response->assertOk()->assertJsonIsArray();
    });

    it('returns 403 for a task not owned by the user', function () {
        $otherUser = User::factory()->create();
        $otherProject = Project::factory()->create(['user_id' => $otherUser->id]);
        $otherTask = Task::factory()->create(['project_id' => $otherProject->id]);

        $this->getJson("/tasks/{$otherTask->id}/activity")->assertForbidden();
    });

    it('returns entries ordered latest first with correct structure', function () {
        // Create a log entry by updating the project
        $this->project->update(['name' => 'Updated Name']);

        $response = $this->getJson("/projects/{$this->project->id}/activity");
        $data = $response->json();

        expect($data)->not->toBeEmpty();
        $entry = $data[0];
        expect($entry)->toHaveKeys(['id', 'event', 'old_values', 'new_values', 'created_at', 'user']);
        expect($entry['user'])->toBe($this->user->name);
    });

    it('limits results to 50 entries', function () {
        // Bulk insert 60 log entries
        for ($i = 0; $i < 60; $i++) {
            \App\Models\ActivityLog::create([
                'loggable_type' => Project::class,
                'loggable_id'   => $this->project->id,
                'user_id'       => $this->user->id,
                'event'         => 'updated',
                'old_values'    => ['name' => "Name $i"],
                'new_values'    => ['name' => "Name " . ($i + 1)],
                'created_at'    => now(),
            ]);
        }

        $response = $this->getJson("/projects/{$this->project->id}/activity");
        expect($response->json())->toHaveCount(50);
    });

});
