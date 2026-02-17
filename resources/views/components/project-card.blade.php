@props(['project'])

@php $pct = $project->completionPercentage() @endphp

<div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition-shadow flex flex-col">
    <div class="p-5 flex-1">
        <div class="flex items-start justify-between gap-2 mb-2">
            <a href="{{ route('projects.show', $project) }}"
               class="font-semibold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 line-clamp-2">
                {{ $project->name }}
            </a>
            @if($project->status === 'archived')
                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    Archivado
                </span>
            @endif
        </div>

        @if($project->description)
            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">{{ $project->description }}</p>
        @endif

        @if($project->deadline)
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">
                📅 {{ $project->deadline->format('d/m/Y') }}
            </p>
        @endif

        <div class="mt-auto">
            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                <span>Progreso</span>
                <span>{{ $pct }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
                <div class="bg-indigo-500 h-1.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    </div>

    <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $project->tasks_count ?? $project->tasks->count() }} tareas</span>
        <a href="{{ route('projects.show', $project) }}"
           class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
            Abrir →
        </a>
    </div>
</div>
