<style>
    .geo-address-control { position: relative; }
    .geo-address-suggestions { position:absolute; z-index:1100; inset-inline:0; top:100%; max-height:18rem; overflow:auto; padding:.35rem; border:1px solid #ddd; border-radius:.5rem; background:#fff; box-shadow:0 10px 28px #0002; }
    .geo-address-suggestions button { display:block; width:100%; padding:.65rem .75rem; border:0; border-radius:.35rem; background:transparent; text-align:left; color:#222; }
    .geo-address-suggestions button:hover,.geo-address-suggestions button:focus { background:#f3f4f2; }
    .geo-address-locate { margin-top:.55rem; }
    .geo-address-status { display:block; min-height:1.2em; margin-top:.3rem; }
</style>
<script>
(() => {
    const suggestionsEndpoint = @json(route('checkout.address.suggestions'));
    const reverseEndpoint = @json(route('checkout.address.reverse'));
    const labels = {
        locate: @json(__('Use my current location')),
        locating: @json(__('Finding your address…')),
        unavailable: @json(__('Location lookup is unavailable. You can enter the address manually.')),
        denied: @json(__('Location access was blocked. You can enter the address manually.')),
        notFound: @json(__('No address was found for your location.')),
        error: @json(__('Could not find your address right now. You can enter it manually.')),
    };
    const requests = new WeakMap();
    async function readJsonResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.toLowerCase().includes('application/json')) {
            throw new Error(labels.error);
        }
        try {
            return await response.json();
        } catch (error) {
            throw new Error(labels.error);
        }
    }

    function fillAddress(input, place) {
        input.value = place.address || place.label || '';
        const form = input.closest('form') || document;
        ['city', 'state', 'zip'].forEach((name) => {
            const field = form.querySelector(`[name="${name}"]`);
            if (field && place[name]) {
                field.value = place[name];
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        const country = form.querySelector('[name="country"]');
        if (country && place.country) {
            const normalized = place.country.trim().toLocaleLowerCase();
            const option = [...country.options].find((item) =>
                item.value.trim().toLocaleLowerCase() === normalized ||
                item.textContent.trim().toLocaleLowerCase() === normalized
            );
            if (option) {
                country.value = option.value;
                country.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function setupInput(input) {
        if (input.dataset.geoAddressReady) return;
        input.dataset.geoAddressReady = '1';

        const field = document.createElement('div');
        field.className = 'geo-address-control';
        input.parentNode.insertBefore(field, input);
        field.append(input);

        const group = input.closest('.form-group') || field.parentElement;
        const locate = document.createElement('button');
        locate.type = 'button';
        locate.className = 'btn btn-sm btn-outline-secondary geo-address-locate';
        locate.textContent = labels.locate;
        const status = document.createElement('small');
        status.className = 'geo-address-status text-muted';
        status.setAttribute('aria-live', 'polite');
        group.append(locate, status);

        locate.addEventListener('click', () => {
            if (!navigator.geolocation) {
                status.textContent = labels.unavailable;
                return;
            }
            locate.disabled = true;
            status.textContent = labels.locating;
            navigator.geolocation.getCurrentPosition(async ({ coords }) => {
                try {
                    const url = new URL(reverseEndpoint, window.location.origin);
                    url.searchParams.set('lat', coords.latitude);
                    url.searchParams.set('lon', coords.longitude);
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    const result = await readJsonResponse(response);
                    if (!response.ok || !result.place) throw new Error(result.message || labels.error);
                    fillAddress(input, result.place);
                    status.textContent = '';
                } catch (error) {
                    status.textContent = error.message || labels.error;
                } finally {
                    locate.disabled = false;
                }
            }, (error) => {
                status.textContent = error.code === error.PERMISSION_DENIED ? labels.denied : labels.error;
                locate.disabled = false;
            }, { enableHighAccuracy: false, timeout: 12000, maximumAge: 300000 });
        });
    }

    document.addEventListener('focusin', (event) => {
        const input = event.target;
        if (input instanceof HTMLInputElement && input.name === 'address') setupInput(input);
    });
    document.querySelectorAll('input[name="address"]').forEach(setupInput);
    new MutationObserver((records) => {
        records.forEach((record) => record.addedNodes.forEach((node) => {
            if (!(node instanceof Element)) return;
            if (node.matches('input[name="address"]')) setupInput(node);
            node.querySelectorAll?.('input[name="address"]').forEach(setupInput);
        }));
    }).observe(document.body, { childList: true, subtree: true });

    document.addEventListener('input', (event) => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || input.name !== 'address') return;
        setupInput(input);
        const field = input.closest('.geo-address-control');
        let menu = field.querySelector('.geo-address-suggestions');
        if (!menu) {
            menu = document.createElement('div');
            menu.className = 'geo-address-suggestions';
            menu.setAttribute('role', 'listbox');
            menu.hidden = true;
            field.append(menu);
        }
        const previous = requests.get(input) || {};
        clearTimeout(previous.timer);
        previous.controller?.abort();
        const text = input.value.trim();
        if (text.length < 3) { menu.hidden = true; return; }

        const timer = setTimeout(async () => {
            const controller = new AbortController();
            requests.set(input, { timer, controller });
            try {
                const url = new URL(suggestionsEndpoint, window.location.origin);
                url.searchParams.set('text', text);
                const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
                if (!response.ok) return;
                const { suggestions = [] } = await readJsonResponse(response);
                menu.replaceChildren();
                suggestions.forEach((place) => {
                    const option = document.createElement('button');
                    option.type = 'button';
                    option.setAttribute('role', 'option');
                    option.textContent = place.label;
                    option.addEventListener('click', () => {
                        fillAddress(input, place);
                        menu.hidden = true;
                    });
                    menu.append(option);
                });
                menu.hidden = suggestions.length === 0;
            } catch (error) {
                if (error.name !== 'AbortError') menu.hidden = true;
            }
        }, 300);
        requests.set(input, { timer, controller: null });
    });

    document.addEventListener('click', (event) => {
        document.querySelectorAll('.geo-address-suggestions').forEach((menu) => {
            if (!menu.parentElement.contains(event.target)) menu.hidden = true;
        });
    });
})();
</script>
