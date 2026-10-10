@if (isset($homeProducts) && $homeProducts->isNotEmpty())
    <section class="site-section my-60">
        <div class="container">
            <div class="section-header left-style">
                <h3 class="title">@lang('Latest Products')</h3>
            </div>
            <div class="row gy-4">
                @foreach ($homeProducts as $product)
                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="catalog-product-tile">
                            @if ($product->brand)
                                <span class="catalog-brand-tag">{{ __($product->brand->name) }}</span>
                            @endif
                            <x-dynamic-component :component="frontendComponent('product-card')" :product="$product" />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
