# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Mini Gestor de Proyectos — a multi-user project/task management app built with Laravel 12, Blade templates, Alpine.js, and Tailwind CSS. Users manage projects via a Kanban board with drag-and-drop task management.

## Common Commands

```bash
# Install dependencies
composer install
npm install

# Frontend build
npm run dev          # Vite dev server (hot reload)
npm run build        # Production build

# Database
php artisan migrate                  # Run migrations
php artisan migrate:fresh --seed     # Reset DB with demo data (demo@example.com / password)

# Development server
php artisan serve

# Code style (Laravel Pint)
./vendor/bin/pint

# Tests (Pest)
composer test
php artisan test
php artisan test --filter=TestName   # Run a single test
```

## Architecture

### Data Model

```
users → projects (user_id) → tasks (project_id) → subtasks (task_id)
```

- `Task.status`: `backlog | in_progress | testing | done`
- `Task.priority`: `high | medium | low`
- `Project.status`: `active | archived`
- `Task.position`: integer used for ordering within Kanban columns
- `Project::completionPercentage()` — only custom computed method on models

### Authorization

Three policies in `app/Policies/`: `ProjectPolicy`, `TaskPolicy`, `SubtaskPolicy`. All gate checks trace ownership back to `users.id` through the relationship chain. Tasks and subtasks are authorized by checking the parent project's `user_id`.

### Controllers & Routing

All routes in `routes/web.php` are behind `auth` + `verified` middleware. No API routes file is used — task/subtask AJAX endpoints return JSON from the same web routes.

- `TaskController@updateStatus` — called on card drop; updates both `status` and `position`
- `TaskController@reorder` — called after drag-drop to bulk-update positions within a column
- All `SubtaskController` endpoints return JSON (used via Alpine.js fetch)

### Frontend Interactivity

The Kanban board (`resources/views/projects/show.blade.php`) is an Alpine.js component (`kanbanBoard()`). It:

1. Initializes SortableJS on all four column containers on `init()`
2. Sends `PATCH /tasks/{id}/status` when a card moves between columns
3. Sends `PATCH /tasks/reorder` for within-column reordering
4. Fetches task detail via `GET /tasks/{id}` (returns JSON) to populate the edit modal

Alpine.js and SortableJS are bundled via Vite. The `resources/js/app.js` imports them.

### Blade Components

- `components/priority-badge.blade.php` — accepts `$priority`, renders color-coded badge
- `components/project-card.blade.php` — renders project card with progress bar
- `tasks/_card.blade.php` — draggable task card (partial, not a component class)
- `tasks/_modal.blade.php` — contains both the task detail modal and new-task creation modal

### CSS Conventions

Custom utilities are defined in `resources/css/app.css`:
- `.kanban-scroll` — styled horizontal scrollbar for the board
- `.sortable-ghost` — applied during drag (opacity + indigo ring)
- `.line-clamp-2` — 2-line text truncation

Tailwind dark mode is enabled (`class` strategy in `tailwind.config.js`).

### Database

Default connection is SQLite (see `.env.example`). The seeder creates one demo user and three projects with realistic task/subtask data.
