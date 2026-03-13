<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="index" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ URL::asset('images/logo.png') }}" alt="" height="40">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ URL::asset('images/logo.png') }}" alt="" height="100">
                        </span>
                    </a>
                </div>

                {{-- <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button> --}}
                <div class="container d-flex align-items-center">
                    @if (request()->routeIs(['admin.earning-report.index', 'admin.earning-report.show']))
                        @role('admin')
                            <a href="{{ route('admin.withdraw-request.index', ['tab' => 'payout']) }}" class="nav-link text-primary fs-5">
                                <i class="ri-exchange-fill fs-4"></i>
                                <span>Withdrawal Requests</span>
                            </a>
                        @endrole
                    @elseif (request()->routeIs(['admin.experts.index', 'admin.experts.create', 'admin.experts.edit', 'admin.experts.show']))
                        @role('admin')
                            <a href="{{ route('admin.expert.profile.index') }}" class="nav-link text-primary fs-5">
                                <i class="ri-file-text-fill fs-4"></i>
                                <span>New Form Submission</span>
                            </a>
                        @endrole
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center">
                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user"
                                src="@if (Auth::user()->profile_picture != '') {{ Auth::user()->profile_picture }}@else{{ URL::asset('build/images/users/avatar-1.jpg') }} @endif"
                                alt="Header Avatar">
                            <span class="text-start ms-xl-2">
                                <span
                                    class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ Auth::user()->first_name }}
                                    {{ Auth::user()->last_name }}</span>
                                <span
                                    class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">{{ Auth::user()->email }}</span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <a class="dropdown-item" href="{{ route('admin.edit-profile') }}"><i
                                class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span
                                class="align-middle">Edit Profile</span></a>
                        <a class="dropdown-item " href="javascript:void();"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                class="bx bx-power-off font-size-16 align-middle me-1"></i> <span key="t-logout">Log
                                Out</span></a>
                        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST"
                            style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
