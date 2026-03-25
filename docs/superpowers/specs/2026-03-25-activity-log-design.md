# Activity Log — Design Spec
**Date:** 2026-03-25
**Status:** Approved
**Project:** Mini Gestor de Proyectos (Laravel 12 + Alpine.js + Tailwind)

---

## Context

The project management app currently has no record of what changed, when, or by whom. Users want a chronological activity history per project and per task so they can audit changes, understand when status transitions happened, and see who made each edit.

**Constraints:**
- No external packages (no Spatie Activity Log)
- Do not log position changes (drag-drop reorder noise)
- Project log modal shows only direct project attribute changes (not task changes)
- Must support future subtask logging without schema changes

---

## Architecture

### Single polymorphic table `activity_logs`

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| loggable_type | string | Fully-qualified class name (e.g. `App\Models\Project`) |
| loggable_id | bigint unsigned | ID of the logged entity |
| user_id | FK → users, cascadeOnDelete, **nullable** | Who made the change; null = system/seeder action |
| event | enum: created / updated / deleted | |
| old_values | json nullable | Field values before the change |
| new_values | json nullable | Field values after the change |
| created_at | timestamp (useCurrent) | Log time; no `updated_at` — logs are immutable |

Composite index on `(loggable_type, loggable_id)` via Laravel `morphs()`.

**`user_id` is nullable** to safely handle seeder/artisan/queue contexts where `auth()->id()` is null. The controller already handles this: `$log->user?->name ?? 'Sistema'`.

---

## Components

### 1. Migration
`database/migrations/2026_03_25_000000_create_activity_logs_table.php`

Uses `$table->morphs('loggable')` and omits `$table->timestamps()`, adding only `$table->timestamp('created_at')->useCurrent()`. The `user_id` column is defined with `->nullable()`.

### 2. `ActivityLog` Model
`app/Models/ActivityLog.php`

- `$timestamps = false`
- Casts: `old_values → array`, `new_values → array`, `created_at → datetime`
- Relationships: `loggable()` (morphTo), `user()` (belongsTo User)
- Scope: `scopeForLoggable(string $type, int $id)`
- Fillable: loggable_type, loggable_id, user_id, event, old_values, new_values, created_at

**Note on `created_at`**: The column has a DB-level `useCurrent()` default, but the trait always passes `created_at => now()` explicitly for clarity. The `created_at` field must be in `$fillable` for this to work with `$timestamps = false`.

### 3. `LogsActivity` Trait
`app/Traits/LogsActivity.php`

Each consuming model must declare:
```php
protected array $loggableAttributes = [...];
```

Boot method (`bootLogsActivity`) registers three Eloquent hooks:

**Guard**: All three hooks check `auth()->check()` first. If false (seeder, artisan, queue context), the hook returns immediately and no log is written.

**`created` hook** (NOT `creating` — model needs its `id` set after INSERT):
- Captures all `$loggableAttributes` values from the new model
- Creates log: event=created, old_values=null, new_values=captured values, user_id=auth()->id()

**`updating` hook**:
- Calls `getDirty()` and intersects keys with `$loggableAttributes`
- If intersection is empty → returns early, **no log written**
- Otherwise: old_values from `getOriginal($key)`, new_values from dirty values
- Creates log: event=updated, user_id=auth()->id()

**`deleting` hook** (fires before DELETE SQL, attributes still accessible):
- Captures all `$loggableAttributes` values
- Creates log: event=deleted, old_values=captured, new_values=null, user_id=auth()->id()

### 4. Model Changes

**`app/Models/Project.php`**
```php
use App\Traits\LogsActivity;
// inside class:
use LogsActivity;
protected array $loggableAttributes = ['name', 'description', 'deadline', 'status'];
```

**`app/Models/Task.php`**
```php
use App\Traits\LogsActivity;
// inside class:
use LogsActivity;
protected array $loggableAttributes = ['title', 'description', 'priority', 'status', 'estimated_hours'];
```

`position` is intentionally absent — drag-drop reorder does not create log entries.

**Why `reorder` does not cause duplicate logs**: `TaskController@reorder` calls `Task::find($id)` (fresh DB fetch) for each task. After `updateStatus` has already committed the new status to the database, `reorder`'s fresh model has `getOriginal('status')` = new status. When `reorder` passes the same status in `update()`, `getDirty()` returns empty for `status` (no change). Only `position` is dirty → filtered by `$loggableAttributes`. No duplicate log entry.

### 5. `ActivityLogController`
`app/Http/Controllers/ActivityLogController.php`

```
GET /projects/{project}/activity  → forProject()   authorize: ProjectPolicy@view
GET /tasks/{task}/activity        → forTask()       authorize: TaskPolicy@update (intentional reuse — TaskPolicy has no 'view' method; 'update' verifies ownership)
```

Both methods:
1. Authorize ownership
2. Query ActivityLog with `with('user')` (prevents N+1)
3. Filter by `forLoggable(ModelClass, $id)`
4. Order `latest('created_at')`, limit 50
5. Map to: `{ id, event, old_values, new_values, created_at (d/m/Y H:i), user (name) }`
6. Return `response()->json($logs)`

Edge case: `$log->user?->name ?? 'Sistema'` handles deleted users and null user_id (seeder-created records).

### 6. Routes
`routes/web.php` — add inside auth+verified group:
```php
Route::get('/projects/{project}/activity', [ActivityLogController::class, 'forProject']);
Route::get('/tasks/{task}/activity', [ActivityLogController::class, 'forTask']);
```

