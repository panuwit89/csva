@props(['href' => null, 'color' => 'gray'])

@php
    $colorClasses = [
        'gray'    => 'bg-gray-800 hover:bg-gray-700 active:bg-gray-900 focus:border-gray-900 focus:ring-gray-300',
        'red'     => 'bg-red-700 hover:bg-red-600 active:bg-red-800 focus:border-red-800 focus:ring-red-300',
        'blue'    => 'bg-blue-500 hover:bg-blue-600 active:bg-blue-700 focus:border-blue-700 focus:ring-blue-300',
        'green'   => 'bg-green-500 hover:bg-green-600 active:bg-green-700 focus:border-green-700 focus:ring-green-300',
        'indigo'  => 'bg-indigo-500 hover:bg-indigo-600 active:bg-indigo-700 focus:border-indigo-700 focus:ring-indigo-300',
    ];

    $baseClasses = 'inline-flex items-center px-4 py-2 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring disabled:opacity-25 transition ease-in-out duration-150';

    $classes = $baseClasses . ' ' . ($colorClasses[$color] ?? $colorClasses['gray']);
@endphp

@if ($href)
    <a {{ $attributes->merge(['href' => $href, 'class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
