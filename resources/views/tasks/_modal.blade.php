{{-- Modal: task detail / edit --}}
<x-modal name="task-detail" maxWidth="2xl">
    <div x-show="activeTask" class="p-6 space-y-5">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <template x-if="activeTask">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="activeTask.title"></h3>
                </template>
            </div>
            <button @click="$dispatch('close'); closeTask()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <template x-if="activeTask">
            <div class="space-y-5">
                {{-- Badges --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-medium px-2 py-1 rounded-full"
                          :class="{
                            'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300': activeTask.priority === 'high',
                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300': activeTask.priority === 'medium',
                            'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300': activeTask.priority === 'low',
                          }"
                          x-text="{ high: 'Alta', medium: 'Media', low: 'Baja' }[activeTask.priority]">
                    </span>
                    <span class="text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded-full"
                          x-text="{ backlog: 'Backlog', in_progress: 'En Progreso', testing: 'Testing', done: 'Terminada' }[activeTask.status]">
                    </span>
                    <template x-if="activeTask.estimated_hours">
                        <span class="text-xs text-gray-500 dark:text-gray-400">⏱ <span x-text="activeTask.estimated_hours"></span>h estimadas</span>
                    </template>
                </div>

                {{-- Description --}}
                <template x-if="activeTask.description">
                    <p class="text-sm text-gray-600 dark:text-gray-400" x-text="activeTask.description"></p>
                </template>

                {{-- Edit form --}}
                <details class="group">
                    <summary class="cursor-pointer text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline list-none flex items-center gap-1">
                        <svg class="w-4 h-4 transition group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        Editar tarea
                    </summary>
                    <template x-if="activeTask">
                        <form :action="`/tasks/${activeTask.id}`" method="POST" class="mt-3 space-y-3 border-t dark:border-gray-700 pt-3">
                            @csrf
                            <input type="hidden" name="_method" value="PUT">

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Título *</label>
                                <input type="text" name="title" required
                                       :value="activeTask.title"
                                       class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción</label>
                                <textarea name="description" rows="2"
                                          class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                          x-text="activeTask.description"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Prioridad</label>
                                    <select name="priority"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        <option value="high" :selected="activeTask.priority === 'high'">Alta</option>
                                        <option value="medium" :selected="activeTask.priority === 'medium'">Media</option>
                                        <option value="low" :selected="activeTask.priority === 'low'">Baja</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                                    <select name="status"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        <option value="backlog" :selected="activeTask.status === 'backlog'">Backlog</option>
                                        <option value="in_progress" :selected="activeTask.status === 'in_progress'">En Progreso</option>
                                        <option value="testing" :selected="activeTask.status === 'testing'">Testing</option>
                                        <option value="done" :selected="activeTask.status === 'done'">Terminada</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Horas estimadas</label>
                                <input type="number" name="estimated_hours" step="0.5" min="0"
                                       :value="activeTask.estimated_hours"
                                       class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                            </div>

                            <div class="flex justify-end gap-2">
                                <button type="submit"
                                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                                    Guardar cambios
                                </button>
                            </div>
                        </form>
                    </template>
                </details>

                {{-- Tags --}}
                <div class="border-t dark:border-gray-700 pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Tags</h4>
                        <button @click="showTagPanel = !showTagPanel"
                                class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                            + Añadir tag
                        </button>
                    </div>

                    {{-- Current tags on task --}}
                    <div class="flex flex-wrap gap-1.5 mb-2">
                        <template x-if="activeTask && activeTask.tags">
                            <template x-for="tag in activeTask.tags" :key="tag.id">
                                <span class="inline-flex items-center gap-1 text-xs text-white rounded-full px-2 py-0.5"
                                      :style="`background-color: ${tag.color}`">
                                    <span x-text="tag.name"></span>
                                    <button @click="toggleTag(tag)" class="hover:opacity-75 leading-none">&times;</button>
                                </span>
                            </template>
                        </template>
                    </div>

                    {{-- Tag panel --}}
                    <div x-show="showTagPanel" x-cloak class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 space-y-3 border border-gray-200 dark:border-gray-700">

                        {{-- Project tags list --}}
                        <div class="space-y-1 max-h-40 overflow-y-auto">
                            <template x-for="tag in projectTags" :key="tag.id">
                                <div class="flex items-center justify-between gap-2 group">
                                    <label class="flex items-center gap-2 cursor-pointer flex-1">
                                        <input type="checkbox"
                                               :checked="activeTask && activeTask.tags && activeTask.tags.some(t => t.id === tag.id)"
                                               @change="toggleTag(tag)"
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                        <span class="w-3 h-3 rounded-full inline-block" :style="`background-color: ${tag.color}`"></span>
                                        <span class="text-sm text-gray-700 dark:text-gray-300" x-text="tag.name"></span>
                                    </label>
                                    <button @click="deleteTag(tag)"
                                            class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 text-xs transition">✕</button>
                                </div>
                            </template>
                            <p x-show="projectTags.length === 0" class="text-xs text-gray-400 dark:text-gray-500">No hay tags en este proyecto.</p>
                        </div>

                        {{-- Create new tag --}}
                        <div class="border-t dark:border-gray-700 pt-2 space-y-2">
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Nuevo tag</p>
                            <div class="flex gap-2">
                                <input type="text" x-model="newTagName" placeholder="Nombre del tag"
                                       class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                            </div>
                            <div class="flex gap-1.5 flex-wrap">
                                <template x-for="color in tagColors" :key="color">
                                    <button type="button"
                                            @click="newTagColor = color"
                                            class="w-5 h-5 rounded-full border-2 transition"
                                            :style="`background-color: ${color}`"
                                            :class="newTagColor === color ? 'border-gray-900 dark:border-white scale-110' : 'border-transparent'">
                                    </button>
                                </template>
                            </div>
                            <button @click="createTag(newTagName, newTagColor)"
                                    :disabled="!newTagName.trim()"
                                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 text-white text-xs font-medium rounded-lg transition">
                                Crear tag
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Subtasks --}}
                <div class="border-t dark:border-gray-700 pt-4">
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">Subtareas</h4>

                    <ul class="space-y-2 mb-3" id="subtask-list">
                        <template x-if="activeTask && activeTask.subtasks">
                            <template x-for="subtask in activeTask.subtasks" :key="subtask.id">
                                <li :id="`subtask-${subtask.id}`">
                                    {{-- Level 1 subtask --}}
                                    <div class="flex items-center gap-2 group">
                                        <input type="checkbox"
                                               :checked="subtask.is_completed"
                                               @change="toggleSubtask(subtask.id, $event.target); subtask.is_completed = !subtask.is_completed"
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" />
                                        <span class="flex-1 text-sm text-gray-700 dark:text-gray-300"
                                              :class="subtask.is_completed ? 'line-through text-gray-400 dark:text-gray-500' : ''"
                                              x-text="subtask.title"></span>
                                        <template x-if="subtask.estimated_hours">
                                            <span class="text-xs text-gray-400 dark:text-gray-500" x-text="`${subtask.estimated_hours}h`"></span>
                                        </template>
                                        {{-- Expand children toggle --}}
                                        <button @click="subtask.expanded = !subtask.expanded"
                                                class="text-xs text-gray-400 hover:text-indigo-500 transition px-1"
                                                x-text="subtask.expanded ? '▾' : '▸'">
                                        </button>
                                        <button @click="deleteSubtask(subtask.id, $el.closest('li'))"
                                                class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition p-0.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Children (level 2) --}}
                                    <div x-show="subtask.expanded" x-cloak class="pl-6 mt-1 space-y-1">
                                        <template x-for="child in (subtask.children || [])" :key="child.id">
                                            <div class="flex items-center gap-2 group" :id="`subtask-${child.id}`">
                                                <input type="checkbox"
                                                       :checked="child.is_completed"
                                                       @change="toggleSubtask(child.id, $event.target); child.is_completed = !child.is_completed"
                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" />
                                                <span class="flex-1 text-xs text-gray-600 dark:text-gray-400"
                                                      :class="child.is_completed ? 'line-through text-gray-400 dark:text-gray-500' : ''"
                                                      x-text="child.title"></span>
                                                <template x-if="child.estimated_hours">
                                                    <span class="text-xs text-gray-400 dark:text-gray-500" x-text="`${child.estimated_hours}h`"></span>
                                                </template>
                                                <button @click="deleteSubtask(child.id, $el.closest('div[id]'))"
                                                        class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition p-0.5">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>

                                        {{-- Add child subtask form --}}
                                        <form @submit.prevent="addChildSubtask(subtask, $el)" class="flex gap-1.5 mt-1">
                                            <input type="text" name="title" required placeholder="Sub-subtarea..."
                                                   class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs" />
                                            <input type="number" name="estimated_hours" step="0.5" min="0" placeholder="h"
                                                   class="w-14 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs" />
                                            <button type="submit"
                                                    class="px-2 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition">
                                                +
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            </template>
                        </template>
                    </ul>

                    {{-- Add subtask form --}}
                    <form @submit.prevent="addSubtask($el)" class="flex gap-2">
                        <template x-if="activeTask">
                            <input type="hidden" name="task_id" :value="activeTask.id" />
                        </template>
                        <input type="text" name="title" required placeholder="Nueva subtarea..."
                               class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                        <input type="number" name="estimated_hours" step="0.5" min="0" placeholder="horas"
                               class="w-20 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                        <button type="submit"
                                class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                            +
                        </button>
                    </form>
                </div>

                {{-- Delete task --}}
                <div class="border-t dark:border-gray-700 pt-3 flex justify-end">
                    <template x-if="activeTask">
                        <form :action="`/tasks/${activeTask.id}`" method="POST"
                              onsubmit="return confirm('¿Eliminar esta tarea?')">
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                Eliminar tarea
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </template>
    </div>
