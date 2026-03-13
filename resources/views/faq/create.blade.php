@extends('layouts.master')
@section('title')
    FAQ
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('backarrow')
            {{ route('admin.faqs.index') }}
        @endslot
        @slot('li_1')
            Dashboard
        @endslot
        @slot('title')
           Frequently Asked Questions
        @endslot
    @endcomponent
    <div class="row">
        <form action="{{ route('admin.faqs.store') }}" method="post">
            @csrf
            <div class="row d-flex justify-content-center">
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Frequently Asked Questions</h4>
                        </div>
                        <div class="card-body">
                            <div class="live-preview">
                                <div class="row gy-4">
                                    <div class="col-xxl-12 col-md-12">
                                        <div>
                                            <label for="question" class="form-label">Question ?<span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="question" name="question"
                                                value="{{ old('question') }}">
                                            @error('question')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-xxl-12 col-md-12">
                                        <div>
                                            <label for="answer" class="form-label">Answer <span
                                                    class="text-danger">*</span></label>
                                            <textarea class="form-control" id="answer" name="answer" rows="4"></textarea>
                                            @error('answer')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-xxl-12 col-md-12">
                                        <div>
                                            <label for="answer" class="form-label">Type <span
                                                    class="text-danger">*</span></label>
                                        <select class="form-select" name="type" id="type">
                                            <option value="astrologer">Astrologer</option> 
                                            <option value="customer">Customer</option>
                                            </select>
                                                    @error('type')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                @include('components.submit-button',['name'=>'Save'])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
