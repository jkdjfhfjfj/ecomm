<ul class="list list--row mobile-menu-icons justify-content-center justify-content-md-end option-list d-lg-none d-flex">
    <li>
        <a href="{{ route('categories') }}" class="ecommerce" id="cate-button">
            <span class="ecommerce__icon">
                <img src="{{ svg('category') }}" alt="">
            </span>
            <span class="ecommerce__text">@lang('Category')</span>
        </a>
    </li>

    @if (gs('product_wishlist'))
        <li>
            <a href="javascript:void(0)" class="ecommerce wish-button">
                <span class="ecommerce__icon">
                    <img src="{{ svg('wishlist') }}" alt="">
                    <span class="ecommerce__is wishlist-count d-none"></span>
                </span>
                <span class="ecommerce__text">@lang('Wishlist')</span>
            </a>
        </li>
    @endif

    @if (gs('product_compare'))
        <li>
            <a href="{{ route('compare.all') }}" class="ecommerce">
                <span class="ecommerce__icon">
                    <img src="{{ svg('compare') }}" alt="">
                    <span class="ecommerce__is compare-count d-none"></span>
                </span>
                <span class="ecommerce__text">@lang('Compare')</span>
            </a>
        </li>
    @endif

    <li>
        <a href="javascript:void(0)" class="ecommerce @auth user-account-btn @endauth" id="account-button" @guest data-bs-toggle="modal" data-bs-target="#loginModal" @endguest>
            <span class="ecommerce__icon">
                <img src="{{ svg('my_account') }}" alt="">
            </span>
            <span class="ecommerce__text">@lang('My Account')</span>
        </a>
    </li>
</ul>


<div class="site-sidebar mobile-menu sidebar-nav">
    <button type="button" class="sidebar-close-btn" aria-label="@lang('Close category menu')">
        <i class="las la-times"></i>
    </button>

    <div class="mobile-menu-header">
        <div class="d-block d-lg-none">
            @include('Template::partials.menu.language_menu')
        </div>
    </div>
    <div class="mobile-menu-body">
        <nav class="mobile-menu-quick-links" aria-label="@lang('Main pages')">
            <a href="{{ route('home') }}"><i class="las la-home" aria-hidden="true"></i><span>@lang('Home')</span></a>
            <a href="{{ route('about') }}"><i class="las la-info-circle" aria-hidden="true"></i><span>@lang('About')</span></a>
            <a href="{{ route('contact') }}"><i class="las la-envelope" aria-hidden="true"></i><span>@lang('Contact us')</span></a>
        </nav>
        <nav class="mobile-category-tree" aria-label="@lang('Shop by category')">
            <div class="mobile-category-tree__heading">
                <div>
                    <span>@lang('Browse')</span>
                    <strong>@lang('Shop by category')</strong>
                </div>
                <a href="{{ route('brands') }}"><i class="las la-tags" aria-hidden="true"></i> @lang('Shop by brand')</a>
            </div>
            <ul class="mobile-category-tree__roots">
                @foreach ($parentCategories as $category)
                    @include('Template::partials.menu.mobile_category_tree_item', ['category' => $category])
                @endforeach
            </ul>
        </nav>
    </div>
</div>

@once
    @push('style')
        <style>
            .mobile-menu-body{overflow-y:auto;max-height:calc(100dvh - 92px);padding:1rem 1rem calc(2rem + env(safe-area-inset-bottom))}
            .mobile-category-tree{border:1px solid #e8edf3;border-radius:16px;background:#fff;padding:.9rem}
            .mobile-category-tree__heading{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.2rem .2rem .85rem;border-bottom:1px solid #edf0f3}
            .mobile-category-tree__heading>div{display:grid;gap:.15rem}
            .mobile-category-tree__heading span{font-size:.65rem;letter-spacing:.1em;text-transform:uppercase;color:#7b8794;font-weight:700}
            .mobile-category-tree__heading strong{font-size:1rem;color:#182230}
            .mobile-category-tree__heading>a{padding:.55rem .7rem;border-radius:10px;background:#f3f6fa;color:#25364a;font-size:.78rem;font-weight:650;white-space:nowrap}
            .mobile-category-tree__roots,.mobile-category-tree__children{list-style:none;margin:0;padding:0}
            .mobile-category-tree__item{margin:0}
            .mobile-category-tree__row{display:flex;align-items:center;gap:.4rem;min-height:48px;border-bottom:1px solid #f0f2f5}
            .mobile-category-tree__row>a{flex:1;display:block;padding:.65rem .4rem;color:#26364a;font-size:.93rem;font-weight:600}
            .mobile-category-tree__toggle{width:34px;height:34px;border:0;border-radius:9px;background:#f3f6fa;color:#334155}
            .mobile-category-tree__children{margin-left:.7rem;padding-left:.65rem;border-left:1px solid #e6ebf1}
            .mobile-category-tree__children .mobile-category-tree__row>a{font-size:.88rem;font-weight:500}
        </style>
    @endpush
    @push('script')
        <script>
            document.addEventListener('click', function (event) {
                const button = event.target.closest('.mobile-category-tree__toggle');
                if (!button) return;
                const children = document.getElementById(button.getAttribute('aria-controls'));
                if (!children) return;
                const expanded = button.getAttribute('aria-expanded') === 'true';
                button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                children.hidden = expanded;
                button.setAttribute('aria-label', `${expanded ? 'Expand' : 'Collapse'} ${button.closest('.mobile-category-tree__row').querySelector('a').textContent.trim()}`);
                button.innerHTML = `<i class="las ${expanded ? 'la-plus' : 'la-minus'}" aria-hidden="true"></i>`;
            });
        </script>
    @endpush
@endonce
