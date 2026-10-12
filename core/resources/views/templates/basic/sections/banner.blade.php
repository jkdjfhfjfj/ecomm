@php
    $content = getContent('banner.content', true);
@endphp

<div class="container banner-container @if (gs('homepage_layout') == 'full_width_banner') single-banner @endif">
    <div class="store-discovery" aria-label="@lang('Store discovery')">
        <div class="store-discovery-copy">
            <span class="store-discovery-kicker">@lang('Visermart')</span>
            <strong>@lang('Find something worth keeping.')</strong>
        </div>
        <form action="{{ route('product.all') }}" method="GET" class="store-discovery-search" role="search">
            <label class="visually-hidden" for="store-discovery-query">@lang('Search products')</label>
            <i class="las la-search" aria-hidden="true"></i>
            <input id="store-discovery-query" type="search" name="search" value="{{ request()->search }}" placeholder="@lang('Search fashion, accessories and more')" autocomplete="off">
            <button type="submit">@lang('Search')</button>
        </form>
        <a class="store-discovery-browse" href="{{ route('categories') }}">@lang('Browse all categories') <span aria-hidden="true">→</span></a>
    </div>
    <div class="hero-section mb-60">
        @if (gs('homepage_layout') == 'sidebar_menu')
            @include('Template::partials.left_category_menu', ['limit' => 13])
        @endif

        <div class="hero-slider">
            @include('Template::partials.banner_sliders')
            @include('Template::partials.banner_categories', ['fixedBanner' => $content->data_values?->fixed_banner])
        </div>

        @if (gs('homepage_layout') == 'sidebar_menu' && @$content->data_values->fixed_banner)
            <div class="hero-banner">
                <a href="{{ @$content->data_values->fixed_banner_link }}" class="d-inline"><img class="w-100 h-100" src="{{ frontendImage('banner', @$content->data_values->fixed_banner, '300x510') }}" alt=""></a>
            </div>
        @endif
    </div>
</div>

<style>
    .store-discovery { display:grid; grid-template-columns:minmax(190px,.85fr) minmax(300px,1.5fr) auto; align-items:center; gap:20px; max-width:1120px; padding:17px 20px; margin:20px auto 0; border:1px solid var(--store-line,#e4dfd5); background:#fffefa; }
    .store-discovery-copy { display:grid; gap:3px; }
    .store-discovery-kicker { color:var(--store-accent,#9b493d); font-size:.63rem; font-weight:700; letter-spacing:.15em; text-transform:uppercase; }
    .store-discovery-copy strong { color:var(--store-ink,#252521); font-family:var(--store-display,Georgia,serif); font-size:1.13rem; font-weight:500; }
    .store-discovery-search { display:flex; align-items:center; min-width:0; height:46px; border:1px solid #ded8cf; background:var(--store-paper,#f8f6f1); }
    .store-discovery-search>i { flex:0 0 42px; color:var(--store-accent,#9b493d); text-align:center; font-size:1.1rem; }
    .store-discovery-search input { width:100%; min-width:0; height:100%; padding:0 8px 0 0; border:0; outline:0; background:transparent; color:var(--store-ink,#252521); font-size:.88rem; }
    .store-discovery-search input::placeholder { color:#8c857b; opacity:1; }
    .store-discovery-search button { flex:0 0 auto; height:34px; padding:0 15px; margin:0 5px; border:0; background:var(--store-ink,#252521); color:#fff; font-size:.78rem; font-weight:650; }
    .store-discovery-browse { color:var(--store-ink,#252521); font-size:.8rem; font-weight:650; text-decoration:none; white-space:nowrap; }
    .store-discovery-browse span { margin-left:4px; color:var(--store-accent,#9b493d); }
    .hero-section { align-items:stretch; margin-top:16px; margin-bottom:34px!important; }
    .hero-section .hero-slider { min-width:0; gap:12px; }
    .hero-section .hero-banner { height:374px; border-radius:2px; }
    .hero-section .hero-banner img { object-fit:cover; }
    .banner-slider.owl-carousel .owl-item img { border-radius:2px; }
    .banner-category-wrap { padding:0 1px 3px; }
    .featured-category-slider { display:flex; }
    .featured-category-slider .owl-stage { display:flex; }
    .featured-category-slider .owl-item { height:auto; }
    .banner-categories { height:100%; padding:0; margin:0; border:0; border-radius:0!important; background:transparent; text-align:left; }
    .banner-category-card { display:flex; align-items:center; gap:11px; height:68px; padding:8px 11px; border:1px solid var(--store-line,#e4dfd5); background:#fffefa; color:var(--store-ink,#252521); text-align:left; text-decoration:none; }
    .banner-category-card:hover { border-color:var(--store-accent,#9b493d); color:var(--store-accent,#9b493d); }
    .banner-category-image { display:grid; flex:0 0 48px; width:48px; height:48px; place-items:center; overflow:hidden; background:#f0ece4; }
    .banner-category-card img { display:block; width:100%; height:100%; max-width:none; padding:0; margin:0; object-fit:cover; }
    .banner-category-title { display:-webkit-box; overflow:hidden; font-size:.78rem; font-weight:650; line-height:1.3; -webkit-box-orient:vertical; -webkit-line-clamp:2; }
    @media(max-width:991px) {
        .store-discovery { grid-template-columns:1fr auto; gap:10px 14px; padding:13px; margin-top:12px; }
        .store-discovery-copy { grid-column:1/-1; }
        .store-discovery-search { grid-column:1; }
        .store-discovery-browse { grid-column:2; }
    }
    @media(max-width:575px) {
        .store-discovery { grid-template-columns:1fr; gap:9px; padding:11px; margin-top:10px; }
        .store-discovery-copy { grid-column:auto; }
        .store-discovery-copy strong { font-size:1rem; }
        .store-discovery-search { grid-column:auto; height:43px; }
        .store-discovery-search>i { flex-basis:35px; }
        .store-discovery-search input { font-size:.78rem; }
        .store-discovery-search button { height:33px; padding:0 11px; font-size:.72rem; }
        .store-discovery-browse { grid-column:auto; justify-self:start; padding-top:1px; }
        .hero-section { margin-top:12px; margin-bottom:22px!important; }
        .hero-section .slider-wrapper { height:clamp(168px,53vw,260px); }
        .hero-section .hero-slider { gap:9px; }
        .banner-category-card { height:58px; gap:7px; padding:6px; }
        .banner-category-image { flex-basis:38px; width:38px; height:38px; }
        .banner-category-title { font-size:.68rem; }
    }
</style>

@push('style-lib')
   <link rel="stylesheet" href="{{ asset(activeTemplate(true). 'css/banner.css') }}">
@endpush
