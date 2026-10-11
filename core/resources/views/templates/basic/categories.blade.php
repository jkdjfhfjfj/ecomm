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
                                <x-dynamic-component :component="frontendComponent('category-card')" :category="$category" />
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
        .category-directory { min-height:60vh; padding:clamp(30px,5vw,72px) 0; }
        .category-directory-heading { position:relative; padding:0 0 28px; margin:0 0 28px; border-bottom:1px solid var(--store-line,#e4dfd5); }
        .category-directory-kicker { color:var(--store-accent,#9b493d); font-size:.7rem; font-weight:700; letter-spacing:.17em; text-transform:uppercase; }
        .category-directory-heading h1 { margin:8px 0 6px; color:var(--store-ink,#252521); font-family:var(--store-display,Georgia,serif); font-size:clamp(2.4rem,5vw,4.2rem); font-weight:500; letter-spacing:-.045em; line-height:1.05; }
        .category-directory-heading p { max-width:510px; margin:0; color:var(--store-muted,#746f67); }
        .category-directory-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; align-items:start; }
        .category-tree-item { min-width:0; padding:16px; border:1px solid var(--store-line,#e4dfd5); background:#fffefa; }
        .category-tree-item .parent-category { overflow:hidden; }
        .category-tree-item .parent-category img { transition:transform .35s ease; }
        .category-tree-item:hover .parent-category img { transform:scale(1.025); }
        .category-tree-item a { color:var(--store-ink,#252521); }
        .category-tree-item a:hover { color:var(--store-accent,#9b493d); }
        @media(max-width:767px) {
            .category-directory { padding:28px 0; }
            .category-directory-heading { padding-bottom:20px; margin-bottom:20px; }
            .category-directory-grid { grid-template-columns:1fr; gap:12px; }
            .category-tree-item { padding:12px; }
        }
    </style>
@endpush
