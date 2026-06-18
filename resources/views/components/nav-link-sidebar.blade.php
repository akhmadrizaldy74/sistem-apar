@props(['active'])

@php
$classes = ($active ?? false)
            ? 'group flex items-center gap-3 px-4 py-3.5 text-sm font-black tracking-[0.16em] bg-red-600 text-white rounded-xl shadow-lg shadow-red-600/30 transition-all duration-300'
            : 'group flex items-center gap-3 px-4 py-3.5 text-sm font-bold tracking-[0.14em] text-slate-400 hover:text-white hover:bg-slate-700/60 rounded-xl transition-all duration-300';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
