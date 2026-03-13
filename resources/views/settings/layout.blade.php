@extends('layouts.master')
@section('title')
    Settings
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css">
    <link href="{{ URL::asset('css/custom-dropzone.css') }}" rel="stylesheet" />
    <style>
        .bootstrap-tagsinput .tag {
            margin-right: 2px;
            color: white;
            background-color: var(--bs-primary);
            border-radius: 5px;
            padding: 0px 5px;
        }

        .font-bold li {
            font-weight: bold;
        }

        .booking {
            float: inline-end;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('backarrow')
            {{ route('admin.settings.index') }}
        @endslot
        @slot('li_1')
            Settings
        @endslot
        @slot('title')
            Settings
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-2">
            <div class="card">
                <div class="card-body">
                    <div class="nav nav-pills flex-column nav-pills-tab custom-verti-nav-pills text-center" role="tablist"
                        aria-orientation="vertical">
                        <a class="nav-link" id="custom-v-pills-prices-tab" data-bs-toggle="pill"
                            href="#custom-v-pills-prices" role="tab" aria-controls="custom-v-pills-prices"
                            aria-selected="false">
                            <i class="fa fa-rupee d-block fs-20 mb-1"></i> Prices
                        </a>
                        <a class="nav-link" id="custom-v-pills-messages-tab" data-bs-toggle="pill"
                            href="#custom-v-pills-messages" role="tab" aria-controls="custom-v-pills-messages"
                            aria-selected="false">
                            <i class="ri-window-fill d-block fs-20 mb-1"></i> Services
                        </a>
                        <a class="nav-link" id="custom-v-pills-bookings-tab" data-bs-toggle="pill"
                            href="#custom-v-pills-bookings" role="tab" aria-controls="custom-v-pills-bookings"
                            aria-selected="false">
                            <i class="ri-computer-fill d-block fs-20 mb-1"></i> Booking Prices
                        </a>
                        <a class="nav-link" id="custom-v-pills-customer-banner-tab" data-bs-toggle="pill"
                            href="#custom-v-pills-customer-banner" role="tab"
                            aria-controls="custom-v-pills-customer-banner" aria-selected="false">
                            <i class=" bx bx-rectangle d-block fs-20 mb-1"></i>
                            Customer Banner
                        </a>
                        <a class="nav-link" id="custom-v-pills-app-settings-tab" data-bs-toggle="pill"
                            href="#custom-v-pills-app-settings" role="tab" aria-controls="custom-v-pills-app-settings"
                            aria-selected="false">
                            <i class=" bx bx-mobile-alt d-block fs-20 mb-1"></i>
                            App Settings
                        </a>
                        @if(env('APP_ENV') !== 'production')
                        <a class="nav-link" id="custom-v-pills-branding-tab" data-bs-toggle="pill"
                            href="#custom-v-pills-branding" role="tab" aria-controls="custom-v-pills-branding"
                            aria-selected="false">
                            <i class=" bx bx-palette d-block fs-20 mb-1"></i>
                            Branding
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-10">
            <div class="tab-content text-muted mt-3 mt-lg-0">
                <div class="tab-pane fade" id="custom-v-pills-prices" role="tabpanel"
                    aria-labelledby="custom-v-pills-prices-tab">
                    @include('settings.prices')
                </div>
                <div class="tab-pane fade" id="custom-v-pills-messages" role="tabpanel"
                    aria-labelledby="custom-v-pills-messages-tab">
                    @include('settings.services')
                </div>
                <div class="tab-pane fade" id="custom-v-pills-bookings" role="tabpanel"
                    aria-labelledby="custom-v-pills-bookings-tab">
                    @include('settings.bookings')
                </div>
                <div class="tab-pane fade" id="custom-v-pills-customer-banner" role="tabpanel"
                    aria-labelledby="custom-v-pills-customer-banner-tab">
                    @include('settings.banner')
                </div>
                <div class="tab-pane fade" id="custom-v-pills-app-settings" role="tabpanel"
                    aria-labelledby="custom-v-pills-app-settings-tab">
                    @include('settings.app-settings')
                </div>
                <div class="tab-pane fade" id="custom-v-pills-branding" role="tabpanel"
                    aria-labelledby="custom-v-pills-branding-tab">
                    @include('settings.branding')
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
    <script src="{{ URL::asset('js/custom-dropzone.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let activeTab = sessionStorage.getItem("activeTab") ||
                "{{ session('active_tab', 'custom-v-pills-prices') }}";
            let tabElement = document.querySelector(`a[href="#${activeTab}"]`);
            if (tabElement) {
                tabElement.click();
            }

            document.querySelectorAll(".nav-link").forEach(tab => {
                tab.addEventListener("click", function() {
                    let selectedTab = this.getAttribute("href").replace("#", "");
                    document.getElementById("active_tab").value = selectedTab;
                    sessionStorage.setItem("activeTab", selectedTab);
                });
            });
        });
    </script>
@endsection
