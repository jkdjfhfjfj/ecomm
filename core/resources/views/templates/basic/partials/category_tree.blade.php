<ul class="child-category">
    @php
        $prefix .= '-';
    @endphp
    @foreach ($subcategories as $subcategory)
        @php
            $subcategoryName = strtolower(trim((string) $subcategory->name));
            $subcategorySlug = strtolower(trim((string) $subcategory->slug));
        @endphp
        @continue(str_contains($subcategoryName, 'daily offer') || str_contains($subcategoryName, 'daily deal') || str_contains($subcategorySlug, 'daily-offer') || str_contains($subcategorySlug, 'daily-deal'))
        <li>
            <div class="text-nowrap">
                @if ($prefix != '-')
                    <span class="prefix">{{ $prefix }}</span>
                @else
                    @if ($subcategory->icon)
                        <span class="icon">
                            <img src="{{ $subcategory->categoryIcon() }}" alt="category-icon" class="icon-img">
                        </span>
                    @endif
                @endif

                <a href="{{ $subcategory->shopLink() }}" @if (Str::length(__($subcategory->name)) > 25) title="{{ __($subcategory->name) }}" @endif class="category-name">{{ strLImit(__($subcategory->name), 25) }}</a>
            </div>
            @include('Template::partials.category_tree', ['subcategories' => $subcategory->allSubcategories, 'prefix' => $prefix])
        </li>
    @endforeach
</ul>
