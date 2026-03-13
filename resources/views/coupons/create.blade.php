@extends('layouts.master')
@section('title')
    Coupon
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('backarrow')
            {{ route('admin.coupons.index') }}
        @endslot
        @slot('li_1')
            Coupon
        @endslot
        @slot('title')
            Add Coupon
        @endslot
    @endcomponent
    <div class="row d-flex justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="live-preview">
                        <form action="{{ route('admin.coupons.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row gy-4  mt-2">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="name" class="form-label">Name <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <input type="text" class="form-control" id="name" name="name"
                                            value="{{ old('name') }}">
                                        @error('name')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="code" class="form-label">Code <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <input type="text" class="form-control" id="code" name="code"
                                            value="{{ old('code') }}">
                                        @error('code')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row gy-4 mt-2">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="name" class="form-label">Discount Type <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <select class="form-control" data-choices name="discount_type" id="discount_type">
                                            <option value="">Select Discount Type</option>
                                            <option value="percentage">Percentage</option>
                                            <option value="fixed">Fixed</option>
                                        </select>

                                        @error('discount_type')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="discount_value" class="form-label">Discount Value <span
                                                class="text-danger">*</span> </label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <input type="text" class="form-control" id="discount_value" name="discount_value"
                                            value="{{ old('discount_value') }}">
                                        @error('discount_value')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row gy-4 mt-2">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="min_order_amount" class="form-label">Minimum Order Amount <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <input type="text" class="form-control" id="min_order_amount"
                                            name="min_order_amount" value="{{ old('min_order_amount') }}">
                                        @error('min_order_amount')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="max_discount" class="form-label">Maximum Discount<span
                                                class="text-danger">*</span> </label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <input type="text" class="form-control" id="max_discount" name="max_discount"
                                            value="{{ old('max_discount') }}">
                                        @error('max_discount')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row gy-4 mt-2">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="start_date" class="form-label">Start Date<span
                                                class="text-danger">*</span> </label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <input type="date" class="form-control flatpickr-input" id="start_date"
                                            name="start_date" value="{{ old('start_date') }}" data-provider="flatpickr">
                                        @error('start_date')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="expiry_date" class="form-label">Expiry Date<span
                                                class="text-danger">*</span> </label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <input type="date" class="form-control flatpickr-input" id="expiry_date"
                                            name="expiry_date" value="{{ old('expiry_date') }}"
                                            data-provider="flatpickr">
                                        @error('expiry_date')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row gy-4 mt-2">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="expiry_date" class="form-label">Used Counts<span
                                                class="text-danger">*</span> </label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <input type="text" class="form-control" id="used_counts" name="used_counts"
                                            value="{{ old('used_counts') }}">
                                        @error('used_counts')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">If you don't want to validate the used count, type
                                            -1</small>
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="used_type" class="form-label">Used Types <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
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
                                    @error('used_type')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row gy-4 mt-2">

                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="active" class="form-label">Active </label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <div class="form-check form-switch form-switch-lg" dir="ltr">
                                            <input name="active" type="checkbox" value="" data-on="1"
                                                data-off="0" class="form-check-input" id="customSwitchsizelg" checked>
                                        </div>
                                        @error('active')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            @include('components.submit-button', ['name' => 'Save'])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
