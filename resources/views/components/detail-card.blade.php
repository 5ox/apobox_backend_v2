@props(['title', 'action' => null, 'actionUrl' => null])
<div {{ $attributes->merge(['class' => 'detail-card']) }}>
    <div class="detail-card__header">
        <h5>{{ $title }}</h5>
        @if($action)
            <a href="{{ $actionUrl }}" class="btn btn-sm btn-outline-primary">{{ $action }}</a>
        @endif
    </div>
    <div class="detail-card__body">
        {{ $slot }}
    </div>
</div>
