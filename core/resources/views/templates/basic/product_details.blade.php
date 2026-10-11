@extends('Template::layouts.master')

@section('content')
    <div class="py-60 storefront-product-page">
        <div class="container">
            <nav class="product-crumbs" aria-label="@lang('Breadcrumb')">
                <a href="{{ route('home') }}">@lang('Home')</a>
                <span aria-hidden="true">/</span>
                @if ($product->categories->isNotEmpty())
                    <a href="{{ $product->categories->first()->shopLink() }}">{{ __($product->categories->first()->name) }}</a>
                    <span aria-hidden="true">/</span>
                @endif
                <span aria-current="page">{{ __($product->name) }}</span>
            </nav>
            <div class="row g-4 g-xl-5">
                <div class="col-xl-9">
                    @include('Template::partials.quick_view')
                    @php
                        $description = preg_replace('/<\/?br>/', '', $product->description, 1);
                        $videoUrls = collect([$product->video_link])
                            ->merge($product->externalVideoUrls())
                            ->filter(fn($url) => is_string($url) && in_array(parse_url($url, PHP_URL_SCHEME), ['https', 'http'], true))
                            ->unique()
                            ->map(function ($url) {
                                $host = strtolower((string) parse_url($url, PHP_URL_HOST));
                                if (str_contains($host, 'vimeo.com') && !str_contains($host, 'player.vimeo.com')) {
                                    $id = trim((string) parse_url($url, PHP_URL_PATH), '/');
                                    return $id !== '' ? 'https://player.vimeo.com/video/' . $id : $url;
                                }
                                if (str_contains($host, 'youtube.com') && str_contains($url, 'watch?')) {
                                    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
                                    return !empty($query['v']) ? 'https://www.youtube-nocookie.com/embed/' . $query['v'] : $url;
                                }
                                if (str_contains($host, 'youtu.be')) {
                                    $id = trim((string) parse_url($url, PHP_URL_PATH), '/');
                                    return $id !== '' ? 'https://www.youtube-nocookie.com/embed/' . $id : $url;
                                }
                                return $url;
                            });
                    @endphp
                    @php
                        $productSchema = [
                            '@context' => 'https://schema.org',
                            '@type' => 'Product',
                            'name' => $product->name,
                            'description' => trim(preg_replace('/\s+/', ' ', strip_tags((string) $product->description))),
                            'image' => array_values(array_filter(array_unique(array_merge(
                                [$product->external_image_url, $product->mainImage(false)],
                                $product->externalGalleryUrls()
                            )))),
                            'sku' => $product->sku ?: (string) $product->external_id,
                            'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
                            'offers' => [
                                '@type' => 'Offer',
                                'url' => $product->link(),
                                'priceCurrency' => gs('cur_text'),
                                'price' => (string) $product->price,
                                'availability' => $product->inStock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                            ],
                        ];
                    @endphp
                    <script type="application/ld+json">@php echo json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); @endphp</script>

                    @if ($product->specification || $description || $videoUrls->isNotEmpty() || gs('product_review'))
                        <div class="products-details-wrapper pt-60">
                            <div class="products-description pt-0">
                                <ul class="nav nav-tabs" id="productTabs">
                                    @if ($product->specification)
                                        <li><a href="#specification" data-bs-toggle="tab">@lang('Specification')</a></li>
                                    @endif
                                    @if ($product->attributeValues->isNotEmpty())
                                        <li><a href="#attributes" data-bs-toggle="tab">@lang('Attributes')</a></li>
                                    @endif

                                    @if ($description)
                                        <li><a href="#description" data-bs-toggle="tab">@lang('Description')</a></li>
                                    @endif

                                    @if ($videoUrls->isNotEmpty())
                                        <li><a href="#video" data-bs-toggle="tab">@lang('Video')</a></li>
                                    @endif

                                    @if (gs('product_review'))
                                        <li class="review-rating-tab"><a href="#reviews"
                                                data-bs-toggle="tab">@lang('Reviews')({{ __($product->reviews_count) }})</a>
                                        </li>
                                    @endif
                                </ul>

                                <div class="tab-content">
                                    @if ($product->specification)
                                        <div class="tab-pane fade" id="specification">
                                            <div class="specification-wrapper">
                                                <div class="specification-table d-flex flex-column">
                                                    @if ($product->productType)
                                                        @foreach ($product->productType->specifications as $specificationGroup)
                                                            @if (collect($product->specification)->whereIn('key', $specificationGroup['attributes'])->whereNotNull('value')->count())
                                                                <div>
                                                                    <h6 class="mb-2">{{ __($specificationGroup['group_name']) }}</h6>
                                                                    <ul>
                                                                        @foreach ($specificationGroup['attributes'] ?? [] as $attribute)
                                                                            @php($specification = collect($product->specification)->firstWhere('key', $attribute))
                                                                            @if (data_get($specification, 'value'))
                                                                                <li><span>{{ __($attribute) }}</span><span>{{ data_get($specification, 'value') }}</span></li>
                                                                            @endif
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <ul>
                                                            @foreach (collect($product->specification) as $fieldKey => $specification)
                                                                @php($key = data_get($specification, 'key', $fieldKey))
                                                                @php($value = data_get($specification, 'value', $specification))
                                                                @if (is_scalar($key) && is_scalar($value) && trim((string) $value) !== '')
                                                                    <li><span>{{ __((string) $key) }}</span><span>{{ (string) $value }}</span></li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($product->attributeValues->isNotEmpty())
                                        <div class="tab-pane fade" id="attributes">
                                            <div class="specification-wrapper">
                                                <ul>
                                                    @foreach ($product->attributeValues as $attributeValue)
                                                        @php($attribute = $product->attributes->firstWhere('id', $attributeValue->attribute_id))
                                                        @php($value = $attributeValue->value ?: $attributeValue->name)
                                                        @if ($value)
                                                            <li>
                                                                <span>{{ $attribute?->name ?? __('Option') }}</span>
                                                                <span>{{ $value }}</span>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($description || $product->extra_descriptions)
                                        <div class="tab-pane fade" id="description">

                                            @if ($description)
                                                <div class="description-item">
                                                    @php echo $product->description @endphp
                                                </div>
                                            @endif

                                            @if ($product->extra_descriptions)
                                                <div class="description-item mt-5">
                                                    @foreach ($product->extra_descriptions as $description)
                                                        <h4>{{ __(@$description['key']) }}</h4>
                                                        <p>
                                                            @php
                                                                echo @$description['value'];
                                                            @endphp
                                                        </p>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($videoUrls->isNotEmpty())
                                        <div class="tab-pane fade" id="video">
                                            @foreach ($videoUrls as $videoUrl)
                                                <iframe class="product-details-video mb-3" src="{{ $videoUrl }}"
                                                    allow="autoplay; encrypted-media" allowfullscreen></iframe>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if (gs('product_review'))
                                        <div class="tab-pane fade" id="reviews">
                                            <div class="review-area"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($otherProducts->count() > 0)
                    <div class="col-md-12 col-xl-3">
                        <div class="sticky-sidebar">
                            <h5 class="product-details-title fw-500 mb-3">{{ __($otherProductsTitle) }}</h5>
                            <div class="row gy-3">
                                @foreach ($otherProducts as $relatedProduct)
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-12">
                                        <div class="best-sell-item">
                                            <div class="best-sell-inner d-flex flex-wrap">
                                                <div class="thumb">
                                                    <a href="{{ $relatedProduct->link() }}">
                                                        <img src="{{ getImage(null) }}"
                                                            data-src="{{ $relatedProduct->mainImage() }}" class="lazyload"
                                                            alt="products">
                                                    </a>
                                                </div>
                                                <div class="content">
                                                    <h6 class="title">
                                                        <a href="{{ $relatedProduct->link() }}">{{ __($relatedProduct->name) }}</a>
                                                    </h6>

                                                    @if (gs('product_review'))
                                                        <div class="ratings-area">
                                                            <span class="ratings">
                                                                @php echo __(displayRating($relatedProduct->reviews_avg_rating)) @endphp
                                                            </span>
                                                            @if ($relatedProduct->reviews_count)
                                                                <span>({{ $relatedProduct->reviews_count }})</span>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    <div class="price fw-500">
                                                        @php
                                                            echo $relatedProduct->formattedPrice();
                                                        @endphp
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


@push('style-lib')
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/product-details.css') }}">
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/xzoom/xzoom.css') }}">
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/xzoom/magnific-popup.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset(activeTemplate(true) . 'js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/owl-carousel.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/xzoom/xzoom.min.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/xzoom/magnific-popup.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/xzoom/setup.js') }}"></script>
@endpush


@push('script')
    <script>
        "use strict";

        @if (gs('product_review'))
            loadReviews(`{{ route('product.reviews', $product->id) }}`);
        @endif

        const firstTabLink = document.querySelector('#productTabs a');

        if (firstTabLink) {
            const firstTab = new bootstrap.Tab(firstTabLink);
            firstTab.show();
        }

        $('.review-rating-tab').on("click", function() {
            $('.review-gallery').each(function() {
                $(this).magnificPopup({
                    delegate: 'a',
                    type: 'image',
                    gallery: {
                        enabled: true
                    }
                });
            });
        });
    </script>
@endpush
