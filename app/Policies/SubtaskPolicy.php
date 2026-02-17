<?php

namespace App\Policies;

use App\Models\Subtask;
use App\Models\User;

class SubtaskPolicy
{
    public function update(User $user, Subtask $subtask): bool
    {
        return $user->id === $subtask->task->project->user_id;
    }

    public function delete(User $user, Subtask $subtask): bool
    {
        return $user->id === $subtask->task->project->user_id;
    }
}
