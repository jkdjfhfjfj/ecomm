@extends('admin.layouts.app')

@section('panel')
    <div class="row mb-3">
        <div class="col-lg-12">
            @if ($missingHomepageSchemaColumns->isNotEmpty())
                <div class="alert alert-danger">
                    <strong>@lang('Homepage offer database setup is incomplete.')</strong>
                    @lang('Missing database columns: :columns. Run the pending Laravel migrations from the HostBuddy build to restore campaign checks and hosted-image filtering.', [
                        'columns' => $missingHomepageSchemaColumns->join(', '),
                    ])
                </div>
            @elseif ($dailyOfferDiagnostics->where('popup_ready', true)->isEmpty())
                <div class="alert alert-warning">
                    <strong>@lang('No daily offer is ready to appear on the homepage.')</strong>
                    @lang('A daily offer must be enabled, within its start and end dates, and have at least one published product with a usable image.')
                    <a href="{{ route('admin.offer.create') }}" class="alert-link">@lang('Create or edit a daily offer')</a>.
                </div>
            @else
                <div class="alert alert-success">
                    <strong>@lang('Homepage daily offer is ready.')</strong>
                    @lang('The active daily campaign has :count published product(s) with images.', [
                        'count' => $dailyOfferDiagnostics->firstWhere('popup_ready', true)->displayable_products_count,
                    ])
                    @lang('If you dismissed it today, clear “Do not show again today” in this browser or use the “Today’s offer” button to reopen it.')
                </div>
            @endif

            @if ($missingHomepageSchemaColumns->isEmpty() && $dailyOfferDiagnostics->isNotEmpty())
                <div class="card b-radius--10">
                    <div class="card-header">
                        <h6 class="mb-0">@lang('Daily offer homepage check')</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table--light style--two table mb-0">
                            <thead>
                                <tr>
                                    <th>@lang('Offer')</th>
                                    <th>@lang('Enabled')</th>
                                    <th>@lang('Schedule')</th>
                                    <th>@lang('Published products with images')</th>
                                    <th>@lang('Homepage popup')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dailyOfferDiagnostics as $dailyDiagnostic)
                                    <tr>
                                        <td>{{ $dailyDiagnostic->name }}</td>
                                        <td>{{ $dailyDiagnostic->status ? __('Yes') : __('No') }}</td>
                                        <td>{{ $dailyDiagnostic->is_running_now ? __('Running now') : __('Outside active dates') }}</td>
                                        <td>{{ $dailyDiagnostic->displayable_products_count }}</td>
                                        <td>
                                            @if ($dailyDiagnostic->popup_ready)
                                                <span class="badge badge--success">@lang('Ready')</span>
                                            @else
                                                <span class="badge badge--warning">@lang('Not ready')</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.offer.edit', $dailyDiagnostic->id) }}" class="btn btn-outline--primary btn-sm">
                                                <i class="la la-pencil"></i>@lang('Edit')
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Campaign')</th>
                                    <th>@lang('Discount Type')</th>
                                    <th>@lang('Total Products')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Expire Date')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($offers as $offer)
                                    <tr>
                                        <td>{{ $offer->name }}</td>
                                        <td>{{ __(ucwords(str_replace('_', ' ', $offer->campaign_type ?? 'standard'))) }}</td>
                                        <td> @php echo $offer->discountTypeBadge() @endphp </td>
                                        <td>{{ $offer->total_products }}</td>
                                        <td>
                                            <x-toggle-switch class="change_status" :checked="$offer->status" data-id="{{ $offer->id }}" />
                                        </td>
                                        <td> {{ showDateTime($offer->ends_at, 'd M, Y') }} </td>
                                        <td>
                                            <a href="{{ route('admin.offer.edit', $offer->id) }}" class="btn btn-outline--primary btn-sm">
                                                <i class="la la-pencil"></i>@lang('Edit')
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($offers->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($offers) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <div class="d-flex gap-3">
        <x-search-form placeholder="Name" />
        <a href="{{ route('admin.offer.create') }}" class="btn btn-sm btn-outline--primary flex-shrink-0"> <i class="las la-plus"></i> @lang('Add New')</a>
    </div>
@endpush

@push('script')
    <script>
        'use strict';
        (function($) {

            $('.change_status').on('change', function() {
                var id = $(this).data('id');

                var data = {
                    _token: `{{ csrf_token() }}`,
                    id: id
                };

                $.ajax({
                    url: "{{ route('admin.offer.status') }}",
                    method: 'POST',
                    data: data,
                    success: function(response) {
                        notify(response.status, response.message);
                    }
                });
            });

        })(jQuery)
    </script>
@endpush
