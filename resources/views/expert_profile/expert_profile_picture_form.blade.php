<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="dark"
    data-sidebar-size="lg" data-sidebar-image="none" data-preloader="enable" style="overflow-x: hidden">

<head>
    <meta charset="utf-8" />
    <title>Expert</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('images/favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @include('layouts.head-css')
    <link href="{{ URL::asset('css/custom-dropzone.css') }}" rel="stylesheet" />
</head>

<body>
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-md-8 col-lg-8 pt-5">
                <div class="card p-4">
                    <div class="card-body">
                        <div class="text-center">
                            <span class="logo-sm">
                                <img src="{{ URL::asset('images/logo.svg') }}" alt="" height="100">
                            </span>
                        </div>
                        {{-- <h3 class="text-center py-4">
                            Join as a {{ env('APP_NAME') }} Expert
                        </h3> --}}
                        <form action="{{ route('expert-ptofile.store') }}" method="post" class="pt-4">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="firstNameinput" class="form-label">First Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="first_name"
                                            value="{{ old('first_name') }}" placeholder="Enter your First Name"
                                            id="firstNameinput">
                                        @error('first_name')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="lastNameinput" class="form-label">Last Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="last_name"
                                            value="{{ old('last_name') }}" placeholder="Enter your Last Name"
                                            id="lastNameinput">
                                        @error('last_name')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="emailidInput" class="form-label">Email Address <span
                                                class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control"
                                            value="{{ old('email') }}" placeholder="example@gamil.com"
                                            id="emailidInput">
                                        @error('email')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="phonenumberInput" class="form-label">Phone Number <span
                                                class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" name="mobile_number"
                                            pattern="[1-9]\d{9}" value="{{ old('mobile_number') }}"
                                            placeholder="Enter Phone Number"
                                            oninput="this.value = this.value.replace(/^0|[^0-9]/g, '').slice(0, 10)"
                                            id="phonenumberInput">
                                        <small class="text-muted">Phone number should not start with 0.</small>
                                        @error('mobile_number')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="row file-upload">
                                            <div class="col-xxl-12 col-md-12">
                                                <label class="form-label">Profile Picture <span
                                                        class="text-danger">*</span></label>
                                                <div class="dz-clickable dropzone-area" id="imageUpload"
                                                    data-type="image" name="profile_picture"
                                                    data-upload-url="{{ route('admin.dropzone.astrologer-upload-file') }}"
                                                    data-delete-url="{{ route('admin.dropzone.astrologer-delete-file') }}"
                                                    data-input-id="uploadedImageFile" data-model="Astrologer">
                                                    <div class="dropzone-custom-ui">
                                                        <div class="dz-content" id="dz-content-image">
                                                            <i class="ri-upload-cloud-2-fill dz-icon"></i>
                                                            <p class="dz-message"><strong>Drag & Drop your file
                                                                    here</strong>
                                                            </p>
                                                            <p class="small text-muted">Supported formats: '.png,
                                                                .jpg, .jpeg, .heic'
                                                            </p>
                                                            <p>OR</p>
                                                            <button type="button"
                                                                class="btn btn-primary waves-effect waves-light dz-clickable">Choose
                                                                File</button>
                                                            <input type="hidden" id="uploadedImageFile"
                                                                name="profile_picture">
                                                            <input type="hidden" id="uploadedCutOutImageFile"
                                                                name="cutout_image">
                                                            <p class="small text-muted mt-2">Maximum size: 10MB</p>
                                                        </div>
                                                        <img src="" alt="Uploaded Image" id="saved-image"
                                                            class="rounded material-shadow d-none">
                                                        <br><a
                                                            class="btn btn-danger btn-sm waves-effect waves-light my-2 remove-uploaded-file d-none"
                                                            id="remove-image">Remove</a>
                                                        <div class="file-preview"></div>
                                                        <div class="dz-progress-container" style="display: none;">
                                                            <p class="dz-file-details">Uploading... </p>
                                                            <div class="progress bg-white">
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                                    role="progressbar" style="width: 0%;"
                                                                    aria-valuenow="0" aria-valuemin="0"
                                                                    aria-valuemax="100"></div>
                                                            </div>
                                                            <p class="text-primary text-center pt-2">Do not refresh the
                                                                page or
                                                                go back while uploading.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="error-message pt-2 text-center text-danger"
                                                    id="error-message">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fa-solid fa-arrow-right-to-bracket me-1"></i>
                                                Save
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.notifications')
    @include('layouts.vendor-scripts')
    <script src="{{ URL::asset('js/custom-dropzone.js') }}"></script>
</body>

</html>
