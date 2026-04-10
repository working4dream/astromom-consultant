@extends('layouts.master')
@section('title')
    User Orders
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/@simonwep/pickr/themes/classic.min.css') }}" />
    <!-- 'classic' theme -->
    <link rel="stylesheet" href="{{ URL::asset('build/libs/@simonwep/pickr/themes/monolith.min.css') }}" />
    <!-- 'monolith' theme -->
    <link rel="stylesheet" href="{{ URL::asset('build/libs/@simonwep/pickr/themes/nano.min.css') }}" />
    <!-- 'nano' theme -->
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            User Orders
        @endslot
        @slot('title')
            Manage Orders
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-3" id="orderFilterForm">
                        <div class="row">
                            <div class="col-md-2">
                                <input type="text" name="order_no" id="order_no" class="form-control"
                                    placeholder="Order ID" value="{{ request()->order_no }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="full_name" class="form-control" placeholder="Full Name"
                                    value="{{ request()->full_name }}">
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
                                <input type="text" id="dateRange" class="form-control" name="date"
                                    data-provider="flatpickr" data-date-format="Y-m-d" data-range-date="true"
                                    value="{{ request()->date }}" placeholder="Date Range">
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
                            <div class="col-md-12 pt-3 text-end">
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-soft-secondary"><i
                                        class="ri-refresh-line"></i></a>
                                <a href="{{ route('admin.orders.export', request()->query()) }}"
                                    class="btn btn-soft-secondary"><i class=" ri-file-excel-line"></i></a>
                            </div>
                        </div>
                    </form>
                    <x-spinner></x-spinner>
                    <div id="order-list">
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
                                    <th style="width: 20%">Order #</th>
                                    <th>Customer </th>
                                    <th>Price </th>
                                    <th>Order Type </th>
                                    <th>Order Status </th>
                                    <th>Order Date </th>
                                    <th>Last Login</th>
                                    <th style="width: 10%">Action</th>
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
                                            {{ class_basename($order->typeable_type) }}{{ $order->typeable?->connect_type ? ' - ' . $order->typeable->connect_type : '' }}
                                            <br>
                                            {{ $order->payment_id === 'freeChat' ? 'Free Chat' : '' }}
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
                                        @php
                                            $lastLoggedInAt =
                                                $order->customer?->last_logged_in_at ??
                                                $order->astrologer?->last_logged_in_at;
                                        @endphp
                                        <td>{{ $lastLoggedInAt ? $lastLoggedInAt : '-' }}</td>
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
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/@simonwep/pickr/pickr.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/form-pickers.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#order-table').DataTable({
                searching: false,
                ordering: true,
                paging: false,
                bInfo: false,
                lengthChange: false
            });
        });
    </script>
    <script>
        function confirmDelete(adminID) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + adminID).submit();
                }
            })
        }
    </script>
    <script>
        document.getElementById('order_no').addEventListener('input', function() {
            this.value = this.value.replace(/#/g, '');
        });
    </script>
    <script>
        $(document).ready(function() {
            let typingTimer;
            const doneTypingInterval = 500;

            function showSpinner() {
                $('#loadingSpinner').show();
                $('#order-list').hide();
            }

            $('#orderFilterForm').on('submit', function() {
                showSpinner();
            });

            $('input[name="order_no"], input[name="full_name"]').on('keyup', function() {
                clearTimeout(typingTimer);
                const value = $(this).val();
                if (value.length >= 3 || value.length === 0) {
                    typingTimer = setTimeout(() => {
                        $('#orderFilterForm').submit();
                    }, doneTypingInterval);
                }
            });

            $('select[name="order_type[]"], select[name="order_status"], select[name="items"]').on('change',
                function() {
                    $('#orderFilterForm').submit();
                });

            flatpickr("#dateRange", {
                mode: "range",
                onClose: function() {
                    $('#orderFilterForm').submit();
                }
            });
        });
    </script>
@endsection
