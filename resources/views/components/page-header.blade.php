@props(['title', 'subtitle' => null])
<div {{ $attributes->merge(['class' => 'page-header']) }}>
    <h2>{{ $title }}</h2>
    @if($subtitle)
        <p class="page-header__subtitle mb-0">{{ $subtitle }}</p>
    @endif
    @if(isset($actions))
        <div class="mt-2">{{ $actions }}</div>
    @endif
</div>
