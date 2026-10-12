@php
    $featuredCategories = \App\Models\Category::where('feature_in_banner', 1)
        ->orderBy('position')
        ->get()
        ->unique('slug')
        ->reject(fn ($category) => strtolower(trim((string) $category->slug)) === 'daily-offers'
            || strtolower(trim((string) $category->name)) === 'daily offers')
        ->filter(fn ($category) => filter_var($category->external_image_url, FILTER_VALIDATE_URL) || filled($category->image))
        ->values();

    if (gs('homepage_layout') == 'sidebar_menu') {
        $categoriesToShow = [
            768 => ['items' => 5],
            992 => ['items' => 5],
            1199 => ['items' => 5],
        ];
        if (!$fixedBanner) {
            $categoriesToShow[1399] = ['items' => 6];
        }
    } else {
        $categoriesToShow = [
            768 => ['items' => 6],
            992 => ['items' => 7],
            1199 => ['items' => 9],
        ];
    }
@endphp

@if (!blank($featuredCategories))
    <div class="overflow-hidden banner-category-wrap">
        <div class="featured-category-slider owl-theme owl-carousel">
            @foreach ($featuredCategories as $category)
                <div class="banner-categories">
                    <a class="banner-category-card" href="{{ $category->shopLink() }}" aria-label="@lang('Shop :category', ['category' => __($category->name)])">
                        <span class="banner-category-image">
                            <img src="{{ $category->categoryImage() }}" alt="{{ __($category->name) }}" loading="eager" decoding="async" onerror="const card=this.closest('.banner-categories');const slide=this.closest('.owl-item');if(slide){slide.remove();if(window.jQuery)window.jQuery('.featured-category-slider').trigger('refresh.owl.carousel')}else if(card){card.remove()}">
                        </span>
                        <span class="banner-category-title">{{ __($category->name) }}</span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    @push('script')
        <script>
            (function($) {
                "use strict";
                const categoriesToShow = @json($categoriesToShow);

                const viewItems = {
                    0: { items: 2, margin: 8 },
                    425: {
                        items: 3,
                        margin: 10,
                    },
                    575: {
                        items: 4,
                        margin: 12,
                    },
                    ...categoriesToShow
                };



                $(".featured-category-slider").owlCarousel({
                    margin: 16,
                    responsiveClass: true,
                    items: 3,
                    nav: false,
                    dots: false,
                    autoplay: true,
                    autoplayTimeout: 4000,
                    loop: {{ $featuredCategories->count() > 4 ? 'true' : 'false' }},
                    lazyLoad: false,
                    responsive: viewItems,
                });
            })(jQuery);
        </script>
    @endpush

@endif
