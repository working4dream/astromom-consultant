<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="dark"
    data-sidebar-size="lg" data-sidebar-image="none" data-preloader="enable" style="overflow-x: hidden">

<head>
    <meta charset="utf-8" />
    <title>Register</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css" />
    <style>
        .choices__list--dropdown .choices__item,
        .choices__list[aria-expanded] .choices__item {
            position: relative;
            padding: 10px;
            font-size: 13px;
        }

        .char-count {
            font-size: 0.9em;
            color: #666;
        }

        label {
            font-weight: normal;
        }

        .card {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, .1), 0 8px 10px -6px rgba(0, 0, 0, .1);
            border-radius: 1rem;
        }

        p {
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-md-8 col-lg-8 pt-5">
                <div class="card p-4">
                    <div class="card-body">
                        <div class="text-center">
                            <a href="index" class="logo logo-light">
                                <span class="logo-sm">
                                    <img src="{{ URL::asset('images/logo.svg') }}" alt="" height="100">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{ URL::asset('images/logo.svg') }}" alt="" height="130">
                                </span>
                            </a>
                        </div>
                        <h3 class="text-center py-4">
                            Join as a Expert
                        </h3>
                        <form action="{{ route('astrologer.store') }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="firstNameinput" class="form-label">Full Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name') }}" placeholder="Enter your Full Name"
                                            id="firstNameinput">
                                        @error('name')
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
                                {{-- <div class="col-md-12">
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
                                </div> --}}
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phonenumberInput" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="hidden" class="form-control" id="mobileCode" name="mobile_code" readonly style="max-width: 80px;">
                                            <input type="tel" class="form-control" name="mobile_number" id="phonenumberInput"
                                                value="{{ old('mobile_number') }}" placeholder="Enter Phone Number"
                                                oninput="this.value = this.value.replace(/\D/g, '')"> <!-- Only numbers allowed -->
                                        </div>
                                        @error('mobile_number')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gender" class="form-label">Gender <span
                                                class="text-danger">*</span></label>
                                        <div>
                                            <div class="form-check mb-2 form-check-inline">
                                                <input class="form-check-input" type="radio" value="Male"
                                                    name="gender" id="flexRadioDefault1"
                                                    {{ old('gender') == 'Male' ? 'checked' : 'checked' }}>
                                                <label class="form-check-label" for="flexRadioDefault1">
                                                    Male
                                                </label>
                                            </div>

                                            <div class="form-check  form-check-inline">
                                                <input class="form-check-input" type="radio" value="Female"
                                                    name="gender" id="flexRadioDefault2"
                                                    {{ old('gender') == 'Female' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="flexRadioDefault2">
                                                    Female
                                                </label>
                                            </div>
                                            <div class="form-check  form-check-inline">
                                                <input class="form-check-input" type="radio" name="gender"
                                                    value="Other" id="flexRadioDefault3"
                                                    {{ old('gender') == 'Other' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="flexRadioDefault3">
                                                    Other
                                                </label>
                                            </div>
                                            @error('image')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="professional_title" class="form-label ">Expert In?<span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" data-choices name="professional_title[]"
                                            id="professional_title" multiple>
                                            <option value="">Choose Expert In</option>
                                            @if ($settings)
                                                @foreach (explode(',', $settings['specialization']) as $sp)
                                                    <option value="{{ $sp }}"
                                                        {{ is_array(old('professional_title')) ? (in_array($sp, old('professional_title')) ? 'selected' : '') : (in_array($sp, explode(',', old('professional_title', ''))) ? 'selected' : '') }}>
                                                        {{ $sp }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('professional_title')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="expertise" class="form-label ">Expertise <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" data-choices name="expertise[]" id="expertise"
                                            multiple>
                                            <option value="">Choose Expertise</option>
                                            @if ($settings)
                                                @foreach (explode(',', $settings['expertise']) as $exp)
                                                    <option value="{{ $exp }}"
                                                        @if (old('expertise')) @if (in_array($exp, old('expertise'))) selected @endif
                                                        @endif>{{ $exp }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('expertise')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="keywords" class="form-label ">Keywords<span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" data-choices name="keywords[]" id="keywords"
                                            multiple>
                                            <option value="">Choose Keywords</option>
                                            @if ($settings)
                                                @foreach (explode(',', $settings['keywords']) as $sp)
                                                    <option value="{{ $sp }}"
                                                        {{ is_array(old('keywords')) ? (in_array($sp, old('keywords')) ? 'selected' : '') : (in_array($sp, explode(',', old('keywords', ''))) ? 'selected' : '') }}>
                                                        {{ $sp }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('keywords')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="inputEmail4" class="form-label">Years of Experience? <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" data-choices name="experience"
                                            id="choices-single-default">
                                            <option value="">Years of Experience</option>
                                            @for ($i = 1; $i <= 40; $i++)
                                                @php $val=$i==1? 'Year' :'Years'; @endphp <option
                                                    value="{{ $i }} {{ $val }}"
                                                    {{ old('experience') == $i . ' ' . $val ? 'selected' : '' }}>
                                                    {{ $i }} {{ $val }}</option>
                                            @endfor
                                        </select>
                                        @error('experience')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="inputEmail4" class="form-label">Languages You Speak? <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" data-choices name="language[]" id="language"
                                            multiple>
                                            <option value="">Choose Language</option>
                                            @if ($settings)
                                                @foreach (explode(',', $settings['languages']) as $language)
                                                    <option value="{{ $language }}"
                                                        @if (old('language')) @if (in_array($language, old('language'))) selected @endif
                                                        @endif>
                                                        {{ $language }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('language')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="inputEmail4" class="form-label">City <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" data-choices name="city_id" id="city_id">
                                            <option value="">Choose city</option>
                                            @foreach ($cities as $city)
                                                <option value="{{ $city->id }}"
                                                    {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                                    {{ $city->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('city_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="inputEmail4" class="form-label">Short Bio <span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control" rows="5" name="description" maxlength="200" onkeyup="updateCharCount()">{{ old('description') }}</textarea>
                                        <div class="char-count mt-1">Characters: <span id="charCount">0</span>/200
                                        </div>
                                        @error('description')
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
                                                    data-input-id="uploadedProfile_pictureFile" data-model="Astrologer">
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
                                                            <input type="hidden" id="uploadedProfile_pictureFile"
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
                                                Register as a Expert
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script>
        function updateCharCount() {
            const textarea = document.querySelector('textarea[name="description"]');
            const charCount = document.getElementById('charCount');
            charCount.textContent = textarea.value.length;
        }
        window.onload = function() {
            updateCharCount();
        }
        document.addEventListener('DOMContentLoaded', function() {
            var element = document.getElementById('expertise');
            new Choices(element, {
                removeItemButton: true,
                maxItemCount: 3,
            });

            var element = document.getElementById('language');
            new Choices(element, {
                removeItemButton: true,
                maxItemCount: 3,
            });
            var element = document.getElementById('professional_title');
            new Choices(element, {
                removeItemButton: true,
                maxItemCount: 3,
            });
            var element = document.getElementById('keywords');
            new Choices(element, {
                removeItemButton: true,
                maxItemCount: 3,
            });
            var element = document.getElementById('city_id');
            new Choices(element, {
                removeItemButton: true,
                maxItemCount: 3,
            });

        });
    </script>
    <script>
        var input = document.querySelector("#phonenumberInput");
        var iti = window.intlTelInput(input, {
            separateDialCode: true,
            initialCountry: "IN",
            preferredCountries: ["IN", "US", "GB"],
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.min.js"
        });

        function updateCountryCode() {
            let countryData = iti.getSelectedCountryData();
            document.querySelector("#mobileCode").value = countryData.dialCode;
        }
    
        input.addEventListener("countrychange", updateCountryCode);
    
        updateCountryCode();

        input.addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, '');
        });
    </script>
    
</body>

</html>
