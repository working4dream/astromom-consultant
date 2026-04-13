@extends('layouts.master')
@section('title')
    Customers
@endsection
@section('add-route')
    {{ route('admin.customers.create') }}
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Customers
        @endslot
        @slot('title')
            Manage Customers
        @endslot
    @endcomponent
    <div class="row">
        @include('components.add-button')
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.customers.index') }}" class="mb-3" id="customerFilterForm">
                        <div class="row">
                            <div class="col-md-2">
                                <input type="text" name="full_name" class="form-control" placeholder="Full Name"
                                    value="{{ request()->full_name }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="email" class="form-control" placeholder="Email"
                                    value="{{ request()->email }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="mobile_number" class="form-control" placeholder="Mobile"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                    value="{{ request()->mobile_number }}">
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control flatpickr-input active" id="dob"
                                    placeholder="DOB" name="dob" value="{{ request()->dob }}" data-provider="flatpickr">
                            </div>
                            <div class="col-md-2">
                                <select id="searchSelect" class="form-control" data-choices data-choices-sorting-false
                                    name="city_id">
                                    <option value="">Select City</option>
                                    @foreach ($cities as $city)
                                        <option value="{{ $city->id }}"
                                            {{ request()->city_id == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}</option>
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
                            <div class="col-md-12 pt-3 text-end">
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-soft-secondary"><i
                                        class="ri-refresh-line"></i></a>
                                <a href="{{ route('admin.customers.export', request()->query()) }}"
                                    class="btn btn-soft-secondary"><i class=" ri-file-excel-line"></i></a>
                            </div>
                        </div>
                    </form>
                    <x-spinner></x-spinner>
                    <div id="customer-list">
                        <table id="customers-table"
                            class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Contact Details</th>
                                    <th>DOB</th>
                                    <th>City</th>
                                    <th>Notes</th>
                                    <th>Last logged in</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customers as $key => $customer)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $customer->profile_picture }}"
                                                    onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                                    class="rounded-circle avatar-sm me-2" alt="Profile Picture" />
                                                <div>
                                                    <div><a href="{{ route('admin.customers.show', $customer->id) . '?tab=appointments' }}">
                                                            {{ $customer->first_name }} {{ $customer->last_name }}</a>
                                                    </div>
                                                    <div class="text-muted">{{ $customer->professional_title }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {{ $customer->email }} <br>
                                            {{ $customer->mobile_number }}
                                        </td>
                                        <td>
                                            @if ($customer->dob)
                                                {{ fmt_date($customer->dob, 'd-m-Y') }}
                                            @endif
                                        </td>
                                        <td>
                                            {{ $customer?->city?->name }}
                                        </td>
                                        <td>
                                            @if ($customer->notes)
                                                {!! substr_replace($customer?->notes, '...', 30) !!}
                                                <a href="javascript:void(0);" class="link-primary previewData"
                                                    data-bs-toggle="modal" data-bs-target="#myModal"
                                                    data-notes="{{ $customer?->notes }}"
                                                    data-title="{{ $customer->first_name }} {{ $customer->last_name }}">Read
                                                    More <i class="ri-arrow-right-s-line align-middle ms-1 lh-1"></i></a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($customer->last_logged_in_at)
                                                {{ user_tz_format($customer->last_logged_in_at, 'd-m-Y H:i:s') }}
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-fill align-middle"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item edit-item-btn"
                                                            href="{{ route('admin.customers.edit', $customer->id) }}">
                                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i>
                                                            Edit</a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item remove-item-btn cursor-pointer"
                                                            onclick="confirmDelete({{ $customer->id }})">
                                                            <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i>
                                                            <span class="text-danger">Delete</span>
                                                        </a>
                                                        <form id="delete-form-{{ $customer->id }}"
                                                            action="{{ route('admin.customers.destroy', $customer->id) }}"
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
                        @if (!$customers->hasPages() && $customers->total() > 0)
                            <p class="small text-muted">
                                Showing
                                <span class="fw-semibold">{{ $customers->firstItem() }}</span>
                                to
                                <span class="fw-semibold">{{ $customers->lastItem() }}</span>
                                of
                                <span class="fw-semibold">{{ $customers->total() }}</span>
                                results
                            </p>
                        @endif
                        {{ $customers->appends(request()->query())->links() }}
                    </div>
                    <div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel"
                        aria-hidden="true" style="display: none;">
                        <div class="modal-dialog modal-lg" role="form">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="live-preview-title" id="myModalLabel">
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="live-preview-data">
                                    </div>
                                </div>
                            </div>
                        </div>
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
        $(".previewData").on('click', (function(evt) {
            $('.live-preview-title').text($(this).attr('data-title'));
            $('.live-preview-data').text($(this).attr('data-notes'));

        }));

        $(document).ready(function() {
            $('#customers-table').DataTable({
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
        document.addEventListener("DOMContentLoaded", function() {
            flatpickr("#dob", {
                dateFormat: "Y-m-d",
                allowInput: true
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            let typingTimer;
            const doneTypingInterval = 500;
    
            function showCustomerSpinner() {
                $('#loadingSpinner').show();
                $('#customer-list').hide();
            }
    
            $('#customerFilterForm').on('submit', function () {
                showCustomerSpinner();
            });
    
            $('input[name="full_name"], input[name="email"], input[name="mobile_number"]').on('keyup', function () {
                clearTimeout(typingTimer);
                const value = $(this).val();
                if (value.length >= 3 || value.length === 0) {
                    typingTimer = setTimeout(() => {
                        $('#customerFilterForm').submit();
                    }, doneTypingInterval);
                }
            });
    
            $('select[name="city_id"], select[name="items"], input[name="dob"]').on('change', function () {
                $('#customerFilterForm').submit();
            });
        });
    </script>
    
@endsection
