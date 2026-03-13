@props([
    'label' => '',
    'name' => 'file',
    'acceptedFormats' => '.jpeg, .jpg, .png, .webp',
    'uploadUrl' => route('admin.dropzone.upload-file'),
    'deleteUrl' => route('admin.dropzone.delete-file'),
    'data' => [],
    'model' => '',
    'required' => true,
    'mediaCount' => 1,
])
<input type="hidden" id="data-json" value='@json($data)'>
<div class="row gy-4 mt-2 file-upload">
    @if ($label != '')
        <div class="col-xxl-2 col-md-2">
            <label class="form-label">{{ $label }}
                @if ($required === true)
                    <span class="text-danger">*</span>
                @endif
            </label>
        </div>
    @endif
    @if ($label == '')
        <div class="col-xxl-12 col-md-12">
    @else
        <div class="col-xxl-10 col-md-10">
    @endif
    <div class="dz-clickable dropzone-area {{ $name }}" id="{{ $name }}Upload" data-type="image"
        data-upload-url="{{ $uploadUrl }}" data-delete-url="{{ $deleteUrl }}"
        data-input-id="uploaded{{ ucfirst($name) }}File" data-model="{{ $model }}"
        data-name="{{ $name }}">
        <div class="dropzone-custom-ui">
            <div class="dz-content" id="dz-content-{{ $name }}">
                <i class="ri-upload-cloud-2-fill dz-icon"></i>
                <p class="dz-message"><strong>Drag & Drop your file here</strong></p>
                <p class="small text-muted">or</p>
                @if ($mediaCount > 0)
                    <button type="button" class="btn btn-secondary waves-effect waves-light media-library"
                        data-bs-toggle="modal" data-bs-target="#mediaLibrary{{ $name }}">Media Library
                    </button>
                @endif
                <button type="button" class="btn btn-primary waves-effect waves-light dz-clickable">Choose File
                </button>
                <input type="hidden" id="uploaded{{ ucfirst($name) }}File" class="dropz" name="{{ $name }}">
                <p class="small text-muted mt-2">Supported formats: {{ $acceptedFormats }}</p>
                @if ($name == 'cut_out_image')
                    <p class="small text-muted mt-2">Cut Out Image must be exactly 380x460 pixels</p>
                @elseif ($name == 'image')
                    <p class="small text-muted mt-2">Thumbnail Image must be exactly 210x190 pixels</p>
                @elseif ($name == 'customer_banner')
                    <p class="small text-muted mt-2">Banner must be exactly 1360x560 pixels</p>
                @elseif ($name == 'expert_banner')
                    <p class="small text-muted mt-2">Banner must be exactly 1360x560 pixels</p>
                @elseif ($name == 'action_thumbnail' || $name == 'activity_thumbnail')
                    <p class="small text-muted mt-2">Thumbnail must be exactly 420x800 pixels</p>
                @endif
            </div>
            @if ($name === 'preview_video')
                <video controls width="50%" class="d-none rounded" id="saved-{{ $name }}">
                    <source src="" type="video/mp4">Your browser
                    does not support the video tag.
                </video>
            @else
                <img src="" alt="Uploaded Image" id="saved-{{ $name }}"
                    class="rounded material-shadow py-3 responsive-img d-none" width="50%">
            @endif
            <br><a
                class="btn btn-danger btn-sm waves-effect waves-light mb-2 remove-uploaded-{{ $name }} d-none"
                id="remove-{{ $name }}">Remove</a>
            <div class="file-preview"></div>
            <div class="dz-progress-container" style="display: none;">
                <p class="dz-file-details">Uploading... </p>
                <div class="progress bg-white">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="text-primary text-center pt-2">Do not refresh the page or go back while uploading.</p>
            </div>
        </div>
    </div>
    <div class="error-message pt-2 text-center text-danger" id="error-message"></div>
    @if ($name == 'cut_out_image')
        <div class="cutout-error-message pt-2 text-center text-danger" id="cutout-error-message"></div>
    @elseif ($name == 'expert_banner')
        <div class="expert-banner-error-message pt-2 text-center text-danger" id="expert-banner-error-message">
        </div>
    @elseif ($name == 'action_thumbnail')
        <div class="action-thumbnail-error-message pt-2 text-center text-danger" id="action-thumbnail-error-message">
        </div>
    @elseif ($name == 'activity_thumbnail')
        <div class="activity-thumbnail-error-message pt-2 text-center text-danger" id="activity-thumbnail-error-message">
        </div>
    @endif
</div>
</div>

<div class="modal fade mediaLibrary" id="mediaLibrary{{ $name }}" tabindex="-1"
    aria-labelledby="mediaLibrary{{ $name }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaLibraryLabel">Media Library</h5>
                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row d-flex flex-wrap justify-content-start" id="media-library-content-{{ $name }}">

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary load-more-media mx-auto">Load More</button>
                <button type="button" class="btn btn-secondary close-media-library-{{ $name }}"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal{{ $name }}" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this file? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
