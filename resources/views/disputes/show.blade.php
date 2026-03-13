@extends('layouts.master')
@section('title')
Dispute Details
@endsection
@section('css')
<style>
.small-text {
    font-size: 12px;
}

.bg-chat {
    background-color: #f3f6f9
}

.discussion-item.active,
.discussion-item:hover {
    background-color: rgb(237, 217, 217);
    border-left: 4px solid #802433;
    transition: all 0.3s ease-in-out;
}
.chat-leftsidebar {
        min-width: 380px !important;
        max-width: 380px;
}
</style>
@endsection
@section('content')
@component('components.breadcrumb')
@slot('backarrow')
{{ route('admin.disputes') }}
@endslot
@slot('li_1')
Dispute
@endslot
@slot('title')
Dispute Details
@endslot
@endcomponent
<div class="row d-flex justify-content-center">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="container-fluid bg-chat pt-1">
                    <div class="chat-wrapper d-lg-flex gap-1 mx-n2 mt-n2 p-2">
                        <div class="chat-leftsidebar minimal-border pt-1">
                            <div class="tab-content text-muted" style="padding: 15px;">
                                <div class="tab-pane active" id="chats" role="tabpanel">
                                    <div class="chat-room-list pt-3 simplebar-scrollable-y" data-simplebar="init">
                                        <div class="simplebar-wrapper" style="margin: -16px 0px 0px;">
                                            <div class="simplebar-height-auto-observer-wrapper">
                                                <div class="simplebar-height-auto-observer"></div>
                                            </div>
                                            <div class="simplebar-mask">
                                                <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                                    <div class="simplebar-content-wrapper" tabindex="0" role="region"
                                                        aria-label="scrollable content"
                                                        style="height: auto; overflow: hidden scroll;">
                                                        <div class="simplebar-content" style="padding: 16px 0px 0px;">
                                                            <div class="chat-message-list">
                                                                <ul class="list-unstyled chat-list chat-user-list"
                                                                    id="userList">
                                                                    @if(isset($dispute->discussions))
                                                                    @foreach ($dispute->discussions as $discussion)
                                                                    <li class="discussion-item"
                                                                        data-comment-id="{{ $discussion->dispute_id }}"
                                                                        data-question="{{ $discussion->booking_id }}"
                                                                        data-comment="{{ $discussion->reason }}"
                                                                        data-user="{{ $discussion->user()->withTrashed()->first()->full_name }}"
                                                                        data-date="{{ \Carbon\Carbon::parse($discussion->created_at)->format('d-M-Y') }}"
                                                                        data-image="{{ $discussion->user()->withTrashed()->first()->profile_picture }}">

                                                                    </li>
                                                                    @endforeach
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="simplebar-placeholder" style="width: 400px; height: 649px;">
                                                <div class="row pt-2">
                                                    <div class="col-xxl-6 col-md-6 ">
                                                        <div> Booking Id </div>
                                                    </div>
                                                    <div class="col-xxl-6 col-md-6"> {{$dispute->booking_id}}</div>
                                                </div>
                                                <div class="row pt-2">
                                                    <div class="col-xxl-6 col-md-6 ">
                                                        <div> Customer </div>
                                                    </div>
                                                    <div class="col-xxl-6 col-md-6">
                                                        {{ $dispute->customer?->first_name }}
                                                        {{ $dispute->customer?->last_name }}</div>
                                                </div>
                                                <div class="row pt-2">
                                                    <div class="col-xxl-6 col-md-6 ">
                                                        <div> Ticket Id</div>
                                                    </div>
                                                    <div class="col-xxl-6 col-md-6">{{ $dispute->ticket_id }}</div>
                                                </div>
                                                <div class="row pt-2">
                                                    <div class="col-xxl-6 col-md-6 ">
                                                        <div> Reason </div>
                                                    </div>
                                                    <div class="col-xxl-6 col-md-6">{{ $dispute->reason }}</div>
                                                </div>
                                                <div class="row pt-2">
                                                    <div class="col-xxl-6 col-md-6 ">
                                                        <div> Other Reason</div>
                                                    </div>
                                                    <div class="col-xxl-6 col-md-6">{{ $dispute->other_reason }}</div>
                                                </div>
                                                <div class="row pt-2">
                                                    <div class="col-xxl-6 col-md-6 ">
                                                        <div> Appointment Date </div>
                                                    </div>
                                                    <div class="col-xxl-6 col-md-6">
                                                        {{ \Carbon\Carbon::parse($dispute->appointment_date)->format('d-m-Y') }}
                                                    </div>
                                                </div>
                                                <div class="row pt-2">
                                                    <div class="col-xxl-6 col-md-6 ">
                                                        <div>Description </div>
                                                    </div>
                                                    <div class="col-xxl-6 col-md-6"> {{ $dispute->description }}</div>
                                                </div>
                                                <div class="row pt-2">
                                                    <div class="col-xxl-6 col-md-6 ">
                                                        <div>Status </div>
                                                    </div>
                                                    <div class="col-xxl-6 col-md-6">
                                                        @php
                                                        $ticketStatus =
                                                        $dispute->status === 1
                                                        ? '<span class="badge bg-success">Open</span>'
                                                        : '<span class="badge bg-danger">Close</span>';
                                                        @endphp
                                                        {!! $ticketStatus !!}</div>
                                                </div>
                                                <div class="row pt-2">
                                                    <div class="col-xxl-6 col-md-6 ">
                                                        <div>File </div>
                                                    </div>
                                                    <div class="col-xxl-6 col-md-6">
                                                        <a href="{{$dispute->file}}" download style=" pointer-events: visible;" target="_blank">
                                                                <i class="ri-download-2-fill"></i> Download 
                                                       </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                                            <div class="simplebar-scrollbar" style="width: 0px; display: none;">
                                            </div>
                                        </div>
                                        <div class="simplebar-track simplebar-vertical" style="visibility: visible;">
                                            <div class="simplebar-scrollbar"
                                                style="height: 245px; transform: translate3d(0px, 0px, 0px); display: block;">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="user-chat w-100 overflow-hidden minimal-border user-chat-show">
                            <div class="chat-content d-lg-flex">
                                <div class="w-100 overflow-hidden position-relative">
                                    <div class="position-relative">
                                        <div class="position-relative" id="users-chat" style="display: block;">
                                            <div class="p-3 user-chat-topbar">
                                                <div class="row align-items-center">
                                                    <div class="col-sm-12 col-8">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 d-block d-lg-none me-3">
                                                                <a href="#"
                                                                    class="user-chat-remove fs-18 p-1"><i
                                                                        class="ri-arrow-left-s-line align-bottom"></i></a>
                                                            </div>
                                                            <div class="flex-grow-1 overflow-hidden">
                                                                <div class="d-flex align-items-center">
                                                                    <div
                                                                        class="flex-shrink-0 chat-user-img online user-own-img align-self-center me-3 ms-0">
                                                                    </div>
                                                                    <div class="chat-user-header">
                                                                        <h5 class="mb-0 fs-16">
                                                                            {{$dispute->reason}}
                                                                        </h5>
                                                                        <p class="text-muted fs-14 mb-0 userStatus">
                                                                            <small>
                                                                                {{$dispute->other_reason}}
                                                                            </small>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="chat-conversation p-3 p-lg-4 simplebar-scrollable-y"
                                                id="chat-conversation" data-simplebar="init">
                                                <div class="simplebar-wrapper" style="margin: -24px;">
                                                    <div class="simplebar-height-auto-observer-wrapper">
                                                        <div class="simplebar-height-auto-observer"></div>
                                                    </div>
                                                    <div class="simplebar-mask">
                                                        <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                                            <div class="simplebar-content-wrapper" tabindex="0"
                                                                role="region" aria-label="scrollable content"
                                                                style="height: 100%; overflow: hidden scroll;">
                                                                <div class="simplebar-content" style="padding: 24px;">
                                                                    <div id="elmLoader"></div>
                                                                    <ul class="list-unstyled chat-conversation-list"
                                                                        id="users-conversation">
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="simplebar-placeholder"
                                                        style="width: 974px; height: 642px;"></div>
                                                </div>
                                                <div class="simplebar-track simplebar-horizontal"
                                                    style="visibility: hidden;">
                                                    <div class="simplebar-scrollbar" style="width: 0px; display: none;">
                                                    </div>
                                                </div>
                                                <div class="simplebar-track simplebar-vertical"
                                                    style="visibility: visible;">
                                                    <div class="simplebar-scrollbar"
                                                        style="height: 244px; display: block; transform: translate3d(0px, 144px, 0px);">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="chat-input-section p-3 p-lg-4">
                                            <form id="chatinput-form"
                                                action="{{ route('admin.disputes.storeDiscussionMessage') }}"
                                                method="POST">
                                                @csrf
                                                <div class="row g-0 align-items-center">
                                                    <div class="col">
                                                        <div class="chat-input-feedback">
                                                            Please Enter a Message
                                                        </div>
                                                        <input type="hidden" name="dispute_id" value="{{$dispute->id}}"
                                                            id="dispute_id">
                                                        <input type="hidden" name="user_id" id="user_id"
                                                            value="{{ auth()->user()->id }}">
                                                        <input type="text"
                                                            class="form-control chat-input bg-light border-light"
                                                            id="chat-input" name="message"
                                                            placeholder="Type your message..." autocomplete="off"
                                                            oninput="toggleButton()">
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="chat-input-links ms-2">
                                                            <div class="links-list-item">
                                                                <button type="submit" id="sendButton"
                                                                    class="btn btn-primary chat-send waves-effect waves-light"
                                                                    disabled>
                                                                    <i class="ri-send-plane-2-fill align-bottom"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
