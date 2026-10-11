@if (isset($homeCategoryRows) && $homeCategoryRows->isNotEmpty())
    @php
        $homeShopRows = $homeCategoryRows
            ->unique(fn ($row) => $row['category']->id)
            ->reject(function ($row) {
                $category = $row['category'];
                $slug = strtolower((string) $category->slug);
                $name = strtolower(trim((string) $category->name));
                return str_contains($slug, 'daily-offer') || in_array($name, ['daily offer', 'daily offers'], true);
            })
            ->values();
        $homeFeaturedRows = $homeShopRows
            ->filter(fn ($row) => filter_var($row['category']->external_image_url, FILTER_VALIDATE_URL) || filled($row['category']->image))
            ->values();
    @endphp
    @if ($homeShopRows->isNotEmpty())
    <section class="home-catalog">
        <div class="container">
            <div class="home-category-intro">
                <div>
                    <span class="home-shelf-eyebrow">@lang('Shop the marketplace')</span>
                    <h2>@lang('Your next find starts here')</h2>
                </div>
                <p>@lang('Explore real collections chosen for the Visermart home page.') </p>
            </div>
            @if ($homeFeaturedRows->isNotEmpty())
                <div class="home-category-feature-grid" aria-label="@lang('Featured categories')">
                    @foreach ($homeFeaturedRows->take(4) as $row)
                        @php($category = $row['category'])
                        <a class="home-category-feature" href="{{ route('product.by.category', $category->slug) }}">
                            <img src="{{ $category->categoryImage() }}" alt="{{ __($category->name) }}" loading="eager" decoding="async" onerror="this.closest('.home-category-feature').remove()">
                            <span class="home-category-feature-label">
                                <span>{{ __($category->name) }}</span>
                                <span aria-hidden="true">↗</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
            @if ($homeShopRows->isNotEmpty())
                <nav class="home-category-links" aria-label="@lang('Shop by category')">
                    @foreach ($homeShopRows as $row)
                        <a href="{{ route('product.by.category', $row['category']->slug) }}">{{ $row['category']->name }}</a>
                    @endforeach
                </nav>
            @endif

            @foreach ($homeShopRows as $row)
                @if (!empty($row['products']) && count($row['products']))
                    @php($carouselId = 'home-products-' . $row['category']->id)
                    <section class="home-product-shelf" aria-labelledby="{{ $carouselId }}-title">
                        <div class="home-shelf-heading">
                            <div>
                                <span class="home-shelf-eyebrow">@lang('Explore the collection')</span>
                                <h2 id="{{ $carouselId }}-title">{{ $row['category']->name }}</h2>
                            </div>
                            <div class="home-shelf-actions">
                                <a class="home-view-all" href="{{ route('product.by.category', $row['category']->slug) }}">@lang('Shop all') <span aria-hidden="true">→</span></a>
                                <button type="button" class="home-carousel-control" data-carousel-target="{{ $carouselId }}" data-direction="-1" aria-label="@lang('Scroll products left')">‹</button>
                                <button type="button" class="home-carousel-control" data-carousel-target="{{ $carouselId }}" data-direction="1" aria-label="@lang('Scroll products right')">›</button>
                            </div>
                        </div>
                        <div class="home-product-track" id="{{ $carouselId }}" tabindex="0" aria-label="{{ $row['category']->name }} @lang('products')">
                            @foreach ($row['products'] as $product)
                                <article class="home-product-slide">
                                    <div class="catalog-product-tile">
                                        @if ($product->brand)
                                            <span class="catalog-brand-tag">{{ __($product->brand->name) }}</span>
                                        @endif
                                        <x-dynamic-component :component="frontendComponent('product-card')" :product="$product" />
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        </div>
    </section>

    <style>
        .home-catalog { padding:clamp(30px,5vw,64px) 0 20px; background:#f1ede5; overflow:hidden; }
        .home-category-intro { display:flex; align-items:end; justify-content:space-between; gap:2rem; padding:0 0 24px; }
        .home-category-intro h2 { margin:.3rem 0 0; color:var(--store-ink,#252521); font-family:var(--store-display,Georgia,serif); font-size:clamp(2rem,4vw,3.35rem); font-weight:500; letter-spacing:-.045em; line-height:1.08; }
        .home-category-intro p { max-width:290px; margin:0 0 .3rem; color:var(--store-muted,#746f67); font-size:.9rem; line-height:1.55; }
        .home-category-feature-grid { display:flex; flex-wrap:wrap; gap:12px; margin-bottom:22px; }
        .home-category-feature { position:relative; flex:1 1 calc(20% - 9px); min-width:0; min-height:176px; overflow:hidden; background:#ded8cf; color:#fff; text-decoration:none; }
        .home-category-feature:first-child { flex:2 1 calc(40% - 9px); min-height:248px; }
        .home-category-feature:after { position:absolute; inset:35% 0 0; content:""; pointer-events:none; background:linear-gradient(transparent,rgba(25,24,21,.7)); }
        .home-category-feature img { position:absolute; inset:0; display:block; width:100%; height:100%; object-fit:cover; transition:transform .45s ease; }
        .home-category-feature:hover img { transform:scale(1.035); }
        .home-category-feature-label { position:absolute; z-index:1; right:15px; bottom:13px; left:15px; display:flex; align-items:center; justify-content:space-between; gap:.5rem; color:#fff; font-family:var(--store-display,Georgia,serif); font-size:clamp(1rem,1.6vw,1.4rem); line-height:1.2; }
        .home-category-feature-label span:first-child { max-width:90%; }
        .home-category-links { display:flex; gap:8px; overflow:auto; padding:0 0 26px; scrollbar-width:thin; }
        .home-category-links a { flex:0 0 auto; padding:9px 13px; border:1px solid #dcd5ca; background:#faf8f3; color:#39362f; font-size:.79rem; font-weight:600; text-decoration:none; }
        .home-category-links a:hover { border-color:var(--store-accent,#9b493d); color:var(--store-accent,#9b493d); }
        .home-product-shelf { padding:23px 0 0; margin:0 0 29px; border-top:1px solid #ded8cf; }
        .home-shelf-heading { display:flex; align-items:end; justify-content:space-between; gap:1rem; margin-bottom:14px; }
        .home-shelf-heading h2 { margin:.25rem 0 0; color:var(--store-ink,#252521); font-family:var(--store-display,Georgia,serif); font-size:clamp(1.5rem,2.5vw,2.15rem); font-weight:500; letter-spacing:-.025em; }
        .home-shelf-actions { display:flex; align-items:center; gap:7px; }
        .home-view-all { margin-right:.4rem; color:var(--store-ink,#252521); font-size:.82rem; font-weight:650; text-decoration:none; white-space:nowrap; }
        .home-view-all:hover { color:var(--store-accent,#9b493d); }
        .home-carousel-control { display:grid; width:37px; height:37px; place-items:center; border:1px solid #d6cec2; border-radius:50%; background:#faf8f3; color:var(--store-ink,#252521); font-size:1.35rem; line-height:1; }
        .home-carousel-control:hover { border-color:var(--store-ink,#252521); background:var(--store-ink,#252521); color:#fff; }
        .home-product-track { display:grid; grid-auto-columns:minmax(195px,22%); grid-auto-flow:column; gap:13px; overflow-x:auto; overscroll-behavior-inline:contain; scroll-snap-type:x mandatory; scroll-behavior:smooth; padding:2px 1px 13px; scrollbar-color:#c8beb0 transparent; scrollbar-width:thin; }
        .home-product-slide { min-width:0; scroll-snap-align:start; }
        .home-product-slide .catalog-product-tile { height:100%; }
        @media(max-width:767px) {
            .home-catalog { padding:32px 0 12px; }
            .home-category-intro { display:block; padding-bottom:18px; }
            .home-category-intro p { max-width:360px; margin-top:9px; font-size:.84rem; }
            .home-category-feature-grid { gap:8px; margin-bottom:17px; }
            .home-category-feature,.home-category-feature:first-child { flex:1 1 calc(50% - 4px); min-height:155px; }
            .home-category-feature:first-child { flex-basis:100%; min-height:205px; }
            .home-category-feature-label { right:11px; bottom:10px; left:11px; font-size:1.1rem; }
            .home-category-links { gap:6px; padding-bottom:21px; }
            .home-category-links a { padding:8px 10px; font-size:.75rem; }
            .home-product-shelf { padding-top:18px; margin-bottom:21px; }
            .home-shelf-heading { align-items:center; gap:8px; }
            .home-shelf-heading h2 { font-size:1.38rem; }
            .home-shelf-actions { gap:4px; }
            .home-view-all { margin-right:0; font-size:.73rem; }
            .home-carousel-control { width:31px; height:31px; font-size:1.15rem; }
            .home-product-track { grid-auto-columns:minmax(164px,66%); gap:9px; }
        }
    </style>
    <script>
        (() => {
            document.querySelectorAll('.home-carousel-control').forEach((button) => {
                if (button.dataset.bound) return;
                button.dataset.bound = 'true';
                button.addEventListener('click', () => {
                    const track = document.getElementById(button.dataset.carouselTarget);
                    if (track) track.scrollBy({ left: Number(button.dataset.direction) * track.clientWidth * 0.82, behavior: 'smooth' });
                });
            });
        })();
    </script>
    @endif
@endif
