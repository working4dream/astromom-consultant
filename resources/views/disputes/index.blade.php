@extends('layouts.master')
@section('title')
    User Disputes
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            User Disputes
        @endslot
        @slot('title')
            Manage Disputes
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-xxl-3 col-sm-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Total Tickets</p>
                            <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value"
                                    data-target="{{ $total_disputes }}"></span>
                            </h2>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle text-info rounded-circle fs-4">
                                    <i class="ri-ticket-2-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Pending Tickets</p>
                            <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value"
                                    data-target="{{ $pending_disputes }}"></span></h2>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle text-info rounded-circle fs-4">
                                    <i class="mdi mdi-timer-sand"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Closed Tickets</p>
                            <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value"
                                    data-target="{{ $closed_disputes }}">0</span></h2>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-4">
                                    <i class=" ri-close-circle-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.disputes') }}" class="mb-3" id="disputeFilterForm">
                        <div class="row">
                            <div class="col-md-2">
                                <input type="text" name="booking_id" class="form-control" placeholder="Booking Id"
                                    value="{{ request()->booking_id }}">
                            </div>

                            <div class="col-md-2">
                                <input type="text" name="full_name" class="form-control" placeholder="Full Name"
                                    value="{{ request()->full_name }}">
                            </div>

                            <div class="col-md-2">
                                <input type="text" id="dateRange" class="form-control" name="date" data-provider="flatpickr"
                                    data-date-format="Y-m-d" data-range-date="true" value="{{ request()->date }}"
                                    placeholder="Appointment Date">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">Select Status</option>
                                    <option value="0" @if (request()->status == '0') selected @endif>Close</option>
                                    <option value="1" @if (request()->status == '1') selected @endif>Open</option>
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
                                <a href="{{ route('admin.disputes') }}" class="btn btn-soft-secondary"><i
                                        class="ri-refresh-line"></i></a>
                            </div>
                        </div>
                    </form>
                    <x-spinner></x-spinner>
                    <div id="dispute-list">
                        <table id="dispute-table"
                            class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Ticket ID </th>
                                    <th>Appointment Id</th>
                                    <th>Customer </th>
                                    <th>Reason</th>
                                    <th>Appointment Date</th>
                                    <th>Status</th>
                                    <th> Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($disputes as $key => $dispute)
                                    <tr>
                                        @php
                                            $ticketStatus =
                                                $dispute->status === 1
                                                    ? '<span class="badge bg-success">Open</span>'
                                                    : '<span class="badge bg-danger">Close</span>';
                                        @endphp
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <a href="{{ route('admin.disputes.show', $dispute->id) }}" role="button"
                                                class="text-primary"> #{{ $dispute->ticket_id }} </a>
                                        </td>
                                        <td>#{{ $dispute?->booking_id }}</td>
                                        <td>
                                            <a href="{{ route('admin.customers.show', $dispute->customer_id) . '?tab=appointments' }}">
                                                <div class="d-flex align-items-center">

                                                    <img src="{{ $dispute?->customer?->profile_picture }}"
                                                        onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                                        class="rounded-circle avatar-sm me-2" alt="Profile Picture" />
                                                    <div>
                                                        <div>{{ $dispute?->customer?->full_name }}</div>
                                                        <div class="text-muted">{{ $dispute?->customer?->email }}</div>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td>{{ $dispute->reason }}</td>
                                        <td>{{ \Carbon\Carbon::parse($dispute->appointment_date)->format('d-m-Y') }}</td>
                                        <td>{!! $ticketStatus !!}</td>
                                        <td>
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-fill align-middle"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a href="{{ route('admin.disputes.show', $dispute->id) }}"
                                                            class="dropdown-item " role="button">
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
                        @if (!$disputes->hasPages() && $disputes->total() > 0)
                            <p class="small text-muted">
                                Showing
                                <span class="fw-semibold">{{ $disputes->firstItem() }}</span>
                                to
                                <span class="fw-semibold">{{ $disputes->lastItem() }}</span>
                                of
                                <span class="fw-semibold">{{ $disputes->total() }}</span>
                                results
                            </p>
                        @endif
                        {{ $disputes->appends(request()->query())->links() }}
                    </div>
                    <div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModal" aria-hidden="true"
                        style="display: none;">
                        <div class="modal-dialog modal-lg" role="form">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="myModalLabel">Lesson
                                        Preview
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <h5 class="live-preview-title"></h5>
                                    <div class="live-preview-data">
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        $(".myModalPreview").on('click', (function(evt) {
            let html = '';
            html += '<div class="row">';
            html += '<div class="row pt-2"> ';
            html += '<div class = "col-xxl-3 col-md-2 "><div> Booking Id </div></div>';
            html += '<div class = "col-xxl-9 col-md-10"> #' + $(this).attr('data-booking_id') + '</div></div>';
            html += '<div class="row pt-2"> ';
            html += '<div class = "col-xxl-3 col-md-2 "><div> Customer </div></div>';
            html += '<div class = "col-xxl-9 col-md-10"> ' + $(this).attr('data-customer') + '</div></div>';
            html += '<div class="row pt-2"> ';
            html += '<div class = "col-xxl-3 col-md-2 "><div> Ticket Id</div></div>';
            html += '<div class = "col-xxl-9 col-md-10"> #' + $(this).attr('data-ticket_id') + '</div></div>';
            html += '<div class="row pt-2"> ';
            html += '<div class = "col-xxl-3 col-md-2 "><div> Reason </div></div>';
            html += '<div class = "col-xxl-9 col-md-10"> ' + $(this).attr('data-reason') + '</div></div>';
            html += '<div class="row pt-2"> ';
            html += '<div class = "col-xxl-3 col-md-2 "><div> Other Reason</div></div>';
            html += '<div class = "col-xxl-9 col-md-10"> ' + $(this).attr('data-other_reason') +
                '</div></div>';
            html += '<div class="row pt-2"> ';
            html += '<div class = "col-xxl-3 col-md-2 "><div> Appointment Date </div></div>';
            html += '<div class = "col-xxl-9 col-md-10"> ' + $(this).attr('data-appointment_date') +
                '</div></div>';
            html += '<div class="row pt-2"> ';
            html += '<div class = "col-xxl-3 col-md-2 "><div>Description </div></div>';
            html += '<div class = "col-xxl-9 col-md-10"> ' + $(this).attr('data-description') + '</div></div>';
            html += '<div class="row pt-2"> ';
            html += '<div class = "col-xxl-3 col-md-2 "><div>Status </div></div>';
            html += '<div class = "col-xxl-9 col-md-10"> ' + $(this).attr('data-status') + '</div></div>';
            html += '<div class="row pt-2"> ';
            html += '<div class = "col-xxl-3 col-md-2 "><div>File </div></div>';
            html += '<div class = "col-xxl-9 col-md-10"> <a href="' + $(this).attr('data-file') +
                '" download><i class="ri-download-2-fill"></i> Download</a></div></div>';
            $('.live-preview-data').html(html);

        }));
        $(document).ready(function() {
            $('#dispute-table').DataTable({
                searching: false,
                ordering: true,
                paging: false,
                bInfo: false,
                lengthChange: false
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            let typingTimer;
            const doneTypingInterval = 500;

            function showDisputeSpinner() {
                $('#loadingSpinner').show();
                $('#dispute-list').hide();
            }

            $('#disputeFilterForm').on('submit', function() {
                showDisputeSpinner();
            });

            $('input[name="booking_id"], input[name="full_name"]').on('keyup', function() {
                clearTimeout(typingTimer);
                const value = $(this).val();
                if (value.length >= 3 || value.length === 0) {
                    typingTimer = setTimeout(() => {
                        $('#disputeFilterForm').submit();
                    }, doneTypingInterval);
                }
            });

            $('select[name="status"], select[name="items"]').on(
                'change',
                function() {
                    $('#disputeFilterForm').submit();
                });

            flatpickr("#dateRange", {
                mode: "range",
                onClose: function() {
                    $('#disputeFilterForm').submit();
                }
            });
        });
    </script>
@endsection
