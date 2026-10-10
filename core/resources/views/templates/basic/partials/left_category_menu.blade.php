@php
    if ($limit) {
        $parentCategories = $parentCategories->take($limit);
    }
@endphp

<div class="left-site-category active vm-category-navigation">
    <div class="vm-category-navigation__heading">
        <span>@lang('Discover')</span>
        <strong>@lang('Shop by category')</strong>
    </div>
    <div class="category-dropdown">
        <ul class="list-inline mega-menu vertical-megamenu" aria-label="@lang('Shop by category')">
            @foreach ($parentCategories as $category)
                <li class="fluid-menu {{ $category->allSubcategories->count() && !multiLayerCategory($category) ? 'dropdown  has-submenu' : '' }}">
                    <a href="{{ $category->shopLink() }}" class="nav-link menu-item @if ($category->allSubcategories->count()) dropdown has-submenu @endif">
                        @if ($category->icon)
                            <span class="menu-item-icon">
                                <img src="{{ $category->categoryIcon() }}" alt="" width="22" height="22" loading="lazy">
                            </span>
                        @endif
                        <span class="vm-category-navigation__label">{{ __($category->name) }}</span>

                        <span class="menu-item-arrowicon">
                            <i class="las la-angle-right"></i>
                        </span>
                    </a>

                    @if ($category->allSubcategories->count())
                        @if (multiLayerCategory($category))
                            <ul class="list-inline categories__mega-menu-wrap categories__mega-menu d-none d-lg-block">
                                <li>
                                    <div class="categories__mega-menu-content">
                                        @foreach ($category->allSubcategories as $subcategory)
                                            <div class="categories__mega-menu-list">
                                                <h5 class="categories__mega-menu-title">
                                                    <a href="{{ $subcategory->shopLink() }}">{{ __($subcategory->name) }}</a>
                                                </h5>

                                                @if ($subcategory->allSubcategories)
                                                    <ul class="list-inline cate__mega-menu-list">
                                                        @foreach ($subcategory->allSubcategories as $subCat)
                                                            <li><a href="{{ $subCat->shopLink() }}">{{ __($subCat->name) }}</a>
                                                                @if ($subCat->allSubcategories)
                                                                    <ul class="categories__mega-submenu">
                                                                        @foreach ($subCat->allSubcategories as $childCategory)
                                                                            @include('Template::partials.subcategory_list', [
                                                                                'childCategory' => $childCategory,
                                                                            ])
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </li>
                            </ul>
                        @else
                            <ul class="list-inline sub-menu">
                                @foreach ($category->allSubcategories as $subCat)
                                    <li><a href="{{ $subCat->shopLink() }}">{{ __($subCat->name) }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</div>

@once
    @push('style')
        <style>
            .vm-category-navigation{overflow:visible!important;border:1px solid #e8eaf0;border-radius:1.15rem;background:#fff;box-shadow:0 14px 34px #1420330d;padding:.45rem}
            .vm-category-navigation__heading{display:flex;flex-direction:column;gap:.15rem;padding:.9rem 1rem .7rem;border-bottom:1px solid #edf0f3}
            .vm-category-navigation__heading span{text-transform:uppercase;letter-spacing:.11em;font-size:.65rem;font-weight:700;color:#7b8794}
            .vm-category-navigation__heading strong{font-size:1rem;color:#182230}
            .vm-category-navigation .category-dropdown{padding:.35rem}
            .vm-category-navigation .vertical-megamenu>li{margin:0!important}
            .vm-category-navigation .vertical-megamenu>li>.menu-item{display:flex;align-items:center;gap:.75rem;min-height:44px;padding:.55rem .7rem!important;border-radius:.65rem;color:#344054;font-weight:550;transition:background .16s ease,color .16s ease}
            .vm-category-navigation .vertical-megamenu>li>.menu-item:hover,.vm-category-navigation .vertical-megamenu>li>.menu-item:focus-visible{background:#f3f6fa;color:#172f4d}
            .vm-category-navigation .menu-item-icon{display:grid;place-items:center;width:30px;height:30px;flex:0 0 30px;border-radius:.55rem;background:#f5f7f9}
            .vm-category-navigation .menu-item-icon img{width:20px;height:20px;object-fit:contain}
            .vm-category-navigation__label{flex:1}
            .vm-category-navigation .menu-item-arrowicon{margin-inline-start:auto;color:#98a2b3}
        </style>
    @endpush
@endonce
