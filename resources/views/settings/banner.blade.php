@php
    $banners = App\Models\Banner::where('type', 1)->orderByDesc('id')->get();
@endphp
<div class="card">
    <div class="card-body">
        <div class="card-header">
            <h5 class="card-title flex-grow-1 mb-0">Customer Banner</h5>
        </div>
        <form action="{{ route('admin.settings.store-banner') }}" method="post">
            @csrf
            <input type="hidden" name="active_tab" id="active_tab">
            <input type="hidden" name="type" value="1">
            <div class="row">
                <div class="col-xxl-12 col-md-12">
                    <div>
                        <x-dropzone label="Banner" name="customer_banner" model="Banner" />
                    </div>
                </div>
            </div>
            <div class="row gy-4 mt-2">
                <div class="col-xxl-2 col-md-2">
                    <div>
                        <label class="form-label">Link</label>
                    </div>
                </div>
                <div class="col-xxl-10 col-md-10">
                    <div>
                        <input type="hidden" name="link_type" value="url">
                        <input type="text" name="url" class="form-control" placeholder="Enter URL">
                        <small class="text-muted">e.g., https://www.google.co.in/</small>
                        @error('url')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row gy-4 mt-2">
                <div class="col-xxl-2 col-md-2">
                    <div>
                        <label class="form-label">Date
                            <span class="text-danger">*</span>
                        </label>
                    </div>
                </div>
                <div class="col-xxl-10 col-md-10">
                    <div>
                        <div class="input-group">
                            <input type="text" class="form-control dash-filter-picker flatpickr-input" id="dateRange"
                                name="date_range" data-provider="flatpickr" data-range-date="true"
                                data-date-format="d M, Y" placeholder="Select Date Range" readonly>
                            <div class="input-group-text bg-primary border-primary text-white">
                                <i class="ri-calendar-2-line"></i>
                            </div>
                        </div>
                        @error('date_range')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row gy-4 mt-2">
                <div class="col-xxl-2 col-md-2">
                    <div>
                        <label class="form-label">Active</label>
                    </div>
                </div>
                <div class="col-xxl-10 col-md-10">
                    <div>
                        <div class="form-check form-switch form-switch-lg" dir="ltr">
                            <input name="is_active" type="checkbox" class="form-check-input">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row gy-4">
                <div class="col-xxl-2 col-md-2"></div>
                <div class="col-xxl-10 col-md-10">
                    <button type="submit" class="btn btn-primary float-end" id="add-button">
                        <i class="ri-add-line"></i> Add
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="card-header">
            <h5 class="card-title flex-grow-1 mb-0">Banner List</h5>
        </div>
        <div class="mt-4">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Banner</th>
                        <th>Link</th>
                        <th>Date Range</th>
                        <th>Total Click</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($banners as $index => $banner)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><img src="{{ $banner->customer_banner }}" alt="Banner" width="100"></td>
                            <td>{{ $banner->link }}</td>
                            <td>{{ $banner->date_range }}</td>
                            <td>{{ $banner->bannerClick->count() }}</td>
                            <td>{!! $banner->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' !!}</td>
                            <td>
                                <button class="btn btn-primary btn-icon waves-effect waves-light edit-banner"
                                    data-id="{{ $banner->id }}" data-banner="{{ $banner->customer_banner }}"
                                    data-link-type="{{ $banner->link_type }}" data-link="{{ $banner->link }}"
                                    data-date-range="{{ $banner->date_range }}"
                                    data-active="{{ $banner->is_active }}">
                                    <i class="ri-pencil-fill fs-4"></i>
                                </button>
                                <button class="btn btn-danger btn-icon waves-effect waves-light delete-banner"
                                    data-id="{{ $banner->id }}">
                                    <i class="ri-delete-bin-fill fs-4"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Edit Banner Modal -->
<div class="modal fade" id="editBannerModal" tabindex="-1" aria-labelledby="editBannerModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBannerModalLabel">Edit Banner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBannerForm" method="post" action="{{ route('admin.settings.update-banner') }}">
                @csrf
                <input type="hidden" name="banner_id" id="editBannerId">
                <input type="hidden" name="type" value="1">

                <div class="modal-body">
                    <div class="row gy-4">
                        <div class="col-xxl-2 col-md-2">
                            <label class="form-label">Banner</label>
                        </div>
                        <div class="col-xxl-10 col-md-10">
                            <img src="" id="editBanner" alt="Banner" width="100%">
                        </div>
                    </div>

                    <div class="row gy-4 mt-2">
                        <div class="col-xxl-2 col-md-2">
                            <label class="form-label">Link</label>
                        </div>
                        <div class="col-xxl-10 col-md-10">
                            <input type="hidden" name="link_type" value="url">
                            <input type="text" name="url" class="form-control" id="editUrl"
                                placeholder="Enter URL">
                            <small class="text-muted">e.g., https://www.google.co.in/</small>
                        </div>
                    </div>

                    <div class="row gy-4 mt-2">
                        <div class="col-xxl-2 col-md-2">
                            <label class="form-label">Date</label>
                        </div>
                        <div class="col-xxl-10 col-md-10">
                            <div class="input-group">
                                <input type="text" class="form-control dash-filter-picker flatpickr-input"
                                    id="editDateRange" name="date_range" data-provider="flatpickr"
                                    data-range-date="true" data-date-format="d M, Y" placeholder="Select Date Range"
                                    readonly>
                                <div class="input-group-text bg-primary border-primary text-white">
                                    <i class="ri-calendar-2-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row gy-4 mt-2">
                        <div class="col-xxl-2 col-md-2">
                            <label class="form-label">Active</label>
                        </div>
                        <div class="col-xxl-10 col-md-10">
                            <div class="form-check form-switch form-switch-lg" dir="ltr">
                                <input name="is_active" type="checkbox" class="form-check-input" id="editIsActive">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-banner');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const bannerId = this.getAttribute('data-id');

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
                        fetch(`/admin/settings/delete-banner/${bannerId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Deleted!', data.success, 'success');
                                    location
                                        .reload(); // Reloads the page to update the banner list
                                }
                            })
                            .catch(error => {
                                Swal.fire('Error!',
                                    'Something went wrong. Please try again.',
                                    'error');
                            });
                    }
                });
            });
        });
    });
</script>
<script>
    $(document).on('click', '.edit-banner', function() {
        const bannerId = $(this).data('id');
        const banner = $(this).data('banner');
        const link = $(this).data('link');
        const dateRange = $(this).data('date-range');
        const isActive = $(this).data('active');

        $('#editBannerId').val(bannerId);
        $('#editBanner').attr('src', banner);
        $('#editDateRange').val(dateRange);
        $('#editIsActive').prop('checked', isActive);
        $('#editUrl').val(link);

        $('#editBannerModal').modal('show');
    });
</script>
<script>
    document.querySelector("#add-button")
        .addEventListener("click", function(event) {
            const isFileUploaded = document.getElementById('uploadedCustomer_bannerFile')?.value;
            if (!isFileUploaded) {
                event.preventDefault();
                document.querySelector(".error-message").textContent =
                    "Please upload at least one file before submitting.";
            }
        });
</script>
