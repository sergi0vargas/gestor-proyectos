<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $demo = User::factory()->create([
            'name'     => 'Demo User',
            'email'    => 'demo@example.com',
            'password' => Hash::make('password'),
        ]);

        // Project 1: App móvil
        $p1 = Project::create([
            'user_id'     => $demo->id,
            'name'        => 'App Móvil E-commerce',
            'description' => 'Desarrollo de la app móvil para el sistema de e-commerce.',
            'deadline'    => now()->addMonths(2),
            'status'      => 'active',
        ]);

        $tasks1 = [
            ['title' => 'Diseño de pantallas', 'status' => 'done', 'priority' => 'high', 'estimated_hours' => 12, 'position' => 0,
             'subtasks' => [['title' => 'Wireframes', 'is_completed' => true], ['title' => 'Mockups en Figma', 'is_completed' => true]]],
            ['title' => 'Autenticación con JWT', 'status' => 'done', 'priority' => 'high', 'estimated_hours' => 8, 'position' => 1,
             'subtasks' => [['title' => 'Login/Registro', 'is_completed' => true], ['title' => 'Refresh token', 'is_completed' => true]]],
            ['title' => 'Listado de productos', 'status' => 'in_progress', 'priority' => 'high', 'estimated_hours' => 10, 'position' => 0,
             'subtasks' => [['title' => 'API endpoint', 'is_completed' => true], ['title' => 'UI con paginación', 'is_completed' => false]]],
            ['title' => 'Carrito de compras', 'status' => 'in_progress', 'priority' => 'medium', 'estimated_hours' => 15, 'position' => 1,
             'subtasks' => [['title' => 'Agregar/quitar items', 'is_completed' => false], ['title' => 'Cálculo de totales', 'is_completed' => false]]],
            ['title' => 'Pasarela de pagos', 'status' => 'backlog', 'priority' => 'high', 'estimated_hours' => 20, 'position' => 0, 'subtasks' => []],
            ['title' => 'Push notifications', 'status' => 'backlog', 'priority' => 'low', 'estimated_hours' => 6, 'position' => 1, 'subtasks' => []],
            ['title' => 'Testing en dispositivos', 'status' => 'testing', 'priority' => 'medium', 'estimated_hours' => 8, 'position' => 0,
             'subtasks' => [['title' => 'iOS', 'is_completed' => false], ['title' => 'Android', 'is_completed' => false]]],
        ];

        foreach ($tasks1 as $taskData) {
            $subtasks = $taskData['subtasks'];
            unset($taskData['subtasks']);
            $task = $p1->tasks()->create($taskData);
            foreach ($subtasks as $sub) {
                $task->subtasks()->create($sub);
            }
        }

        // Project 2: Dashboard Analytics
        $p2 = Project::create([
            'user_id'     => $demo->id,
            'name'        => 'Dashboard Analytics',
            'description' => 'Panel de métricas y reportes para el equipo de negocio.',
            'deadline'    => now()->addWeeks(3),
            'status'      => 'active',
        ]);

        $tasks2 = [
            ['title' => 'Setup del proyecto', 'status' => 'done', 'priority' => 'high', 'estimated_hours' => 4, 'position' => 0, 'subtasks' => []],
            ['title' => 'Conexión a base de datos', 'status' => 'done', 'priority' => 'high', 'estimated_hours' => 3, 'position' => 1, 'subtasks' => []],
            ['title' => 'Gráficas de ventas', 'status' => 'in_progress', 'priority' => 'high', 'estimated_hours' => 12, 'position' => 0,
             'subtasks' => [['title' => 'Chart.js integración', 'is_completed' => true], ['title' => 'Filtro por fecha', 'is_completed' => false]]],
            ['title' => 'Tabla de usuarios activos', 'status' => 'backlog', 'priority' => 'medium', 'estimated_hours' => 6, 'position' => 0, 'subtasks' => []],
            ['title' => 'Exportar a CSV', 'status' => 'backlog', 'priority' => 'low', 'estimated_hours' => 4, 'position' => 1, 'subtasks' => []],
        ];

        foreach ($tasks2 as $taskData) {
            $subtasks = $taskData['subtasks'];
            unset($taskData['subtasks']);
            $task = $p2->tasks()->create($taskData);
            foreach ($subtasks as $sub) {
                $task->subtasks()->create($sub);
            }
        }

        // Project 3: API REST (archived)
        $p3 = Project::create([
            'user_id'     => $demo->id,
            'name'        => 'API REST v1',
            'description' => 'Primera versión de la API REST. Migrada a v2.',
            'deadline'    => now()->subMonth(),
            'status'      => 'archived',
        ]);

        $tasks3 = [
            ['title' => 'Endpoints CRUD', 'status' => 'done', 'priority' => 'high', 'estimated_hours' => 16, 'position' => 0, 'subtasks' => []],
            ['title' => 'Autenticación API tokens', 'status' => 'done', 'priority' => 'high', 'estimated_hours' => 8, 'position' => 1, 'subtasks' => []],
            ['title' => 'Documentación Swagger', 'status' => 'done', 'priority' => 'medium', 'estimated_hours' => 6, 'position' => 2, 'subtasks' => []],
        ];

        foreach ($tasks3 as $taskData) {
            $subtasks = $taskData['subtasks'];
            unset($taskData['subtasks']);
            $p3->tasks()->create($taskData);
        }
    }
}
