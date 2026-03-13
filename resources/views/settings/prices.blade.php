<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.settings.update-price') }}" method="post">
            @csrf
            <input type="hidden" name="active_tab" id="active_tab">
            <div class="row gy-4 mt-2">
                <div class="col-xxl-3 col-md-2">
                    <div>
                        <label for="appointment_gst_type" class="form-label">
                            Appointment GST
                        </label>
                    </div>
                </div>
                <div class="col-xxl-9 col-md-10">
                    <div>
                        @php
                            $gstType = $settings['appointment_gst_type'] ?? '';
                        @endphp

                        <select class="form-control" id="appointment_gst_type" name="appointment_gst_type">
                            <option value="" {{ $gstType === '' ? 'selected' : '' }}>Select GST Type</option>
                            <option value="gst_5" {{ $gstType === 'gst_5' ? 'selected' : '' }}>GST 5%</option>
                            <option value="gst_12" {{ $gstType === 'gst_12' ? 'selected' : '' }}>GST 12%</option>
                            <option value="gst_18" {{ $gstType === 'gst_18' ? 'selected' : '' }}>GST 18%</option>
                            <option value="gst_28" {{ $gstType === 'gst_28' ? 'selected' : '' }}>GST 28%</option>
                        </select>

                        @error('appointment_gst_type')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row gy-4 mt-2">
                <div class="col-xxl-3 col-md-2">
                </div>
                <div class="col-xxl-9 col-md-10">
                    <button class="btn btn-primary float-end mt-3" onclick="document.querySelector('form').submit();">
                        <span class="btn-text"><i class="fa fa-paper-plane"></i> Save </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
