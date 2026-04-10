<!doctype html >
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="enable">

<head>
    <meta charset="utf-8" />
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="currency-symbol" content="{{ config('app.currency_symbol') }}">
    <meta name="dropzone-get-media-url" content="{{ route('admin.dropzone.get-media') }}">
    <meta name="dropzone-delete-media-url" content="{{ route('admin.dropzone.delete-existing-file') }}">
    <!-- App favicon -->
    @if (env('APP_ENV') === 'production')
        <link rel="shortcut icon" href="{{ URL::asset('images/favicon.ico')}}">
    @else
        <link rel="shortcut icon" href="{{ URL::asset('images/logo.png') }}">
    @endif
    @include('layouts.head-css')
</head>

@section('body')
    @include('layouts.body')
@show
    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('layouts.footer')
        </div>
        <!-- end main content-->
    </div>
    @include('layouts.notifications')
    <!-- END layout-wrapper -->
    <!-- JAVASCRIPT -->
    @include('layouts.vendor-scripts')
</body>

</html>
