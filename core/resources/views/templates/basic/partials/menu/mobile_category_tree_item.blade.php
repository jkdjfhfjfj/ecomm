<li class="mobile-category-tree__item">
    <div class="mobile-category-tree__row">
        <a href="{{ $category->shopLink() }}">{{ __($category->name) }}</a>
        @if ($category->allSubcategories->isNotEmpty())
            <button type="button" class="mobile-category-tree__toggle" aria-expanded="true"
                aria-controls="mobile-category-children-{{ $category->id }}" aria-label="@lang('Collapse') {{ __($category->name) }}">
                <i class="las la-minus" aria-hidden="true"></i>
            </button>
        @endif
    </div>
    @if ($category->allSubcategories->isNotEmpty())
        <ul class="mobile-category-tree__children" id="mobile-category-children-{{ $category->id }}">
            @foreach ($category->allSubcategories as $childCategory)
                @include('Template::partials.menu.mobile_category_tree_item', ['category' => $childCategory])
            @endforeach
        </ul>
    @endif
</li>
