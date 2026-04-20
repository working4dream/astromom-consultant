@extends('layouts.master')
@section('css')
    <link href="{{ URL::asset('css/custom-dropzone.css') }}" rel="stylesheet" />
@endsection
@section('title')
    Notification
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Notification
        @endslot
        @slot('title')
            Manage Notification
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12">
            <span class="small float-end pb-1">(Only Active Users On App)</span>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.notification.index') }}" class="mb-3"
                        id="notificationFilterForm">
                        <div class="row">
                            <div class="col-md-2">
                                <input type="text" name="full_name" class="form-control" placeholder="Full Name"
                                    value="{{ request()->full_name }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="email" class="form-control" placeholder="Email"
                                    value="{{ request()->email }}">
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('admin.notification.index') }}" class="btn btn-soft-secondary"><i
                                        class="ri-refresh-line"></i></a>
                            </div>
                        </div>
                    </form>
                    <button id="send-notification-btn" type="button" class="btn btn-primary mt-3" data-bs-toggle="modal"
                        data-bs-target="#sendNotificationModal" style="display: none;">
                        Send Notification
                    </button>
                    <div class="py-2 d-flex align-items-center gap-2">
                        <div id="selected-count-div" style="display: none;"><span id="selected-count">0</span> users
                            selected.</div>
                        <a href="#" id="select-entire" style="display: none;">Select Entire Users</a>
                    </div>
                    <x-spinner></x-spinner>
                    <div id="user-list">
                        <table id="users-table" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all"></th>
                                    <th>Name</th>
                                    <th>Contact Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $key => $user)
                                    <tr>
                                        <td><input type="checkbox" class="user-checkbox" value="{{ $user->id }}"></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $user->profile_picture }}"
                                                    onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                                    class="rounded-circle avatar-sm me-2" alt="Profile Picture" />
                                                <div>
                                                    <div><a href="#">
                                                            {{ $user->first_name }} {{ $user->last_name }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {{ $user->email }} <br>
                                            {{ $user->mobile_number }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if (!$users->hasPages() && $users->total() > 0)
                            <p class="small text-muted">
                                Showing
                                <span class="fw-semibold">{{ $users->firstItem() }}</span>
                                to
                                <span class="fw-semibold">{{ $users->lastItem() }}</span>
                                of
                                <span class="fw-semibold">{{ $users->total() }}</span>
                                results
                            </p>
                        @endif
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="sendNotificationModal" tabindex="-1" aria-labelledby="sendNotificationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.notification.send') }}">
                    @csrf
                    <input type="hidden" name="select_all_users" id="select_all_users" value="0">
                    <input type="hidden" name="user_type" id="user_type" value="customer">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sendNotificationModalLabel">Send Notification</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="selected_users" id="selected_users">
                        <div class="mb-3">
                            <label for="notification-title" class="form-label">Title <span
                                    class="text-danger">*</span></label>
                            <div class="d-flex justify-content-between align-items-center">
                                <input type="text" class="form-control" id="notification-title" name="title"
                                    maxlength="45" required>
                            </div>
                            <small id="titleCount" class="text-muted ms-2">Characters: 0/45</small>
                        </div>
                        <div class="mb-3">
                            <label for="notification-body" class="form-label">Body <span
                                    class="text-danger">*</span></label>
                            <div class="d-flex justify-content-between align-items-center">
                                <textarea class="form-control" id="notification-body" name="body" rows="4" maxlength="130" required></textarea>
                            </div>
                            <small id="bodyCount" class="text-muted ms-2">Characters: 0/130</small>
                        </div>
                        <div class="mb-3">
                            <label for="notification-body" class="form-label">Button Text </label>
                            <div class="d-flex justify-content-between align-items-center">
                                <input type="text" class="form-control" id="notification-button-text" name="button_text">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="notification-body" class="form-label">Link </label>
                            <div class="d-flex justify-content-between align-items-center">
                                <input type="text" class="form-control" id="notification-blink" name="link">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="sendNotificationBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"
                                id="sendLoader"></span>
                            Send
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#users-table').DataTable({
                searching: false,
                ordering: false,
                paging: false,
                bInfo: false,
                lengthChange: false
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.user-checkbox');
            const sendNotificationBtn = document.getElementById('send-notification-btn');
            const selectedCountDiv = document.getElementById('selected-count-div');
            const selectedUsersInput = document.getElementById('selected_users');

            function toggleSendNotificationButton() {
                const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
                sendNotificationBtn.style.display = anyChecked ? 'block' : 'none';
                selectedCountDiv.style.display = anyChecked ? 'block' : 'none';
            }

            selectAllCheckbox.addEventListener('change', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                toggleSendNotificationButton();
            });

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', toggleSendNotificationButton);
            });

            sendNotificationBtn.addEventListener('click', function() {
                const selectedUserIds = Array.from(checkboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.value);

                selectedUsersInput.value = JSON.stringify(selectedUserIds);
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('notification-title');
            const bodyTextarea = document.getElementById('notification-body');
            const titleCount = document.getElementById('titleCount');
            const bodyCount = document.getElementById('bodyCount');

            titleInput.addEventListener('input', function() {
                const currentLength = titleInput.value.length;
                titleCount.textContent = `Characters: ${currentLength}/45`;
            });

            bodyTextarea.addEventListener('input', function() {
                const currentLength = bodyTextarea.value.length;
                bodyCount.textContent = `Characters: ${currentLength}/130`;
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const selectAllCheckbox = document.getElementById("select-all");
            const userCheckboxes = document.querySelectorAll(".user-checkbox");
            const selectedCount = document.getElementById("selected-count");
            const sendNotificationBtn = document.getElementById("send-notification-btn");
            const selectEntire = document.getElementById("select-entire");
            const selectAllUsersInput = document.getElementById("select_all_users");
            let totalRecords = {{ $users->total() }};

            function updateSelectedCount() {
                let checkedCount = document.querySelectorAll(".user-checkbox:checked").length;
                selectedCount.textContent = checkedCount;

                if (checkedCount === userCheckboxes.length && totalRecords > userCheckboxes.length) {
                    selectEntire.style.display = "inline";
                } else {
                    selectEntire.style.display = "none";
                }

                sendNotificationBtn.style.display = checkedCount > 0 ? "block" : "none";
            }

            selectAllCheckbox.addEventListener("change", function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateSelectedCount();
            });

            userCheckboxes.forEach(checkbox => {
                checkbox.addEventListener("change", function() {
                    if (!this.checked) {
                        selectAllCheckbox.checked = false;
                    }
                    updateSelectedCount();
                });
            });

            selectEntire.addEventListener("click", function(e) {
                e.preventDefault();
                selectedCount.textContent = totalRecords;
                sendNotificationBtn.style.display = "block";
                selectAllUsersInput.value = "1";
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('#sendNotificationModal form');
            const sendButton = document.getElementById('sendNotificationBtn');
            const loader = document.getElementById('sendLoader');

            form.addEventListener('submit', function() {
                sendButton.disabled = true;
                loader.classList.remove('d-none');
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            let typingTimer;
            const doneTypingInterval = 500;
            
            $('#notificationFilterForm').on('submit', function () {
                $('#loadingSpinner').show();
                $('#user-list').hide();
            });

            $('input[name="full_name"], input[name="email"]').on('keyup', function () {
                clearTimeout(typingTimer);
                const value = $(this).val();
                if (value.length >= 3 || value.length === 0) {
                    typingTimer = setTimeout(() => {
                        $('#notificationFilterForm').submit();
                    }, doneTypingInterval);
                }
            });
    
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const linkInput = document.getElementById('notification-blink');
            const buttonTextInput = document.getElementById('notification-button-text');
    
            linkInput.addEventListener('input', function () {
                if (linkInput.value.trim() !== '') {
                    buttonTextInput.setAttribute('required', 'required');
                } else {
                    buttonTextInput.removeAttribute('required');
                }
            });
        });
    </script>
@endsection
