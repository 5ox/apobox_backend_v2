@props(['title' => null])
<div {{ $attributes->merge(['class' => 'form-section']) }}>
    @if($title)
        <h5 class="form-section__title">{{ $title }}</h5>
    @endif
    {{ $slot }}
</div>
