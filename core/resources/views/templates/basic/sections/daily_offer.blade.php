@if (!empty($dailyOffer))
    @php
        $dailyOfferProducts = $dailyOffer->products->filter(fn ($product) => $product->hasDisplayableImage())->values();
        $dailyOfferEndsAt = $dailyOffer->ends_at?->timestamp * 1000;
        $dailyOfferUrl = route('offer.products', encrypt($dailyOffer->id));
    @endphp
    <button type="button" class="daily-offer-reopen" id="dailyOfferReopen">
        <i class="las la-bolt" aria-hidden="true"></i>
        <span>@lang("Today's offer")</span>
    </button>
    <div class="modal fade" id="dailyOfferModal" tabindex="-1" role="dialog" aria-labelledby="dailyOfferTitle" aria-hidden="true"
        data-countdown-target="{{ $dailyOfferEndsAt }}" data-offer-id="{{ $dailyOffer->id }}">
        <div class="modal-dialog modal-dialog-centered daily-offer-dialog" role="document">
            <div class="modal-content daily-offer-content">
                <button type="button" class="daily-offer-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="@lang('Close')">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="daily-offer-card">
                    <div class="daily-offer-copy">
                        <span class="daily-deal-eyebrow">{{ $dailyOffer->banner_title ? __('DAILY OFFER') : __('TODAY’S DAILY OFFER') }}</span>
                        <h2 id="dailyOfferTitle">{{ $dailyOffer->banner_title ?: $dailyOffer->name }}</h2>
                        <p>{{ $dailyOffer->banner_message ?: __('Special picks, limited time. Shop before this offer ends.') }}</p>
                        <div class="daily-deal-timer" aria-label="@lang('Time left in this offer')">
                            <span><strong data-time-days>00</strong><small>@lang('Days')</small></span>
                            <span><strong data-time-hours>00</strong><small>@lang('Hours')</small></span>
                            <span><strong data-time-minutes>00</strong><small>@lang('Minutes')</small></span>
                            <span><strong data-time-seconds>00</strong><small>@lang('Seconds')</small></span>
                        </div>
                        <a class="daily-deal-cta" href="{{ $dailyOfferUrl }}">{{ $dailyOffer->banner_button_text ?: __("Shop today's offer") }}</a>
                    </div>
                    @if ($dailyOfferProducts->isEmpty() && $dailyOffer->banner && $dailyOffer->show_banner)
                        <a class="daily-deal-image" href="{{ $dailyOfferUrl }}" aria-label="@lang('Shop :offer', ['offer' => $dailyOffer->name])">
                            <img src="{{ getImage(getFilePath('offerBanner') . '/' . $dailyOffer->banner, getFileSize('offerBanner')) }}" alt="{{ $dailyOffer->banner_title ?: $dailyOffer->name }}">
                        </a>
                    @elseif ($dailyOfferProducts->isNotEmpty())
                        <div class="daily-deal-carousel" data-daily-offer-carousel aria-label="@lang('Products in this offer')">
                            @foreach ($dailyOfferProducts as $index => $offerProduct)
                                <a class="daily-deal-image daily-deal-slide" href="{{ $dailyOfferUrl }}" @if ($index > 0) hidden @endif data-slide-index="{{ $index }}">
                                    <img src="{{ $offerProduct->mainImage() }}" alt="{{ $offerProduct->name }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}" onerror="this.closest('.daily-deal-slide').remove()">
                                    <span class="daily-deal-product-name">{{ $offerProduct->name }}</span>
                                </a>
                            @endforeach
                            @if ($dailyOfferProducts->count() > 1)
                                <button type="button" class="daily-deal-carousel-arrow daily-deal-carousel-prev" aria-label="@lang('Previous offer product')"><i class="las la-angle-left"></i></button>
                                <button type="button" class="daily-deal-carousel-arrow daily-deal-carousel-next" aria-label="@lang('Next offer product')"><i class="las la-angle-right"></i></button>
                                <div class="daily-deal-carousel-dots" role="group" aria-label="@lang('Choose offer product')">
                                    @foreach ($dailyOfferProducts as $index => $offerProduct)
                                        <button type="button" aria-label="@lang('Show offer product :number', ['number' => $index + 1])" aria-current="{{ $index === 0 ? 'true' : 'false' }}" data-slide-to="{{ $index }}"></button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                <label class="daily-offer-suppress">
                    <input type="checkbox" id="dailyOfferSkipToday">
                    <span>@lang('Do not show again today')</span>
                </label>
            </div>
        </div>
    </div>

    <style>
        .daily-offer-dialog { max-width: 780px; }
        .daily-offer-content { position: relative; overflow: hidden; border: 0; border-radius: 18px; background: #fff; box-shadow: 0 24px 70px rgba(13,24,38,.3); }
        .daily-offer-card { position:relative; overflow:hidden; display:flex; align-items:center; justify-content:space-between; gap:30px; min-height:300px; padding:42px 48px; color:#fff; background:radial-gradient(circle at 78% 34%,rgba(255,255,255,.18),transparent 31%),linear-gradient(115deg,#182333 0%,#293b4d 55%,#f05a35 145%); }
        .daily-offer-copy { position:relative; z-index:1; max-width:520px; }
        .daily-deal-eyebrow { display:inline-block; margin-bottom:12px; color:#ffc9a8; font-size:12px; font-weight:800; letter-spacing:.16em; }
        .daily-offer-card h2 { margin:0 0 8px; color:#fff; font-size:clamp(27px,4vw,42px); font-weight:800; }
        .daily-offer-card p { margin:0 0 19px; color:rgba(255,255,255,.86); }
        .daily-deal-timer { display:flex; gap:9px; margin-bottom:22px; }
        .daily-deal-timer span { min-width:58px; padding:9px 8px 7px; border:1px solid rgba(255,255,255,.22); border-radius:9px; background:rgba(255,255,255,.1); text-align:center; }
        .daily-deal-timer strong,.daily-deal-timer small { display:block; }
        .daily-deal-timer strong { font-size:21px; line-height:1.1; }
        .daily-deal-timer small { margin-top:3px; color:rgba(255,255,255,.75); font-size:10px; text-transform:uppercase; }
        .daily-deal-cta { display:inline-block; padding:11px 19px; border-radius:7px; background:#f15a36; color:#fff; font-weight:700; text-decoration:none; }
        .daily-deal-cta:hover { background:#dd4828; color:#fff; }
        .daily-deal-carousel { position:relative; flex:0 0 36%; max-width:300px; min-width:0; }
        .daily-deal-image { display:flex; flex-direction:column; align-items:center; justify-content:center; height:220px; }
        .daily-deal-image[hidden] { display:none!important; }
        .daily-deal-image img { width:100%; height:calc(100% - 24px); object-fit:contain; filter:drop-shadow(0 16px 24px rgba(0,0,0,.2)); }
        .daily-deal-product-name { display:block; max-width:100%; overflow:hidden; color:#fff; font-size:11px; line-height:1.25; text-align:center; text-overflow:ellipsis; white-space:nowrap; }
        .daily-deal-carousel-arrow { position:absolute; top:42%; display:grid; place-items:center; width:32px; height:32px; border:0; border-radius:50%; background:#ffffffdd; color:#182333; }
        .daily-deal-carousel-prev { left:-8px; }
        .daily-deal-carousel-next { right:-8px; }
        .daily-deal-carousel-dots { display:flex; justify-content:center; gap:6px; margin-top:5px; }
        .daily-deal-carousel-dots button { width:7px; height:7px; padding:0; border:0; border-radius:50%; background:#ffffff80; }
        .daily-deal-carousel-dots button[aria-current="true"] { width:18px; border-radius:6px; background:#fff; }
        .daily-offer-close { position:absolute; z-index:3; top:10px; right:12px; width:38px; height:38px; border:0; border-radius:50%; background:rgba(255,255,255,.92); color:#1d2938; font-size:28px; line-height:1; }
        .daily-offer-reopen{position:fixed;right:22px;bottom:22px;z-index:1030;display:inline-flex;align-items:center;gap:.55rem;padding:.8rem 1rem;border:0;border-radius:999px;background:#f15a36;color:#fff;font-weight:750;box-shadow:0 8px 28px rgba(33,42,55,.24)}
        .daily-offer-reopen:hover,.daily-offer-reopen:focus-visible{background:#d94829;color:#fff}
        .daily-offer-suppress { display:flex; align-items:center; gap:9px; margin:0; padding:14px 20px; color:#394454; font-size:14px; cursor:pointer; }
        .daily-offer-suppress input { width:17px; height:17px; accent-color:#f15a36; }
        @media(max-width:575px) {
            .daily-offer-dialog { width:calc(100% - 2rem); max-width:390px; margin:1rem auto; }
            .daily-offer-content { max-height:calc(100dvh - 2rem); overflow-y:auto; border-radius:14px; }
            .daily-offer-card { min-height:0; flex-direction:column-reverse; align-items:stretch; gap:7px; padding:28px 16px 16px; }
            .daily-offer-image { flex:0 0 auto; align-self:center; width:100%; height:clamp(105px,20vh,145px); }
            .daily-deal-carousel { flex:0 0 auto; width:100%; max-width:220px; align-self:center; }
            .daily-deal-carousel-arrow { width:28px; height:28px; }
            .daily-offer-card h2 { font-size:clamp(22px,7vw,30px); }
            .daily-offer-card p { margin-bottom:12px; font-size:.9rem; }
            .daily-deal-timer { margin-bottom:14px; }
            .daily-deal-timer { gap:5px; }
            .daily-deal-timer span { min-width:0; flex:1; padding:8px 4px 6px; }
            .daily-deal-timer strong { font-size:18px; }
            .daily-offer-suppress { padding:12px 16px calc(12px + env(safe-area-inset-bottom)); }
        }
    </style>
    <script>
        (() => {
            const modal = document.getElementById('dailyOfferModal');
            if (!modal) return;
            const offerId = modal.dataset.offerId;
            const dateKey = () => {
                const date = new Date();
                return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
            };
            const todayStorageKey = `daily-offer-hidden:${offerId}`;
            const checkbox = modal.querySelector('#dailyOfferSkipToday');
            const reopenButton = document.getElementById('dailyOfferReopen');
            const target = Number(modal.dataset.countdownTarget);
            let countdownTimer;
            let slideshowTimer;
            let shownThisTime = false;
            const slides = [...modal.querySelectorAll('.daily-deal-slide')];
            const dots = [...modal.querySelectorAll('[data-slide-to]')];
            let activeSlide = 0;
            const showSlide = (index) => {
                if (!slides.length) return;
                activeSlide = (index + slides.length) % slides.length;
                slides.forEach((slide, i) => { slide.hidden = i !== activeSlide; });
                dots.forEach((dot, i) => dot.setAttribute('aria-current', i === activeSlide ? 'true' : 'false'));
            };
            modal.querySelector('.daily-deal-carousel-prev')?.addEventListener('click', () => showSlide(activeSlide - 1));
            modal.querySelector('.daily-deal-carousel-next')?.addEventListener('click', () => showSlide(activeSlide + 1));
            dots.forEach((dot) => dot.addEventListener('click', () => showSlide(Number(dot.dataset.slideTo))));
            const readStorage = (storage, key) => {
                try { return storage.getItem(key); } catch (error) { return null; }
            };
            const writeStorage = (storage, key, value) => {
                try { storage.setItem(key, value); } catch (error) { /* Storage can be disabled in private browsing. */ }
            };
            const removeStorage = (storage, key) => {
                try { storage.removeItem(key); } catch (error) { /* Storage can be disabled in private browsing. */ }
            };
            const handleShown = () => {
                if (shownThisTime) return;
                shownThisTime = true;
                renderCountdown();
                countdownTimer = window.setInterval(renderCountdown, 1000);
                if (slides.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    slideshowTimer = window.setInterval(() => showSlide(activeSlide + 1), 3500);
                }
            };
            const handleHidden = () => {
                window.clearInterval(countdownTimer);
                window.clearInterval(slideshowTimer);
                if (checkbox.checked) writeStorage(localStorage, todayStorageKey, dateKey());
                shownThisTime = false;
            };
            const renderCountdown = () => {
                let remaining = Math.max(0, target - Date.now());
                const days = Math.floor(remaining / 86400000); remaining %= 86400000;
                const hours = Math.floor(remaining / 3600000); remaining %= 3600000;
                const minutes = Math.floor(remaining / 60000); remaining %= 60000;
                const seconds = Math.floor(remaining / 1000);
                modal.querySelector('[data-time-days]').textContent = String(days).padStart(2, '0');
                modal.querySelector('[data-time-hours]').textContent = String(hours).padStart(2, '0');
                modal.querySelector('[data-time-minutes]').textContent = String(minutes).padStart(2, '0');
                modal.querySelector('[data-time-seconds]').textContent = String(seconds).padStart(2, '0');
            };
            checkbox.addEventListener('change', () => {
                if (checkbox.checked) writeStorage(localStorage, todayStorageKey, dateKey());
                else removeStorage(localStorage, todayStorageKey);
            });
            const showModal = (manual = false) => {
                if (!manual && readStorage(localStorage, todayStorageKey) === dateKey()) return;
                if (window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(modal).show();
                } else if (window.jQuery && typeof window.jQuery(modal).modal === 'function') {
                    window.jQuery(modal).modal('show');
                } else {
                    modal.style.display = 'block';
                    modal.classList.add('show');
                    modal.setAttribute('aria-modal', 'true');
                    modal.removeAttribute('aria-hidden');
                    document.body.classList.add('modal-open');
                    if (!document.querySelector('.daily-offer-backdrop')) {
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show daily-offer-backdrop';
                        document.body.appendChild(backdrop);
                    }
                    handleShown();
                }
            };
            reopenButton?.addEventListener('click', () => showModal(true));
            modal.querySelector('.daily-offer-close')?.addEventListener('click', () => {
                if (window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(modal).hide();
                } else if (window.jQuery && typeof window.jQuery(modal).modal === 'function') {
                    window.jQuery(modal).modal('hide');
                } else {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    modal.setAttribute('aria-hidden', 'true');
                    modal.removeAttribute('aria-modal');
                    document.body.classList.remove('modal-open');
                    document.querySelector('.daily-offer-backdrop')?.remove();
                    handleHidden();
                }
            });
            if (window.jQuery) {
                window.jQuery(modal).on('shown.bs.modal', handleShown).on('hidden.bs.modal', handleHidden);
            } else {
                modal.addEventListener('shown.bs.modal', handleShown);
                modal.addEventListener('hidden.bs.modal', handleHidden);
            }
            const scheduleModal = () => window.setTimeout(showModal, 650);
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', scheduleModal, { once: true });
            } else {
                scheduleModal();
            }
        })();
    </script>
@endif
