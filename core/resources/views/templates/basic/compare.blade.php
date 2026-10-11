@extends('Template::layouts.master')
@section('content')
    <div class="compare-section py-60">
        <div class="container">
            <div class="compare-section-wrapper">
                <div class="compare-section-content">
                    @if ($compareProducts->count() >= 2)
                        <table class="table comparison-table count-{{ $compareProducts->count() + 1 }}">
                            <tbody>
                                <tr class="comparison-header">
                                    <td class="compare-blurb name">
                                        <div class="compare-top-head">
                                            <h4 class="page-heading">{{ __($pageTitle) }}</h4>
                                            {{ $compareProducts->count() }} {{ Str::plural('product', $compareProducts->count()) }} @lang('selected')
                                            <form action="{{ route('compare.remove') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-danger fw-500">@lang('Remove All')</button>
                                            </form>
                                        </div>
                                    </td>
                                    @foreach ($compareProducts as $compareProduct)
                                        <td class="value">
                                            <div class="compare-item-wrapper">
                                                <form action="{{ route('compare.remove', $compareProduct->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="compare-item-btn"> <i class="las la-trash"></i> </button>
                                                </form>
                                                <div class="compare-product-img">
                                                    <img src="{{ $compareProduct->mainImage(false) }}" alt="img">
                                                </div>
                                                <a class="compare-product-name" href="{{ $compareProduct->link() }}">
                                                    <span>{{ __($compareProduct->name) }}</span></a>
                                                <h5 class="compare-product-price">
                                                    @php echo $compareProduct->formattedPrice() @endphp
                                                </h5>

                                                @if (gs('product_review'))
                                                    <div class="ratings-area">
                                                        <span class="ratings">
                                                            @php echo displayRating($compareProduct->reviews_avg_rating) @endphp
                                                    </div>
                                                    <p class="rating-text mb-0">@lang("Based on $compareProduct->reviews_count reviews.")</p>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>

                            </tbody>
                        </table>

                        @if ($comparisonRows->isNotEmpty())
                            <table class="table comparison-table count-{{ $compareProducts->count() + 1 }} border-top-none">
                                <tbody>
                                    @foreach ($comparisonRows as $row)
                                        <tr>
                                            <td class="name">{{ __($row['label']) }}</td>
                                            @foreach ($compareProducts as $compareProduct)
                                                <td class="value">{{ $row['values'][$compareProduct->id] ?? '—' }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        @foreach ($specificationTemplate->specifications ?? [] as $groupSpecification)
                            <table class="table comparison-table count-{{ $compareProducts->count() + 1 }} border-top-none">
                                <thead class="sliding-header">
                                    <tr>
                                        <td colspan="2">
                                            <i class="material-icons las la-angle-down"></i>
                                            <strong>{{ __($groupSpecification['group_name']) }}</strong>
                                        </td>
                                        @for ($i = 0; $i < $compareProducts->count(); $i++)
                                            <td></td>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody class="sliding-body">
                                    @foreach ($groupSpecification['attributes'] ?? [] as $singleAttribute)
                                        <tr>
                                            <td class="name">{{ $singleAttribute }}</td>
                                            @foreach ($compareProducts as $compareProduct)
                                                <td class="value">
                                                    {{ collect($compareProduct->specification)->where('key', $singleAttribute)?->first()->value ?? '---' }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endforeach
                    @else
                        <div class="empty-message text-center">
                            <div class="empty-thumb">
                                <img src="{{ asset('assets/images/empty_list.png') }}" alt="image">
                            </div>
                            <h5 class="mt-3 text-muted">
                                {{ $compareProducts->count() === 1 ? __('One product selected') : __('Your comparison is empty') }}
                            </h5>
                            <p class="message">
                                @if ($compareProducts->count() === 1)
                                    @lang('Add one more product to compare it with') <strong>{{ $compareProducts->first()->name }}</strong>.
                                @else
                                    @lang('Add at least two products using the compare button on product cards.')
                                @endif
                            </p>
                            <a href="{{ route('product.all') }}" class="btn btn--base mt-3">@lang('Browse products')</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            'use strict';
            $('.sliding-header').on('click', function() {
                $(this).parents('.comparison-table').find('.sliding-body').toggle();
            });
        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .empty-message img {
            max-width: 100px;
        }
        .empty-message{max-width:620px;margin:auto;padding:2rem 1rem;border:1px solid #e8eaf0;border-radius:1rem;background:#fff}
    </style>
@endpush
