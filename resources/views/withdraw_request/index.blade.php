@extends('layouts.master')
@section('title')
    Withdraw Requests
@endsection
@section('css')
    <style>
        .custom-nav-item {
            width: 200px !important;
        }

        .nav-tabs-custom .nav-item .nav-link.active {
            color: white !important;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Withdraw Requests
        @endslot
        @slot('title')
            Manage Withdraw Requests
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-xxl-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-pills nav-primary  mb-3 text-center" role="tablist">
                        <li class="nav-item custom-nav-item">
                            <a class="nav-link {{ request()->query('tab') == 'payout' ? 'active' : '' }}"
                                href="{{ route('admin.withdraw-request.index', ['tab' => 'payout']) }}" role="tab"
                                aria-selected="false">
                                Payout
                            </a>
                        </li>
                        <li class="nav-item custom-nav-item">
                            <a class="nav-link {{ request()->query('tab') == 'pending' ? 'active' : '' }}"
                                href="{{ route('admin.withdraw-request.index', ['tab' => 'pending']) }}" role="tab"
                                aria-selected="false">
                                Pending
                            </a>
                        </li>
                        <li class="nav-item custom-nav-item">
                            <a class="nav-link {{ request()->query('tab') == 'approved' ? 'active' : '' }}"
                                href="{{ route('admin.withdraw-request.index', ['tab' => 'approved']) }}" role="tab"
                                aria-selected="false">
                                Approved
                            </a>
                        </li>
                        <li class="nav-item custom-nav-item">
                            <a class="nav-link {{ request()->query('tab') == 'rejected' ? 'active' : '' }}"
                                href="{{ route('admin.withdraw-request.index', ['tab' => 'rejected']) }}" role="tab"
                                aria-selected="false">
                                Rejected
                            </a>
                        </li>

                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="base-justified-home" role="tabpanel">
                            @if ($tab === 'payout')
                                @include('withdraw_request.payout-list')
                            @else
                                @include('withdraw_request.requests-list')
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="viewwithdrawRequestModal" tabindex="-1" aria-labelledby="viewwithdrawRequestLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewwithdrawRequestLabel">Withdraw Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card" id="contact-view-detail">
                        <div class="card-body text-center">
                            <div class="position-relative d-inline-block">
                                <img id="astrologerAvatar" src="assets/images/users/avatar-10.jpg" alt=""
                                    class="avatar-lg rounded-circle img-thumbnail material-shadow">
                            </div>
                            <h5 class="mt-3 mb-1" id="astrologerName"></h5>
                            <h5 class="mb-1">Amount: <span class="text-primary">{{ $currencySymbol }} </span><span class="text-primary" id="amount"></span></h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="fw-medium" scope="row">Email ID</td>
                                            <td id="astrologerEmail"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Phone No</td>
                                            <td id="astrologerPhone"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Account Holder Name</td>
                                            <td id="astrologerAccountHolderName"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Account Number</td>
                                            <td id="astrologerAccountNumber"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Bank Name</td>
                                            <td id="astrologerBankName"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">IFSC Code</td>
                                            <td id="astrologerIFSCCode"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">PAN Number</td>
                                            <td id="astrologerPANNumber"></td>
                                        </tr>
                                        <tr id="rejectedReasonTr">
                                            <td class="fw-medium" scope="row">Rejected Reason</td>
                                            <td id="rejectedReason"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3" id="approvalCommentDiv">
                                <label for="approvalComment" class="fw-medium">Comment (Optional)</label>
                                <textarea id="approvalComment" class="form-control" rows="2" placeholder="Enter your comment here..."></textarea>
                                <input type="hidden" id="withdrawRequestId" name="id" value="">
                                <input type="hidden" id="astrologerId" name="astrologer_id" value="">
                                <input type="hidden" id="amountHidden" name="amount" value="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <a class="btn btn-success approve-btn" data-status="28">
                            <i class="ri-check-line" aria-hidden="true"></i>
                            Approve
                        </a>
                        <a class="btn btn-success payout-btn" data-status="28">
                            <i class="ri-check-line" aria-hidden="true"></i>
                            Payout
                        </a>
                        <a class="btn btn-danger approve-btn" data-status="29">
                            <i class="ri-close-line" aria-hidden="true"></i>
                            Reject
                        </a>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class=" ri-close-circle-fill" aria-hidden="true"></i>
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#withdrawRequests-table').DataTable({
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
            $('.view-withdrawRequest-btn').click(function() {
                let status = $(this).data('status');
                let tab = '{{ $tab }}';

                $('#withdrawRequestId').val($(this).data('id'));
                $('#amount').text($(this).data('amount'));
                $('#astrologerName').text($(this).data('name'));
                $('#astrologerEmail').text($(this).data('email'));
                $('#astrologerPhone').text($(this).data('phone'));
                $('#astrologerAvatar').attr('src', $(this).data('avatar'));
                $('#astrologerAccountHolderName').text($(this).data('holder-name'));
                $('#astrologerAccountNumber').text($(this).data('account-number'));
                $('#astrologerBankName').text($(this).data('bank-name'));
                $('#astrologerIFSCCode').text($(this).data('ifsc-code'));
                $('#astrologerPANNumber').text($(this).data('pan-number'));
                $('#rejectedReason').text($(this).data('rejected-reason'));
                $('#rejectedReasonTr').hide();
                $('#approvalCommentDiv').hide();
                $('#astrologerId').val($(this).data('astrologer-id'));
                $('#amountHidden').val($(this).data('amount'));
                if (status === 29) {
                    $('#rejectedReasonTr').show();
                }
                // Accept & Reject Disabled
                if (status === 27) {
                    $(".approve-btn[data-status='28']").show();
                    $(".approve-btn[data-status='29']").show();
                    $(".payout-btn[data-status='28']").hide();
                    $("#approvalCommentDiv").show();
                } else if (tab === 'payout' ) {
                    $(".payout-btn[data-status='28']").show();
                    $(".approve-btn[data-status='28']").hide();
                    $(".approve-btn[data-status='29']").hide();
                    $("#approvalCommentDiv").show();
                } else {
                    $(".approve-btn[data-status='28']").hide();
                    $(".payout-btn[data-status='28']").hide();
                    $(".approve-btn[data-status='29']").hide();
                }
            });
        });
    </script>
    <script>
        // Approved or not
        $(document).ready(function() {
            $(".approve-btn,.payout-btn").click(function() {
                var withdrawRequestId = $("#withdrawRequestId").val();
                var status = $(this).data("status");
                var tab = '{{ $tab }}';
                var actionText = tab == 'payout' ? "Payout" : (status == 28 ? "Approve" : "Reject");
                var successMessage = tab == 'payout' ? "Withdraw Request has been payout successfully!" : 
                (status == 28 ? "Withdraw Request has been approved successfully!" : "Withdraw Request has been rejected successfully!");
                var comment = $("#approvalComment").val().trim();
                if (status == 29) {
                    $(".modal").modal("hide");
                    Swal.fire({
                        title: "Reject Withdraw Request",
                        text: "Please provide a reason for rejection:",
                        input: "text",
                        inputPlaceholder: "Enter reason here...",
                        showCancelButton: true,
                        confirmButtonColor: "#dc3545",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Reject",
                        cancelButtonText: "Cancel",
                        inputValidator: (value) => {
                            if (!value) {
                                return "Rejection reason is required!";
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var reason = result.value;
                            var astrologerId = $("#astrologerId").val();
                            var amount = $("#amountHidden").val();
                            sendApprovalRequest(withdrawRequestId, status, reason, comment, astrologerId, amount, tab, successMessage);
                        }
                    });
                } else {
                    Swal.fire({
                        title: `Are you sure?`,
                        text: `Do you really want to ${actionText.toLowerCase()} this request?`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#28a745",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: `Yes, ${actionText}`,
                        cancelButtonText: "Cancel",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var astrologerId = $("#astrologerId").val();
                            var amount = $("#amountHidden").val();
                            sendApprovalRequest(withdrawRequestId, status, null, comment, astrologerId, amount, tab, successMessage);
                        }
                    });
                }
            });

            function sendApprovalRequest(withdrawRequestId, status, reason, comment, astrologerId, amount, tab, successMessage) {
                $.ajax({
                    url: "{{ route('updateWithdrawApprovalStatus') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: withdrawRequestId,
                        status: status,
                        reason: reason,
                        comment: comment,
                        astrologer_id: astrologerId,
                        amount: amount,
                        tab: tab
                    },
                    success: function(response) {
                        Swal.fire({
                            title: "Success!",
                            text: successMessage,
                            icon: "success",
                            timer: 2000,
                            showConfirmButton: false
                        });

                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: "Error!",
                            text: "Error updating approval status!",
                            icon: "error"
                        });
                        console.log("Error: " + xhr.responseText);
                    }
                });
            }
        });
    </script>
@endsection
