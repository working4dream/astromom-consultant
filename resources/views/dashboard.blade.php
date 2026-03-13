@extends('layouts.master')
@section('title')
    Dashboards
@endsection
@section('css')
    <style>
        .card-footer {
            padding-bottom: 0px !important;
        }

        .sales-range.active {
            background-color: rgba(var(--bs-primary-rgb), 0.8) !important;
            color: white !important;
        }
        .chart-container {
            position: relative;
            height: 300px; /* Chart height */
            width: 100%;
        }

        .chart-container #line {
            width: 100% !important;
            height: 100% !important;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboards
        @endslot
        @slot('title')
            Welcome, {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
        @endslot
    @endcomponent
    <div class="row">
            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                        </div>
                        <x-custom-date-range action="{{ route('admin.dashboard') }}"></x-custom-date-range>
                    </div>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body d-flex gap-3 align-items-center">
                                <div class="avatar-sm">
                                    <div
                                        class="avatar-title border bg-warning-subtle border-warning border-opacity-25 rounded-2 fs-17">
                                        <i class="ri-bar-chart-2-line text-warning fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fs-15">₹ {{ number_format($totalSales, 2) }}</h5>
                                    <p class="mb-0 text-muted">Total Sales</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body d-flex gap-3 align-items-center">
                                <div class="avatar-sm">
                                    <div
                                        class="avatar-title border bg-success-subtle border-success border-opacity-25 rounded-2 fs-17">
                                        <i class="ri-shopping-cart-line text-success fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fs-15">{{ $totalOrders }} <span>({{ $totalPaidOrders }} Paid,
                                            {{ $totalFreeOrders }} Free) </span></h5>
                                    <p class="mb-0 text-muted">Total Orders</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body d-flex gap-3 align-items-center">
                                <div class="avatar-sm">
                                    <div
                                        class="avatar-title border bg-danger-subtle border-danger border-opacity-25 rounded-2 fs-17">
                                        <i class="ri-user-line text-danger fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fs-15">{{ $totalCustomers }}</h5>
                                    <p class="mb-0 text-muted">Total Customers</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body d-flex gap-3 align-items-center">
                                <div class="avatar-sm">
                                    <div
                                        class="avatar-title border bg-primary-subtle border-primary border-opacity-25 rounded-2 fs-17">
                                        <i class="ri-user-line text-primary fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fs-15">{{ $totalExperts }}</h5>
                                    <p class="mb-0 text-muted">Total Experts</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body d-flex gap-3 align-items-center">
                                <div class="avatar-sm">
                                    <div
                                        class="avatar-title border bg-success-subtle border-success border-opacity-25 rounded-2 fs-17">
                                        <i class="ri-calendar-check-line text-success fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fs-15">{{ $appointmentsBookedCount }} <span>(₹
                                            {{ $appointmentsBookedAmount }})</span></h5>
                                    <p class="mb-0 text-muted">Appointments Booked</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body d-flex gap-3 align-items-center">
                                <div class="avatar-sm">
                                    <div
                                        class="avatar-title border bg-primary-subtle border-primary border-opacity-25 rounded-2 fs-17">
                                        <i class="ri-user-line text-primary fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fs-15">{{ $activeFreeChatUsers }}</h5>
                                    <p class="mb-0 text-muted">Active Free Chat Users</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body d-flex gap-3 align-items-center">
                                <div class="avatar-sm">
                                    <div
                                        class="avatar-title border bg-danger-subtle border-danger border-opacity-25 rounded-2 fs-17">
                                        <i class="ri-hourglass-line text-danger fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fs-15">{{ $pendingWithdrawalRequests }}</h5>
                                    <p class="mb-0 text-muted">Pending Withdrawal Requests</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body d-flex gap-3 align-items-center">
                                <div class="avatar-sm">
                                    <div
                                        class="avatar-title border bg-info-subtle border-info border-opacity-25 rounded-2 fs-17">
                                        <i class="ri-ticket-line text-info fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fs-15">{{ $couponsRedeemed }}</h5>
                                    <p class="mb-0 text-muted">Coupons Redeemed</p>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    @foreach ($totalAppointmentsMinutes as $totalAppointmentsMinute)
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="card-body d-flex gap-3 align-items-center">
                                    <div class="avatar-sm">
                                        <div
                                            class="avatar-title border bg-success-subtle border-success border-opacity-25 rounded-2 fs-17">
                                            <i class="ri-timer-line text-success fs-2"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fs-15">{{ $totalAppointmentsMinute->total_minutes }} Mins</h5>
                                        <p class="mb-0 text-muted">{{ ucfirst($totalAppointmentsMinute->connect_type) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <!-- Orders Analytics -->
            <div class="col-xl-12">
                <div class="row align-items-stretch">
                    <div class="col-xl-12 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Sales</h4>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary sales-range"
                                        data-range="daily">Daily</button>
                                    <button class="btn btn-sm btn-outline-primary sales-range"
                                        data-range="weekly">Weekly</button>
                                    <button class="btn btn-sm btn-outline-primary sales-range"
                                        data-range="monthly">Monthly</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="bar" class="chartjs-chart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Orders</h4>
                                <div>
                                    <button class="btn btn-sm btn-outline-success filter-status" data-status="7">Paid</button>
                                    <button class="btn btn-sm btn-outline-warning filter-status"
                                        data-status="6">Pending</button>
                                    <button class="btn btn-sm btn-outline-danger filter-status"
                                        data-status="8">Cancelled</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered" id="salesTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Product Type</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- User Analytics -->
            <div class="col-xl-12">
                <div class="row align-items-stretch">
                    <div class="col-xl-12 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Latest Customers</h4>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered" id="customerTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Contact Details</th>
                                            <th>DOB</th>
                                            <th>City</th>
                                            <th>Joined At</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
@section('script-bottom')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="{{ URL::asset('build/libs/chart.js/chart.umd.js') }}"></script>
    {{-- <script src="{{ URL::asset('build/js/pages/chartjs.init.js') }}"></script> --}}
    <script>
        let ctx = document.getElementById('bar');
        let salesChart;

        function loadChart(range = 'monthly') {
            fetch(`{{ route('admin.sales-data', ['range' => ':range']) }}`.replace(':range', range))
                .then(res => res.json())
                .then(chartData => {
                    if (salesChart) {
                        salesChart.data.labels = chartData.labels;
                        salesChart.data.datasets[0].data = chartData.data;
                        salesChart.update();
                    } else {
                        salesChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: chartData.labels,
                                datasets: [{
                                    label: "Sales",
                                    backgroundColor: "rgb(103,103,103,0.8)",
                                    borderColor: "rgb(103,103,103,0.8)",
                                    borderWidth: 1,
                                    hoverBackgroundColor: "rgb(103,103,103,0.9)",
                                    hoverBorderColor: "rgb(103,103,103,0.9)",
                                    data: chartData.data
                                }]
                            },
                            options: {
                                scales: {
                                    x: {
                                        ticks: {
                                            font: {
                                                family: 'Poppins'
                                            }
                                        }
                                    },
                                    y: {
                                        ticks: {
                                            font: {
                                                family: 'Poppins'
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        labels: {
                                            font: {
                                                family: 'Poppins'
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
        }

        document.querySelectorAll('[data-range]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('[data-range]').forEach(b => {
                    b.disabled = false;
                    b.classList.remove('active');
                });
                btn.disabled = true;
                btn.classList.add('active');

                loadChart(btn.getAttribute('data-range'));
            });
        });
        document.querySelector('[data-range="monthly"]').disabled = true;
        document.querySelector('[data-range="monthly"]').classList.add('active');

        loadChart();
    </script>
    <script>
        $(document).ready(function() {
            var table = $('#salesTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                lengthChange: false,
                ordering: false,
                ajax: {
                    url: "{{ route('orders.filter') }}",
                    data: function(d) {
                        d.status = $('.filter-status.active').data('status') || 7;
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'order_id_link',
                        name: 'order_id_link'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'total_price',
                        name: 'total_price'
                    },
                    {
                        data: 'typeable_type',
                        name: 'typeable_type'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    }
                ]
            });

            $(".filter-status").on("click", function() {
                $(".filter-status").removeClass("active");
                $(this).addClass("active");
                table.ajax.reload();
            });

            // Default active Paid button
            $(".filter-status[data-status='7']").addClass("active");
        });
    </script>
    <script>
        let lc = document.getElementById('line');
        let lineChart;

        function loadLineChart() {
            fetch("{{ route('orders.free-chat-usage-data') }}")
                .then(res => res.json())
                .then(chartData => {
                    if (lineChart) {
                        lineChart.data.labels = chartData.labels;
                        lineChart.data.datasets[0].data = chartData.data;
                        lineChart.update();
                    } else {
                        lineChart = new Chart(lc, {
                            type: 'line',
                            data: {
                                labels: chartData.labels,
                                datasets: [{
                                    label: "Free Chat Usage",
                                    fill: true,
                                    lineTension: 0.5,
                                    backgroundColor: "rgb(103,103,103,0.8)",
                                    borderColor: "rgb(103,103,103,0.8)",
                                    borderCapStyle: 'butt',
                                    borderDash: [],
                                    borderDashOffset: 0.0,
                                    borderJoinStyle: 'miter',
                                    pointBorderColor: "rgb(103,103,103,0.8)",
                                    pointBackgroundColor: "#fff",
                                    pointBorderWidth: 1,
                                    pointHoverRadius: 5,
                                    pointHoverBackgroundColor: "rgb(103,103,103,0.8)",
                                    pointHoverBorderColor: "#fff",
                                    pointHoverBorderWidth: 2,
                                    pointRadius: 1,
                                    pointHitRadius: 10,
                                    data: chartData.data
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    x: {
                                        ticks: {
                                            font: {
                                                family: 'Poppins'
                                            }
                                        }
                                    },
                                    y: {
                                        ticks: {
                                            font: {
                                                family: 'Poppins'
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        labels: {
                                            font: {
                                                family: 'Poppins'
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
        }
        loadLineChart();
    </script>
    <script>
        $(function () {
            $('#customerTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                lengthChange: false,
                ordering: false,
                ajax: '{{ route("latest-customers") }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'contact_details', name: 'contact_details' },
                    { data: 'dob', name: 'dob' },
                    { data: 'city', name: 'city' },
                    { data: 'created_at', name: 'created_at', orderable: false, searchable: false }
                ],
            });
        });
    </script>
@endsection
