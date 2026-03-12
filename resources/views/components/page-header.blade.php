@props(['title', 'subtitle' => null])
<div {{ $attributes->merge(['class' => 'page-header d-flex flex-wrap justify-content-between align-items-center gap-2']) }}>
    <div>
        <h2 class="mb-0">{{ $title }}</h2>
        @if($subtitle)
            <p class="page-header__subtitle mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div>{{ $actions }}</div>
    @endif
</div>
