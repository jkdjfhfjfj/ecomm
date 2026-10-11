<a href="{{ $category->shopLink() }}" class="vm-category-card" aria-label="@lang('Shop') {{ __($category->name) }}">
    <span class="vm-category-card__image">
        <img src="{{ $category->categoryImage() }}" alt="{{ __($category->name) }}" width="360" height="280" loading="lazy" decoding="async" onerror="this.hidden=true">
    </span>
    <span class="vm-category-card__title line-limitation-1">{{ __($category->name) }}</span>
</a>

@once
    @push('style')
        <style>
            .vm-category-card{display:flex!important;flex-direction:column;gap:.85rem;height:100%;padding:.7rem;border:1px solid #e8eaf0;border-radius:1rem;background:#fff;color:#182230;text-align:left;transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease}
            .vm-category-card:hover,.vm-category-card:focus-visible{transform:translateY(-3px);border-color:#cbd5e1;box-shadow:0 12px 28px #15233b14;color:#182230}
            .vm-category-card__image{display:block;overflow:hidden;aspect-ratio:1.28;border-radius:.72rem;background:linear-gradient(135deg,#f3f5f7,#e8edf2)}
            .vm-category-card__image img{display:block;width:100%;height:100%;object-fit:cover;transition:transform .35s ease}
            .vm-category-card:hover .vm-category-card__image img{transform:scale(1.04)}
            .vm-category-card__title{font-size:.96rem;font-weight:650;line-height:1.35;padding:0 .2rem .15rem}
            @media(prefers-reduced-motion:reduce){.vm-category-card,.vm-category-card__image img{transition:none}}
        </style>
    @endpush
@endonce
