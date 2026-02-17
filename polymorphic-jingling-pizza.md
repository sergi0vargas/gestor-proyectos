# Mini Gestor de Proyectos — Plan de Implementación

## Contexto

App Laravel para que desarrolladores gestionen proyectos, tareas y subtareas con estimaciones de tiempo. Multi-usuario con autenticación. Implementación lista para producción.

**Stack:** Laravel 12 + Breeze (auth) + Blade + Alpine.js + Tailwind CSS + SQLite

## ~~Fase 1: Scaffolding del proyecto~~ ✅ COMPLETADA

1. ~~Crear proyecto Laravel: `composer create-project laravel/laravel gestor_proyectos`~~
2. ~~Instalar Laravel Breeze (Blade): `composer require laravel/breeze --dev` → `php artisan breeze:install blade`~~
3. ~~Configurar SQLite en `.env`: `DB_CONNECTION=sqlite`~~
4. ~~Crear archivo `database/database.sqlite`~~
5. ~~Instalar SortableJS para drag & drop: `npm install sortablejs`~~
6. ~~`npm install && npm run build`~~
7. ~~`php artisan migrate`~~
8. ~~Inicializar git~~

**Archivos clave:**
- `.env` — configuración de BD
- `vite.config.js` — configuración de assets

## ~~Fase 2: Modelos, migraciones y relaciones~~ ✅ COMPLETADA

### Migración `projects`
```
- id
- user_id (FK → users, cascade delete)
- name (string, 255)
- description (text, nullable)
- deadline (date, nullable)
- status (enum: active/archived, default: active)
- timestamps
```

### Migración `tasks`
```
- id
- project_id (FK → projects, cascade delete)
- title (string, 255)
- description (text, nullable)
- priority (enum: high/medium/low, default: medium)
- status (enum: backlog/in_progress/testing/done, default: backlog)
- estimated_hours (decimal 8,2, nullable)
- position (integer, default: 0) — para ordenar en el kanban
- timestamps
```

### Migración `subtasks`
```
- id
- task_id (FK → tasks, cascade delete)
- title (string, 255)
- is_completed (boolean, default: false)
- estimated_hours (decimal 8,2, nullable)
- timestamps
```

### Modelos con relaciones

**User:** `hasMany(Project::class)`
**Project:** `belongsTo(User::class)`, `hasMany(Task::class)`
**Task:** `belongsTo(Project::class)`, `hasMany(Subtask::class)`
**Subtask:** `belongsTo(Task::class)`

**Archivos a crear:**
- `app/Models/Project.php`
- `app/Models/Task.php`
- `app/Models/Subtask.php`
- `database/migrations/xxxx_create_projects_table.php`
- `database/migrations/xxxx_create_tasks_table.php`
- `database/migrations/xxxx_create_subtasks_table.php`

## ~~Fase 3: Controllers y rutas~~ ✅ COMPLETADA

### Routes (`routes/web.php`)
Todas protegidas con middleware `auth`:

```
GET    /dashboard                    → DashboardController@index
GET    /projects                     → ProjectController@index
POST   /projects                     → ProjectController@store
GET    /projects/{project}           → ProjectController@show (vista kanban)
PUT    /projects/{project}           → ProjectController@update
DELETE /projects/{project}           → ProjectController@destroy

POST   /projects/{project}/tasks          → TaskController@store
PUT    /tasks/{task}                      → TaskController@update
PATCH  /tasks/{task}/status               → TaskController@updateStatus (para drag & drop)
DELETE /tasks/{task}                      → TaskController@destroy
PATCH  /tasks/reorder                     → TaskController@reorder (actualizar posiciones)

POST   /tasks/{task}/subtasks             → SubtaskController@store
PUT    /subtasks/{subtask}                → SubtaskController@update
DELETE /subtasks/{subtask}                → SubtaskController@destroy
PATCH  /subtasks/{subtask}/toggle         → SubtaskController@toggle
```

### Controllers
- **DashboardController** — Resumen: proyectos activos, tareas por estado, horas totales estimadas
- **ProjectController** — CRUD completo con autorización (solo ver/editar tus proyectos)
- **TaskController** — CRUD + updateStatus (PATCH para cambio de estado via AJAX desde kanban) + reorder
- **SubtaskController** — CRUD + toggle completado

### Políticas de autorización
- **ProjectPolicy** — Solo el dueño puede ver/editar/eliminar sus proyectos
- **TaskPolicy** — A través del proyecto padre (verificar que el usuario es dueño del proyecto)
- **SubtaskPolicy** — A través de la tarea/proyecto padre

