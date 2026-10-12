@extends('Template::layouts.master')
@section('content')
    @include('Template::sections.banner')
    @if ($homepageFlashOffers->isNotEmpty())
        @foreach ($homepageFlashOffers as $flashOffer)
            @include('Template::sections.offer', ['data' => $flashOffer])
        @endforeach
    @else
        @include('Template::sections.homepage_offer_banners', ['offerPlacement' => 'flash'])
    @endif
    @include('Template::sections.home_category_carousels')
    @include('Template::sections.homepage_offer_banners', ['offerPlacement' => 'other'])
    @include('Template::sections.catalog_products')
    <div id="daily-offer-trigger" aria-hidden="true" style="height:1px"></div>
    @include('Template::sections.daily_offer')
    @include('Template::sections.recent_viewed')

    @if ($sections->secs != null)
        @foreach (collect(json_decode($sections->secs) ?: []) as $sec)
            @php
                $cmsSectionData = is_string($sec) ? getSectionData($sec, false, true) : null;
            @endphp
            @if (in_array($sec, ['banner', 'homepage_offer_banners', 'catalog_products', 'home_category_carousels', 'recent_viewed', 'daily_offer'], true))
                @continue
            @elseif ($cmsSectionData && (($cmsSectionData['data']->campaign_type ?? null) === 'flash' || str_contains(strtolower((string) ($cmsSectionData['data']->name ?? '')), 'flash sale')))
                @continue
            @elseif (View::exists('Template::sections.' . $sec))
                @include('Template::sections.' . $sec)
            @else
                @if ($cmsSectionData && array_key_exists('section', $cmsSectionData))
                    @include($cmsSectionData['section'], ['data' => @$cmsSectionData['data']])
                @endif
            @endif
        @endforeach
    @endif

@endsection

@push('style-lib')
    <link href="{{ asset(activeTemplate(true) . 'css/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset(activeTemplate(true) . 'css/product-details.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/xzoom/xzoom.css') }}">
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/xzoom/magnific-popup.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset(activeTemplate(true) . 'js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/owl-carousel.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/xzoom/xzoom.min.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/xzoom/magnific-popup.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/xzoom/setup.js') }}"></script>
@endpush
