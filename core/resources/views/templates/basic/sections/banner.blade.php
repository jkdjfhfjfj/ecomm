@php
    $content = getContent('banner.content', true);
@endphp

<div class="container banner-container @if (gs('homepage_layout') == 'full_width_banner') single-banner @endif">
    <div class="hero-section mb-60" id="homepage-hero">
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
    .hero-section { align-items:stretch; margin-top:0; margin-bottom:34px!important; }
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
    @media(max-width:575px) {
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
