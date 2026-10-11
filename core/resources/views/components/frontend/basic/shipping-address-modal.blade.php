@props(['countries' => []])
<div class="modal custom--modal fade" id="addressModal" tabindex="-1" role="dialog" aria-labelledby="addressModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <h5 class="modal-title" id="addressModalTitle"></h5>
                <button type="button" class="close modal-close-btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
                <form method="POST" action="">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>@lang('Title')</label>
                                <input type="text" class="form-control form--control" name="label" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('First Name')</label>
                                <input type="text" class="form-control form--control" name="firstname" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Last Name')</label>
                                <input type="text" class="form-control form--control" name="lastname" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Mobile')</label>
                                <input type="text" class="form-control form--control" name="mobile" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Email')</label>
                                <input type="text" class="form-control form--control" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('City')</label>
                                <input type="text" class="form-control form--control" name="city" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('State')</label>
                                <input type="text" class="form-control form--control" name="state" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Zip')</label>
                                <input type="text" class="form-control form--control" name="zip" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Country')</label>
                                <select name="country" class="form-control form--control form-select select2">
                                    <option value="" hidden>@lang('Select One')</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->country }}">{{ __($country->country) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label>@lang('Address')</label>
                                <input type="text" class="form-control form--control" name="address" autocomplete="shipping street-address" aria-autocomplete="list" required>
                                @if (gs('geoapify_address_enabled'))
                                    <small class="text-muted">Start typing to choose a suggested address, or enter it manually.</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn--base w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    #addressModal .modal-dialog { width: min(100% - 1rem, 760px); max-height: calc(100dvh - 1rem); margin: .5rem auto; }
    #addressModal .modal-content { max-height: calc(100dvh - 1rem); }
    #addressModal .modal-body { overflow-y: auto; overscroll-behavior: contain; padding-bottom: max(1rem, env(safe-area-inset-bottom)); }
    #addressModal .modal-close-btn { z-index: 2; }
    @media(max-width: 575.98px) {
        #addressModal .modal-dialog { width: 100%; max-width: none; height: 100dvh; max-height: 100dvh; margin: 0; }
        #addressModal .modal-content { height: 100%; max-height: 100dvh; border-radius: 0; }
        #addressModal .modal-body { height: 100%; padding: 1.25rem 1rem calc(1rem + env(safe-area-inset-bottom)); }
        #addressModal form > .col-12 { position: sticky; bottom: 0; z-index: 2; padding: .75rem 0 calc(.75rem + env(safe-area-inset-bottom)); background: var(--bs-modal-bg, #fff); }
    }
</style>

@push('script')
    <script>
        "use strict";
        (function($) {
            let modal = $('#addressModal');
            let action = `{{ route('user.shipping.address.store') }}`;

            $('.newAddress').on('click', function() {
                modal.find('.modal-title').text(`@lang('Add New Shipping Address')`);
                modal.find('form').attr('action', action);
                modal.modal('show');
            });

            $('.editAddress').on('click', function() {
                modal.find('.modal-title').text(`@lang('Update Shipping Address')`);
                let address = $(this).data('resource');

                modal.find('[name=firstname]').val(address.firstname);
                modal.find('[name=lastname]').val(address.lastname);
                modal.find('[name=mobile]').val(address.mobile);
                modal.find('[name=email]').val(address.email);
                modal.find('[name=city]').val(address.city);
                modal.find('[name=state]').val(address.state);
                modal.find('[name=zip]').val(address.zip);
                modal.find('[name=country]').val(address.country);
                modal.find('[name=address]').val(address.address);
                modal.find('[name=label]').val(address.label);

                modal.find('form').attr('action', `${action}/${address.id}`);
                modal.modal('show');
            });

            modal.on('hidden.bs.modal', function() {
                modal.find('form')[0].reset();
            });

        })(jQuery);
    </script>
@endpush
