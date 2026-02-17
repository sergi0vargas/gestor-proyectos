<div class="task-card bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 p-3 cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow select-none"
     data-id="{{ $task->id }}"
     data-status="{{ $task->status }}"
     @click="openTask({{ $task->id }})">

    <div class="flex items-start justify-between gap-2 mb-2">
        <p class="text-sm font-medium text-gray-800 dark:text-gray-100 line-clamp-2 flex-1">{{ $task->title }}</p>
        <x-priority-badge :priority="$task->priority" class="shrink-0 mt-0.5" />
    </div>

    @if($task->description)
        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-2">{{ $task->description }}</p>
    @endif

    <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-100 dark:border-gray-600">
        @if($task->estimated_hours)
            <span class="text-xs text-gray-400 dark:text-gray-500">⏱ {{ $task->estimated_hours }}h</span>
        @else
            <span></span>
        @endif

        @php $subtaskCount = $task->subtasks->count(); $doneCount = $task->subtasks->where('is_completed', true)->count(); @endphp
        @if($subtaskCount > 0)
            <span class="text-xs text-gray-400 dark:text-gray-500">✓ {{ $doneCount }}/{{ $subtaskCount }}</span>
        @endif
    </div>
</div>
