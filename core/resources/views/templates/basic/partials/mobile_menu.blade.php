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
            @include('Template::partials.menu.language_menu', ['drawer' => true])
        </div>
    </div>
    <div class="mobile-menu-body">
        <nav class="mobile-category-tree" aria-label="@lang('Shop by category')">
            <ul class="mobile-category-tree__roots">
                @foreach ($parentCategories as $category)
                    @include('Template::partials.menu.mobile_category_tree_item', ['category' => $category])
                @endforeach
                <li class="mobile-category-tree__item mobile-category-tree__brand-item">
                    <div class="mobile-category-tree__row">
                        <a href="{{ route('brands') }}">@lang('Shop by brand')</a>
                        <span aria-hidden="true"><i class="las la-arrow-right"></i></span>
                    </div>
                </li>
            </ul>
        </nav>
    </div>
    <nav class="mobile-menu-quick-links" aria-label="@lang('Main pages')">
        <a href="{{ route('home') }}"><i class="las la-home" aria-hidden="true"></i><span>@lang('Home')</span></a>
        <a href="{{ route('about') }}"><i class="las la-info-circle" aria-hidden="true"></i><span>@lang('About')</span></a>
        <a href="{{ route('contact') }}"><i class="las la-envelope" aria-hidden="true"></i><span>@lang('Contact us')</span></a>
    </nav>
</div>

@once
    @push('style')
        <style>
            .mobile-menu.sidebar-nav{display:flex!important;flex-direction:column;height:100dvh;padding:32px 0 0!important;overflow:hidden}
            .mobile-menu-header{flex:0 0 auto;padding:1rem 1.25rem .85rem;border-bottom:1px solid #eee9e1;background:#fbfaf7}
            .dropdown--lang-drawer{position:relative;width:100%}
            .dropdown--lang-drawer .language-selector-label{display:block;margin:0 0 .45rem;color:#81796d;font-size:.68rem;font-weight:750;letter-spacing:.12em;text-transform:uppercase}
            .dropdown--lang-drawer>.dropdown-toggle{display:flex;align-items:center;gap:.7rem;width:100%;min-height:52px;padding:.65rem .85rem;border:1px solid #e6e0d6;border-radius:12px;background:#fff;color:#252521;font-size:.95rem;font-weight:650;text-align:left;box-shadow:0 2px 8px rgba(37,37,33,.04)}
            .dropdown--lang-drawer>.dropdown-toggle::after{margin-left:auto;color:#81796d}
            .dropdown--lang-drawer .dropdown-flag{width:24px;height:24px;flex:0 0 24px;border-radius:50%;object-fit:cover}
            .dropdown--lang-drawer .language-selector-name{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
            .dropdown--lang-drawer>.dropdown-menu{position:absolute!important;top:calc(100% + .4rem)!important;right:0!important;left:0!important;z-index:1055;width:100%;max-height:min(42dvh,300px);margin:0;padding:.35rem;overflow-y:auto;transform:none!important;border:1px solid #e9e4dc;border-radius:12px;background:#fff;box-shadow:0 14px 32px rgba(31,29,25,.16)}
            .dropdown--lang-drawer .dropdown-item{display:flex;align-items:center;gap:.7rem;min-height:46px;padding:.6rem .7rem;border-radius:8px;color:#38352f;font-size:.92rem}
            .dropdown--lang-drawer .dropdown-item:hover,.dropdown--lang-drawer .dropdown-item:focus{background:#f6f2eb;color:#252521}
            .mobile-menu-body{flex:1 1 auto;min-height:0;overflow-y:auto;max-height:none!important;padding:1rem 1.25rem}
            .mobile-menu-quick-links{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr));gap:.25rem;flex:0 0 auto;padding:.55rem .7rem calc(.55rem + env(safe-area-inset-bottom));margin:auto 0 0;border-top:1px solid #e9e5dc;border-bottom:0;background:#fbfaf7}
            .mobile-menu-quick-links a{display:flex!important;flex-direction:column;align-items:center;justify-content:center;gap:.2rem;min-width:0;min-height:52px;padding:.35rem .2rem;margin:0;border:0;border-radius:0;background:transparent;color:#39362f;font-size:.74rem;font-weight:650;text-decoration:none}
            .mobile-menu-quick-links a i{display:block!important;visibility:visible!important;opacity:1!important;color:#9b493d;font-size:1.2rem;line-height:1}
            .mobile-category-tree{border:0;border-radius:0;background:transparent;padding:0}
            .mobile-category-tree__heading{display:flex;align-items:baseline;justify-content:space-between;gap:.75rem;padding:.35rem 0 .85rem;border-bottom:1px solid #e9e5dc}
            .mobile-category-tree__heading strong{font-size:1.05rem;color:#252521}
            .mobile-category-tree__roots,.mobile-category-tree__children{list-style:none;margin:0;padding:0}
            .mobile-category-tree__item{margin:0}
            .mobile-category-tree__row{display:flex;align-items:center;gap:.4rem;min-height:48px;border-bottom:1px solid #eeeae3}
            .mobile-category-tree__row>a{flex:1;display:block;padding:.65rem .4rem;color:#252521;font-size:.93rem;font-weight:600;text-decoration:none}
            .mobile-category-tree__brand-item .mobile-category-tree__row{margin-top:4px;border-top:1px solid #eeeae3}
            .mobile-category-tree__brand-item .mobile-category-tree__row>a{color:#9b493d}
            .mobile-category-tree__brand-item .mobile-category-tree__row>span{padding:0 .5rem;color:#9b493d}
            .mobile-category-tree__toggle{width:32px;height:32px;border:0;border-radius:50%;background:transparent;color:#746f67}
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
