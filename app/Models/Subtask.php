<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subtask extends Model
{
    protected $fillable = ['task_id', 'parent_id', 'title', 'is_completed', 'estimated_hours'];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Subtask::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Subtask::class, 'parent_id');
    }

    public function scopeTopLevel(Builder $query): void
    {
        $query->whereNull('parent_id');
    }
}
