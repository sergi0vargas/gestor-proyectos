@props(['priority'])

@php
$classes = match($priority) {
    'high'   => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    'medium' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
    'low'    => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    default  => 'bg-gray-100 text-gray-700',
};
$label = match($priority) {
    'high'   => 'Alta',
    'medium' => 'Media',
    'low'    => 'Baja',
    default  => $priority,
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded text-xs font-medium $classes"]) }}>
    {{ $label }}
</span>
