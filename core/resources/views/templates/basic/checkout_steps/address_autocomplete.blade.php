<style>
    .geo-address-suggestions { position:absolute; z-index:1100; inset-inline:0; top:100%; max-height:18rem; overflow:auto; padding:.35rem; border:1px solid #ddd; border-radius:.5rem; background:#fff; box-shadow:0 10px 28px #0002; }
    .geo-address-suggestions button { display:block; width:100%; padding:.65rem .75rem; border:0; border-radius:.35rem; background:transparent; text-align:left; color:#222; }
    .geo-address-suggestions button:hover,.geo-address-suggestions button:focus { background:#f3f4f2; }
</style>
<script>
(() => {
    const endpoint = @json(route('checkout.address.suggestions'));
    let timer;
    let controller;
    document.addEventListener('input', (event) => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || input.name !== 'address') return;
        clearTimeout(timer);
        controller?.abort();
        const wrapper = input.parentElement;
        wrapper.style.position = 'relative';
        let menu = wrapper.querySelector('.geo-address-suggestions');
        if (!menu) {
            menu = document.createElement('div');
            menu.className = 'geo-address-suggestions';
            menu.setAttribute('role', 'listbox');
            menu.hidden = true;
            wrapper.append(menu);
        }
        const text = input.value.trim();
        if (text.length < 3) { menu.hidden = true; return; }
        timer = setTimeout(async () => {
            controller = new AbortController();
            try {
                const url = new URL(endpoint, window.location.origin);
                url.searchParams.set('text', text);
                const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
                if (!response.ok) return;
                const { suggestions = [] } = await response.json();
                menu.replaceChildren();
                suggestions.forEach((place) => {
                    const option = document.createElement('button');
                    option.type = 'button';
                    option.setAttribute('role', 'option');
                    option.textContent = place.label;
                    option.addEventListener('click', () => {
                        input.value = place.address || place.label;
                        const form = input.closest('form') || document;
                        ['city', 'state', 'zip'].forEach((name) => {
                            const field = form.querySelector(`[name="${name}"]`);
                            if (field && place[name]) field.value = place[name];
                        });
                        const country = form.querySelector('[name="country"]');
                        if (country && place.country) {
                            const option = [...country.options].find((item) => item.value.toLowerCase() === place.country.toLowerCase());
                            if (option) { country.value = option.value; country.dispatchEvent(new Event('change', { bubbles: true })); }
                        }
                        menu.hidden = true;
                    });
                    menu.append(option);
                });
                menu.hidden = suggestions.length === 0;
            } catch (error) {
                if (error.name !== 'AbortError') menu.hidden = true;
            }
        }, 300);
    });
    document.addEventListener('click', (event) => {
        document.querySelectorAll('.geo-address-suggestions').forEach((menu) => {
            if (!menu.parentElement.contains(event.target)) menu.hidden = true;
        });
    });
})();
</script>
