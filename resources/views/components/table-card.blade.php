@props(['title' => null, 'action' => null, 'actionUrl' => null])
<div {{ $attributes->merge(['class' => 'table-card']) }}>
    @if($title)
        <div class="table-card__header">
            <h5>{{ $title }}</h5>
            @if($action)
                <a href="{{ $actionUrl }}" class="btn btn-sm btn-outline-primary">{{ $action }}</a>
            @endif
        </div>
    @endif
    {{ $slot }}
    @if(isset($footer))
        <div class="table-card__footer">
            {{ $footer }}
        </div>
    @endif
</div>
