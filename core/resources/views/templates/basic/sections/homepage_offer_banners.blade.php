@php
    $offerPlacement = $offerPlacement ?? 'other';
    $homepageOfferList = $homepageOfferBanners ?? collect();
    $isFlashOffer = fn ($offer) => $offer->campaign_type === 'flash'
        || str_contains(strtolower((string) $offer->name), 'flash sale');
    $offersToShow = $offerPlacement === 'flash'
        ? $homepageOfferList->filter($isFlashOffer)->take(1)
        : $homepageOfferList->reject($isFlashOffer);
@endphp
@if ($offersToShow->isNotEmpty())
    <section class="homepage-offers" id="{{ $offerPlacement === 'flash' ? 'homepage-flash-sale' : 'homepage-promotion-banners' }}" aria-label="{{ $offerPlacement === 'flash' ? __('Flash sale') : __('Current promotions') }}">
        <div class="container">
            <div class="homepage-offers-grid">
                @foreach ($offersToShow as $offer)
                    @php
                        $offerEnd = $offer->ends_at?->timestamp * 1000;
                        $campaignLabel = match ($offer->campaign_type) {
                            'flash' => __('FLASH SALE'),
                            'clearance' => __('STOCK CLEARANCE'),
                            default => __('SPECIAL OFFER'),
                        };
                    @endphp
                    <article class="homepage-offer-card homepage-offer-card--{{ $offer->campaign_type }}">
                        <div class="homepage-offer-copy">
                            <span class="homepage-offer-eyebrow">{{ $campaignLabel }}</span>
                            <h2>{{ $offer->banner_title ?: $offer->name }}</h2>
                            @if ($offer->banner_message)
                                <p>{{ $offer->banner_message }}</p>
                            @endif
                            @if (($offer->show_countdown || $offer->campaign_type === 'flash') && $offerEnd)
                                <div class="homepage-offer-countdown" data-offer-countdown="{{ $offerEnd }}" aria-label="@lang('Time left in offer')">
                                    <span><b data-days>00</b><small>@lang('Days')</small></span>
                                    <span><b data-hours>00</b><small>@lang('Hours')</small></span>
                                    <span><b data-minutes>00</b><small>@lang('Minutes')</small></span>
                                    <span><b data-seconds>00</b><small>@lang('Seconds')</small></span>
                                </div>
                            @endif
                            <a class="homepage-offer-button" href="{{ route('offer.products', encrypt($offer->id)) }}">
                                {{ $offer->banner_button_text ?: __('Shop offer') }}
                            </a>
                        </div>
                        @if ($offer->banner)
                            <a class="homepage-offer-image" href="{{ route('offer.products', encrypt($offer->id)) }}" tabindex="-1" aria-hidden="true">
                                <img src="{{ getImage(getFilePath('offerBanner') . '/' . $offer->banner, getFileSize('offerBanner')) }}" alt="" loading="lazy" onerror="this.closest('.homepage-offer-image').remove()">
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <style>
        .homepage-offers { padding:14px 0 5px; }
        .homepage-offers-grid { display:grid; gap:12px; }
        .homepage-offer-card { position:relative; isolation:isolate; display:flex; align-items:center; justify-content:space-between; gap:24px; min-height:196px; padding:28px clamp(20px,4vw,42px); overflow:hidden; border:1px solid #3b3a35; border-radius:2px; color:#fff; background:#292a26; }
        .homepage-offer-card--flash { background:#79392f; border-color:#79392f; }
        .homepage-offer-card--clearance { background:#4d513b; border-color:#4d513b; }
        .homepage-offer-copy { position:relative; z-index:1; max-width:700px; }
        .homepage-offer-eyebrow { display:inline-block; padding-bottom:5px; margin-bottom:8px; border-bottom:1px solid rgba(255,255,255,.38); color:#f1d0ad; font-size:10px; font-weight:700; letter-spacing:.16em; }
        .homepage-offer-card h2 { margin:0 0 8px; color:#fff; font-family:var(--store-display,Georgia,serif); font-size:clamp(25px,3.6vw,39px); font-weight:500; letter-spacing:-.025em; }
        .homepage-offer-card p { max-width:580px; margin:0 0 14px; color:rgba(255,255,255,.82); }
        .homepage-offer-countdown { display:flex; gap:7px; margin:0 0 15px; }
        .homepage-offer-countdown span { min-width:48px; padding:6px 7px; border:1px solid rgba(255,255,255,.24); background:rgba(255,255,255,.07); text-align:center; }
        .homepage-offer-countdown b,.homepage-offer-countdown small { display:block; }
        .homepage-offer-countdown b { font-size:19px; line-height:1.15; }
        .homepage-offer-countdown small { color:rgba(255,255,255,.73); font-size:9px; text-transform:uppercase; }
        .homepage-offer-button { display:inline-block; padding:10px 15px; border:1px solid #f8f6f1; border-radius:2px; background:#f8f6f1; color:#252521; font-size:.8rem; font-weight:700; text-decoration:none; }
        .homepage-offer-button:hover { border-color:#fff; background:transparent; color:#fff; }
        .homepage-offer-image { flex:0 0 27%; max-width:310px; height:168px; }
        .homepage-offer-image img { width:100%; height:100%; object-fit:contain; }
        @media(max-width:767px) { .homepage-offers { padding-top:10px; } .homepage-offer-card { min-height:unset; align-items:flex-start; gap:10px; padding:19px 15px; } .homepage-offer-copy { min-width:0; } .homepage-offer-image { flex:0 0 28%; height:120px; } .homepage-offer-countdown { gap:4px; } .homepage-offer-countdown span { min-width:40px; padding:6px 4px; } .homepage-offer-countdown b { font-size:16px; } .homepage-offer-card h2 { font-size:clamp(21px,6vw,29px); } }
        @media(max-width:420px) { .homepage-offer-card { flex-direction:column-reverse; } .homepage-offer-image { align-self:center; flex:0 0 auto; width:100%; height:105px; } .homepage-offer-countdown span { min-width:0; } }
    </style>
    <script>
        (() => {
            document.querySelectorAll('[data-offer-countdown]').forEach((timer) => {
                if (timer.dataset.countdownBound) return;
                timer.dataset.countdownBound = 'true';
                const target = Number(timer.dataset.offerCountdown);
                const update = () => {
                    const remaining = Math.max(0, target - Date.now());
                    const values = {
                        days: Math.floor(remaining / 86400000),
                        hours: Math.floor((remaining % 86400000) / 3600000),
                        minutes: Math.floor((remaining % 3600000) / 60000),
                        seconds: Math.floor((remaining % 60000) / 1000),
                    };
                    Object.entries(values).forEach(([key, value]) => {
                        const output = timer.querySelector(`[data-${key}]`);
                        if (output) output.textContent = String(value).padStart(2, '0');
                    });
                };
                update();
                window.setInterval(update, 1000);
            });
        })();
    </script>
@endif
