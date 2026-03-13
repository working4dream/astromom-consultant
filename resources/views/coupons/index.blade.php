@extends('layouts.master')
@section('title')
    Coupons
@endsection
@section('content')
    @component('components.breadcrumb')
        @section('add-route')
            {{ route('admin.coupons.create') }}
        @endsection
        @slot('li_1')
            Coupons
        @endslot
        @slot('title')
            Manage Coupons
        @endslot
    @endcomponent
    <div class="row">
        @include('components.add-button')
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.coupons.index') }}" class="mb-3" id="couponFilterForm">
                        <div class="row">
                            <div class="col-md-2">
                                <input type="text" name="name" class="form-control" placeholder="Name"
                                    value="{{ request()->name }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="code" class="form-control" placeholder="Code"
                                    value="{{ request()->code }}">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" data-choices data-choices-multiple-groups="true"
                                    data-placeholder="Used Type" name="used_type[]" multiple>
                                    @if ($settings)
                                        @foreach (explode(',', $settings['service_types']) as $service_type)
                                            <option @if (isset(request()->used_type) && in_array($service_type, request()->used_type)) selected @endif
                                                value="{{ $service_type }}">
                                                {{ $service_type }}</option>
                                        @endforeach
                                    @endif
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
                            <div class="col-md-4 text-end">
                                <a href="{{ route('admin.coupons.index') }}" class="btn btn-soft-secondary"><i
                                        class="ri-refresh-line"></i></a>
                            </div>
                        </div>
                    </form>
                    <x-spinner></x-spinner>
                    <div id="coupon-list">
                        <table id="coupon-table"
                            class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th style="width: 20%">Name</th>
                                    <th>Code</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Used Type</th>
                                    <th>Status</th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($coupons as $key => $coupon)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td> <a href="{{ route('admin.coupons.edit', $coupon->id) }}">{{ $coupon->name }}
                                            </a>
                                        </td>
                                        <td>{{ $coupon->code }}</td>
                                        <td>{{ $coupon->start_date }}</td>
                                        <td>{{ $coupon->expiry_date }}</td>
                                        <td>{{ $coupon->used_type }}</td>
                                        <td>
                                            <div class="form-check form-switch form-switch-lg" dir="ltr"
                                                style="padding-left:10px;">
                                                @if ($coupon->active == 1)
                                                    <span class="badge bg-success"> Active</span>
                                                @else
                                                    <span class="badge bg-danger"> InActive</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-fill align-middle"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item edit-item-btn"
                                                            href="{{ route('admin.coupons.edit', $coupon->id) }}">
                                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i>
                                                            Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item remove-item-btn text-danger"
                                                            onclick="confirmDelete({{ $coupon->id }})">
                                                            <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i>
                                                            Delete
                                                        </a>
                                                        <form id="delete-form-{{ $coupon->id }}"
                                                            action="{{ route('admin.coupons.destroy', $coupon->id) }}"
                                                            method="POST" style="display: none;">
                                                            @csrf
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if (!$coupons->hasPages() && $coupons->total() > 0)
                            <p class="small text-muted">
                                Showing
                                <span class="fw-semibold">{{ $coupons->firstItem() }}</span>
                                to
                                <span class="fw-semibold">{{ $coupons->lastItem() }}</span>
                                of
                                <span class="fw-semibold">{{ $coupons->total() }}</span>
                                results
                            </p>
                        @endif
                        {{ $coupons->appends(request()->query())->links() }}
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
        $(document).ready(function() {
            $('#coupon-table').DataTable({
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
        $(document).ready(function() {
            let typingTimer;
            const doneTypingInterval = 500;

            function showCouponSpinner() {
                $('#loadingSpinner').show();
                $('#coupon-list').hide();
            }

            $('#couponFilterForm').on('submit', function() {
                showCouponSpinner();
            });

            $('input[name="name"], input[name="code"]').on('keyup', function() {
                clearTimeout(typingTimer);
                const value = $(this).val();
                if (value.length >= 3 || value.length === 0) {
                    typingTimer = setTimeout(() => {
                        $('#couponFilterForm').submit();
                    }, doneTypingInterval);
                }
            });

            $('select[name="used_type[]"], select[name="items"]').on('change', function() {
                $('#couponFilterForm').submit();
            });
        });
    </script>
@endsection
