@extends('admin.layouts.app')

@section('panel')
    <div class="row gy-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-2">@lang('Parse.bot Catalog Import')</h4>
                    <p class="text-muted mb-3">
                        @lang('Runs one bounded import step at a time. Listing progress is saved by the importer so you can return and resume later.')
                    </p>
                    <div class="alert alert-info mb-0">
                        <strong>@lang('Daily request budget'):</strong>
                        {{ $creditsReservedToday }} / {{ $dailyBudget }} @lang('reserved today').
                        @lang('Each run can reserve up to') {{ $maxRunBudget }} @lang('requests. Unused reserved requests are not automatically refunded.')
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">@lang('API Key')</h5></div>
                <div class="card-body">
                    <p>
                        @lang('Deployment environment key'):
                        <strong>{{ $environmentKeyConfigured ? __('configured (takes priority)') : __('not configured') }}</strong>
                    </p>
                    <p>
                        @lang('Encrypted database fallback'):
                        <strong>{{ $fallbackKeyConfigured ? __('configured') : __('not configured') }}</strong>
                    </p>
                    <p class="small text-muted">
                        @lang('The key is encrypted with APP_KEY and never shown again. If the deployment environment key is set, rotate it in the deployment secrets/settings; this fallback is used only when that key is absent.')
                    </p>
                    <form method="POST" action="{{ route('admin.products.parsebot.key') }}">
                        @csrf
                        <label class="form-label" for="parsebot-api-key">@lang('Save encrypted fallback key')</label>
                        <input id="parsebot-api-key" name="api_key" type="password" class="form-control"
                            autocomplete="new-password" required minlength="8" maxlength="500">
                        @error('api_key') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        <button class="btn btn--primary mt-3" type="submit">@lang('Save Key')</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">@lang('Run / Resume Import')</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.products.parsebot.run') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="parsebot-mode">@lang('Import step')</label>
                            <select id="parsebot-mode" name="mode" class="form-control" required>
                                <option value="listings">@lang('Fetch category listings')</option>
                                <option value="details">@lang('Enrich products from detail endpoint')</option>
                                <option value="stored">@lang('Sync details from saved snapshots (no API requests)')</option>
                                <option value="categories">@lang('Refresh category records and image URLs (no API requests)')</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="parsebot-category-ids">@lang('Source category IDs')</label>
                            <input id="parsebot-category-ids" name="category_ids" class="form-control"
                                placeholder="10709,871,31" value="{{ old('category_ids') }}">
                            @error('category_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            <div class="form-text">@lang('Leave blank only when fetching all saved categories or enriching existing products.')</div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="all_pages" value="1" id="parsebot-all-pages">
                            <label class="form-check-label" for="parsebot-all-pages">@lang('Continue category pagination from saved checkpoints')</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="parsebot-credit-limit">@lang('Maximum API requests reserved for this step')</label>
                            <input id="parsebot-credit-limit" name="credit_limit" type="number" min="1"
                                max="{{ $maxRunBudget }}" value="1" class="form-control" required>
                        </div>
                        @error('mode') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                        @error('credit_limit') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                        @error('import') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                        @error('all_pages') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                        <button class="btn btn--primary" type="submit"
                            onclick="return confirm('@lang('Run this bounded Parse.bot import step now?')')">
                            @lang('Run Import Step')
                        </button>
                    </form>
                    @if (session('parsebot_output'))
                        <hr>
                        <h6>@lang('Latest step output')</h6>
                        <pre class="bg--dark text-white p-3 rounded" style="white-space:pre-wrap;max-height:28rem;overflow:auto">{{ session('parsebot_output') }}</pre>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
