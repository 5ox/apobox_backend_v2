@props(['label', 'editUrl' => null])
<div {{ $attributes->merge(['class' => 'address-card']) }}>
    <div class="address-card__label">
        {{ $label }}
        @if($editUrl)
            <a href="{{ $editUrl }}" class="small text-decoration-none">Edit</a>
        @endif
    </div>
    @if($slot->isNotEmpty())
        <address class="mb-0">{{ $slot }}</address>
    @else
        <p class="text-muted mb-0 small">Not set</p>
    @endif
</div>