function toggleButton() {
    let input = document.getElementById("chat-input").value.trim();
    let button = document.getElementById("sendButton");
    button.disabled = input === "";
}
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let discussionItems = document.querySelectorAll(".discussion-item");

    function selectDiscussion(item) {
        discussionItems.forEach(i => i.classList.remove("active"));
        item.classList.add("active");
        console.log(item.getAttribute("data-comment-id"));

        let commentId = item.getAttribute("data-comment-id");
        let question = item.getAttribute("data-question");
        let comment = item.getAttribute("data-comment");
        let user = item.getAttribute("data-user");
        let date = item.getAttribute("data-date");
        let image = item.getAttribute("data-image");
        let userId = document.getElementById("user_id").value;

        document.getElementById("dispute_id").value = commentId;
        fetch(`/admin/disputes/get-discussion-dispute-messages/${commentId}`)
            .then(response => response.json())
            .then(data => {
                let chatBox = document.getElementById("users-conversation");
                chatBox.innerHTML = "";
                data.forEach(msg => {
                    let messageClass = msg.user_id == userId ? "right" : "left";
                    let profile_picture = msg.profile_picture != null ? msg.profile_picture :
                        '/build/images/users/no-user.png'
                    chatBox.innerHTML += `
                        <li class="chat-list ${messageClass}">
                            <div class="conversation-list">
                                <div class="chat-avatar"><img src="${profile_picture}" alt=""></div>
                                <div class="user-chat-content">
                                    <div class="ctext-wrap">
                                        <div class="ctext-wrap-content">
                                            <p class="mb-0 ctext-content">${msg.comment}</p>
                                        </div>
                                    </div>
                                    <div class="conversation-name">
                                        <span class="name">${msg.full_name}</span>
                                        <small class="text-muted time">${msg.created_at}</small>
                                    </div>
                                </div>
                            </div>
                        </li>
                    `;
                });
            })
            .catch(error => console.error("Error fetching messages:", error));
    }

    if (discussionItems.length > 0) {
        selectDiscussion(discussionItems[0]);
    }

    discussionItems.forEach(item => {
        item.addEventListener("click", function() {
            selectDiscussion(this);
        });
    });
});
</script>

@endsection