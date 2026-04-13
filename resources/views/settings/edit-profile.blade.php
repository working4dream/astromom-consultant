@extends('layouts.master')
@section('title')
    Edit Profile
@endsection
@section('content')
    @component('components.breadcrumb')
        @section('add-route')
            {{ route('admin.edit-profile') }}
        @endsection
        @slot('li_1')
            @if (auth()->user()->hasRole('admin'))
                Admin
            @else
                Staff
            @endif
        @endslot
        @slot('title')
            Edit Profile
        @endslot
    @endcomponent
    <div class="row d-flex justify-content-center">
        <div class="col-xxl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 flex-grow-1">Personal Details</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.edit-profile.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-2">
                            <div class="text-center">
                                <div class="profile-user position-relative d-inline-block mx-auto  mb-4">
                                    @if (auth()->user()->profile_picture)
                                        <img id="profile-preview" src="{{ auth()->user()->profile_picture }}"
                                            class="rounded-circle avatar-xl img-thumbnail user-profile-image material-shadow"
                                            alt="user-profile-image">
                                    @else
                                        <img id="profile-preview" src="{{ URL::asset('build/images/users/no-user.png') }}"
                                            class="rounded-circle avatar-xl img-thumbnail user-profile-image material-shadow"
                                            alt="user-profile-image">
                                    @endif
                                    <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                        <input id="profile-img-file-input" type="file" class="profile-img-file-input" name="profile_picture">
                                        <label for="profile-img-file-input" class="profile-photo-edit avatar-xs">
                                            <span class="avatar-title rounded-circle bg-light text-body material-shadow">
                                                <i class="ri-camera-fill"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label for="firstnameInput" class="form-label">First Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="firstnameInput" name="first_name"
                                    placeholder="Enter your firstname" value="{{ old('first_name', auth()->user()->first_name) }}">
                                @error('first_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-lg-6">
                                <label for="lastnameInput" class="form-label">Last Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="lastnameInput" name="last_name"
                                    placeholder="Enter your lastname" value="{{ old('last_name', auth()->user()->last_name) }}">
                                @error('last_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-lg-12">
                                <label for="timezoneInput" class="form-label">Timezone</label>
                                <select name="timezone" id="timezoneInput" class="form-select @error('timezone') is-invalid @enderror">
                                    <option value="">Default ({{ config('app.display_timezone') }})</option>
                                    @foreach ($timezones as $tz)
                                        <option value="{{ $tz }}" @selected(old('timezone', auth()->user()->timezone) === $tz)>{{ $tz }}</option>
                                    @endforeach
                                </select>
                                @error('timezone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <small class="text-muted">Dates and reports use this timezone for your account.</small>
                            </div>
                            <div class="col-lg-12">
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 flex-grow-1">Change Password</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.change-password.store') }}" method="post">
                        @csrf
                        <div class="row g-2">
                            <div class="col-lg-6">
                                <label class="form-label" for="old-password-input">Old Password <span class="text-danger">*</span></label>
                                <div class="position-relative auth-pass-inputgroup mb-3">
                                    <input type="password"
                                        class="form-control password-input pe-5 @error('old_password') is-invalid @enderror"
                                        name="old_password" placeholder="Enter old password" id="old-password-input">
                                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                        type="button"><i class="ri-eye-fill align-middle"></i></button>
                                    @error('old_password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label" for="password-input">Password <span
                                        class="text-danger">*</span></label>
                                <div class="position-relative auth-pass-inputgroup mb-3">
                                    <input type="password"
                                        class="form-control password-input pe-5 @error('password') is-invalid @enderror"
                                        name="password" placeholder="Enter password" id="password-input">
                                    <button
                                        class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                        type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>
    <script>
        document.getElementById('profile-img-file-input').addEventListener('change', function(event) {
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profile-preview').src = e.target.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        });
        </script>
@endsection
