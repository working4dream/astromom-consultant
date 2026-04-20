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
                    <div>
                        <label for="free_chat_status" class="form-label">
                            Free Chat
                        </label>
                    </div>
                </div>
                <div class="col-xxl-9 col-md-10">
                    <div>
                        @php
                            $freeChatStatus = $settings['free_chat_status'] ?? '0';
                        @endphp
                        <div class="form-check form-switch mb-3" dir="ltr">
                            <input type="hidden" name="free_chat_status" value="0">
                            <input type="checkbox" class="form-check-input" id="free_chat_status"
                                name="free_chat_status" value="1" {{ $freeChatStatus == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="free_chat_status">Enable Free Chat</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row gy-4 mt-2" id="free_chat_limit_row" style="display: {{ $freeChatStatus == '1' ? 'flex' : 'none' }};">
                <div class="col-xxl-3 col-md-2">
                    <div>
                        <label for="free_chat_limit" class="form-label">
                            Free Chat Limit
                        </label>
                    </div>
                </div>
                <div class="col-xxl-9 col-md-10">
                    <div>
                        @php
                            $freeChatLimit = $settings['free_chat_limit'] ?? '1';
                        @endphp
                        <select class="form-control" id="free_chat_limit" name="free_chat_limit">
                            <option value="-1" {{ $freeChatLimit == '-1' ? 'selected' : '' }}>Unlimited</option>
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ $freeChatLimit == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
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

<script>
    function toggleFreeChatLimit(checkbox) {
        var limitRow = document.getElementById('free_chat_limit_row');
        if (checkbox.checked) {
            limitRow.style.display = 'flex';
        } else {
            limitRow.style.display = 'none';
        }
    }
    
    document.addEventListener("DOMContentLoaded", function() {
        var checkbox = document.getElementById('free_chat_status');
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                toggleFreeChatLimit(this);
            });
        }
    });
</script>
