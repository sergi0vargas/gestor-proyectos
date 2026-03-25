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
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Horas estimadas</p>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $hoursProgressClasses['badge'] }}">
                                {{ $hoursPct }}%
                            </span>
                        </div>
                        <div class="mt-1 flex flex-wrap items-end gap-x-4 gap-y-1">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($totalEstimatedHours, 1) }}h</p>
                            <p class="text-sm font-medium {{ $hoursProgressClasses['text'] }}">
                                Completadas: {{ number_format($completedHours, 1) }}h
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Task distribution + Hours --}}
            <div class="grid gap-6 items-stretch" style="grid-template-columns: repeat(2, minmax(0, 1fr));">

                {{-- Distribucion de tareas --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 min-w-0 h-full">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Distribucion de tareas</h3>
                    @if($totalTasks === 0)
                        <p class="text-center text-gray-500 dark:text-gray-400">No hay tareas registradas.</p>
                    @else
                        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)] gap-6 xl:items-center h-full">
                            <div class="space-y-3 min-w-0">
                                @foreach($taskSegments as $segment)
                                    <div class="flex items-center justify-between gap-3 rounded-lg bg-gray-50 dark:bg-gray-700/40 px-3 py-2">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $segment['stroke_color'] }}"></span>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">{{ $segment['label'] }}</p>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 shrink-0">
                                            {{ $segment['count'] }} - {{ $segment['percentage'] }}%
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex items-center justify-center min-w-0">
                                <svg width="160" height="160" viewBox="0 0 100 100" class="text-gray-800 dark:text-white">
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-width="14"/>
                                    @foreach($taskSegments as $segment)
                                        @if($segment['dash'] > 0)
                                            <circle
                                                cx="50" cy="50" r="40"
                                                fill="none"
                                                stroke="{{ $segment['stroke_color'] }}"
                                                stroke-width="14"
                                                stroke-dasharray="{{ $segment['dash'] }} {{ $segment['gap'] }}"
                                                stroke-dashoffset="{{ $segment['offset'] }}"
                                                transform="rotate(-90 50 50)"
                                            />
                                        @endif
                                    @endforeach
                                    <text x="50" y="46" text-anchor="middle" font-size="13" font-weight="bold" fill="currentColor">{{ $overallProgress }}%</text>
                                    <text x="50" y="59" text-anchor="middle" font-size="7" fill="#9ca3af">avance</text>
                                </svg>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Horas estimadas vs completadas --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 min-w-0 h-full">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                        <div>
                            <h3 class="font-semibold text-gray-800 dark:text-gray-200">Horas estimadas vs completadas</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Avance calculado con horas de tareas terminadas.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold shrink-0 {{ $hoursProgressClasses['badge'] }}">
                            {{ $hoursPct }}% completado
                        </span>
                    </div>
                    @if($totalEstimatedHours == 0)
                        <p class="text-center text-gray-500 dark:text-gray-400">No hay horas estimadas registradas.</p>
                    @else
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-700/40 px-4 py-3">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Horas estimadas</p>
                                    <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($totalEstimatedHours, 1) }}h</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-700/40 px-4 py-3">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Horas completadas</p>
                                    <p class="mt-1 text-2xl font-bold {{ $hoursProgressClasses['text'] }}">{{ number_format($completedHours, 1) }}h</p>
                                </div>
                            </div>
                            <div class="relative w-full bg-gray-200 dark:bg-gray-600 rounded-full h-4 overflow-hidden">
                                <div
                                    class="absolute inset-y-0 left-0 rounded-full transition-all {{ $hoursProgressClasses['bar'] }}"
                                    style="width: {{ $hoursPct }}%"
                                ></div>
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span>Faltan {{ number_format($remainingHours, 1) }}h por completar.</span>
                                <span class="{{ $hoursProgressClasses['text'] }}">
                                    {{ number_format($completedHours, 1) }}h de {{ number_format($totalEstimatedHours, 1) }}h
                                </span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($hoursBreakdown as $segment)
                                    <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 dark:border-gray-700 px-3 py-2">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $segment['stroke_color'] }}"></span>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $segment['label'] }}</p>
                                        </div>
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 shrink-0">
                                            {{ number_format($segment['hours'], 1) }}h
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            {{-- Active projects list --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">Proyectos activos</h3>
                    <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Ver todos -></a>
                </div>
                @if($projects->isEmpty())
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        No hay proyectos activos.
                        <a href="{{ route('projects.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline ml-1">Crear uno -></a>
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
