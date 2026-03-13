@extends('layouts.master')
@section('title')
    Free Chat Usage
@endsection
@section('css')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Free Chat Usage
        @endslot
        @slot('title')
            Manage Free Chat Usage
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.freeChatUsage.index') }}" class="mb-3" id="orderFilterForm">
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
                            <div class="col-md-2">
                                <a href="{{ route('admin.freeChatUsage.index') }}" class="btn btn-soft-secondary"><i
                                        class="ri-refresh-line"></i></a>
                            </div>
                        </div>
                    </form>
                    <x-spinner></x-spinner>
                    <div id="chat-usage-list">
                        <p class="small text-muted">
                            Showing
                            <span class="fw-semibold">{{ $orders->firstItem() }}</span>
                            to
                            <span class="fw-semibold">{{ $orders->lastItem() }}</span>
                            of
                            <span class="fw-semibold">{{ $orders->total() }}</span>
                            results
                        </p>
                        <table id="chat-usage-table"
                            class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th style="width: 20%">Order #</th>
                                    <th>Customer </th>
                                    <th>Appointment With </th>
                                    <th>Duration </th>
                                    <th>Order Date </th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $shownPairs = [];
                                @endphp
                                @foreach ($orders as $key => $order)
                                    @if (!$order->typeable?->callLog?->call_time)
                                        @continue
                                    @endif
                                    @php
                                        $pairKey = $order->customer_id . '-' . $order->astrologer_id;
                                        if (in_array($pairKey, $shownPairs)) {
                                            continue;
                                        }
                                        $shownPairs[] = $pairKey;
                                    @endphp
                                    <tr>
                                        <td>{{ $orders->firstItem() + $key }}</td>
                                        <td> <a href="{{ route('admin.orders.show', $order->id) }}">
                                                #{{ $order->order_id }}</a></td>
                                        <td>
                                            <a href="{{ route('admin.customers.show', $order->customer_id) . '?tab=appointments' }}">
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        $user = $order->customer;
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
                                        <td>
                                            <a href="{{ route('admin.experts.show', $order->astrologer_id) . '?tab=appointments' }}">
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        $user = $order->astrologer;
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
                                        <td>{{ $order->typeable?->callLog?->call_time }}</td>
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
                                                            href="{{ route('admin.freeChatUsage.show', $order->id) }}">
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
            $('#chat-usage-table').DataTable({
                searching: false,
                ordering: true,
                paging: false,
                bInfo: false,
                lengthChange: false
            });
        });
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
                $('#chat-usage-list').hide();
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

            $('select[name="items"]').on('change',
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
