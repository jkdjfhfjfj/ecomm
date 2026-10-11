@if (isset($homeCategoryRows) && $homeCategoryRows->isNotEmpty())
    <section class="home-catalog py-5">
        <div class="container">
            <div class="home-category-intro">
                <div>
                    <span class="home-shelf-eyebrow">@lang('Find your next favourite')</span>
                    <h2>@lang('A world of good things')</h2>
                </div>
                <p>@lang('Browse by collection, then take your time with the details.') </p>
            </div>
            <div class="home-category-feature-grid">
                @foreach ($homeCategoryRows->take(4) as $row)
                    <a class="home-category-feature" href="{{ route('product.by.category', $row['category']->slug) }}">
                        <span class="home-category-feature-art" aria-hidden="true"></span>
                        @if (filter_var($row['category']->external_image_url, FILTER_VALIDATE_URL) || $row['category']->image)
                            <img src="{{ $row['category']->categoryImage() }}" alt="{{ __($row['category']->name) }}" loading="lazy" onerror="this.remove()">
                        @endif
                        <span class="home-category-feature-label">
                            <span>{{ __($row['category']->name) }}</span>
                            <span aria-hidden="true">↗</span>
                        </span>
                    </a>
                @endforeach
            </div>
            <nav class="home-category-links" aria-label="@lang('Shop by category')">
                @foreach ($homeCategoryRows as $row)
                    <a href="{{ route('product.by.category', $row['category']->slug) }}">{{ $row['category']->name }}</a>
                @endforeach
            </nav>

            @foreach ($homeCategoryRows as $row)
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
            @endforeach
        </div>
    </section>

    <style>
        .home-catalog { background:#f4f0e9; overflow:hidden; }
        .home-category-intro { display:flex; align-items:end; justify-content:space-between; gap:2rem; padding:1rem 0 2rem; }
        .home-category-intro h2 { margin:.2rem 0 0; font-family:var(--store-display); font-size:clamp(2rem,4vw,3.5rem); font-weight:500; letter-spacing:-.04em; }
        .home-category-intro p { max-width:280px; margin:0 0 .4rem; color:#6c655d; font-size:.95rem; }
        .home-category-feature-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; margin-bottom:2rem; }
        .home-category-feature { position:relative; min-height:235px; overflow:hidden; background:#ded7cc; color:#fff; text-decoration:none; }
        .home-category-feature-art { position:absolute; z-index:0; inset:0; background:radial-gradient(ellipse at 76% 28%,rgba(255,255,255,.4),transparent 28%),linear-gradient(135deg,#c64d31,#e38b51 48%,#272f2d); }
        .home-category-feature:after { position:absolute; inset:35% 0 0; content:""; background:linear-gradient(transparent,rgba(20,19,17,.66)); }
        .home-category-feature img { position:absolute; z-index:1; inset:0; width:100%; height:100%; min-height:235px; object-fit:cover; transition:transform .45s ease; }
        .home-category-feature:hover img { transform:scale(1.04); }
        .home-category-feature-label { position:absolute; z-index:3; right:1rem; bottom:1rem; left:1rem; display:flex; align-items:center; justify-content:space-between; gap:.5rem; font-family:var(--store-display); font-size:1.35rem; }
        .home-category-links { display:flex; gap:.65rem; overflow:auto; padding:.25rem 0 1.5rem; scrollbar-width:thin; }
        .home-category-links a { flex:0 0 auto; padding:.65rem 1rem; border:1px solid #e2e3df; border-radius:999px; background:#fff; color:#252824; font-size:.88rem; text-decoration:none; }
        .home-category-links a:hover { border-color:#a67c42; color:#805a27; }
        .home-product-shelf { margin:0 0 2.8rem; }
        .home-shelf-heading { display:flex; align-items:end; justify-content:space-between; gap:1rem; margin-bottom:1.1rem; }
        .home-shelf-eyebrow { color:#92764c; font-size:.72rem; font-weight:700; letter-spacing:.13em; text-transform:uppercase; }
        .home-shelf-heading h2 { margin:.2rem 0 0; color:#171a17; font-size:clamp(1.45rem,2vw,2rem); font-weight:650; }
        .home-shelf-actions { display:flex; align-items:center; gap:.5rem; }
        .home-view-all { margin-right:.45rem; color:#222; font-size:.9rem; font-weight:650; text-decoration:none; white-space:nowrap; }
        .home-carousel-control { width:2.5rem; height:2.5rem; border:1px solid #d9d9d3; border-radius:50%; background:#fff; color:#252824; font-size:1.5rem; line-height:1; }
        .home-carousel-control:hover { background:#222; color:#fff; }
        .home-product-track { display:grid; grid-auto-columns:minmax(205px, 23%); grid-auto-flow:column; gap:1rem; overflow-x:auto; overscroll-behavior-inline:contain; scroll-snap-type:x mandatory; scroll-behavior:smooth; padding:.2rem .1rem 1rem; scrollbar-width:thin; }
        .home-product-slide { min-width:0; scroll-snap-align:start; }
        @media(max-width:767px) { .home-category-intro { display:block; padding-top:.25rem; } .home-category-intro p { margin-top:.7rem; } .home-category-feature-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:.65rem; } .home-category-feature,.home-category-feature img { min-height:170px; } .home-category-feature-label { font-size:1.1rem; } .home-product-track { grid-auto-columns:minmax(170px, 68%); gap:.75rem; } .home-shelf-heading { align-items:center; } .home-shelf-actions { gap:.3rem; } .home-view-all { font-size:.8rem; } .home-carousel-control { width:2.15rem; height:2.15rem; } }
    </style>
    <script>
        document.querySelectorAll('.home-carousel-control').forEach((button) => {
            button.addEventListener('click', () => {
                const track = document.getElementById(button.dataset.carouselTarget);
                if (track) track.scrollBy({ left: Number(button.dataset.direction) * track.clientWidth * 0.85, behavior: 'smooth' });
            });
        });
    </script>
@endif
