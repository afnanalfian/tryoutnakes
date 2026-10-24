@props(['category'])
<span {{ $attributes->merge(['class' => 'badge']) }} style="background-color: {{ $category->color }}1a; color: {{ $category->color }}">
    <x-icon :name="$category->icon" class="h-3.5 w-3.5" stroke="2" /> {{ $category->label() }}
</span>
