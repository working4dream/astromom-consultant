@yield('css')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<!-- Layout config Js -->
<script src="{{ URL::asset('build/js/layout.js') }}"></script>
<!-- Bootstrap Css -->
<link href="{{ URL::asset('css/bootstrap.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ URL::asset('build/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="{{ URL::asset('css/app.css') }}" id="app-style" rel="stylesheet" type="text/css" />
{{-- <link href="{{ URL::asset('build/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" /> --}}
<!-- custom Css-->
<link href="{{ URL::asset('build/css/custom.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
<!--datatable css-->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<!--datatable responsive css-->
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<!--- toastr --->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!--- dropzone --->
<link href="{{ URL::asset('build/libs/dropzone/dropzone.css') }}" rel="stylesheet">
<!--- daterangepicker --->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    :root{
            --bs-primary: {{ env('APP_ENV') !== 'production' ? isset($settings['primary_color']) ? $settings['primary_color'] : '#211324' : '#802433' }};
            --bs-primary-rgb: {{ implode(',', sscanf(env('APP_ENV') !== 'production' ? isset($settings['primary_color']) ? $settings['primary_color'] : '#211324' : '#802433', "#%02x%02x%02x")) }};
        }
    </style>

{{-- @yield('css') --}}
