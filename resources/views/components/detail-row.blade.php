@props(['label'])
<div class="detail-card__row">
    <div class="detail-card__label">{{ $label }}</div>
    <div class="detail-card__value">{{ $slot }}</div>
</div>