</x-modal>

{{-- Modal: new task --}}
<x-modal name="new-task" focusable>
    <form method="POST" action="{{ route('tasks.store', $project) }}" class="p-6 space-y-4">
        @csrf
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nueva tarea</h3>

        <input type="hidden" name="status" :value="newTaskStatus" />

        <div>
            <x-input-label for="task_title" value="Título *" />
            <x-text-input id="task_title" name="title" type="text" class="mt-1 block w-full" required autofocus
                          placeholder="Título de la tarea" x-model="newTaskTitle" />
        </div>

        <div>
            <x-input-label for="task_desc" value="Descripción" />
            <textarea id="task_desc" name="description" rows="2"
                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                      placeholder="Descripción opcional"
                      x-model="newTaskDescription"></textarea>
        </div>

        {{-- Botón IA --}}
        <div>
            <button type="button"
                    @click="suggestSubtasks()"
                    :disabled="!newTaskTitle.trim() || aiLoading"
                    class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 disabled:opacity-40 disabled:cursor-not-allowed">
                <template x-if="!aiLoading">
                    <span>✨ Sugerir subtareas con IA</span>
                </template>
                <template x-if="aiLoading">
                    <span class="flex items-center gap-1.5">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        Analizando...
                    </span>
                </template>
            </button>
            <p x-show="aiError" x-text="aiError" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <x-input-label for="task_priority" value="Prioridad" />
                <select id="task_priority" name="priority"
                        x-model="newTaskPriority"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="high">Alta</option>
                    <option value="medium">Media</option>
                    <option value="low">Baja</option>
                </select>
            </div>
            <div>
                <x-input-label for="task_hours" value="Horas estimadas" />
                <x-text-input id="task_hours" name="estimated_hours" type="number" step="0.5" min="0"
                              class="mt-1 block w-full" placeholder="0" />
            </div>
        </div>

        {{-- Subtareas sugeridas por IA --}}
        <div x-show="aiSubtasks.length > 0" class="space-y-2">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Subtareas sugeridas</p>
            <template x-for="(subtask, index) in aiSubtasks" :key="index">
                <div class="flex items-center gap-2">
                    <input type="text"
                           :name="`subtasks[${index}][title]`"
                           x-model="subtask.title"
                           placeholder="Título de la subtarea"
                           class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                    <input type="number"
                           :name="`subtasks[${index}][estimated_hours]`"
                           x-model="subtask.estimated_hours"
                           step="0.5" min="0" placeholder="h"
                           class="w-16 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                    <button type="button" @click="removeAiSubtask(index)"
                            class="text-gray-400 hover:text-red-500 text-lg leading-none">&times;</button>
                </div>
            </template>
            <button type="button" @click="addAiSubtask()"
                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                + Añadir subtarea
            </button>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
            <x-primary-button>Crear tarea</x-primary-button>
        </div>
    </form>
</x-modal>
