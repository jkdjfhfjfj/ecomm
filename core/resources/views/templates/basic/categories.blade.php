@extends('Template::layouts.master')

@section('content')
    <main class="category-directory">
        <div class="container">
            <header class="category-directory-heading">
                <span class="category-directory-kicker">@lang('Browse Visermart')</span>
                <h1>@lang('Shop by category')</h1>
                <p>@lang('Start with a department, then explore the collections inside.') </p>
            </header>
            <div class="category-tree">
                <div class="category-directory-grid">
                    @forelse ($categories as $category)
                        <section class="category-tree-item">
                            <div class="parent-category">
                                <a href="{{ $category->shopLink() }}" class="category-parent-card" aria-label="@lang('Shop') {{ __($category->name) }}">
                                    <span class="category-parent-card__image">
                                        <span class="category-parent-card__fallback" aria-hidden="true"><i class="las la-layer-group"></i></span>
                                        @if (filter_var($category->external_image_url, FILTER_VALIDATE_URL) || filled($category->image))
                                            <img src="{{ $category->categoryImage() }}" alt="{{ __($category->name) }}" loading="lazy" decoding="async" onerror="this.remove()">
                                        @endif
                                    </span>
                                    <span class="category-parent-card__title">{{ __($category->name) }}</span>
                                </a>
                            </div>

                            @if (!blank($category->allSubcategories))
                                @include('Template::partials.category_tree', ['subcategories' => $category->allSubcategories, 'prefix' => ''])
                            @endif
                        </section>

                    @empty
                        <x-dynamic-component :component="frontendComponent('empty-message')" message="No category found" />
                    @endforelse
                </div>
            </div>
        </div>
    </main>
@endsection


@push('style-lib')
    <link rel="stylesheet" href="{{ asset(activeTemplate(true). 'css/categories-page.css') }}">
@endpush

@push('style')
    <style>
        .category-directory { min-height:60vh; padding:clamp(28px,5vw,68px) 0; background:#f8f6f1; }
        .category-directory-heading { position:relative; padding:0 0 22px; margin:0 0 22px; border-bottom:1px solid var(--store-line,#e4dfd5); }
        .category-directory-kicker { color:var(--store-accent,#9b493d); font-size:.68rem; font-weight:750; letter-spacing:.17em; text-transform:uppercase; }
        .category-directory-heading h1 { margin:8px 0 6px; color:var(--store-ink,#252521); font-family:var(--store-display,Georgia,serif); font-size:clamp(2.25rem,5vw,4rem); font-weight:500; letter-spacing:-.045em; line-height:1.05; }
        .category-directory-heading p { max-width:560px; margin:0; color:var(--store-muted,#746f67); }
        .category-directory-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; align-items:start; }
        .category-tree-item { min-width:0; padding:17px; overflow:hidden; border:1px solid var(--store-line,#e4dfd5); border-radius:12px; background:#fffefa; transition:border-color .2s ease,box-shadow .2s ease; }
        .category-tree-item:hover { border-color:#d3cabe; box-shadow:0 10px 26px rgba(37,37,33,.06); }
        .category-tree-item .parent-category { overflow:hidden; }
        .category-parent-card { display:grid; grid-template-columns:88px minmax(0,1fr); align-items:center; gap:14px; color:var(--store-ink,#252521); text-decoration:none; }
        .category-parent-card:hover { color:var(--store-accent,#9b493d); }
        .category-parent-card__image { position:relative; display:grid; width:88px; aspect-ratio:1; place-items:center; overflow:hidden; border-radius:9px; background:#f1eee8; color:#9b493d; }
        .category-parent-card__fallback { display:grid; width:100%; height:100%; place-items:center; font-size:1.55rem; }
        .category-parent-card__image img { position:absolute; inset:0; display:block; width:100%; height:100%; object-fit:cover; transition:transform .35s ease; }
        .category-parent-card__title { font-size:1.12rem; font-weight:700; line-height:1.25; }
        .category-tree-item:hover .parent-category img { transform:scale(1.035); }
        .category-tree-item a { color:var(--store-ink,#252521); }
        .category-tree-item a:hover { color:var(--store-accent,#9b493d); }
        .category-tree-item>.child-category { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:7px 12px; padding:14px 0 0; margin:15px 0 0; border-top:1px solid #eee9e0; list-style:none; }
        .category-tree-item .child-category li { min-width:0; padding:0; }
        .category-tree-item .child-category li div { display:flex; align-items:center; gap:6px; margin:0; font-weight:600; }
        .category-tree-item .child-category .category-name { display:block; min-width:0; color:#504b44; font-size:.87rem; line-height:1.4; text-decoration:none; }
        .category-tree-item .child-category .category-name:hover { color:var(--store-accent,#9b493d)!important; }
        .category-tree-item .child-category .prefix { color:#b8afa2; }
        .category-tree-item .child-category .child-category { display:flex; flex-direction:column; gap:5px; padding-left:10px; margin:5px 0 2px 8px; border-left:1px solid #e7e0d6; }
        .category-tree-item .child-category .child-category li div { font-weight:400; }
        .category-tree-item .child-category .child-category .category-name { font-size:.81rem!important; }
        @media(max-width:767px) {
            .category-directory { padding:24px 0 38px; }
            .category-directory-heading { padding-bottom:18px; margin-bottom:16px; }
            .category-directory-grid { grid-template-columns:1fr; gap:10px; }
            .category-tree-item { padding:14px; border-radius:10px; }
            .category-parent-card { grid-template-columns:72px minmax(0,1fr); gap:12px; }
            .category-parent-card__image { width:72px; }
            .category-parent-card__title { font-size:1.02rem; }
            .category-tree-item>.child-category { gap:7px 9px; }
            .category-tree-item .child-category .category-name { font-size:.81rem; }
        }
    </style>
@endpush
