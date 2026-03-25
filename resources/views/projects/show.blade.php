<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="min-w-0">
                <a href="{{ route('projects.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-sm">← Proyectos</a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 truncate mt-0.5">{{ $project->name }}</h2>
                @if($project->deadline)
                    <p class="text-sm text-gray-500 dark:text-gray-400">📅 {{ $project->deadline->format('d/m/Y') }}</p>
                @endif
            </div>
            <div x-data="{}" class="flex items-center gap-2 shrink-0">
                <x-export-dropdown :project="$project" />
                <button @click="$dispatch('open-modal', 'edit-project')"
                        class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    Editar
                </button>
                <form method="POST" action="{{ route('projects.destroy', $project) }}"
                      onsubmit="return confirm('¿Eliminar este proyecto y todas sus tareas?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="px-3 py-1.5 text-sm text-red-600 dark:text-red-400 bg-white dark:bg-gray-700 border border-red-300 dark:border-red-600 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    {{-- Entire page content within kanbanBoard scope so modals have access --}}
    <div x-data="kanbanBoard()" x-init="init()">

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                @if(session('success'))
                    <div class="mb-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Project activity log button --}}
                <div class="flex justify-end mb-3">
                    <button @click="openProjectLog()"
                            :disabled="loadingProjectLog"
                            class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 transition">
                        <span x-show="!loadingProjectLog">Historial del proyecto</span>
                        <span x-show="loadingProjectLog">Cargando...</span>
                    </button>
                </div>

                {{-- Kanban board --}}
                <div class="flex gap-4 overflow-x-auto pb-4 kanban-scroll" style="min-height: 70vh">

                    @foreach(['backlog' => 'Backlog', 'in_progress' => 'En Progreso', 'testing' => 'Testing', 'done' => 'Terminada'] as $colStatus => $label)
                    <div class="kanban-column flex-shrink-0 w-72 flex flex-col">

                        {{-- Column header --}}
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                <span class="bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs font-medium px-1.5 py-0.5 rounded-full column-count"
                                      data-status="{{ $colStatus }}">{{ $columns[$colStatus]->count() }}</span>
                            </div>
                            <button @click="openNewTaskModal('{{ $colStatus }}')"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition p-1 rounded"
                                    title="Nueva tarea">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Drop zone --}}
                        <div class="sortable-list flex-1 space-y-2 p-2 rounded-lg bg-gray-100 dark:bg-gray-800/50 min-h-[200px]"
                             id="column-{{ $colStatus }}"
                             data-status="{{ $colStatus }}">
                            @foreach($columns[$colStatus] as $task)
                                @include('tasks._card', ['task' => $task])
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>

        {{-- Modals (inside kanbanBoard scope) --}}
        @include('tasks._modal', ['project' => $project])

        {{-- Edit project modal --}}
        <x-modal name="edit-project" focusable>
            <form method="POST" action="{{ route('projects.update', $project) }}" class="p-6 space-y-4">
                @csrf @method('PUT')
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Editar proyecto</h3>
                <div>
                    <x-input-label for="edit_name" value="Nombre *" />
                    <x-text-input id="edit_name" name="name" type="text" class="mt-1 block w-full" required
                                  value="{{ old('name', $project->name) }}" />
                </div>
                <div>
                    <x-input-label for="edit_description" value="Descripción" />
                    <textarea id="edit_description" name="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('description', $project->description) }}</textarea>
                </div>
                <div>
                    <x-input-label for="edit_deadline" value="Deadline" />
                    <x-text-input id="edit_deadline" name="deadline" type="date" class="mt-1 block w-full"
                                  value="{{ old('deadline', $project->deadline?->format('Y-m-d')) }}" />
                </div>
                <div>
                    <x-input-label for="edit_status" value="Estado" />
                    <select id="edit_status" name="status"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        <option value="active" @selected(old('status', $project->status) === 'active')>Activo</option>
                        <option value="archived" @selected(old('status', $project->status) === 'archived')>Archivado</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                    <x-primary-button>Guardar</x-primary-button>
                </div>
            </form>
        </x-modal>

        {{-- Project Activity Log Modal --}}
        <x-modal name="project-activity" maxWidth="2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Historial del proyecto</h3>
                    <button @click="$dispatch('close')"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div x-show="loadingProjectLog" class="flex justify-center py-8">
                    <svg class="animate-spin h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </div>

                <p x-show="!loadingProjectLog && projectLog.length === 0"
                   class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
                    No hay actividad registrada.
                </p>

                <ul x-show="!loadingProjectLog && projectLog.length > 0"
                    class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
                    <template x-for="entry in projectLog" :key="entry.id">
                        <li class="text-sm border-b dark:border-gray-700 pb-3 last:border-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-medium text-gray-800 dark:text-gray-200" x-text="entry.user"></span>
                                <span class="text-xs px-1.5 py-0.5 rounded font-medium"
                                      :class="{
                                        'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300': entry.event === 'created',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300': entry.event === 'updated',
                                        'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300': entry.event === 'deleted',
                                      }"
                                      x-text="{ created: 'Creado', updated: 'Actualizado', deleted: 'Eliminado' }[entry.event]">
                                </span>
                                <span class="text-xs text-gray-400 dark:text-gray-500 ml-auto" x-text="entry.created_at"></span>
                            </div>

                            <template x-if="entry.event === 'updated' && entry.new_values">
                                <ul class="space-y-0.5 mt-1 pl-2">
                                    <template x-for="(newVal, field) in entry.new_values" :key="field">
                                        <li class="text-xs text-gray-600 dark:text-gray-400">
                                            <span class="font-medium"
                                                  x-text="{ name: 'Nombre', description: 'Descripción', deadline: 'Fecha límite', status: 'Estado' }[field] || field">
                                            </span>:
                                            <span class="line-through text-red-500 dark:text-red-400"
                                                  x-text="translateValue(field, entry.old_values[field])"></span>
                                            →
                                            <span class="text-green-600 dark:text-green-400"
                                                  x-text="translateValue(field, newVal)"></span>
                                        </li>
                                    </template>
                                </ul>
                            </template>

                            <template x-if="entry.event === 'created' && entry.new_values">
                                <ul class="space-y-0.5 mt-1 pl-2">
                                    <template x-for="(val, field) in entry.new_values" :key="field">
                                        <li class="text-xs text-gray-600 dark:text-gray-400"
                                            x-show="val !== null && val !== ''">
                                            <span class="font-medium"
                                                  x-text="{ name: 'Nombre', description: 'Descripción', deadline: 'Fecha límite', status: 'Estado' }[field] || field">
                                            </span>:
                                            <span x-text="translateValue(field, val)"></span>
                                        </li>
                                    </template>
                                </ul>
                            </template>
                        </li>
                    </template>
                </ul>
            </div>
        </x-modal>

    </div>{{-- end kanbanBoard scope --}}

    @push('scripts')
    <script>
    const __kanban = {
        projectId: {{ $project->id }},
        projectTags: {!! json_encode($projectTags, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) !!},
    };

    function kanbanBoard() {
        return {
            activeTask: null,
            newTaskStatus: 'backlog',
            newTaskTitle: '',
            newTaskDescription: '',
            newTaskPriority: 'medium',
            aiLoading: false,
            aiError: null,
            aiSubtasks: [],
            projectTags: __kanban.projectTags,
            showTagPanel: false,
            newTagName: '',
            newTagColor: '#6366f1',
            tagColors: ['#6366f1','#ec4899','#f59e0b','#10b981','#3b82f6','#ef4444','#8b5cf6','#14b8a6'],
            csrfToken: document.querySelector('meta[name="csrf-token"]').content,
            projectLog: [],
            loadingProjectLog: false,
            taskLog: [],
            loadingTaskLog: false,

            init() {
                this.initSortable();
            },

            initSortable() {
                document.querySelectorAll('.sortable-list').forEach(el => {
                    Sortable.create(el, {
                        group: 'tasks',
                        animation: 150,
                        ghostClass: 'opacity-40',
                        onEnd: (evt) => {
                            const taskId = evt.item.dataset.id;
                            const newStatus = evt.to.dataset.status;
                            const oldStatus = evt.from.dataset.status;

                            fetch(`/tasks/${taskId}/status`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ status: newStatus }),
                            });

                            const tasks = [...evt.to.querySelectorAll('.task-card')].map((el, i) => ({
                                id: parseInt(el.dataset.id),
                                position: i,
                                status: newStatus,
                            }));

                            fetch('/tasks/reorder', {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ tasks }),
                            });

                            this.updateColumnCount(oldStatus);
                            this.updateColumnCount(newStatus);
                        }
                    });
                });
            },

            updateColumnCount(status) {
                const count = document.getElementById(`column-${status}`)?.querySelectorAll('.task-card').length ?? 0;
                const badge = document.querySelector(`.column-count[data-status="${status}"]`);
                if (badge) badge.textContent = count;
            },

            openTask(taskId) {
                fetch(`/tasks/${taskId}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken }
                })
                .then(r => r.json())
                .then(data => {
                    // Initialize expanded state for subtasks
                    if (data.subtasks) {
                        data.subtasks.forEach(s => { s.expanded = false; });
                    }
                    this.activeTask = data;
                    this.showTagPanel = false;
                    this.newTagName = '';
                    this.$dispatch('open-modal', 'task-detail');
                });
            },

            openNewTaskModal(status) {
                this.newTaskStatus = status;
                this.newTaskTitle = '';
                this.newTaskDescription = '';
                this.newTaskPriority = 'medium';
                this.aiLoading = false;
                this.aiError = null;
                this.aiSubtasks = [];
                this.$dispatch('open-modal', 'new-task');
            },

            closeTask() {
                this.activeTask = null;
            },

            async openProjectLog() {
                this.loadingProjectLog = true;
                this.projectLog = [];
                this.$dispatch('open-modal', 'project-activity');
                try {
                    // __kanban.projectId is defined at line ~121: const __kanban = { projectId: {{ $project->id }}, ... }
                    const res = await fetch(`/projects/${__kanban.projectId}/activity`, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    });
                    this.projectLog = await res.json();
                } finally {
                    this.loadingProjectLog = false;
                }
            },

            async openTaskLog() {
                if (!this.activeTask) return;
                this.loadingTaskLog = true;
                this.taskLog = [];
                this.$dispatch('open-modal', 'task-activity');
                try {
                    const res = await fetch(`/tasks/${this.activeTask.id}/activity`, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    });
                    this.taskLog = await res.json();
                } finally {
                    this.loadingTaskLog = false;
                }
            },

            translateValue(field, value) {
                if (value === null || value === undefined) return '—';
                const enumMap = {
                    active: 'Activo', archived: 'Archivado',
                    backlog: 'Pendiente', in_progress: 'En progreso',
                    testing: 'En revisión', done: 'Terminada',
                    high: 'Alta', medium: 'Media', low: 'Baja',
                };
                return enumMap[value] ?? String(value);
            },

            async toggleSubtask(subtaskId, checkbox) {
                const res = await fetch(`/subtasks/${subtaskId}/toggle`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (!data.success) checkbox.checked = !checkbox.checked;
            },

            async deleteSubtask(subtaskId, el) {
                if (!confirm('¿Eliminar subtarea?')) return;
                const res = await fetch(`/subtasks/${subtaskId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                });
                if ((await res.json()).success) el.remove();
            },

            async addSubtask(form) {
                const fd = new FormData(form);
                const taskId = this.activeTask.id;
                const res = await fetch(`/tasks/${taskId}/subtasks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        title: fd.get('title'),
                        estimated_hours: fd.get('estimated_hours') || null,
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    this.activeTask.subtasks.push(data.subtask);
                    form.reset();
                }
            },

            async suggestSubtasks() {
                if (!this.newTaskTitle.trim()) return;
                this.aiLoading = true;
                this.aiError = null;
                try {
                    const res = await fetch('/ai/decompose-task', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                        },
                        body: JSON.stringify({
                            title: this.newTaskTitle,
                            description: this.newTaskDescription,
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.error || 'Error del servidor');
                    this.newTaskPriority = data.priority;
                    this.aiSubtasks = data.subtasks.map(s => ({
                        title: s.title,
                        estimated_hours: s.estimated_hours ?? '',
                    }));
                } catch (e) {
                    this.aiError = e.message;
                } finally {
                    this.aiLoading = false;
                }
            },

            addAiSubtask() {
                this.aiSubtasks.push({ title: '', estimated_hours: '' });
            },

            removeAiSubtask(index) {
                this.aiSubtasks.splice(index, 1);
            },

            async addChildSubtask(parentSubtask, form) {
                const fd = new FormData(form);
                const res = await fetch(`/subtasks/${parentSubtask.id}/subtasks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        title: fd.get('title'),
                        estimated_hours: fd.get('estimated_hours') || null,
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    if (!parentSubtask.children) parentSubtask.children = [];
                    parentSubtask.children.push(data.subtask);
                    form.reset();
                }
            },

            async toggleTag(tag) {
                if (!this.activeTask) return;
                const taskId = this.activeTask.id;
                const tagId = tag.id;
                const hasTag = this.activeTask.tags && this.activeTask.tags.some(t => t.id === tagId);

                if (hasTag) {
                    await fetch(`/tasks/${taskId}/tags/${tagId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                    });
                    this.activeTask.tags = this.activeTask.tags.filter(t => t.id !== tagId);
                } else {
                    await fetch(`/tasks/${taskId}/tags/${tagId}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                    });
                    if (!this.activeTask.tags) this.activeTask.tags = [];
                    this.activeTask.tags.push(tag);
                }
            },

            async createTag(name, color) {
                if (!name.trim()) return;
                const projectId = __kanban.projectId;
                const res = await fetch(`/projects/${projectId}/tags`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ name: name.trim(), color }),
                });
                const data = await res.json();
                if (data.success) {
                    this.projectTags.push(data.tag);
                    // Auto-assign to current task
                    await this.toggleTag(data.tag);
                    this.newTagName = '';
                }
            },

            async deleteTag(tag) {
                if (!confirm(`¿Eliminar el tag "${tag.name}"?`)) return;
                await fetch(`/tags/${tag.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                });
                this.projectTags = this.projectTags.filter(t => t.id !== tag.id);
                if (this.activeTask && this.activeTask.tags) {
                    this.activeTask.tags = this.activeTask.tags.filter(t => t.id !== tag.id);
                }
            },
        }
    }
    </script>
    @endpush

</x-app-layout>
