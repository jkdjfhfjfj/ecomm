@if (!empty($dailyOffer))
    @php
        $featuredOfferProduct = $dailyOffer->products->first(fn ($product) => $product->hasDisplayableImage());
        $dailyOfferImage = $featuredOfferProduct?->mainImage();
        $dailyOfferEndsAt = $dailyOffer->ends_at?->timestamp * 1000;
    @endphp
    <section class="daily-deal-wrap" data-countdown-target="{{ $dailyOfferEndsAt }}">
        <div class="container">
            <div class="daily-deal-card">
                <div class="daily-deal-copy">
                    <span class="daily-deal-eyebrow">{{ $dailyOffer->banner_title ? __('DAILY OFFER') : __('TODAY’S DAILY OFFER') }}</span>
                    <h2>{{ $dailyOffer->banner_title ?: $dailyOffer->name }}</h2>
                    <p>{{ $dailyOffer->banner_message ?: __('Special picks, limited time. Shop before this offer ends.') }}</p>
                    <div class="daily-deal-timer" aria-label="Time left in this offer">
                        <span><strong data-time-days>00</strong><small>Days</small></span>
                        <span><strong data-time-hours>00</strong><small>Hours</small></span>
                        <span><strong data-time-minutes>00</strong><small>Minutes</small></span>
                        <span><strong data-time-seconds>00</strong><small>Seconds</small></span>
                    </div>
                    <a class="daily-deal-cta" href="{{ route('offer.products', encrypt($dailyOffer->id)) }}">{{ $dailyOffer->banner_button_text ?: __("Shop today's offer") }}</a>
                </div>
                @if ($dailyOffer->banner && $dailyOffer->show_banner)
                    <a class="daily-deal-image" href="{{ route('offer.products', encrypt($dailyOffer->id)) }}" aria-label="Shop {{ $dailyOffer->name }}">
                        <img src="{{ getImage(getFilePath('offerBanner') . '/' . $dailyOffer->banner, getFileSize('offerBanner')) }}" alt="{{ $dailyOffer->banner_title ?: $dailyOffer->name }}">
                    </a>
                @elseif ($dailyOfferImage)
                    <a class="daily-deal-image" href="{{ route('offer.products', encrypt($dailyOffer->id)) }}" aria-label="Shop {{ $dailyOffer->name }}">
                        <img src="{{ $dailyOfferImage }}" alt="{{ $featuredOfferProduct->name }}" onerror="this.closest('.daily-deal-image').remove()">
                    </a>
                @endif
            </div>
        </div>
    </section>

    <style>
        .daily-deal-wrap { padding: 26px 0 4px; }
        .daily-deal-card { position:relative; overflow:hidden; display:flex; align-items:center; justify-content:space-between; gap:30px; min-height:285px; padding:38px 48px; border-radius:18px; color:#fff; background:radial-gradient(circle at 78% 34%,rgba(255,255,255,.18),transparent 31%),linear-gradient(115deg,#182333 0%,#293b4d 55%,#f05a35 145%); box-shadow:0 16px 38px rgba(27,39,54,.16); }
        .daily-deal-copy { position:relative; z-index:1; max-width:600px; }
        .daily-deal-eyebrow { display:inline-block; margin-bottom:12px; color:#ffc9a8; font-size:12px; font-weight:800; letter-spacing:.16em; }
        .daily-deal-card h2 { margin:0 0 8px; color:#fff; font-size:clamp(27px,4vw,42px); font-weight:800; }
        .daily-deal-card p { margin:0 0 19px; color:rgba(255,255,255,.82); }
        .daily-deal-timer { display:flex; gap:10px; margin-bottom:22px; }
        .daily-deal-timer span { min-width:65px; padding:9px 10px 7px; border:1px solid rgba(255,255,255,.22); border-radius:9px; background:rgba(255,255,255,.1); text-align:center; }
        .daily-deal-timer strong,.daily-deal-timer small { display:block; }
        .daily-deal-timer strong { font-size:21px; line-height:1.1; }
        .daily-deal-timer small { margin-top:3px; color:rgba(255,255,255,.75); font-size:10px; text-transform:uppercase; }
        .daily-deal-cta { display:inline-block; padding:11px 19px; border-radius:7px; background:#f15a36; color:#fff; font-weight:700; text-decoration:none; }
        .daily-deal-cta:hover { background:#dd4828; color:#fff; }
        .daily-deal-image { display:block; flex:0 0 30%; max-width:310px; height:220px; }
        .daily-deal-image img { width:100%; height:100%; object-fit:contain; filter:drop-shadow(0 16px 24px rgba(0,0,0,.2)); }
        @media(max-width:767px) { .daily-deal-card { min-height:unset; padding:27px 22px; } .daily-deal-image { flex-basis:34%; height:150px; } .daily-deal-timer { gap:6px; } .daily-deal-timer span { min-width:50px; padding:8px 5px; } .daily-deal-timer strong { font-size:18px; } }
    </style>
    <script>
        (() => {
            const section = document.querySelector('.daily-deal-wrap[data-countdown-target]');
            if (!section) return;
            const target = Number(section.dataset.countdownTarget);
            const render = () => {
                let remaining = Math.max(0, target - Date.now());
                const days = Math.floor(remaining / 86400000); remaining %= 86400000;
                const hours = Math.floor(remaining / 3600000); remaining %= 3600000;
                const minutes = Math.floor(remaining / 60000); remaining %= 60000;
                const seconds = Math.floor(remaining / 1000);
                section.querySelector('[data-time-days]').textContent = String(days).padStart(2, '0');
                section.querySelector('[data-time-hours]').textContent = String(hours).padStart(2, '0');
                section.querySelector('[data-time-minutes]').textContent = String(minutes).padStart(2, '0');
                section.querySelector('[data-time-seconds]').textContent = String(seconds).padStart(2, '0');
                if (target <= Date.now()) section.remove();
            };
            render();
            window.setInterval(render, 1000);
        })();
    </script>
@endif
