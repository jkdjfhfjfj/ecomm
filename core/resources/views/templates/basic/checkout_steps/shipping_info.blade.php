@extends('Template::layouts.checkout')

@section('blade')
    @include('Template::partials.storefront_styles')
    <div class="address-wrapper">
        @foreach ($shippingAddresses as $item)
            @php
                $checkoutData = session('shipping_info');
                $guestShippingInfo = session('guest_shipping_info');
                if (@$checkoutData['shipping_address_id'] == $item->id) {
                    $isChecked = true;
                } elseif ($loop->first) {
                    $isChecked = true;
                } else {
                    $isChecked = false;
                }
            @endphp

            <label class="address-single" for="address-{{ $item->id }}">
                <div class="flex-fill">
                    <div class="address-item-left">
                        <div class="form--check d-inline-block">
                            <input class="form-check-input mt-0" type="radio" name="shipping_address_id" value="{{ $item->id }}" form="shipping-form" value="" id="address-{{ $item->id }}" @checked($isChecked)>
                        </div>
                        <h6>{{ $item->label }}</h6>
                    </div>
                    <div class="address-item-right">
                        <div class="address-item-inner">
                            <span class="address-item-label">@lang('Address')</span>
                            <span class="address-item-value"> <span class="item-devide">:</span> {{ $item->address }}</span>
                        </div>
                        <div class="address-item-inner">
                            <span class="address-item-label">@lang('Zip Code')</span>
                            <span class="address-item-value"> <span class="item-devide">:</span> {{ $item->zip }}</span>
                        </div>
                        <div class="address-item-inner">
                            <span class="address-item-label">@lang('City')</span>
                            <span class="address-item-value"> <span class="item-devide">:</span> {{ $item->city }}</span>
                        </div>
                        <div class="address-item-inner">
                            <span class="address-item-label">@lang('State')</span>
                            <span class="address-item-value"> <span class="item-devide">:</span> {{ $item->state }}</span>
                        </div>
                        <div class="address-item-inner">
                            <span class="address-item-label">@lang('Country')</span>
                            <span class="address-item-value"> <span class="item-devide">:</span> {{ $item->country }}</span>
                        </div>
                        <div class="address-item-inner">
                            <span class="address-item-label">@lang('Phone')</span>
                            <span class="address-item-value"> <span class="item-devide">:</span> {{ $item->mobile }}</span>
                        </div>
                    </div>
                </div>

                <div class="position-relative text-end">
                    <button type="button" data-resource="{{ $item }}" class="btn btn-outline--light editAddress">
                        <i class="la la-pencil"></i>
                        <span class="d-none d-sm-inline">@lang('Change')</span>
                    </button>
                </div>
            </label>
        @endforeach

        <button type="button" class="newAddress w-100 address-single-add-new">
            <i class="las la-plus-circle"></i>
            <span class="add-new-title d-block">@lang('Add New Address')</span>
        </button>
    </div>

    <div class="d-flex align-items-center justify-content-between flex-wrap mt-4">
        <a href="{{ route('cart.page') }}" class="text--base">
            <i class="las la-angle-left"></i> @lang('Back to Cart')
        </a>

        <form action="{{ route('checkout.shipping.info.add') }}" method="POST" id="shipping-form">
            @csrf
            <button type="submit" class="btn btn--base h-45">@lang('Continue to Next') <i class="las la-angle-right"></i></button>
        </form>

    </div>
    @if ($addressAutocompleteEnabled)
        @include('Template::checkout_steps.address_autocomplete')
    @endif
@endsection

@push('modal')
    <x-dynamic-component :component="frontendComponent('shipping-address-modal')" :countries="$countries" />
@endpush


@push('style')
    <style>
        .address-wrapper { display:grid; gap:14px; padding-bottom:12px; }
        .address-single { display:flex; align-items:flex-start; gap:18px; padding:18px; border:1px solid var(--store-line,#e4dfd5); background:#fffefa; color:var(--store-ink,#252521); }
        .address-single:has(input:checked) { border-color:var(--store-accent,#9b493d); box-shadow:inset 3px 0 var(--store-accent,#9b493d); }
        .address-item-left { display:flex; align-items:center; gap:11px; margin-bottom:12px; }
        .address-item-left h6 { margin:0; font-weight:700; }
        .address-item-left input { accent-color:var(--store-accent,#9b493d); }
        .address-item-right { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px 20px; }
        .address-item-inner { display:flex; gap:8px; min-width:0; font-size:.87rem; }
        .address-item-label { flex:0 0 76px; color:var(--store-muted,#746f67); }
        .item-devide { display:none; }
        .address-item-value { overflow-wrap:anywhere; }
        .address-single .btn { white-space:nowrap; }
        .address-single-add-new { min-height:110px; display:grid; place-content:center; gap:5px; border:1px dashed #b7aa97; background:rgba(255,254,250,.55); color:var(--store-accent,#9b493d); }
        .address-single-add-new i { font-size:1.4rem; }
        .add-new-title { font-size:1rem; color:inherit; }
        #shipping-form .btn--base { min-height:46px; padding:0 20px; border-radius:3px; background:var(--store-ink,#252521); }
        @media(max-width:767px) {
            .address-wrapper { gap:10px; }
            .address-single { gap:9px; padding:13px; }
            .address-item-right { grid-template-columns:1fr; gap:5px; }
            .address-item-label { flex-basis:68px; }
            .address-single .position-relative { align-self:flex-start; }
            .address-wrapper+.d-flex { gap:14px!important; padding-bottom:calc(96px + env(safe-area-inset-bottom)); }
            #shipping-form { width:100%; }
            #shipping-form .btn--base { width:100%; }
        }
    </style>
@endpush
