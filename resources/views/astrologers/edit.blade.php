@extends('layouts.master')
@section('css')
    <link href="{{ URL::asset('css/custom-dropzone.css') }}" rel="stylesheet" />
@endsection
@section('title')
    Experts
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('backarrow')
            {{ route('admin.experts.index') }}
        @endslot
        @slot('li_1')
            Experts
        @endslot
        @slot('title')
            Edit Experts
        @endslot
    @endcomponent
    <div class="row d-flex justify-content-center">
        <div class="col-lg-8">
            @if ($astrologer->is_approved === null || $astrologer->is_approved === 0)
                <div class="alert alert-danger" role="alert">
                    This expert is not verified. Approve them to display in the app.
                </div>
            @endif
            <div class="card">
                <div class="card-body">
                    <div class="live-preview">
                        <form action="{{ route('admin.experts.update', $astrologer->id) }}" method="post"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row gy-4 ">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="first_name" class="form-label">First Name <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <input type="text" class="form-control" id="first_name" name="first_name"
                                            value="{{ old('first_name', $astrologer->first_name) }}">
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
                                            value="{{ old('last_name', $astrologer->last_name) }}">
                                        @error('last_name')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row gy-4 mt-2">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="email" class="form-label">Email <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-10 col-md-10">
                                    <div>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="{{ old('email', $astrologer->email) }}">
                                        @error('email')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row gy-4 mt-2">
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
                                            name="mobile_number"
                                            value="{{ old('mobile_number', $astrologer->mobile_number) }}">
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
                                                {{ old('gender', $astrologer->gender) == 'Male' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                Male
                                            </label>
                                        </div>

                                        <div class="form-check  form-check-inline">
                                            <input class="form-check-input" type="radio" value="Female" name="gender"
                                                id="flexRadioDefault2"
                                                {{ old('gender', $astrologer->gender) == 'Female' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                Female
                                            </label>
                                        </div>
                                        <div class="form-check  form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" value="Other"
                                                id="flexRadioDefault2"
                                                {{ old('gender', $astrologer->gender) == 'Other' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                Other
                                            </label>
                                        </div>
                                        @error('gender')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <x-dropzone label="Profile Picture" name="profile_picture" data="{{ $astrologer }}"
                                model="Astrologer" />
                            <x-dropzone label="Cut Out Image" name="cut_out_image" data="{{ $astrologer }}" model="AstrologerCutOut" acceptedFormats=".png"/>
                            <div class="row gy-4 mt-2">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="professional_title" class="form-label">Expert In? <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-10 col-md-10">
                                    <div>
                                        <select class="form-control" name="professional_title[]" id="professional_title"
                                            multiple>
                                            <option value="">Choose Expert In?</option>
                                            @if ($settings)
                                                @foreach (explode(',', $settings['specialization']) as $sp)
                                                    <option value="{{ $sp }}"
                                                        {{ in_array($sp, explode(',', $astrologer->professional_title)) ? 'selected' : '' }}>
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
                            </div>
                            <div class="row gy-4 mt-2">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="keywords" class="form-label">Keywords <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-10 col-md-10">
                                    <div>
                                        <select class="form-control" name="keywords[]" id="keywords" multiple>
                                            <option value="">Choose Keywords</option>
                                            @if ($settings)
                                                @foreach (explode(',', $settings['keywords']) as $sp)
                                                    <option value="{{ $sp }}"
                                                        {{ in_array($sp, explode(',', $astrologer->keywords)) ? 'selected' : '' }}>
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
                            </div>
                            <div class="row gy-4 mt-2">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="expertise" class="form-label">Expertise <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-10 col-md-10">
                                    <div>
                                        <select class="form-control" name="expertise[]" id="expertise" multiple>
                                            <option value="">Choose Expertise</option>
                                            @if ($settings)
                                                @foreach (explode(',', $settings['expertise']) as $exp)
                                                    <option value="{{ $exp }}"
                                                        @if (in_array($exp, explode(',', $astrologer->expertise))) selected @endif>
                                                        {{ $exp }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('expertise')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row gy-4 mt-2">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="language" class="form-label">Language <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-10 col-md-10">
                                    <div>
                                        <select class="form-control" multiple name="language[]" id="language">
                                            @if ($settings)
                                                @foreach (explode(',', $settings['languages']) as $language)
                                                    <option value="{{ $language }}"
                                                        {{ in_array($language, explode(',', $astrologer->language)) ? 'selected' : '' }}>
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
                            </div>
                            <div class="row gy-4 mt-2">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="experience" class="form-label">Experience<span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-10 col-md-10">
                                    <div>
                                        <select class="form-control" name="experience" id="choices-single-default">
                                            <option value="">Years of Experience</option>
                                            @for ($i = 1; $i <= 40; $i++)
                                                @php $val=$i==1? 'Year' :'Years'; @endphp <option value="{{ $i }} {{ $val }}"
                                                    {{ $astrologer->experience == $i . ' ' . $val ? 'selected' : '' }}>
                                                    {{ $i }} {{ $val }}</option>
                                            @endfor
                                        </select>
                                        @error('experience')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row gy-4 mt-2">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="city_id" class="form-label">City <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-10 col-md-10">
                                    <select id="searchSelect" class="form-control" data-choices data-choices-sorting-false
                                        name="city_id">
                                        <option value="">Select an option...</option>
                                        @foreach ($cities as $city)
                                            <option value="{{ $city->id }}"
                                                {{ $astrologer->city_id == $city->id ? 'selected' : '' }}>
                                                {{ $city->name }}</option>
                                        @endforeach

                                    </select>
                                    @error('city_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row gy-4 mt-2">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="description" class="form-label">Short Bio <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-xxl-10 col-md-10">
                                    <div>
                                        <textarea type="text" class="form-control text-start" id="description" name="description" maxlength="200"
                                            onkeyup="updateCharCount()">{{ old('description', trim($astrologer->description)) }}</textarea>

                                        <small class="char-count mt-1 text-muted">Characters: <span
                                                id="charCount">0</span>/200
                                        </small>
                                        @error('description')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row gy-4 mt-2">
                                <div class="col-xxl-2 col-md-2">
                                    <div>
                                        <label for="active" class="form-label">Active </label>
                                    </div>
                                </div>
                                <div class="col-xxl-10 col-md-10">
                                    <div>
                                        <div class="form-check form-switch form-switch-lg" dir="ltr">
                                            <input name="status" type="checkbox" value="1" data-on="1"
                                                data-off="0" class="form-check-input"
                                                {{ $astrologer?->status == 1 ? 'checked' : '' }} id="customSwitchsizelg">
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
    <script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="{{ URL::asset('js/custom-dropzone.js') }}"></script>
    <script>
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

        });
    </script>
    <script>
        function updateCharCount() {
            const textarea = document.querySelector('textarea[name="description"]');
            const charCount = document.getElementById('charCount');
            charCount.textContent = textarea.value.length;
        }
        window.onload = function() {
            updateCharCount();
        }
    </script>
@endsection