**Archivos a crear:**
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/ProjectController.php`
- `app/Http/Controllers/TaskController.php`
- `app/Http/Controllers/SubtaskController.php`
- `app/Policies/ProjectPolicy.php`
- `app/Policies/TaskPolicy.php`

## Fase 4: Vistas Blade

### Layout
Extender el layout de Breeze (`layouts/app.blade.php`), agregar navegación con links a Dashboard y Proyectos.

### Vistas a crear

**`resources/views/dashboard.blade.php`**
- Tarjetas resumen: total proyectos, tareas pendientes, horas estimadas
- Lista de proyectos activos con barra de progreso (% tareas completadas)

**`resources/views/projects/index.blade.php`**
- Grid de proyectos con nombre, descripción truncada, deadline, progreso
- Botón crear proyecto (abre modal con Alpine.js)
- Filtro por estado (active/archived)

**`resources/views/projects/show.blade.php`** — LA VISTA PRINCIPAL
- Header con info del proyecto + botón editar/eliminar
- Tablero Kanban con 4 columnas: Backlog | En Progreso | Testing | Terminada
- Cada tarea es una card con: título, prioridad (badge de color), horas estimadas
- Drag & drop entre columnas con SortableJS + Alpine.js
- Al soltar una tarea en otra columna → PATCH AJAX para actualizar estado
- Botón "+" en cada columna para crear tarea rápida
- Click en tarea abre modal de detalle

**`resources/views/tasks/_card.blade.php`** (partial)
- Card de tarea para el kanban

**`resources/views/tasks/_modal.blade.php`** (partial)
- Modal de detalle/edición de tarea
- Lista de subtareas con checkbox para toggle
- Formulario para agregar subtareas
- Edición de título, descripción, prioridad, horas estimadas

**`resources/views/components/`**
- `project-card.blade.php` — componente Blade para card de proyecto
- `priority-badge.blade.php` — badge de prioridad con colores (alta=rojo, media=amarillo, baja=verde)
- `modal.blade.php` — componente modal reutilizable con Alpine.js

## Fase 5: Interactividad con Alpine.js

### Kanban Board (`projects/show.blade.php`)
```javascript
// Alpine.js component para el kanban
Alpine.data('kanbanBoard', () => ({
    // Inicializar SortableJS en cada columna
    // Al mover tarea: fetch PATCH /tasks/{id}/status con nuevo estado
    // Actualizar posiciones: fetch PATCH /tasks/reorder
}))
```

### Modales
```javascript
// Alpine.js para modales de crear/editar
x-data="{ showModal: false, editing: null }"
```

### Subtareas
```javascript
// Toggle con fetch PATCH, actualizar UI sin reload
```

## Fase 6: Estilos y UX

- Tailwind CSS (viene con Breeze) para todo el styling
- Colores de prioridad: high=red-500, medium=yellow-500, low=green-500
- Columnas kanban con fondo diferenciado
- Cards con sombra, hover effect
- Responsive: en móvil las columnas se stackean verticalmente con scroll horizontal
- Dark mode: usar las clases `dark:` de Tailwind (Breeze ya lo soporta)

## Fase 7: Seeders y datos de ejemplo

- **DatabaseSeeder** con datos de ejemplo:
  - 1 usuario demo (demo@example.com / password)
  - 3 proyectos con tareas variadas en diferentes estados
  - Subtareas de ejemplo

**Archivos:**
- `database/seeders/DatabaseSeeder.php`

## Verificación

1. `composer install && npm install && npm run build`
2. `php artisan migrate --seed`
3. `php artisan serve`
4. Registro de usuario nuevo → login → crear proyecto → crear tareas → drag & drop entre columnas → crear subtareas → toggle completado
5. Verificar que un usuario no puede ver proyectos de otro
6. Verificar cálculo de horas estimadas y progreso
7. Verificar responsive en móvil

## Orden de implementación

1. ~~Scaffolding (Fase 1)~~ ✅
2. ~~Modelos y migraciones (Fase 2)~~ ✅
3. ~~Controllers, rutas y policies (Fase 3)~~ ✅
4. Dashboard y CRUD de proyectos (Fase 4 parcial)
5. Vista Kanban + cards de tareas (Fase 4 + Fase 5)
6. Modal de tarea + subtareas (Fase 4 + Fase 5)
7. Seeders (Fase 7)
8. Pulido de estilos y responsive (Fase 6)
9. Verificación final
