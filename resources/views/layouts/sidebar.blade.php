<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                    <img src="{{ !empty($settings['brand_logo'] ?? null) ? Storage::disk('s3')->temporaryUrl($settings['brand_logo'], now()->addMinutes(60)) : URL::asset('images/logo.png') }}" alt="" height="40">
            </span>
            <span class="logo-lg">
                <img src="{{ !empty($settings['brand_logo'] ?? null) ? Storage::disk('s3')->temporaryUrl($settings['brand_logo'], now()->addMinutes(60)) : URL::asset('images/logo.png') }}" alt="" height="100">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>
    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        href="{{ route('admin.dashboard') }}">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarProducts" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarProducts">
                        <i class="ri-archive-2-fill"></i> <span>Products</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarProducts">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                                    href="{{ route('admin.products.index') }}">
                                    Services
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarorders" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarorders">
                        <i class="ri-shopping-cart-2-fill"></i>Orders
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarorders">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}"
                                    href="{{ route('admin.orders.index') }}">
                                    Sales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.coupons.index') ? 'active' : '' }}"
                                    href="{{ route('admin.coupons.index') }}">
                                    Coupons
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarUser" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarUser">
                        <i class="ri-user-fill"></i>Users
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarUser">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.customers.index') ? 'active' : '' }}"
                                    href="{{ route('admin.customers.index') }}">
                                    Customers
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.experts.index') ? 'active' : '' }}"
                                    href="{{ route('admin.experts.index') }}">
                                    Experts
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarReport" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarReport">
                        <i class="ri-file-list-3-line"></i>Reports
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarReport">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.earning-report.index') ? 'active' : '' }}"
                                    href="{{ route('admin.earning-report.index') }}">
                                    <span>Earning Report</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.account-report.index') ? 'active' : '' }}"
                                    href="{{ route('admin.account-report.index') }}">
                                    Sales Report
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.media-library') ? 'active' : '' }}"
                        href="{{ route('admin.media-library') }}">
                        <i class="bx bx-folder"></i> <span>Media Library</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.notification.index') ? 'active' : '' }}"
                        href="{{ route('admin.notification.index') }}">
                        <i class="ri-notification-3-fill"></i>
                        <span>Notifications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}"
                        href="{{ route('admin.settings.index') }}">
                        <i class="ri-settings-5-fill"></i>
                        <span>Settings</span>
                    </a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.faqs.index') ? 'active' : '' }}"
                        href="{{ route('admin.faqs.index') }}">
                        <i class="ri-question-line"></i>
                        <span>FAQs</span>
                    </a>
                </li> --}}
            </ul>
        </div>
    </div>
</div>
<div class="sidebar-background"></div>
<div class="vertical-overlay"></div>
