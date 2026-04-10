@extends('layouts.master')
@section('title')
    Account Report
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Account Report
        @endslot
        @slot('title')
            Manage Account Report
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12 mb-3">
            <div class="d-flex align-items-lg-center flex-lg-row flex-column justify-content-end">
                <div class="flex-grow-1"></div>
                <x-custom-date-range action="{{ route('admin.account-report.index') }}"></x-custom-date-range>
            </div>
        </div>
    </div>
    <div class="row mb-3 pb-1">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body d-flex gap-3 align-items-center">
                    <div class="avatar-sm">
                        <div class="avatar-title border bg-primary-subtle border-primary border-opacity-25 rounded-2">
                            <i class="ri-wallet-3-line text-primary fs-2"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fs-15">
                            {{ $currencySymbol }}
                            {{ number_format($totalRevenue, 2) }}
                        </h5>
                        <p class="mb-0 text-muted">Total Revenue</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body d-flex gap-3 align-items-center">
                    <div class="avatar-sm">
                        <div class="avatar-title border bg-success-subtle border-success border-opacity-25 rounded-2">
                            <i class="ri-shopping-cart-fill text-success fs-2"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fs-15">{{ $completedOrders }}</h5>
                        <p class="mb-0 text-muted">Total Completed Orders</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body d-flex gap-3 align-items-center">
                    <div class="avatar-sm">
                        <div class="avatar-title border bg-warning-subtle border-warning border-opacity-25 rounded-2">
                            <i class="ri-time-line text-warning fs-2"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fs-15">
                            {{ $pendingOrders }}
                        </h5>
                        <p class="mb-0 text-muted">Total Pending Orders</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body d-flex gap-3 align-items-center">
                    <div class="avatar-sm">
                        <div class="avatar-title border bg-danger-subtle border-danger border-opacity-25 rounded-2">
                            <i class="ri-close-circle-line text-danger fs-2"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fs-15">{{ $cancelledOrders }}</h5>
                        <p class="mb-0 text-muted">Total Cancelled Orders</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.account-report.index') }}" class="mb-3"
                    id="accountReportFilterForm">
                    <div class="row">
                        <div class="col-md-2">
                            <input type="text" name="full_name" class="form-control" placeholder="Full Name"
                                value="{{ request()->full_name }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="mobile_number" class="form-control" placeholder="Mobile Number"
                                value="{{ request()->mobile_number }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" data-choices data-choices-multiple-groups="true"
                                data-placeholder="Order Type" name="order_type[]" multiple>
                                <option @if (isset(request()->order_type) && in_array('Appointment', request()->order_type)) selected @endif value="Appointment">
                                    Appointment</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="order_status" class="form-control">
                                <option value="">Select Status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}"
                                        @if (request()->order_status == $status->id) selected @endif>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="items" class="form-control">
                                <option value="">Show Items</option>
                                <option value="10" @if (request()->items == 10) selected @endif>10</option>
                                <option value="25" @if (request()->items == 25) selected @endif>25</option>
                                <option value="50" @if (request()->items == 50) selected @endif>50</option>
                                <option value="100" @if (request()->items == 100) selected @endif>100</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="{{ route('admin.account-report.index') }}" class="btn btn-soft-secondary"><i
                                    class="ri-refresh-line"></i></a>
                        </div>
                    </div>
                </form>
                <x-spinner></x-spinner>
                <div id="account-report-list">
                    <p class="small text-muted">
                        Showing
                        <span class="fw-semibold">{{ $orders->firstItem() }}</span>
                        to
                        <span class="fw-semibold">{{ $orders->lastItem() }}</span>
                        of
                        <span class="fw-semibold">{{ $orders->total() }}</span>
                        results
                    </p>
                    <table id="account-report-table"
                        class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>User </th>
                                <th>Price </th>
                                <th>Order Type </th>
                                <th>Order Status </th>
                                <th>Order Date </th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $key => $order)
                                <tr>
                                    <td> <a href="{{ route('admin.orders.show', $order->id) }}">
                                            #{{ $order->order_id }}</a>
                                    </td>
                                    <td>
                                        @php
                                            $isCustomer = $order->customer_id && $order->customer_id != 0;
                                        @endphp
                                        <a
                                            href="{{ $isCustomer ? route('admin.customers.show', $order->customer_id) . '?tab=appointments' : route('admin.experts.show', $order->astrologer_id) . '?tab=appointments' }}">
                                            <div class="d-flex align-items-center">
                                                @php
                                                    $user = $order->customer ?? $order->astrologer;
                                                @endphp

                                                <img src="{{ $user?->profile_picture }}"
                                                    onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                                    class="rounded-circle avatar-sm me-2" alt="Profile Picture" />

                                                <div>
                                                    <div>{{ $user?->full_name }}</div>
                                                    <div class="text-muted">{{ $user?->email }}</div>
                                                    <div class="text-muted">{{ $user?->mobile_number }}</div>
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td>{{ $currencySymbol }} {{ $order->total_price }}</td>
                                    <td>
                                        Appointment
                                    </td>
                                    <td>
                                        @php
                                            $status = strtolower($order->status?->name);
                                            $badgeClass = 'bg-primary';

                                            switch ($status) {
                                                case 'pending':
                                                    $badgeClass = 'bg-warning';
                                                    break;
                                                case 'completed':
                                                    $badgeClass = 'bg-success';
                                                    break;
                                                case 'cancelled':
                                                    $badgeClass = 'bg-danger';
                                                    break;
                                            }
                                        @endphp
                                        <span class="badge {{ $badgeClass }} ">{{ $order->status?->name }}</span>
                                    </td>
                                    <td>{{ $order->created_at }}</td>
                                    <td>
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item edit-item-btn"
                                                        href="{{ route('admin.orders.show', $order->id) }}">
                                                        <i class="ri-eye-fill align-bottom me-2 text-muted"></i>
                                                        View</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if (!$orders->hasPages() && $orders->total() > 0)
                        <p class="small text-muted">
                            Showing
                            <span class="fw-semibold">{{ $orders->firstItem() }}</span>
                            to
                            <span class="fw-semibold">{{ $orders->lastItem() }}</span>
                            of
                            <span class="fw-semibold">{{ $orders->total() }}</span>
                            results
                        </p>
                    @endif
                    {{ $orders->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script-bottom')
    <script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            let typingTimer;
            const doneTypingInterval = 500;

            function showAccountReportSpinner() {
                $('#loadingSpinner').show();
                $('#account-report-list').hide();
            }

            $('#accountReportFilterForm').on('submit', function() {
                showAccountReportSpinner();
            });

            $('input[name="full_name"], input[name="mobile_number"]').on('keyup', function() {
                clearTimeout(typingTimer);
                const value = $(this).val();
                if (value.length >= 3 || value.length === 0) {
                    typingTimer = setTimeout(() => {
                        $('#accountReportFilterForm').submit();
                    }, doneTypingInterval);
                }
            });

            $('select[name="order_type[]"], select[name="order_status"], select[name="items"]').on('change', function() {
                $('#accountReportFilterForm').submit();
            });
        });
    </script>
@endsection
