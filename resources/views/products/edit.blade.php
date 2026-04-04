@extends('layouts.master')
@section('title')
    Products
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('backarrow')
            {{ route('admin.products.index') }}
        @endslot
        @slot('li_1')
            Products
        @endslot
        @slot('title')
            Edit Products
        @endslot
    @endcomponent
    <div class="row">
        <form action="{{ route('admin.products.update', $product->id) }}" method="post">
            @csrf
            <div class="row d-flex justify-content-center">
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-body">
                            <div class="live-preview">
                                <div class="row gy-4">
                                    <div class="col-xxl-12 col-md-12">
                                        <div>
                                            <label for="title" class="form-label">Title
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="title" name="title"
                                                value="{{ $product->title }}">
                                            @error('title')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-xxl-6 col-md-6">
                                        <div>
                                            <label for="duration" class="form-label">Duration <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="duration" name="duration"
                                                value="{{ $product->duration }}">
                                            <small class="text-muted">(Ex., 30 Min, 1 Hour, 1.5 Hour )</small>
                                            @error('duration')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-xxl-6 col-md-6">
                                        <div>
                                            <label for="duration_in_min" class="form-label">Duration in Minutes<span
                                                    class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="duration_in_min"
                                                name="duration_in_min" value="{{ $product->duration_in_min }}">
                                            @error('duration_in_min')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-xxl-12 col-md-12">
                                        <div>
                                            <label for="description" class="form-label">Description <span
                                                    class="text-danger">*</span></label>
                                            <textarea type="text" class="form-control ckeditor-classic" id="description" name="description">{{ $product->description }}</textarea>
                                            @error('description')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-xxl-12 col-md-12">
                                        <div>
                                            <label for="price" class="form-label">Price <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="price" name="price"
                                                value="{{ $product->price }}" min="0" pattern="\d*"
                                                oninput="validatePrice(this)" onkeypress="return isNumberKey(event)">
                                            @error('price')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-xxl-12 col-md-12">
                                        <div>
                                            <label for="is_gst_checkbox" class="form-label">Is GST Applicable </label>
                                            <div class="form-check form-switch form-switch-lg" dir="ltr">
                                                <input id="is_gst_checkbox" name="is_gst" type="checkbox" value="1"
                                                    data-on="1" data-off="0" class="form-check-input" {{ $product->is_gst === 1 ? 'checked' : '' }}>
                                            </div>
                                            @error('is_gst')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-xxl-12 col-md-12" id="gst_type_wrapper">
                                        <div>
                                            <label for="gst_type" class="form-label">GST Type <span
                                                    class="text-danger">*</span></label>
                                            <select name="gst_type" id="gst_type" class="form-control">
                                                <option value="">Select GST Type</option>
                                                <option value="gst_5" {{ $product->gst_type === 'gst_5' ? 'selected' : '' }}>GST_5 (5%)</option>
                                                <option value="gst_12" {{ $product->gst_type === 'gst_12' ? 'selected' : '' }}>GST_12 (12%)</option>
                                                <option value="gst_18" {{ $product->gst_type === 'gst_18' ? 'selected' : '' }}>GST_18 (18%)</option>
                                                <option value="gst_28" {{ $product->gst_type === 'gst_28' ? 'selected' : '' }}>GST_28 (28%)</option>
                                            </select>
                                            @error('gst_type')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-xxl-12 col-md-12" id="gst_amount_wrapper">
                                        <div>
                                            <label for="gst_amount" class="form-label">GST Amount</label>
                                            <input type="number" class="form-control bg-light" id="gst_amount" name="gst_amount"
                                                value="{{ $product->gst_amount }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-xxl-12 col-md-12" id="total_price_wrapper">
                                        <div>
                                            <label for="total_price" class="form-label">Total Price (Read Only)</label>
                                            <input type="number" class="form-control bg-light" id="total_price" name="total_price"
                                                value="{{ $product->total_price }}" readonly>
                                            @error('total_price')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-xxl-12 col-md-12">
                                        <div>
                                            <label for="price" class="form-label">Status </label>
                                            <div class="form-check form-switch form-switch-lg" dir="ltr">
                                                <input name="status" type="checkbox" value="1" data-on="1"
                                                    data-off="0" class="form-check-input"
                                                    {{ $product->status === 1 ? 'checked' : '' }}>
                                            </div>
                                            @error('status')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @include('components.submit-button', ['name' => 'Save'])
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="{{ URL::asset('build/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/form-editor.init.js') }}"></script>
    <script>
        function validatePrice(input) {
            if (input.value === "0") return;
            input.value = input.value.replace(/^0+(?=\d)/, '');
            if (input.value < 0) input.value = '';
        }

        function isNumberKey(evt) {
            var charCode = evt.which ? evt.which : evt.keyCode;
            if (charCode == 45) return false;
            return true;
        }

        $(document).ready(function() {
            function toggleGstType() {
                if ($('#is_gst_checkbox').is(':checked')) {
                    $('#gst_type_wrapper').removeClass('d-none');
                    $('#total_price_wrapper').removeClass('d-none');
                    $('#gst_amount_wrapper').removeClass('d-none');
                } else {
                    $('#gst_type_wrapper').addClass('d-none');
                    $('#total_price_wrapper').addClass('d-none');
                    $('#gst_amount_wrapper').addClass('d-none');
                }
            }

            toggleGstType();

            $('#is_gst_checkbox').on('change', function() {
                toggleGstType();
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            function calculateTotalPrice() {
                let price = parseFloat($('#price').val()) || 0;
                let isGstChecked = $('#is_gst_checkbox').is(':checked');
                let gstType = $('#gst_type').val();
                let gstPercent = 0;

                if (isGstChecked) {
                    switch (gstType) {
                        case 'gst_5':
                            gstPercent = 5;
                            break;
                        case 'gst_12':
                            gstPercent = 12;
                            break;
                        case 'gst_18':
                            gstPercent = 18;
                            break;
                        case 'gst_28':
                            gstPercent = 28;
                            break;
                    }
                }

                let totalPrice = price + (price * gstPercent / 100);
                let gstAmount = price * gstPercent / 100;
                $('#gst_amount').val(gstAmount.toFixed(2));
                $('#total_price').val(totalPrice.toFixed(2));
            }

            $('#price, #gst_type').on('input change', calculateTotalPrice);
            $('#is_gst_checkbox').on('change', function() {
                $('#gst_type_wrapper').toggleClass('d-none', !this.checked);
                $('#gst_amount_wrapper').toggleClass('d-none', !this.checked);
                calculateTotalPrice();
            });

            if (!$('#is_gst_checkbox').is(':checked')) {
                $('#gst_type_wrapper').addClass('d-none');
                $('#gst_amount_wrapper').addClass('d-none');
            }
            calculateTotalPrice();
        });
    </script>
@endsection
