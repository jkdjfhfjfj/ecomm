@if (($homepageOfferBanners ?? collect())->isNotEmpty())
    <section class="homepage-offers" aria-label="@lang('Current promotions')">
        <div class="container">
            <div class="homepage-offers-grid">
                @foreach ($homepageOfferBanners as $offer)
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
                            @if ($offer->show_countdown && $offerEnd)
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
        .homepage-offers { padding: 24px 0 4px; }
        .homepage-offers-grid { display:grid; gap:18px; }
        .homepage-offer-card { position:relative; isolation:isolate; display:flex; align-items:center; justify-content:space-between; gap:24px; min-height:230px; padding:30px clamp(22px,4vw,48px); overflow:hidden; border-radius:18px; color:#fff; background:linear-gradient(115deg,#12253a,#176b73 68%,#35a39a); box-shadow:0 14px 34px rgba(16,37,57,.16); }
        .homepage-offer-card--flash { background:linear-gradient(115deg,#311627,#8b2345 67%,#ef6944); }
        .homepage-offer-card--clearance { background:linear-gradient(115deg,#25321f,#52672d 67%,#c28b32); }
        .homepage-offer-copy { position:relative; z-index:1; max-width:700px; }
        .homepage-offer-eyebrow { display:inline-block; margin-bottom:9px; color:#ffe0b7; font-size:11px; font-weight:800; letter-spacing:.16em; }
        .homepage-offer-card h2 { margin:0 0 8px; color:#fff; font-size:clamp(25px,3.6vw,39px); font-weight:800; }
        .homepage-offer-card p { max-width:580px; margin:0 0 14px; color:rgba(255,255,255,.86); }
        .homepage-offer-countdown { display:flex; gap:8px; margin:0 0 16px; }
        .homepage-offer-countdown span { min-width:54px; padding:6px 8px; border:1px solid rgba(255,255,255,.24); border-radius:8px; background:rgba(255,255,255,.1); text-align:center; }
        .homepage-offer-countdown b,.homepage-offer-countdown small { display:block; }
        .homepage-offer-countdown b { font-size:19px; line-height:1.15; }
        .homepage-offer-countdown small { color:rgba(255,255,255,.76); font-size:9px; text-transform:uppercase; }
        .homepage-offer-button { display:inline-block; padding:10px 17px; border-radius:7px; background:#fff; color:#233349; font-weight:750; text-decoration:none; }
        .homepage-offer-button:hover { color:#111; transform:translateY(-1px); }
        .homepage-offer-image { flex:0 0 29%; max-width:330px; height:190px; }
        .homepage-offer-image img { width:100%; height:100%; object-fit:contain; filter:drop-shadow(0 12px 20px rgba(0,0,0,.19)); }
        @media(max-width:767px) { .homepage-offer-card { min-height:unset; padding:24px 20px; } .homepage-offer-image { flex-basis:30%; height:125px; } .homepage-offer-countdown { gap:5px; } .homepage-offer-countdown span { min-width:44px; padding:6px 4px; } .homepage-offer-countdown b { font-size:16px; } }
    </style>
    <script>
        (() => {
            document.querySelectorAll('[data-offer-countdown]').forEach((timer) => {
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
