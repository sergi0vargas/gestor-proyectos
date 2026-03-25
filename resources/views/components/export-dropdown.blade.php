@props(['project', 'compact' => false])

<div x-data="{ open: false }" :class="open ? 'relative z-30' : 'relative z-0'">
    <button
        @click="open = !open"
        @keydown.escape.window="open = false"
        type="button"
        title="Exportar proyecto"
        @class([
            'inline-flex items-center border rounded-lg transition',
            'gap-1.5 px-3 py-1.5 text-sm' => ! $compact,
            'p-1.5' => $compact,
            'text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600',
        ])
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        @unless($compact)
            Exportar
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        @endunless
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        @click.away="open = false"
        class="absolute right-0 mt-1 w-44 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg"
    >
        <a href="{{ route('projects.export', [$project, 'format' => 'json']) }}"
           @click="open = false"
           class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-t-lg transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Exportar JSON
        </a>
        <a href="{{ route('projects.export', [$project, 'format' => 'csv']) }}"
           @click="open = false"
           class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-b-lg transition border-t border-gray-100 dark:border-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 3v18M14 3v18" />
            </svg>
            Exportar CSV
        </a>
    </div>
</div>
