@extends('layouts.master')
@section('title')
    Media Library
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">
    <style>
        .media-container {
            position: relative;
            width: 150px;
            height: 150px;
            overflow: hidden;
            border-radius: 5px;
        }

        @media (min-width: 1440px) {
            .media-container {
                width: 180px;
                height: 180px;
            }
        }

        @media (min-width: 1700px) {
            .media-container {
                width: 220px;
                height: 220px;
            }
        }

        .media-container img,
        .media-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 5px;
        }

        .delete-media {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            padding: 2px 6px;
            font-size: 12px;
            opacity: 0;
            /* Hidden by default */
            transition: opacity 0.3s ease-in-out;
        }

        .media-container:hover .delete-media {
            opacity: 1;
            /* Show on hover */
        }

        .modal-dialog {
            max-width: calc(100vw - 100px) !important;
            max-height: calc(100vh - 30px) !important;
        }

        .modal-content {
            height: 100%;
            overflow: auto;
        }

        .dropzone {
            border: 2px dashed #802433 !important;
            border-radius: 10px !important;
            background: #ffffff !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .dropzone:hover {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .dz-error-message {
            top: 47px !important;
        }

        .dz-progress {
            left: 60px !important;
            top: 60px !important;
        }

        .media-filename {
            margin-top: 5px;
            font-size: 14px;
            word-break: break-all;
        }

        .video-container {
            position: relative;
        }

        .play-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 30px;
            color: white;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            padding: 0px 7px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .video-container:hover .play-button {
            opacity: 1;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboard
        @endslot
        @slot('title')
            Media Library
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12">
            <button id="delete-selected" class="btn btn-danger waves-effect waves-light mb-3 d-none">
                <i class="ri-delete-bin-fill"></i>
                Delete Selected
            </button>
            <button class="btn btn-primary waves-effect waves-light mb-3 float-end" data-bs-toggle="modal"
                data-bs-target="#uploadModal">
                <i class="ri-upload-cloud-2-fill"></i>
                Upload Media
            </button>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row d-flex flex-wrap justify-content-start" id="media-container">
                        @foreach (getMediaGlobaly() as $file)
                            @php
                                $fileUrl = generateS3Url($file->path);
                            @endphp
                            @if (fileExistsInS3($fileUrl))
                                <div class="col-2 text-center media-wrapper pb-3 position-relative">
                                    <div class="media-container video-container position-relative">
                                        <!-- Checkbox -->
                                        <input type="checkbox" class="select-media position-absolute top-0 start-0 m-2"
                                            data-id="{{ $file->id }}" data-url="{{ $fileUrl }}" data-video-path="{{ $file->video_path }}">

                                        <button type="button" class="btn btn-danger btn-sm delete-media"
                                            data-delete-url="{{ route('admin.dropzone.delete-existing-file') }}"
                                            data-url="{{ $fileUrl }}" data-video-path="{{ $file->video_path }}" data-id="{{ $file->id }}">×
                                        </button>
                                        @if ($file->type == 'videoFile')
                                            <img src="{{ $file->video_path ? $fileUrl : URL::asset('/video-placeholder.png') }}"
                                                class="rounded material-shadow border selectable-image hovering-zoom"
                                                data-image-url="{{ $fileUrl }}">

                                            <div class="play-button" data-bs-toggle="modal" data-bs-target="#videoModal"
                                                data-video-url="{{ $file->video_path ?? $fileUrl }}">
                                                <i class="ri-play-circle-line"></i>
                                            </div>
                                        @else
                                            <img src="{{ $fileUrl }}"
                                                class="rounded material-shadow border selectable-image hovering-zoom"
                                                data-image-url="{{ $fileUrl }}">
                                        @endif
                                    </div>
                                    <div class="media-filename">{{ preg_replace('/^\d+_/', '', $file->name) }}</div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <button id="load-more" class="btn btn-primary" data-page="2">
                            <span id="load-text">Load More</span>
                            <span id="load-spinner" class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Media</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="upload-form" action="{{ route('admin.media-library') }}">
                        <div class="dropzone" id="dropzone-area"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        onclick="location.reload();">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Video Playback Modal -->
    <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Video Playback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <video id="videoPlayer" controls style="width: 100%; max-height: 80vh;">
                        <source src="" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>
    <script>
        let uploadInProgress = 0;
        const closeButton = document.querySelector('.modal-footer .btn-secondary');
        closeButton.disabled = true;

        let dropzoneInstance = new Dropzone("#dropzone-area", {
            url: "{{ route('admin.dropzone.upload-file') }}",
            paramName: "file",
            maxFilesize: 2048,
            maxFiles: 5,
            acceptedFiles: "image/jpeg,image/jpg,image/png,image/heic,video/mp4,audio/mp3,application/pdf",
            addRemoveLinks: true,
            parallelUploads: 1,
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
            },
            init: function() {
                this.on("addedfile", file => {
                    let allowedTypes = [
                        "image/jpeg",
                        "image/jpg",
                        "image/png",
                        "image/heic",
                        "video/mp4",
                        "audio/mp3",
                        "application/pdf"
                    ];

                    if (!allowedTypes.includes(file.type)) {
                        this.removeFile(file);
                        Swal.fire("Error", `Only allowed file types: ${allowedTypes.join(", ")}`,
                            "error");
                    }
                    if (this.files.length > 5) {
                        this.removeFile(file);
                        Swal.fire("Limit Exceeded", "You can upload a maximum of 5 files.", "warning");
                    }

                    if (file.type === "video/mp4") {
                        this.options.paramName = "videoFile";
                    } else if (file.type.startsWith("image/")) {
                        this.options.paramName = "imageFile";
                    } else if (file.type === "audio/mp3") {
                        this.options.paramName = "audioFile";
                    } else if (file.type === "application/pdf") {
                        this.options.paramName = "pdfFile";
                    } else {
                        this.options.paramName = "file";
                    }
                });

                this.on("sending", () => {
                    uploadInProgress++;
                    closeButton.disabled = true;
                });

                this.on("success", (file, response) => {
                    uploadInProgress--;
                    if (uploadInProgress === 0) {
                        closeButton.disabled =
                            false;
                    }
                    let removeLink = file.previewElement.querySelector(".dz-remove");
                    if (removeLink || errorMsg) {
                        removeLink.remove();
                    }
                });

                this.on("error", file => {
                    uploadInProgress--;
                    if (uploadInProgress === 0) {
                        closeButton.disabled = false;
                    }
                    let removeButton = file.previewElement.querySelector(".dz-error-mark");
                    if (removeButton || errorMsg) {
                        removeButton.remove();
                    }
                    Swal.fire("Error", "File upload failed!", "error");
                });
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.delete-media').forEach(button => {
                button.addEventListener('click', function() {
                    let deleteUrl = this.getAttribute('data-delete-url');
                    let fileUrl = this.getAttribute('data-url');
                    let videoUrl = this.getAttribute('data-video-path');
                    let fileId = this.getAttribute('data-id');
                    let mediaWrapper = this.closest('.media-wrapper');

                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(deleteUrl, {
                                    method: "POST",
                                    headers: {
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                        "Content-Type": "application/json"
                                    },
                                    body: JSON.stringify({
                                        id: fileId,
                                        filepath: fileUrl,
                                        videoPath: videoUrl,
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        toastr.success("Your file has been deleted!");
                                        mediaWrapper.remove();
                                    } else {
                                        Swal.fire("Error!", "Something went wrong.",
                                            "error");
                                    }
                                })
                                .catch(error => {
                                    console.error("Error:", error);
                                    Swal.fire("Error!", "Could not delete the file.",
                                        "error");
                                });
                        }
                    });
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#load-more').on('click', function() {
                let page = $(this).data('page');
                let loadText = $('#load-text');
                let spinner = $('#load-spinner');

                loadText.addClass('d-none');
                spinner.removeClass('d-none');

                $.ajax({
                    url: "{{ route('admin.get-media-library') }}",
                    type: "GET",
                    data: {
                        page: page
                    },
                    success: function(response) {
                        if (response.data.length > 0) {
                            response.data.forEach(function(file) {
                                let fileName = file.name.replace(/^\d+_/, '');
                                let mediaHtml = `<div class="col-2 text-center media-wrapper pb-3">
                                    <div class="media-container video-container position-relative">
                                        <input type="checkbox" class="select-media position-absolute top-0 start-0 m-2"
                                            data-url="${file.url}" data-id="${file.id}">
                                        <button type="button" class="btn btn-danger btn-sm delete-media"
                                            data-delete-url="{{ route('admin.dropzone.delete-existing-file') }}"
                                            data-url="${file.url}" data-id="${file.id}">×
                                        </button>
                                        `;

                                if (file.type === 'videoFile') {
                                    mediaHtml += `<img src="${file.video_path ? file.url : '/video-placeholder.png'}"
                                        class="rounded material-shadow border selectable-image hovering-zoom"
                                        data-image-url="${file.url}">
                                    
                                    <div class="play-button" data-bs-toggle="modal" data-bs-target="#videoModal"
                                        data-video-url="${file.video_path || file.url}">
                                        <i class="ri-play-circle-line"></i>
                                    </div>`;
                                } else {
                                    mediaHtml += `<img src="${file.url}"
                                            class="rounded material-shadow border selectable-image hovering-zoom"
                                            data-image-url="${file.url}">`;
                                }

                                mediaHtml += `</div>
                                        <div class="media-filename">${fileName}</div>
                                    </div>`;

                                $('#media-container').append(mediaHtml);
                            });

                            $('#load-more').data('page', response.next_page);
                        }

                        if (!response.has_more) {
                            $('#load-more').hide();
                        }
                    },
                    complete: function() {
                        loadText.removeClass('d-none');
                        spinner.addClass('d-none');
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const videoModal = document.getElementById('videoModal');
            const videoPlayer = document.getElementById('videoPlayer');

            videoModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const videoUrl = button.getAttribute('data-video-url');
                videoPlayer.src = videoUrl;
            });

            videoModal.addEventListener('hidden.bs.modal', function() {
                videoPlayer.pause();
                videoPlayer.currentTime = 0;
                videoPlayer.src = '';
            });
        });
    </script>
    <script>
        const selectedFiles = new Set();
        $(document).on('change', '.select-media', function() {
            const fileId = $(this).attr('data-id');
            if (this.checked) {
                selectedFiles.add(fileId);
            } else {
                selectedFiles.delete(fileId);
            }
            $('#delete-selected').toggleClass('d-none', selectedFiles.size === 0);
        });

        document.getElementById('delete-selected').addEventListener('click', function() {
            if (selectedFiles.size === 0) return;

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const fileIds = Array.from(selectedFiles);
                    const deleteButton = document.getElementById('delete-selected');
                    const loadSpinner = document.createElement('span');

                    loadSpinner.className = 'spinner-border spinner-border-sm ms-2';
                    deleteButton.appendChild(loadSpinner);
                    deleteButton.disabled = true;

                    fetch("{{ route('admin.multi.delete-existing-file') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                fileIds
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            deleteButton.disabled = false;
                            loadSpinner.remove();

                            if (data.success) {
                                fileIds.forEach(id => {
                                    document.querySelector(`input[data-id="${id}"]`).closest(
                                        '.media-wrapper').remove();
                                });
                                selectedFiles.clear();
                                deleteButton.classList.add('d-none');

                                Swal.fire(
                                    'Deleted!',
                                    'Selected files have been deleted successfully.',
                                    'success'
                                );
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'An error occurred while deleting files.',
                                    'error'
                                );
                            }
                        });
                }
            });
        });
    </script>
@endsection
