@if (gs('multi_language'))
    @php
        $language = App\Models\Language::orderBy('name')->get();
        $activeLanguage = $language->firstWhere('code', session('lang')) ?? $language->first();
        $isDrawerLanguageMenu = $drawer ?? false;
    @endphp
    @if ($activeLanguage)
        <div class="dropdown dropdown--lang {{ $isDrawerLanguageMenu ? 'dropdown--lang-drawer' : '' }}">
            @if ($isDrawerLanguageMenu)
                <span class="language-selector-label">@lang('Language')</span>
            @endif
            <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                aria-label="@lang('Choose language')">
                @if ($activeLanguage->image)
                    <img class="dropdown-flag lazyload"
                        src="{{ getImage(getFilePath('language') . '/' . $activeLanguage->image, getFileSize('language')) }}"
                        alt="">
                @endif
                <span class="language-selector-name">{{ __($activeLanguage->name) }}</span>
            </button>

            <div class="dropdown-menu">
                @foreach ($language as $lang)
                    @if ($activeLanguage->code === $lang->code)
                        @continue
                    @endif
                    <a class="dropdown-item" href="{{ route('lang', $lang->code) }}">
                        @if ($lang->image)
                            <img class="dropdown-flag lazyload"
                                src="{{ getImage(getFilePath('language') . '/' . $lang->image, getFileSize('language')) }}"
                                alt="">
                        @endif
                        <span>{{ __($lang->name) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endif
