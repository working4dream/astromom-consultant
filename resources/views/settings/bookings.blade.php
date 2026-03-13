<form action="{{ route('admin.settings.update-price') }}" method="post">
    @csrf
    <div class="row">
        <div class="col-xxl-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Book Appointment</h5>
                    <hr>
                    <ul class="font-bold">
                        <li>Video</li>
                        <ul>
                            <li>30 Minutes</li>
                            <div class="row gy-4 mt-1">
                                <div class="col-xxl-4 col-md-4">
                                    <label for="video_30_min_price" class="form-label booking"> Minimum Price</label>
                                </div>
                                <div class="col-xxl-8 col-md-8">
                                    <div>
                                        <input type="text" class="form-control "
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            id="video_30_min_price" name="video_30_min_price"
                                            value="{{ old('video_30_min_price', $settings['video_30_min_price'] ?? null) }}">
                                        @error('video_30_min_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row gy-4 mt-1">
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <label for="video_30_max_price" class="form-label booking"> Maximum
                                            Price</label>
                                    </div>
                                </div>
                                <div class="col-xxl-8 col-md-8">
                                    <div>
                                        <input type="text" class="form-control "
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            id="video_30_max_price" name="video_30_max_price"
                                            value="{{ old('video_30_max_price', $settings['video_30_max_price'] ?? null) }}">
                                        @error('video_30_max_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror

                                    </div>
                                </div>
                            </div>
                            <li>60 Minutes</li>
                            <div class="row gy-4 mt-1">
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <label for="video_60_min_price" class="form-label booking"> Minimum
                                            Price</label>
                                    </div>
                                </div>
                                <div class="col-xxl-8 col-md-8">
                                    <div>
                                        <input type="text" class="form-control "
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            id="video_60_min_price" name="video_60_min_price"
                                            value="{{ old('video_60_min_price', $settings['video_60_min_price'] ?? null) }}">
                                        @error('video_60_min_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror

                                    </div>
                                </div>
                            </div>
                            <div class="row gy-4 mt-1">
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <label for="video_60_max_price" class="form-label booking"> Maximum
                                            Price</label>
                                    </div>
                                </div>
                                <div class="col-xxl-8 col-md-8">
                                    <div>
                                        <input type="text" class="form-control "
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            id="video_60_max_price" name="video_60_max_price"
                                            value="{{ old('video_60_max_price', $settings['video_60_max_price'] ?? null) }}">
                                        @error('video_60_max_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror

                                    </div>
                                </div>
                            </div>
                        </ul>
                        <li>Voice</li>
                        <ul>
                            <li>30 Minutes</li>
                            <div class="row gy-4 mt-1">
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <label for="voice_30_min_price" class="form-label booking"> Minimum
                                            Price</label>
                                    </div>
                                </div>
                                <div class="col-xxl-8 col-md-8">
                                    <div>
                                        <input type="text" class="form-control "
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            id="voice_30_min_price" name="voice_30_min_price"
                                            value="{{ old('voice_30_min_price', $settings['voice_30_min_price'] ?? null) }}">
                                        @error('voice_30_min_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror

                                    </div>
                                </div>
                            </div>
                            <div class="row gy-4 mt-1">
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <label for="voice_30_max_price" class="form-label booking"> Maximum
                                            Price</label>
                                    </div>
                                </div>
                                <div class="col-xxl-8 col-md-8">
                                    <div>
                                        <input type="text" class="form-control "
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            id="voice_30_max_price" name="voice_30_max_price"
                                            value="{{ old('voice_30_max_price', $settings['voice_30_max_price'] ?? null) }}">
                                        @error('voice_30_max_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror

                                    </div>
                                </div>
                            </div>
                            <li>60 Minutes</li>
                            <div class="row gy-4 mt-1">
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <label for="voice_60_min_price" class="form-label booking"> Minimum
                                            Price</label>
                                    </div>
                                </div>
                                <div class="col-xxl-8 col-md-8">
                                    <div>
                                        <input type="text" class="form-control "
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            id="voice_60_min_price" name="voice_60_min_price"
                                            value="{{ old('voice_60_min_price', $settings['voice_60_min_price'] ?? null) }}">
                                        @error('voice_60_min_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror

                                    </div>
                                </div>
                            </div>
                            <div class="row gy-4 mt-1">
                                <div class="col-xxl-4 col-md-4">
                                    <div>
                                        <label for="voice_60_max_price" class="form-label booking"> Maximum
                                            Price</label>
                                    </div>
                                </div>
                                <div class="col-xxl-8 col-md-8">
                                    <div>
                                        <input type="text" class="form-control "
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            id="voice_60_max_price" name="voice_60_max_price"
                                            value="{{ old('voice_60_max_price', $settings['voice_60_max_price'] ?? null) }}">
                                        @error('voice_60_max_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror

                                    </div>
                                </div>
                            </div>
                        </ul>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xxl-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Book Now</h5>
                    <hr>
                    <ul class="font-bold">
                        <li>Chat</li>

                        <div class="row gy-4 mt-1">
                            <div class="col-xxl-4 col-md-4">
                                <div>
                                    <label for="chat_min_price" class="form-label booking"> Minimum Price</label>
                                </div>
                            </div>
                            <div class="col-xxl-8 col-md-8">
                                <div>
                                    <div class="input-group">
                                        <input type="text" class="form-control "
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            id="chat_min_price" name="chat_min_price"
                                            value="{{ old('chat_min_price', $settings['chat_min_price'] ?? null) }}">
                                        <span class="input-group-text" id="basic-addon2">Per Minute</span>
                                    </div>
                                    @error('chat_min_price')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror

                                </div>
                            </div>
                        </div>
                        <div class="row gy-4 mt-1">
                            <div class="col-xxl-4 col-md-4">
                                <div>
                                    <label for="chat_max_price" class="form-label booking"> Maximum Price</label>
                                </div>
                            </div>
                            <div class="col-xxl-8 col-md-8">
                                <div>
                                    <div class="input-group">
                                        <input type="text" class="form-control "
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            id="chat_max_price" name="chat_max_price"
                                            value="{{ old('chat_max_price', $settings['chat_max_price'] ?? null) }}">
                                        <span class="input-group-text" id="basic-addon2">Per Minute</span>
                                    </div>
                                    @error('chat_max_price')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror

                                </div>
                            </div>
                        </div>
                        <li>Voice </li>

                        <div class="row gy-4 mt-1">
                            <div class="col-xxl-4 col-md-4">
                                <div>
                                    <label for="voice_min_price" class="form-label booking"> Minimum Price</label>
                                </div>
                            </div>
                            <div class="col-xxl-8 col-md-8">
                                <div>
                                    <div class="input-group">
                                        <input type="text" class="form-control "
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            id="voice_min_price" name="voice_min_price"
                                            value="{{ old('voice_min_price', $settings['voice_min_price'] ?? null) }}">
                                        <span class="input-group-text" id="basic-addon2">Per Minute</span>
                                    </div>
                                    @error('voice_min_price')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror

                                </div>
                            </div>
                        </div>
                        <div class="row gy-4 mt-1">
                            <div class="col-xxl-4 col-md-4">
                                <div>
                                    <label for="voice_max_price" class="form-label booking"> Maximum Price</label>
                                </div>
                            </div>
                            <div class="col-xxl-8 col-md-8">
                                <div>
                                    <div class="input-group">
                                        <input type="text" class="form-control "
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            id="voice_max_price" name="voice_max_price"
                                            value="{{ old('voice_max_price', $settings['voice_max_price'] ?? null) }}">
                                        <span class="input-group-text" id="basic-addon2">Per Minute</span>
                                    </div>
                                    @error('voice_max_price')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror

                                </div>
                            </div>
                        </div>

                        <li>Video </li>

                        <div class="row gy-4 mt-1">
                            <div class="col-xxl-4 col-md-4">
                                <div>
                                    <label for="video_min_price" class="form-label booking"> Minimum Price</label>
                                </div>
                            </div>
                            <div class="col-xxl-8 col-md-8">
                                <div>
                                    <div class="input-group">
                                        <input type="text" class="form-control "
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            id="video_min_price" name="video_min_price"
                                            value="{{ old('video_min_price', $settings['video_min_price'] ?? null) }}">
                                        <span class="input-group-text" id="basic-addon2">Per Minute</span>
                                    </div>
                                    @error('video_min_price')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror

                                </div>
                            </div>
                        </div>
                        <div class="row gy-4 mt-1">
                            <div class="col-xxl-4 col-md-4">
                                <div>
                                    <label for="video_max_price" class="form-label booking"> Maximum Price</label>
                                </div>
                            </div>
                            <div class="col-xxl-8 col-md-8">
                                <div>
                                    <div class="input-group">
                                        <input type="text" class="form-control "
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            id="video_max_price" name="video_max_price"
                                            value="{{ old('video_max_price', $settings['video_max_price'] ?? null) }}">
                                        <span class="input-group-text" id="basic-addon2">Per Minute</span>
                                    </div>
                                    @error('video_max_price')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror

                                </div>
                            </div>
                        </div>

                    </ul>
                    <div class="row gy-4 mt-5">
                        <div class="col-xxl-4 col-md-4">

                        </div>
                        <div class="col-xxl-8 col-md-8">
                            <button class="btn btn-primary float-end mt-3"
                                onclick="document.querySelector('form').submit();">
                                <span class="btn-text"><i class="fa fa-paper-plane"></i> Save </span>
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>
