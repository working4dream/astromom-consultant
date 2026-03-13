@extends('layouts.master')
@section('title')
    Earning Report
@endsection
@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Earning Report
        @endslot
        @slot('title')
            Manage Earning Report
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
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.earning-report.index') }}" class="mb-3"
                        id="earningReportFilterForm">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="full_name" class="form-control" placeholder="Full Name"
                                    value="{{ request()->full_name }}">
                            </div>
                            <div class="col-md-4">
                                <input type="text" id="daterange" class="form-control" name="date_range"
                                    value="{{ request()->date_range }}" placeholder="Date Range">
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="{{ route('admin.earning-report.index') }}" class="btn btn-soft-secondary"><i
                                        class="ri-refresh-line"></i></a>
                            </div>
                        </div>
                    </form>
                    <x-spinner></x-spinner>
                    <div id="earning-report-list">
                        <p class="small text-muted">
                            Showing
                            <span class="fw-semibold">{{ $earningReports->firstItem() }}</span>
                            to
                            <span class="fw-semibold">{{ $earningReports->lastItem() }}</span>
                            of
                            <span class="fw-semibold">{{ $earningReports->total() }}</span>
                            results
                        </p>
                        <table id="earning-report-table"
                            class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Expert </th>
                                    <th>Expert Earning </th>
                                    <th>Paid Amount </th>
                                    <th>Due Amount </th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($earningReports as $key => $earningReport)
                                    <tr>
                                        <td>{{ $earningReports->firstItem() + $key }}</td>
                                        <td>
                                            <a href="{{ route('admin.experts.show', $earningReport->astrologer_id) . '?tab=appointments' }}">
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        $astrologer = $earningReport->astrologer;
                                                    @endphp

                                                    <img src="{{ $astrologer?->profile_picture }}"
                                                        onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                                        class="rounded-circle avatar-sm me-2" alt="Profile Picture" />

                                                    <div>
                                                        <div>{{ $astrologer?->full_name }}</div>
                                                        <div class="text-muted">{{ $astrologer?->email }}</div>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td><i class="fa fa-rupee"></i> {{ $earningReport->total_earning ?? 0 }}</td>
                                        <td><i class="fa fa-rupee"></i> {{ $earningReport->paid_amount ?? 0 }}</td>
                                        <td><i class="fa fa-rupee"></i> {{ $earningReport->total_earning - $earningReport->paid_amount }}</td>
                                        <td>
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-fill align-middle"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item edit-item-btn"
                                                            href="{{ route('admin.earning-report.show', $earningReport->id) }}">
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
                        @if (!$earningReports->hasPages() && $earningReports->total() > 0)
                            <p class="small text-muted">
                                Showing
                                <span class="fw-semibold">{{ $earningReports->firstItem() }}</span>
                                to
                                <span class="fw-semibold">{{ $earningReports->lastItem() }}</span>
                                of
                                <span class="fw-semibold">{{ $earningReports->total() }}</span>
                                results
                            </p>
                        @endif
                        {{ $earningReports->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script-bottom')
    <script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            let typingTimer;
            const doneTypingInterval = 500;

            function showEarningReportSpinner() {
                $('#loadingSpinner').show();
                $('#earning-report-list').hide();
            }

            $('#earningReportFilterForm').on('submit', function() {
                showEarningReportSpinner();
            });

            $('input[name="full_name"]').on('keyup', function() {
                clearTimeout(typingTimer);
                const value = $(this).val();
                if (value.length >= 3 || value.length === 0) {
                    typingTimer = setTimeout(() => {
                        $('#earningReportFilterForm').submit();
                    }, doneTypingInterval);
                }
            });
        });
    </script>
    <script>
        $(function() {
            const form = $('#earningReportFilterForm');
    
            const $daterange = $('#daterange');
    
            $daterange.daterangepicker({
                opens: 'right',
                autoUpdateInput: false,
                locale: {
                    format: 'DD MMM, YYYY'
                },
                alwaysShowCalendars: true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,'month').endOf('month')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                }
            });
    
            $daterange.on('apply.daterangepicker', function(ev, picker) {
                const start = picker.startDate.format('DD MMM, YYYY');
                const end = picker.endDate.format('DD MMM, YYYY');
    
                $daterange.val(start + ' - ' + end);
    
                form.submit();
            });
        });
    </script>
@endsection
