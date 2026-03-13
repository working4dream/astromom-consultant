document.addEventListener("DOMContentLoaded", function () {
    Dropzone.autoDiscover = false;
    document
        .querySelectorAll(".dropzone-area")
        .forEach(function (dropzoneElement) {
            let uploadUrl = dropzoneElement.getAttribute("data-upload-url");
            let deleteUrl = dropzoneElement.getAttribute("data-delete-url");
            let inputId = dropzoneElement.getAttribute("data-input-id");
            let param = dropzoneElement.getAttribute("data-model");
            let acceptedFormats =
                "image/jpeg, image/png, image/jpg, image/heic, .heic, image/webp, video/mp4, .mp4";
            let dzInstance = new Dropzone("#" + dropzoneElement.id, {
                url: uploadUrl,
                createImageThumbnails: false,
                paramName: param,
                maxFiles: 1,
                acceptedFiles: acceptedFormats,
                addRemoveLinks: true,
                dictDefaultMessage: "",
                clickable: `#${dropzoneElement.id} .dz-clickable`,
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                accept: function (file, done) {
                    const imageValidationRules = {
                        uploadedCut_out_imageFile: {
                            width: 380,
                            height: 460,
                            type: "image/png",
                            errorMsg:
                                "Cutout image must be a PNG and exactly 380x460  pixels.",
                        },
                        uploadedImageFile: {
                            width: 210,
                            height: 190,
                            errorMsg: "Image must be exactly 210x190 pixels.",
                        },
                        uploadedCustomer_bannerFile: {
                            width: 1360,
                            height: 560,
                            errorMsg: "Banner must be exactly 1360x560 pixels.",
                        },
                        uploadedExpert_bannerFile: {
                            width: 1360,
                            height: 560,
                            errorMsg: "Banner must be exactly 1360x560 pixels.",
                        },
                    };

                    const imageMaxSizeMB = 10;
                    const videoMaxSizeMB = 2048;

                    const fileSizeMB = file.size / (1024 * 1024); // convert to MB

                    if (file.type.startsWith("video/")) {
                        if (fileSizeMB > videoMaxSizeMB) {
                            return done(
                                `Video must be less than ${videoMaxSizeMB} MB.`
                            );
                        }
                        return done();
                    }

                    if (fileSizeMB > imageMaxSizeMB) {
                        return done(
                            `Image must be less than ${imageMaxSizeMB} MB.`
                        );
                    }

                    if (inputId in imageValidationRules) {
                        const { width, height, type, errorMsg } =
                            imageValidationRules[inputId];

                        if (type && file.type !== type) {
                            return done(`Invalid file format! ${errorMsg}`);
                        }

                        const reader = new FileReader();
                        reader.onload = function (event) {
                            const img = new Image();
                            img.onload = function () {
                                if (
                                    img.width !== width ||
                                    img.height !== height
                                ) {
                                    done(`Invalid image size! ${errorMsg}`);
                                } else {
                                    done();
                                }
                            };
                            img.src = event.target.result;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        done();
                    }
                },
                init: function () {
                    const dzContent =
                        dropzoneElement.querySelector(".dz-content");
                    const dzProgressContainer = dropzoneElement.querySelector(
                        ".dz-progress-container"
                    );
                    const filePreviewContainer =
                        dropzoneElement.querySelector(".file-preview");
                    const progressText =
                        dropzoneElement.querySelector(".dz-file-details");
                    const errorMessageText =
                        document.querySelector("#error-message");
                    const cutoutErrorMessageText = document.querySelector(
                        "#cutout-error-message"
                    );
                    const expertBannerErrorMessageText = document.querySelector(
                        "#expert-banner-error-message"
                    );
                    const submitButton = document.querySelector(
                        "button[type='submit']"
                    );

                    this.on("addedfile", function (file) {
                        dzContent.style.display = "none";
                        dzProgressContainer.style.display = "block";
                        filePreviewContainer.innerHTML = "";
                        submitButton.disabled = true;
                        errorMessageText.textContent = "";
                    });

                    this.on("uploadprogress", function (file, progress) {
                        dzProgressContainer.querySelector(
                            ".progress-bar"
                        ).style.width = `${progress}%`;
                        dzProgressContainer
                            .querySelector(".progress-bar")
                            .setAttribute("aria-valuenow", progress);
                        if (progressText) {
                            progressText.textContent = `Uploading (${Math.round(
                                progress
                            )}%)`;
                        }
                    });

                    this.on("success", function (file, response) {
                        dzProgressContainer.style.display = "none";
                        document.querySelector(`#${inputId}`).value =
                            response.file_path;
                        let previewHTML = "";
                        if (file.type.startsWith("video/")) {
                            previewHTML = `
                                <video controls class="rounded material-shadow responsive-img py-3" width="100%">
                                    <source src="${response.full_url}" type="${file.type}">
                                    Your browser does not support the video tag.
                                </video>`;
                        } else {
                            previewHTML = `
                                <img src="${response.full_url}" alt="Uploaded Image" class="rounded material-shadow responsive-img py-3">`;
                        }

                        filePreviewContainer.innerHTML = `
                            ${previewHTML}
                            <br>
                            <a class="btn btn-danger btn-sm waves-effect waves-light mb-2 remove-uploaded-file"
                            data-url="${response.full_url}"> Remove </a>`;

                        filePreviewContainer
                            .querySelector(".remove-uploaded-file")
                            .addEventListener("click", function () {
                                fetch(deleteUrl, {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": document
                                            .querySelector(
                                                'meta[name="csrf-token"]'
                                            )
                                            .getAttribute("content"),
                                    },
                                    body: JSON.stringify({
                                        filepath: this.getAttribute("data-url"),
                                    }),
                                })
                                    .then((res) => res.json())
                                    .then((data) => {
                                        if (data.success) {
                                            filePreviewContainer.innerHTML = "";
                                            document.querySelector(
                                                `#${inputId}`
                                            ).value = "";
                                            dzInstance.removeFile(file);
                                            toastr.success(
                                                "Your file has been deleted!"
                                            );
                                        } else {
                                            console.error(
                                                "Error deleting file:",
                                                data.error
                                            );
                                        }
                                    })
                                    .catch((error) =>
                                        console.error("Request failed:", error)
                                    );
                            });

                        submitButton.disabled = false;
                    });

                    this.on("error", function (_, errorMessage) {
                        let inputId =
                            dropzoneElement.getAttribute("data-input-id");
                        dzProgressContainer.style.display = "none";
                        dzContent.style.display = "block";
                        if (inputId === "uploadedCut_out_imageFile") {
                            cutoutErrorMessageText.textContent = "";
                            cutoutErrorMessageText.textContent = errorMessage;
                        } else if (
                            inputId === "uploadedExpert_bannerFile") {
                            expertBannerErrorMessageText.textContent = "";
                            expertBannerErrorMessageText.textContent =
                                errorMessage;
                        } else {
                            errorMessageText.textContent = errorMessage;
                        }
                    });

                    this.on("removedfile", function () {
                        dzProgressContainer.style.display = "none";
                        dzContent.style.display = "block";
                    });
                },
            });
        });
    document
        .querySelector("button[type='submit']")
        .addEventListener("click", function (event) {
            let inputId = $(".dz-clickable.dropzone-area").data("input-id");
            const isFileUploaded = document.getElementById(`${inputId}`)?.value;
            
            if (inputId !== "uploadedProfile_pictureFile" && inputId !== "uploadedPreview_videoFile" && 
                inputId !== "uploadedAction_thumbnailFile" && inputId !== "uploadedActivity_thumbnailFile") {
                if (!isFileUploaded) {
                    event.preventDefault();
                    document.querySelector(".error-message").textContent =
                        "Please upload at least one file before submitting.";
                }
            } else if (inputId === "uploadedActivity_thumbnailFile" || inputId === "uploadedAction_thumbnailFile") {
                if (isFileUploaded && !document.querySelector("#popup-button-text").value) {
                    event.preventDefault();
                    document.querySelector(".popup-button-text-error-message").textContent =
                        "Please enter popup button text, if you have uploaded a thumbnail.";
                }
            }
        });

    let dropZoneFields = document.querySelectorAll(".dropz");
    let jsonData = JSON.parse(document.getElementById("data-json").value);
    let decodedData = null;
    if (jsonData.length > 0) {
        decodedData = jsonData.replace(/&quot;/g, '"').replace(/&amp;/g, "&");
    }
    let data = null;
    if (decodedData) {
        data = JSON.parse(decodedData);
    }

    dropZoneFields.forEach(function (dropzoneField) {
        const FieldName = dropzoneField.name;

        if (data && data.length > 0) {
            data.forEach((item) => {
                if (item[FieldName]) {
                    displayImage(item[FieldName], FieldName, item.id);
                }
            });
        }

        if (data && data[FieldName]) {
            const imageUrl =
                FieldName === "category_image"
                    ? data["image"]
                    : data[FieldName];
            displayImage(imageUrl, FieldName, data.id);
        }

        document.addEventListener("click", function (event) {
            if (
                event.target.classList.contains("remove-uploaded-" + FieldName)
            ) {
                handleRemoveUpload(event, FieldName);
            } else if (event.target.classList.contains("selectable-image")) {
                handleImageSelection(event, FieldName);
            } else if (event.target.classList.contains("delete-media")) {
                handleMediaDeletion(event);
            }
        });
    });

    function displayImage(imageUrl, FieldName, fileId = null) {
        const savedImage = document.querySelector("#saved-" + FieldName);
        const inputId = $(".dz-clickable.dropzone-area." + FieldName).data(
            "input-id"
        );
        const urlObj = new URL(imageUrl);
        const extractedPath = urlObj.pathname.substring(1);

        if (savedImage) {
            savedImage.src = imageUrl;
            $(".remove-uploaded-" + FieldName).attr("data-url", imageUrl);
            $(".remove-uploaded-" + FieldName).attr("data-id", fileId);

            savedImage.classList.remove("d-none");
            document.querySelector("#dz-content-" + FieldName).style.display =
                "none";
            document
                .querySelector("#remove-" + FieldName)
                .classList.remove("d-none");
            document.querySelector(`#${inputId}`).value = extractedPath;
        }
    }

    function handleRemoveUpload(event, FieldName) {
        const fileUrl = event.target.getAttribute("data-url");
        const fileId = event.target.getAttribute("data-id");
        const deleteUrl = $(".dz-clickable.dropzone-area." + FieldName).data(
            "delete-url"
        );
        const model = $(".dz-clickable.dropzone-area." + FieldName).data(
            "model"
        );

        if (!fileId || !fileUrl) {
            console.error("Missing file ID or URL");
            window.location.reload();
            return;
        }

        fetch(deleteUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                id: fileId,
                filepath: fileUrl,
                model: model,
                filename: FieldName,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    window.location.reload();
                } else {
                    console.error("Error deleting file:", data.error);
                }
            })
            .catch((error) => console.error("Request failed:", error));
    }

    function handleImageSelection(event, FieldName) {
        const fileName = event.target.getAttribute("data-image-name");
        if (FieldName === fileName) {
            const imageUrl = event.target.getAttribute("data-image-url");
            displayImage(imageUrl, FieldName);
            document.querySelector(".close-media-library-" + FieldName).click();
        }
    }

    function handleMediaDeletion(event) {
        const fileID = event.target.getAttribute("data-id");
        const fileURL = event.target.getAttribute("data-url");
        const deleteUrl = event.target.getAttribute("data-delete-url");

        Swal.fire({
            title: "Are you sure?",
            text: "You will not be able to revert this! If this file is used elsewhere, it will be removed.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
        }).then((confirmed) => {
            if (confirmed.isConfirmed) {
                fetch(deleteUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify({ id: fileID, filepath: fileURL }),
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            document.querySelector(".media-wrapper").remove();
                        } else {
                            console.error("Error deleting file:", data.error);
                            alert(
                                "Failed to delete the file. Please try again."
                            );
                        }
                    })
                    .catch((error) => {
                        console.error("Request failed:", error);
                        alert("Failed to delete the file. Please try again.");
                    });
            }
        });
    }
});
$(document).ready(function () {
    $(".media-library").on("click", function () {
        var modalId = $(this).data("bs-target");
        var mediaContainer = $(modalId).find(
            "#media-library-content-" +
                $(this).attr("data-bs-target").replace("#mediaLibrary", "")
        );
        var loadMoreButton = $(modalId).find(".load-more-media");
        loadMoreButton.show().html("Load More");

        mediaContainer.html("");

        var getMediaUrl = $('meta[name="dropzone-get-media-url"]').attr(
            "content"
        );
        let dropzoneElement = event.target.closest(
            ".dz-clickable.dropzone-area"
        );
        let dropzoneName = dropzoneElement ? dropzoneElement.dataset.name : "";
        let dropzoneModel = dropzoneElement
            ? dropzoneElement.dataset.model
            : "";

        var deleteMediaUrl = $('meta[name="dropzone-delete-media-url"]').attr(
            "content"
        );
        var nextPageUrl = `${getMediaUrl}?route_type=${dropzoneModel}`;

        function loadMedia() {
            if (!nextPageUrl) return;
            loadMoreButton
                .html(
                    '<span class="spinner-border spinner-border-sm"></span> Loading...'
                )
                .prop("disabled", true);

            $.ajax({
                url: nextPageUrl,
                type: "GET",
                success: function (response) {
                    if (response.data.length > 0) {
                        var mediaHtml = "";
                        response.data.forEach(function (file) {
                            var fileUrl = file.url;
                            var filePath = file.video_path;
                            var fileType = file.type;
                            let fileName = file.name.replace(/^\d+_/, "");
                            
                            if (fileType === "videoFile") return;

                            let mediaContent = "";

                            if (fileType.startsWith("video")) {
                                mediaContent = `
                                    <video controls class="rounded material-shadow border" width="100%">
                                        <source src="${fileUrl}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>`;
                            } else {
                                mediaContent = `
                                    <img src="${fileUrl}" class="rounded material-shadow border"
                                        data-image-url="${fileUrl}" data-image-name="${dropzoneName}">`;
                            }

                            mediaHtml += `
                                <div class="col-2 text-center media-wrapper pb-3">
                                    <div class="media-container position-relative">
                                        <button type="button" class="btn btn-danger btn-sm delete-media"
                                            data-delete-url="${deleteMediaUrl}"
                                            data-url="${fileUrl}" data-id="${
                                file.id
                            }">×
                                        </button>
                                        ${mediaContent}
                                        <a class="select-button btn btn-sm btn-primary position-absolute selectable-image" 
                                            data-image-url="${
                                                fileType == "CoursePreviewVideo"
                                                    ? filePath
                                                    : fileUrl
                                            }" 
                                            data-image-name="${dropzoneName}">Select</a>
                                    </div>
                                    <p class="media-filename">${fileName}</p>
                                </div>`;
                        });

                        mediaContainer.append(mediaHtml);
                        nextPageUrl = response.next_page;

                        if (!response.has_more) {
                            loadMoreButton.hide();
                        } else {
                            loadMoreButton
                                .html("Load More")
                                .prop("disabled", false);
                        }
                    } else {
                        loadMoreButton.hide();
                    }
                },
                error: function () {
                    loadMoreButton.hide();
                    mediaContainer.append(
                        '<p class="text-center text-danger">Failed to load media.</p>'
                    );
                },
            });
        }
        loadMedia(true);
        loadMoreButton.off("click").on("click", function () {
            loadMedia(false);
        });
    });
});
