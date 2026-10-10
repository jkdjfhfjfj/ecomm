@extends('admin.layouts.app')

@section('panel')
    <form action="{{ route('admin.offer.store', $offer->id ?? 0) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row gy-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">@lang('General Information')</h5>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-md-3">
                                        <label>@lang('Banner') </label>
                                    </div>
                                    <div class="col-md-9">
                                        <x-image-uploader name="banner" type="offerBanner" :image="@$offer->banner" :showInfo="true" :required="false" />
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-3">
                                        <label>@lang('Offer Name') </label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" name="offer_name" value="{{ old('offer_name', @$offer->name) }}">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-3"><label for="banner_title">@lang('Banner Headline')</label></div>
                                    <div class="col-md-9">
                                        <input id="banner_title" type="text" class="form-control" name="banner_title" maxlength="120" value="{{ old('banner_title', @$offer->banner_title) }}" placeholder="{{ @$offer->name ?: __('Limited-time offer') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-3"><label for="banner_message">@lang('Banner Message')</label></div>
                                    <div class="col-md-9">
                                        <textarea id="banner_message" class="form-control" name="banner_message" rows="3" maxlength="1000" placeholder="@lang('Add the offer details shoppers should see')">{{ old('banner_message', @$offer->banner_message) }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-3"><label for="banner_button_text">@lang('Button Text')</label></div>
                                    <div class="col-md-9">
                                        <input id="banner_button_text" type="text" class="form-control" name="banner_button_text" maxlength="40" value="{{ old('banner_button_text', @$offer->banner_button_text) }}" placeholder="@lang('Shop offer')">
                                        <small class="text-muted">@lang('Shown on the homepage banner. The button links to this offer.')</small>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-3">
                                        <label>@lang('Campaign Type')</label>
                                    </div>
                                    <div class="col-md-9">
                                        <select class="form-control" name="campaign_type" required>
                                            @foreach (['standard' => 'Standard Offer', 'daily' => 'Daily Offer', 'flash' => 'Flash Sale', 'clearance' => 'Stock Clearance'] as $type => $label)
                                                <option value="{{ $type }}" @selected(old('campaign_type', @$offer->campaign_type ?? 'standard') === $type)>{{ __($label) }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">@lang('Daily offers appear on the homepage with an end-time countdown. Flash sales and stock-clearance campaigns appear in the offers area.')</small>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-3">
                                        <label>@lang('Discount Type')</label>
                                    </div>
                                    <div class="col-md-9">
                                        <select class="form-control" name="discount_type" required>
                                            <option value="" selected hidden>@lang('Select One')</option>
                                            <option value="{{ Status::DISCOUNT_FIXED }}">@lang('Flat')</option>
                                            <option value="{{ Status::DISCOUNT_PERCENT }}">@lang('Percentage')</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-3">
                                        <label>@lang('Amount')</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <input type="number" step="any" class="form-control" name="amount" value="{{ old('amount', @$offer->amount) }}" required>
                                            <span class="input-group-text" id="amountType"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-3">
                                        <label for="starts_from">@lang('Starts From')</label>
                                    </div>

                                    <div class="col-md-9">
                                        <input type="text" name="starts_from" class="form-control" value="{{ old('starts_from') }}" autocomplete="off">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-3">
                                        <label for="ends_at">@lang('Ends At')</label>
                                    </div>

                                    <div class="col-md-9">
                                        <input type="text" name="ends_at" class="form-control" value="{{ old('ends_at') }}" autocomplete="off">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-3">
                                        <label>@lang('Show Banner')</label>
                                        <small><i class="la la-info-circle text-muted" title="@lang('The banner appears on the homepage while this offer is active.')"></i></small>
                                    </div>
                                    <div class="col-md-9">
                                        <x-toggle-switch name="show_banner" :checked="old('show_banner', $offer->show_banner ?? true)" value="1" />
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-3">
                                        <label>@lang('Show Countdown')</label>
                                        <small><i class="la la-info-circle text-muted" title="@lang('The countdown will be shown on frontend if enabled.')"></i></small>
                                    </div>
                                    <div class="col-md-9">
                                        <x-toggle-switch name="show_countdown" :checked="old('show_countdown', in_array(old('campaign_type', $offer->campaign_type ?? 'standard'), ['daily', 'flash'], true) || ($offer->show_countdown ?? false))" value="1" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">

                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <h5 class="card-title mb-0">@lang('Products')</h5>
                        <button type="button" class="btn btn--primary addMoreProduct"><i class="la la-plus"></i>@lang('Add Products')</button>
                    </div>
                    <div class="card-body has-select2">

                        <div class="alert alert-info">
                            <div class="alert-body p-3">
                                <small class="text--info"><i class="la la-info-circle"></i> @lang('If a product has both a sale price and is part of an offer, the discount from the offer will still be applied on top of the sale price. If the product is already part of a previous offer, it will automatically be replaced by the new offer.')</small>

                            </div>
                        </div>


                        <x-product-picker :products="@$offer?->products" :url="route('admin.offer.products')" />

                        <small class="text-muted flat-discount-info"><i class="la la-info-circle"></i> @lang('Select products carefully when the discount type is set to FLAT. If the discount amount is equal to or greater than the product price, the sale price will be 0.')</small>



                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
            </div>
        </div>

    </form>

    @stack('modal')
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.offer.index') }}"></x-back>
@endpush

@push('style-lib')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict';
        (function($) {
            const discountTypeField = $('[name=discount_type]');
            const startsFromField = $('[name=starts_from]');
            const endsAtField = $('[name=ends_at]');
            const discountType = `{{ old('discount_type', @$offer->discount_type) }}`;

            startsFromField.daterangepicker({
                timePicker: true,
                singleDatePicker: true,
                drops: 'auto',
                locale: {
                    format: 'YYYY-MM-DD hh:mm A'
                }
            });

            endsAtField.daterangepicker({
                timePicker: true,
                singleDatePicker: true,
                drops: 'auto',
                locale: {
                    format: 'YYYY-MM-DD hh:mm A'
                }
            });

            @if (isset($offer))
                let startDate = @json(showDateTime($offer->starts_from, 'Y-m-d h:i A'));
                let endDate = @json(showDateTime($offer->ends_at, 'Y-m-d h:i A'));

                startsFromField.data('daterangepicker').setStartDate(startDate);
                startsFromField.data('daterangepicker').setEndDate(startDate);
                endsAtField.data('daterangepicker').setStartDate(endDate);
                endsAtField.data('daterangepicker').setEndDate(endDate);
            @endif

            discountTypeField.val(discountType);

            const discountTypeChangeHandler = (value) => {
                $('.flat-discount-info').hide();
                if (discountTypeField.val() == '{{ Status::DISCOUNT_FIXED }}') {
                    $('#amountType').text(`{{ gs('cur_text') }}`);
                    $('.flat-discount-info').show();
                } else if (discountTypeField.val() == '{{ Status::DISCOUNT_PERCENT }}') {
                    $('.flat-discount-info').hide();
                    $('#amountType').text(`%`);
                }
            }

            discountTypeChangeHandler();
            discountTypeField.on('change', () => discountTypeChangeHandler());

        })(jQuery)
    </script>
@endpush

@push('style')
    <style>
        .image--uploader,
        .image-upload-wrapper {
            width: 250px;
        }
    </style>
@endpush
