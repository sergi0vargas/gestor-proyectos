<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class AITaskDecomposerService
{
    public function decompose(string $title, ?string $description = null): array
    {
        $text = "Task title: {$title}";
        if ($description) {
            $text .= "\nTask description: {$description}";
        }

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Eres un asistente de gestión de proyectos. Descompones tareas en subtareas. Responde siempre en español con JSON válido, sin markdown.',
                ],
                [
                    'role' => 'user',
                    'content' => "Descompón esta tarea en subtareas con horas estimadas. Sugiere también la prioridad (high, medium o low) para la tarea principal.\n\nDevuelve JSON con esta estructura (los títulos de las subtareas en español):\n{\"priority\": \"high|medium|low\", \"subtasks\": [{\"title\": \"...\", \"estimated_hours\": 1.5}, ...]}\n\n{$text}",
                ],
            ],
            'response_format' => ['type' => 'json_object'],
            'max_tokens' => 1000,
        ]);

        $data = json_decode($response->choices[0]->message->content, true);

        return [
            'priority' => in_array($data['priority'] ?? '', ['high', 'medium', 'low'])
                ? $data['priority']
                : 'medium',
            'subtasks' => array_map(fn($s) => [
                'title' => (string) ($s['title'] ?? ''),
                'estimated_hours' => is_numeric($s['estimated_hours'] ?? null)
                    ? (float) $s['estimated_hours']
                    : null,
            ], array_slice($data['subtasks'] ?? [], 0, 20)),
        ];
    }
}
