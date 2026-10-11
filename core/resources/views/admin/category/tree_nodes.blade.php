<li id="{{ $subcategory->id }}" data-category-id="{{ $subcategory->id }}">
    <span class="category-tree-node-label">{{ __($subcategory->name) }}</span>
    <span class="category-tree-product-count" title="@lang('Products in this category')" aria-label="{{ number_format($subcategory->products_count ?? 0) }} @lang('products')">{{ number_format($subcategory->products_count ?? 0) }}</span>

    @if ($subcategory->allSubcategories->count() > 0)
        <ul>
            @foreach ($subcategory->allSubcategories as $childCategory)
                @include('admin.category.tree_nodes', ['subcategory' => $childCategory])
            @endforeach
        </ul>
    @endif
</li>
