@extends('layouts.master')
@section('title')
    Earning Report
@endsection
@section('css')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('backarrow')
            {{ route('admin.earning-report.index') }}
        @endslot
        @slot('li_1')
            Earning Report
        @endslot
        @slot('title')
            {{ $earning->astrologer->full_name }}
        @endslot
    @endcomponent
    <div class="row">
        <div class="row mb-3 pb-1">
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <div class="avatar-sm">
                            <div class="avatar-title border bg-primary-subtle border-primary border-opacity-25 rounded-2">
                                <i class="ri-money-rupee-circle-line text-primary fs-2"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fs-15">₹ {{ number_format($totalAmount, 2) }}</h5>
                            <p class="mb-0 text-muted">Total Amount</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <div class="avatar-sm">
                            <div class="avatar-title border bg-success-subtle border-success border-opacity-25 rounded-2">
                                <i class="ri-wallet-3-fill text-success fs-2"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fs-15">₹ {{ number_format($totalPaidAmount, 2) }}</h5>
                            <p class="mb-0 text-muted">Total Paid Amount</p>
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
                            <h5 class="fs-15">₹ {{ number_format($totalDueAmount, 2) }}</h5>
                            <p class="mb-0 text-muted">Total Due Amount</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                    <div class="flex-grow-1"></div>
                    <x-date-filter action="{{ route('admin.earning-report.show', $earning->id) }}"></x-date-filter>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <p class="small text-muted">
                        Showing
                        <span class="fw-semibold">{{ $orders->firstItem() }}</span>
                        to
                        <span class="fw-semibold">{{ $orders->lastItem() }}</span>
                        of
                        <span class="fw-semibold">{{ $orders->total() }}</span>
                        results
                    </p>
                    <table id="order-table" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                        style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Order ID</th>
                                <th>Appointment with </th>
                                <th>Connect Type </th>
                                <th>Date </th>
                                <th>Duration </th>
                                <th>Expert Earning </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $key => $order)
                                <tr>
                                    <td>{{ $orders->firstItem() + $key }}</td>
                                    <td> <a href="{{ route('admin.orders.show', $order->id) }}">
                                        #{{ $order->order_id }}</a></td>
                                    <td>
                                        @php
                                            $customer = $order->customer;
                                        @endphp
                                        <a href="{{ route('admin.customers.show', $customer->id) . '?tab=appointments' }}">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $customer?->profile_picture }}"
                                                    onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                                    class="rounded-circle avatar-sm me-2" alt="Profile Picture" />
                                                <div>
                                                    <div>{{ $customer?->full_name }}</div>
                                                    <div class="text-muted">{{ $customer?->email }}</div>
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td>{{ $order->typeable?->connect_type }}</td>
                                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d-M-Y') }}</td>
                                    <td>{{ $order->typeable?->callLog?->call_time }}</td>
                                    <td>₹ {{ number_format($order->typeable?->earnings?->first()?->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if (!$orders->hasPages() && $orders->total() > 0)
                        <p>Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of
                            {{ $orders->total() }}
                            result</p>
                    @endif
                    {{ $orders->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
