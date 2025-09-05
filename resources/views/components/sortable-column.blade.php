@props(['field', 'label'])

@php
    $currentSort = request('sort');
    $currentDirection = request('direction', 'asc');
    $isActive = $currentSort === $field;
    $newDirection = ($isActive && $currentDirection === 'asc') ? 'desc' : 'asc';
@endphp

<a href="{{ route(Route::currentRouteName(), array_merge(request()->all(), ['sort' => $field, 'direction' => $newDirection])) }}" 
   class="text-decoration-none text-white">
    {{ $label }}
    @if($isActive)
        @if($currentDirection === 'asc')
            <i class="ri-arrow-up-s-line text-warning"></i>
        @else
            <i class="ri-arrow-down-s-line text-warning"></i>
        @endif
    @else
        <i class="ri-arrow-up-down-line text-white"></i>
    @endif
</a>
