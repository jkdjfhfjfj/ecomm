<!doctype html>
<html lang="{{ config('app.locale') }}" itemscope itemtype="http://schema.org/WebPage">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> {{ gs()->siteName(__($pageTitle)) }}</title>
    @include('partials.seo')
    <link type="image/x-icon" href="{{ siteFavicon() }}" rel="shortcut icon">
    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}">
    @stack('style-lib')
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/main.css') }}">
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/color.php?color=' . gs('base_color')) }}">
    @stack('style')
    <link href="{{ asset(activeTemplate(true) . 'css/custom.css') }}" rel="stylesheet">
</head>

@php echo loadExtension('google-analytics') @endphp

<body>
    <div class="body-overlay" id="body-overlay"></div>
    @include('Template::partials.preloader')
    <script>
        // Keep the storefront usable if a remote image or extension stalls the
        // window load event that normally removes this loader.
        window.setTimeout(function () {
            const loader = document.querySelector('.preloader');
            if (!loader) return;
            loader.style.transition = 'opacity .35s ease, visibility .35s ease';
            loader.style.opacity = '0';
            loader.style.visibility = 'hidden';
            loader.style.pointerEvents = 'none';
            window.setTimeout(function () { loader.remove(); }, 400);
        }, 1800);
    </script>

    @yield('app')

    @stack('modal')

    <script src="{{ asset('assets/global/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/jquery.validate.js') }}"></script>
    @stack('script-lib')
    <script src="{{ asset(activeTemplate(true) . 'js/main.js') }}"></script>
    @php echo loadExtension('tawk-chat') @endphp
    @include('partials.notify')
    @if (gs('pn'))
        @include('partials.push_script')
    @endif
    @stack('script')
</body>

</html>
