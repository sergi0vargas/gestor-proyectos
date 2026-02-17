<x-app-layout>
    <x-slot name="header">
        <div x-data class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Proyectos</h2>
            <button @click="$dispatch('open-modal', 'create-project')"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo proyecto
            </button>
        </div>
    </x-slot>

    <div x-data class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

            {{-- Flash --}}
            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Filter --}}
            <div class="flex gap-2">
                <a href="{{ route('projects.index', ['status' => 'active']) }}"
                   class="px-4 py-1.5 rounded-full text-sm font-medium transition
                          {{ $status === 'active' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    Activos
                </a>
                <a href="{{ route('projects.index', ['status' => 'archived']) }}"
                   class="px-4 py-1.5 rounded-full text-sm font-medium transition
                          {{ $status === 'archived' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    Archivados
                </a>
            </div>

            {{-- Grid --}}
            @if($projects->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400 mb-3">No hay proyectos {{ $status === 'archived' ? 'archivados' : 'activos' }}.</p>
                    @if($status === 'active')
                        <button @click="$dispatch('open-modal', 'create-project')"
                                class="inline-flex items-center gap-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                            + Crear primer proyecto
                        </button>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($projects as $project)
                        <x-project-card :project="$project" />
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    {{-- Modal: crear proyecto --}}
    <x-modal name="create-project" focusable>
        <form method="POST" action="{{ route('projects.store') }}" class="p-6 space-y-4">
            @csrf
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nuevo proyecto</h3>

            <div>
                <x-input-label for="name" value="Nombre *" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required autofocus
                              value="{{ old('name') }}" placeholder="Nombre del proyecto" />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="description" value="Descripción" />
                <textarea id="description" name="description" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                          placeholder="Descripción opcional">{{ old('description') }}</textarea>
            </div>

            <div>
                <x-input-label for="deadline" value="Deadline" />
                <x-text-input id="deadline" name="deadline" type="date" class="mt-1 block w-full"
                              value="{{ old('deadline') }}" />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button>Crear proyecto</x-primary-button>
            </div>
        </form>
    </x-modal>

</x-app-layout>
