<style>
    .palette {
        width: 60px;
        height: 30px;
        border-radius: 6px;
        overflow: hidden;
        display: flex;
        cursor: pointer;
        border: 2px solid #ddd;
    }

    .palette span {
        flex: 1;
    }

    .palette:hover {
        border-color: #000;
    }

    .palette.active {
        border-color: #000;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.4);
    }
</style>
<div class="card">
    <div class="card-body">
        <form id="brand_form" action="{{ route('admin.settings.update-branding') }}" method="post"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="active_tab" id="active_tab">
            <div class="row gy-4 mt-2">
                <div class="col-xxl-2 col-md-2">
                    <label class="form-label">Select Color Palette</label>
                </div>
                <div class="col-xxl-10 col-md-10">
                    <div class="d-flex gap-3 flex-wrap">

                        <div class="palette" data-primary="#4B1D6D" data-secondary="#A66BFF">
                            <span style="background:#4B1D6D"></span>
                            <span style="background:#A66BFF"></span>
                        </div>

                        <div class="palette" data-primary="#31694E" data-secondary="#F0E491">
                            <span style="background:#31694E"></span>
                            <span style="background:#F0E491"></span>
                        </div>

                        <div class="palette" data-primary="#0B113A" data-secondary="#6C4BC7">
                            <span style="background:#0B113A"></span>
                            <span style="background:#6C4BC7"></span>
                        </div>

                        <div class="palette" data-primary="#003B36" data-secondary="#33D1C6">
                            <span style="background:#003B36"></span>
                            <span style="background:#33D1C6"></span>
                        </div>

                        <div class="palette" data-primary="#2A1F5A" data-secondary="#6E89FF">
                            <span style="background:#2A1F5A"></span>
                            <span style="background:#6E89FF"></span>
                        </div>

                    </div>
                </div>
                <div class="col-xxl-2 col-md-2">
                    <div>
                        <label for="primary_color" class="form-label">Primary Color</label>
                    </div>
                </div>
                <div class="col-xxl-10 col-md-10">
                    <div>
                        <input type="text" class="form-control" id="primary_color" name="primary_color"
                            value="{{ $settings['primary_color'] ?? '' }}">
                        @error('primary_color')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-2 col-md-2">
                    <div>
                        <label for="secondary_color" class="form-label">Secondary Color</label>
                    </div>
                </div>
                <div class="col-xxl-10 col-md-10">
                    <div>
                        <input type="text" class="form-control" id="secondary_color" name="secondary_color"
                            value="{{ $settings['secondary_color'] ?? '' }}">
                        @error('secondary_color')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-2 col-md-2">
                    <div>
                        <label for="brand_logo" class="form-label">Brand Logo</label>
                    </div>
                </div>
                <div class="col-xxl-10 col-md-10">

                    <input type="file" class="form-control" name="brand_logo" id="brand_logo_input" accept="image/*">

                    <img id="brand_logo_preview" src="#"
                        style="max-height:100px; margin-top:10px; display:none; border-radius:6px;">

                    @if (!empty($settings['brand_logo']))
                        <div class="mt-3 d-flex align-items-center gap-3">
                            <img src="{{ Storage::disk('s3')->temporaryUrl($settings['brand_logo'], now()->addMinutes(30)) }}"
                                style="max-height:100px; border-radius:6px;">

                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteBrandLogo()">
                                <i class="fa fa-trash"></i> Delete
                            </button>

                            <form id="delete_brand_logo_form" action="{{ route('admin.settings.delete-brand-logo') }}"
                                method="POST" style="display:none;">
                                @csrf
                            </form>
                        </div>
                    @endif

                </div>
            </div>
            <div class="row gy-4 mt-2">
                <div class="col-xxl-3 col-md-2">
                </div>
                <div class="col-xxl-9 col-md-10">
                    <button class="btn btn-primary float-end mt-3"
                        onclick="document.getElementById('brand_form').submit();">
                        <span class="btn-text"><i class="fa fa-paper-plane"></i> Save </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    document.querySelectorAll('.palette').forEach(palette => {
        palette.addEventListener('click', function() {
            document.querySelectorAll('.palette').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('primary_color').value = this.getAttribute('data-primary');
            document.getElementById('secondary_color').value = this.getAttribute('data-secondary');
        });
    });
    document.getElementById('brand_logo_input').addEventListener('change', function(event) {
        let img = document.getElementById('brand_logo_preview');
        img.src = URL.createObjectURL(event.target.files[0]);
        img.style.display = 'block';
    });

    function deleteBrandLogo() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("{{ route('admin.settings.delete-brand-logo') }}", {
                        method: "POST",
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toastr.success('Logo deleted successfully');
                            location.reload();
                        } else {
                            toastr.error('Failed to delete logo');
                        }
                    })
                    .catch(error => {
                        toastr.error('Failed to delete logo');
                    });
            }
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        const primary = "{{ $settings['primary_color'] ?? '' }}";
        const secondary = "{{ $settings['secondary_color'] ?? '' }}";

        document.querySelectorAll('.palette').forEach(palette => {
            if (
                palette.getAttribute('data-primary').toLowerCase() === primary.toLowerCase() &&
                palette.getAttribute('data-secondary').toLowerCase() === secondary.toLowerCase()
            ) {
                palette.classList.add('active');
            }
        });
    });
</script>
