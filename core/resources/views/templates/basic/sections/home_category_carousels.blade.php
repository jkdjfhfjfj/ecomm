@if (isset($homeCategoryRows) && $homeCategoryRows->isNotEmpty())
    @php
        $homeShopRows = $homeCategoryRows
            ->unique(fn ($row) => $row['category']->id)
            ->reject(function ($row) {
                $category = $row['category'];
                $slug = strtolower((string) $category->slug);
                $name = strtolower(trim((string) $category->name));
                return $slug === 'daily-offers' || $name === 'daily offers';
            })
            ->values();
    @endphp
    @if ($homeShopRows->isNotEmpty())
    <section class="home-catalog">
        <div class="container">
            <div class="home-category-intro">
                <div>
                    <span class="home-shelf-eyebrow">@lang('Browse departments')</span>
                    <h2>@lang('Shop by category')</h2>
                </div>
                <div class="home-category-controls">
                    <a class="home-view-all" href="{{ route('categories') }}">@lang('All categories') <span aria-hidden="true">→</span></a>
                    <button type="button" class="home-carousel-control" data-category-scroll="-1" aria-label="@lang('Scroll categories left')">‹</button>
                    <button type="button" class="home-carousel-control" data-category-scroll="1" aria-label="@lang('Scroll categories right')">›</button>
                </div>
            </div>
            <div class="home-category-carousel" id="home-category-carousel" tabindex="0" aria-label="@lang('Shop by category')">
                @foreach ($homeShopRows as $row)
                    @php($category = $row['category'])
                    <a class="home-category-card" href="{{ route('product.by.category', $category->slug) }}">
                        <span class="home-category-card__image">
                            <span class="home-category-card__fallback" aria-hidden="true"><i class="las la-layer-group"></i></span>
                            @if (filter_var($category->external_image_url, FILTER_VALIDATE_URL) || filled($category->image))
                                <img src="{{ $category->categoryImage() }}" alt="{{ __($category->name) }}" loading="lazy" decoding="async" onerror="this.remove()">
                            @endif
                        </span>
                        <span class="home-category-card__name">{{ __($category->name) }}</span>
                        <span class="home-category-card__arrow" aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <style>
        .home-catalog { padding:clamp(30px,5vw,64px) 0 20px; background:#f1ede5; overflow:hidden; }
        .home-category-intro { display:flex; align-items:end; justify-content:space-between; gap:2rem; padding:0 0 20px; }
        .home-category-intro h2 { margin:.3rem 0 0; color:var(--store-ink,#252521); font-family:var(--store-display,Georgia,serif); font-size:clamp(2rem,4vw,3.35rem); font-weight:500; letter-spacing:-.045em; line-height:1.08; }
        .home-category-controls { display:flex; align-items:center; gap:7px; }
        .home-category-carousel { display:grid; grid-auto-columns:minmax(160px,18%); grid-auto-flow:column; gap:12px; overflow-x:auto; overscroll-behavior-inline:contain; scroll-snap-type:x mandatory; scroll-behavior:smooth; padding:2px 1px 13px; scrollbar-width:thin; }
        .home-category-card { position:relative; display:flex; min-width:0; flex-direction:column; gap:10px; padding:9px; border:1px solid #ded8cf; background:#faf8f3; color:var(--store-ink,#252521); text-decoration:none; scroll-snap-align:start; }
        .home-category-card:hover { border-color:var(--store-accent,#9b493d); color:var(--store-accent,#9b493d); }
        .home-category-card__image { position:relative; display:grid; width:100%; aspect-ratio:1.3; place-items:center; overflow:hidden; background:#ede9e1; color:#9b493d; }
        .home-category-card__fallback { display:grid; width:100%; height:100%; place-items:center; font-size:1.6rem; }
        .home-category-card__image img { position:absolute; inset:0; display:block; width:100%; height:100%; object-fit:cover; }
        .home-category-card__name { padding:0 22px 0 2px; font-size:.88rem; font-weight:700; line-height:1.3; }
        .home-category-card__arrow { position:absolute; right:11px; bottom:13px; color:var(--store-accent,#9b493d); }
        .home-view-all { margin-right:.4rem; color:var(--store-ink,#252521); font-size:.82rem; font-weight:650; text-decoration:none; white-space:nowrap; }
        .home-view-all:hover { color:var(--store-accent,#9b493d); }
        .home-carousel-control { display:grid; width:37px; height:37px; place-items:center; border:1px solid #d6cec2; border-radius:50%; background:#faf8f3; color:var(--store-ink,#252521); font-size:1.35rem; line-height:1; }
        .home-carousel-control:hover { border-color:var(--store-ink,#252521); background:var(--store-ink,#252521); color:#fff; }
        .home-category-carousel { scrollbar-color:#c8beb0 transparent; }
        .home-category-carousel:focus-visible { outline:2px solid var(--store-accent,#9b493d); outline-offset:3px; }
        .home-category-controls .home-view-all { margin-right:3px; }
        @media(max-width:767px) {
            .home-catalog { padding:32px 0 12px; }
            .home-category-intro { display:block; padding-bottom:18px; }
            .home-category-controls { margin-top:12px; }
            .home-category-intro h2 { font-size:1.8rem; }
            .home-category-carousel { grid-auto-columns:minmax(138px,42%); gap:9px; }
            .home-category-card { gap:8px; padding:7px; }
            .home-category-card__name { font-size:.81rem; }
            .home-view-all { margin-right:0; font-size:.73rem; }
            .home-carousel-control { width:31px; height:31px; font-size:1.15rem; }
        }
    </style>
    <script>
        (() => {
            document.querySelectorAll('.home-carousel-control').forEach((button) => {
                if (button.dataset.bound) return;
                button.dataset.bound = 'true';
                button.addEventListener('click', () => {
                    const categoryDirection = button.dataset.categoryScroll;
                    const track = categoryDirection !== undefined
                        ? document.getElementById('home-category-carousel')
                        : document.getElementById(button.dataset.carouselTarget);
                    const direction = Number(categoryDirection ?? button.dataset.direction);
                    if (track) track.scrollBy({ left: direction * track.clientWidth * 0.82, behavior: 'smooth' });
                });
            });
        })();
    </script>
    @endif
@endif
