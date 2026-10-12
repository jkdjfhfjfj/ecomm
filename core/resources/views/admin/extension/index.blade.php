@extends('admin.layouts.app')

@section('panel')
    @if ($extensions->contains('act', 'tawk-chat'))
        <div class="alert alert-info mb-4">
            <h5 class="mb-2"><i class="las la-comments"></i> @lang('Free live support chat with Tawk.to')</h5>
            <ol class="mb-2 ps-3">
                <li>@lang('Create a free account at') <a href="https://www.tawk.to/" target="_blank" rel="noopener noreferrer">tawk.to</a> @lang('and add your website as a property.')</li>
                <li>@lang('In Tawk.to, open Administration → Channels → Chat Widget and copy the widget ID path shown in the embed URL after embed.tawk.to/ (property ID/widget ID).')</li>
                <li>@lang('On this page, use Configure for Tawk.to, paste that path into the widget ID field, and save.')</li>
                <li>@lang('Use Enable on the Tawk.to row to show chat on the storefront. Disable it here any time to remove the widget.')</li>
            </ol>
            <div class="small">
                @lang('The mobile launcher is offset above the fixed shop navigation so it does not cover Category, Wishlist, Compare, or Account controls. The chat panel itself opens only after a shopper taps the launcher.')
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two custom-data-table">
                            <thead>
                                <tr>
                                    <th>@lang('Extension')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($extensions as $extension)
                                <tr>
                                    <td>
                                        <div class="user">
                                            <div class="thumb"><img src="{{ getImage(getFilePath('extensions') .'/'. $extension->image,getFileSize('extensions')) }}" alt="{{ __($extension->name) }}" class="plugin_bg"></div>
                                            <span class="name">{{ __($extension->name) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            echo $extension->statusBadge;
                                        @endphp
                                    </td>
                                    <td>
                                        <div class="button--group">
                                            <button type="button" class="btn btn-sm btn-outline--primary ms-1 mb-2 editBtn"
                                                    data-name="{{ __($extension->name) }}"
                                                    data-provider="{{ $extension->act }}"
                                                    data-shortcode="{{ json_encode($extension->shortcode) }}"
                                                    data-action="{{ route('admin.extensions.update', $extension->id) }}">
                                                <i class="la la-cogs"></i> @lang('Configure')
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline--dark ms-1 mb-2 helpBtn"
                                                    data-description="{{ __($extension->description) }}"
                                                    data-provider="{{ $extension->act }}"
                                                    data-support="{{ __($extension->support) }}">
                                                <i class="la la-question"></i> @lang('Help')
                                            </button>
                                            @if($extension->status == Status::DISABLE)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline--success ms-1 mb-2 confirmationBtn"
                                                        data-action="{{ route('admin.extensions.status', $extension->id) }}"
                                                        data-question="@lang('Are you sure to enable this extension?')">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline--danger mb-2 confirmationBtn"
                                                data-action="{{ route('admin.extensions.status', $extension->id) }}"
                                                data-question="@lang('Are you sure to disable this extension?')">
                                                        <i class="la la-eye-slash"></i> @lang('Disable')
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>



    {{-- EDIT METHOD MODAL --}}
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Update Extension'): <span class="extension-name"></span></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="col-md-12 control-label fw-bold">@lang('Script')</label>
                            <div class="col-md-12">
                                <textarea name="script" class="form-control" required rows="8" placeholder="@lang('Paste your script with proper key')">{{ old('script') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45" id="editBtn">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- HELP METHOD MODAL --}}
    <div id="helpModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Need Help')?</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection


@push('breadcrumb-plugins')
        <div class="d-inline">
            <div class="input-group justify-content-end">
                <input type="text" name="search_table" class="form-control bg--white" placeholder="@lang('Search')...">
                <button class="btn btn--primary input-group-text"><i class="fas fa-search"></i></button>
            </div>
        </div>
@endpush


@push('script')
    <script>
        (function ($) {
            "use strict";

            $(document).on('click', '.editBtn',function () {
                var modal = $('#editModal');
                var shortcode = $(this).data('shortcode');
                var provider = $(this).data('provider');

                modal.find('.extension-name').text($(this).data('name'));
                modal.find('form').attr('action', $(this).data('action'));

                var html = '';
                $.each(shortcode, function (key, item) {
                    const label = key === 'app_key' && provider === 'tawk-chat'
                        ? @json(__('Tawk.to widget ID path (property ID/widget ID)'))
                        : item.title;
                    const placeholder = key === 'app_key' && provider === 'tawk-chat'
                        ? 'property-id/widget-id'
                        : '--';
                    const group = $('<div>', { class: 'form-group' });
                    $('<label>', { class: 'col-md-12 control-label fw-bold', text: label }).appendTo(group);
                    $('<div>', { class: 'col-md-12' }).append(
                        $('<input>', {
                            name: key,
                            class: 'form-control',
                            placeholder: placeholder,
                            value: item.value,
                            required: true
                        })
                    ).appendTo(group);
                    html += group.prop('outerHTML');
                })
                modal.find('.modal-body').html(html);

                modal.modal('show');
            });

            $(document).on('click', '.helpBtn',function () {
                var modal = $('#helpModal');
                var path = "{{ asset(getFilePath('extensions')) }}";
                const provider = $(this).data('provider');
                if (provider === 'tawk-chat') {
                    modal.find('.modal-body').html(`
                        <p>@lang('Tawk.to provides the hosted live-chat service. Create a property in your Tawk.to dashboard, then copy the path from the widget embed URL after embed.tawk.to/ . It looks like property-id/widget-id.')</p>
                        <p>@lang('Paste that path in Configure. Save the setting, then enable Tawk.to in the extension list. You can disable it here without removing the saved configuration.')</p>
                        <p>@lang('No Tawk API key is needed for the storefront widget.')</p>
                    `);
                    return modal.modal('show');
                }
                modal.find('.modal-body').text($(this).data('description'));
                if ($(this).data('support') != 'na') {
                    $('<img>', { src: `${path}/${$(this).data('support')}`, alt: 'Image' }).appendTo(modal.find('.modal-body'));
                }
                modal.modal('show');
            });

        })(jQuery);

    </script>
@endpush
