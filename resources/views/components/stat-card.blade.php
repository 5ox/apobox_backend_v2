@props(['value', 'label', 'icon' => null])
<div class="card card-hover">
    <div class="stat-card">
        @if($icon)
            <div class="stat-card__icon">
                <i data-lucide="{{ $icon }}" class="icon--lg"></i>
            </div>
        @endif
        <div class="stat-card__number">{{ $value }}</div>
        <div class="stat-card__label">{{ $label }}</div>
    </div>
</div>
