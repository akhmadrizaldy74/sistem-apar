@props(['crumbs' => []])

@php
    $items = $crumbs;
@endphp

@if(count($items) > 0)
<nav class="flex items-center gap-2 mt-3 text-sm">
    @foreach($items as $index => $item)
        @if($index > 0)
            <svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        @endif
        @if(!empty($item['url']))
            <a href="{{ $item['url'] }}" class="text-slate-400 font-medium hover:text-red-600 transition capitalize">{{ $item['label'] }}</a>
        @else
            <span class="text-slate-700 font-bold capitalize">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
@endif