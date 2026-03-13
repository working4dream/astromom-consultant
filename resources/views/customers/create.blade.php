@extends('layouts.master')
@section('css')
<link href="{{ URL::asset('css/custom-dropzone.css') }}" rel="stylesheet" />
@endsection
@section('title')
Customers
@endsection
@section('content')
@component('components.breadcrumb')
@slot('backarrow')
{{ route('admin.customers.index') }}
@endslot
@slot('li_1')
Customers
@endslot
@slot('title')
Add Customers
@endslot
@endcomponent
<div class="row">
    <form action="{{ route('admin.customers.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row d-flex justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">
                        <div class="live-preview">
                            <div class="row gy-4">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="first_name" class="form-label">First Name <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <input type="text" class="form-control" id="first_name" name="first_name"
                                            value="{{ old('first_name') }}">
                                        @error('first_name')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="last_name" class="form-label">Last Name <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <input type="text" class="form-control" id="last_name" name="last_name"
                                            value="{{ old('last_name') }}">
                                        @error('last_name')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="email" class="form-label">Email <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="{{ old('email') }}">
                                        @error('email')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="mobile_number" class="form-label">Mobile Number<span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <input type="number" class="form-control" id="mobile_number" pattern="\d{10}"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            name="mobile_number" value="{{ old('mobile_number') }}">
                                        @error('mobile_number')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-md-2 ">
                                    <div>
                                        <label for="image" class="form-label">Gender </label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <div class="form-check mb-2 form-check-inline">
                                            <input class="form-check-input" type="radio" value="Male" name="gender"
                                                id="flexRadioDefault1"
                                                {{ old('gender') == 'Male' ? 'checked' : 'checked' }}>
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                Male
                                            </label>
                                        </div>

                                        <div class="form-check  form-check-inline">
                                            <input class="form-check-input" type="radio" value="Female" name="gender"
                                                id="flexRadioDefault2" {{ old('gender') == 'Female' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                Female
                                            </label>
                                        </div>
                                        <div class="form-check  form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" value="Other"
                                                id="flexRadioDefault3" {{ old('gender') == 'Other' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="flexRadioDefault3">
                                                Other
                                            </label>
                                        </div>
                                        @error('image')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="dob" class="form-label">DOB </label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <input type="date" class="form-control flatpickr-input active" id="dob"
                                            name="dob" value="{{ old('dob') }}" data-provider="flatpickr">
                                        @error('dob')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="dob" class="form-label">City </label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <select id="searchSelect" class="form-control" data-choices
                                        data-choices-sorting-false name="city_id">
                                        <option value="">Select an option...</option>
                                        @foreach ($cities as $city)
                                        <option value="{{ $city->id }}"
                                            {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}</option>
                                        @endforeach
                                        @error('city_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </select>
                                </div>
                            </div>
                            <x-dropzone label="Profile Picture" name="profile_picture" model="Customer" />
                            <div class="row gy-4 mt-3">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="dob" class="form-label">Notes</label>
                                    </div>
                                </div>
                                <div class="col-xxl-10 col-md-10">
                                    <div>
                                        <textarea class="form-control " id="text" rows="10"
                                            name="notes"> </textarea>
                                        @error('text')
                                        <div class="text-danger">{{ $message }}
                                        </div>
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
</div>
</form>
</div>
@endsection
@section('script')
<script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
<script src="{{ URL::asset('js/custom-dropzone.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    flatpickr("#dob", {
        dateFormat: "Y-m-d",
        allowInput: true
    });
});
</script>
@endsection
