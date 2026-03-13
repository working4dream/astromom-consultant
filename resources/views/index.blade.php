<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-topbar="light" data-sidebar-image="none"
    data-theme="default" data-theme-colors="default">

<head>
    <meta charset="utf-8" />
    <title>
        @if (env('APP_ENV') === 'production')
            {{ env('APP_NAME') }}
        @else
            {{ env('APP_NAME') }}
        @endif
    </title>
    <link rel="shortcut icon" href="{{ URL::asset('images/favicon.ico') }}">
    @include('layouts.head-css')
</head>

<body>
    <div class="d-flex justify-content-center align-items-center vh-50">
        <img src="{{ URL::asset('images/logo.svg') }}" alt="Logo" height="500">
    </div>
    <div class="d-flex justify-content-center align-items-center mt-5">
        <a href="{{ route('astrologer.signup') }}" class="btn btn-primary"><i class="ri-user-add-fill"></i> Join as
            Expert</a>
    </div>

</body>

</html>
