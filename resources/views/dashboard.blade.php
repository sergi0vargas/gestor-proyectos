<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 flex items-center gap-4">
                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-full">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Proyectos activos</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $activeProjects }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 flex items-center gap-4">
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tareas pendientes</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $pendingTasks }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 flex items-center gap-4">
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Horas estimadas</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($totalEstimatedHours, 1) }}h</p>
                    </div>
                </div>
            </div>

            {{-- Active projects list --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">Proyectos activos</h3>
                    <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Ver todos →</a>
                </div>
                @if($projects->isEmpty())
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        No hay proyectos activos.
                        <a href="{{ route('projects.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline ml-1">Crear uno →</a>
                    </div>
                @else
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($projects as $project)
                            @php $pct = $project->completionPercentage() @endphp
                            <li class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <div class="flex-1 min-w-0 mr-4">
                                    <a href="{{ route('projects.show', $project) }}" class="font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 truncate block">
                                        {{ $project->name }}
                                    </a>
                                    @if($project->deadline)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            Deadline: {{ $project->deadline->format('d/m/Y') }}
                                        </p>
                                    @endif
                                    <div class="mt-2 w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
                                        <div class="bg-indigo-500 h-1.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300 shrink-0">{{ $pct }}%</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