---

## Frontend

### Project Activity Modal (`resources/views/projects/show.blade.php`)

**Button placement**: The `<x-slot name="header">` section (lines 2–26) is OUTSIDE the `<div x-data="kanbanBoard()">` wrapper (line 29). The "Historial" button must be placed INSIDE the kanbanBoard div, at the top of the board content area (before the kanban columns), so it has access to `openProjectLog()` in the Alpine scope.

**New Alpine.js state** (added to `kanbanBoard()` data):
```js
projectLog: [],
loadingProjectLog: false,
```

**New Alpine.js methods**:
```js
openProjectLog()       // dispatch open-modal (string form), fetch /projects/{id}/activity, set projectLog
translateProjectValue(field, value)  // maps enum values to Spanish
```

**Modal open dispatch**: Use string form to match existing codebase pattern:
```js
this.$dispatch('open-modal', 'project-activity')
// NOT: this.$dispatch('open-modal', { name: 'project-activity' })
```

**New UI elements**:
- "Historial" button at the top of the kanban board area (inside `kanbanBoard()` div, before columns)
- `<x-modal name="project-activity" maxWidth="2xl">` (placed before `</div>` that closes kanbanBoard) with:
  - Loading spinner (shown while `loadingProjectLog`)
  - Empty state message
  - `<ul>` of log entries showing: user, event badge (color-coded), timestamp, before→after fields

### Task Activity Modal (`resources/views/tasks/_modal.blade.php`)

**New Alpine.js state** (added to `kanbanBoard()`):
```js
taskLog: [],
loadingTaskLog: false,
```

**New Alpine.js methods**:
```js
openTaskLog()          // dispatch open-modal (string form), fetch /tasks/{activeTask.id}/activity, set taskLog
translateTaskValue(field, value)  // maps enum values to Spanish
```

**New UI elements**:
- "Historial" button in task-detail modal footer (left side; "Eliminar tarea" stays right). Footer layout changes from `flex justify-end` to `flex items-center justify-between`.
- `<x-modal name="task-activity" maxWidth="2xl">` (appended to end of file) with same log display format

### Log Entry Display Format
```
[Sergio]  [Actualizado]                       25/03/2026 14:30
  Estado:     Pendiente  →  En progreso
  Prioridad:  Media      →  Alta
```

Field name translations (Alpine.js maps):
- `name→Nombre`, `description→Descripción`, `deadline→Fecha límite`, `status→Estado`
- `title→Título`, `priority→Prioridad`, `estimated_hours→Horas estimadas`

Enum value translations (match existing UI labels, e.g. `done = 'Terminada'` per show.blade.php line 43):
- `backlog→Pendiente`, `in_progress→En progreso`, `testing→En revisión`, `done→Terminada`
- `high→Alta`, `medium→Media`, `low→Baja`, `active→Activo`, `archived→Archivado`

---

## Key Implementation Notes

1. **`created` not `creating`**: The `creating` hook fires before INSERT so `$model->id` is null. Always use `created` for the creation log.

2. **No recursion risk**: `ActivityLog` itself does NOT use `LogsActivity`. Safe to call `ActivityLog::create()` inside the trait.

3. **`updateStatus` behavior**: Sets both `status` and `position`. `position` not in `$loggableAttributes` → filtered. `status` IS watched → logged if it changed. Dragging across columns = logged. ✓

4. **`reorder` behavior**: Fetches fresh model via `Task::find()`. Status already committed → not dirty → no log. Position only → filtered. ✓

5. **SQLite + JSON**: Laravel's `array` cast handles serialization. Works transparently with SQLite.

6. **`auth()->check()` guard**: Protects against seeder/artisan/queue contexts. `user_id` is nullable for the same reason.

---

## Future Extensibility (Subtasks)

Adding activity logging to `Subtask` requires only:
```php
// In app/Models/Subtask.php
use App\Traits\LogsActivity;

use LogsActivity;
protected array $loggableAttributes = ['title', 'is_completed', 'estimated_hours'];
```

And a new route + controller method. No schema changes needed.

---

## Files Summary

| Action | Path |
|---|---|
| CREATE | `database/migrations/2026_03_25_000000_create_activity_logs_table.php` |
| CREATE | `app/Models/ActivityLog.php` |
| CREATE | `app/Traits/LogsActivity.php` |
| CREATE | `app/Http/Controllers/ActivityLogController.php` |
| MODIFY | `app/Models/Project.php` |
| MODIFY | `app/Models/Task.php` |
| MODIFY | `routes/web.php` |
| MODIFY | `resources/views/projects/show.blade.php` |
| MODIFY | `resources/views/tasks/_modal.blade.php` |
| CREATE | `tests/Feature/ActivityLogTest.php` |

---

## Verification

1. **Project creation** → Historial modal shows 1 "Creado" entry with initial field values
2. **Project update** (name + deadline) → modal shows "Actualizado" with only changed fields; unchanged fields not shown
3. **Task creation** → Historial modal shows 1 "Creado" entry
4. **Drag card across columns** → task Historial shows status change (e.g. Pendiente → En progreso); only ONE entry (not two)
5. **Drag card within column** → no new log entries
6. **Task field edit** (priority + title) → Historial shows both changes in one "Actualizado" entry
7. **Seeder** (`php artisan migrate:fresh --seed`) completes without DB errors
8. **403 check** → a different authenticated user gets HTTP 403 on `/projects/{other}/activity`
9. **Null/deleted user** → log entry displays "Sistema" instead of crashing
