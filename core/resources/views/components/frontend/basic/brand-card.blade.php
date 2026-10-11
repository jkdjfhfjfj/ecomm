@php
    $brandImageUrl = $brand->external_image_url ?: ($brand->logo ? $brand->logo() : null);
@endphp
<a href="{{ $brand->shopLink() }}" class="source-brand-card">
    @if ($brandImageUrl)
        <img src="{{ $brandImageUrl }}" class="w-100" alt="{{ $brand->name }} representative product" loading="lazy" onerror="this.remove()">
    @else
        <span class="source-brand-card__monogram" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($brand->name, 0, 1)) }}</span>
    @endif
    <span class="source-brand-card__name">{{ $brand->name }}</span>
</a>
