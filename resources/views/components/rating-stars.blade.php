@props([
    'rating' => null,
    'max' => 5,
    'sizeClass' => 'text-sm',
    'activeClass' => 'text-amber-400',
    'inactiveClass' => 'text-slate-300',
    'emptyText' => 'Belum ada rating',
    'emptyClass' => 'text-xs font-semibold text-slate-400',
])

@php
    $normalizedRating = is_numeric($rating) ? max(0, min((int) $rating, (int) $max)) : null;
@endphp

@if(is_null($normalizedRating))
    <span class="{{ $emptyClass }}">{{ $emptyText }}</span>
@else
    <span class="inline-flex items-center gap-0.5 {{ $sizeClass }}" aria-label="Rating {{ $normalizedRating }} dari {{ $max }}">
        @for($star = 1; $star <= $max; $star++)
            <span class="{{ $star <= $normalizedRating ? $activeClass : $inactiveClass }}">&#9733;</span>
        @endfor
    </span>
@endif
