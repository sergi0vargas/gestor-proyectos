<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            if (! auth()->check()) {
                return;
            }

            ActivityLog::create([
                'loggable_type' => get_class($model),
                'loggable_id'   => $model->id,
                'user_id'       => auth()->id(),
                'event'         => 'created',
                'old_values'    => null,
                'new_values'    => $model->getLoggableAttributeValues(),
                'created_at'    => now(),
            ]);
        });

        static::updating(function ($model) {
            if (! auth()->check()) {
                return;
            }

            $watchedKeys = $model->loggableAttributes ?? [];
            $changedKeys = array_intersect(array_keys($model->getDirty()), $watchedKeys);

            if (empty($changedKeys)) {
                return;
            }

            $oldValues = [];
            $newValues = [];
            foreach ($changedKeys as $key) {
                $oldValues[$key] = $model->getOriginal($key);
                $newValues[$key] = $model->getDirty()[$key];
            }

            ActivityLog::create([
                'loggable_type' => get_class($model),
                'loggable_id'   => $model->id,
                'user_id'       => auth()->id(),
                'event'         => 'updated',
                'old_values'    => $oldValues,
                'new_values'    => $newValues,
                'created_at'    => now(),
            ]);
        });

        static::deleting(function ($model) {
            if (! auth()->check()) {
                return;
            }

            ActivityLog::create([
                'loggable_type' => get_class($model),
                'loggable_id'   => $model->id,
                'user_id'       => auth()->id(),
                'event'         => 'deleted',
                'old_values'    => $model->getLoggableAttributeValues(),
                'new_values'    => null,
                'created_at'    => now(),
            ]);
        });
    }

    protected function getLoggableAttributeValues(): array
    {
        $watchedKeys = $this->loggableAttributes ?? [];
        return collect($this->getAttributes())->only($watchedKeys)->toArray();
    }
}
