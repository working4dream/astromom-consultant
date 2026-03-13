@extends('layouts.master')
@section('title')
    Messages
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/@simonwep/pickr/themes/classic.min.css') }}" />
    <!-- 'classic' theme -->
    <link rel="stylesheet" href="{{ URL::asset('build/libs/@simonwep/pickr/themes/monolith.min.css') }}" />
    <!-- 'monolith' theme -->
    <link rel="stylesheet" href="{{ URL::asset('build/libs/@simonwep/pickr/themes/nano.min.css') }}" />
    <!-- 'nano' theme -->
    <style>
        .sticky-date-header {
            position: sticky;
            top: 0;
            z-index: 2;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('backarrow')
            {{ route('admin.freeChatUsage.index') }}
        @endslot
        @slot('li_1')
            Messages
        @endslot
        @slot('title')
            Manage Messages
        @endslot
    @endcomponent
    <div class="container-fluid">
        <div class="chat-wrapper d-lg-flex gap-1 mx-n4 mt-n4 p-1">
            <div class="user-chat w-100 overflow-hidden minimal-border">
                <div class="chat-content d-lg-flex">
                    <div class="w-100 overflow-hidden position-relative">
                        <div class="position-relative">
                            <div class="position-relative" id="users-chat" style="display: block;">
                                <div class="p-3 user-chat-topbar">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="chat-user-img online user-own-img align-self-center me-3 ms-0">
                                                <img src="{{ $order->customer?->profile_picture }}"
                                                    onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                                    class="rounded-circle avatar-xs" alt="">
                                            </div>
                                            <div class="overflow-hidden">
                                                <h5 class="text-truncate mb-0 fs-16">
                                                    {{ $order->customer?->full_name }}
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="chat-user-img online align-self-center me-3">
                                                <img src="{{ $order->astrologer?->profile_picture }}"
                                                    onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                                    class="rounded-circle avatar-xs" alt="">
                                            </div>
                                            <div class="overflow-hidden">
                                                <h5 class="text-truncate mb-0 fs-16">
                                                    {{ $order->astrologer?->full_name ?? 'Expert' }}
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="chat-conversation p-3 p-lg-4 simplebar-scrollable-y" id="chat-conversation"
                                    data-simplebar style="height: calc(100vh - 200px);">
                                    <div class="simplebar-wrapper" style="margin: -24px;">
                                        <div class="simplebar-height-auto-observer-wrapper">
                                            <div class="simplebar-height-auto-observer"></div>
                                        </div>
                                        <div class="simplebar-mask">
                                            <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                                <div class="simplebar-content-wrapper" tabindex="0" role="region"
                                                    aria-label="scrollable content"
                                                    style="height: 100%; overflow: hidden scroll;">
                                                    <div class="simplebar-content" style="padding: 24px;">
                                                        <div id="elmLoader"></div>
                                                        <ul class="list-unstyled chat-conversation-list"
                                                            id="users-conversation">
                                                            @foreach ($groupedMessages as $date => $messagesForDate)
                                                                <div class="sticky-date-header text-center mb-2">
                                                                    <span class="badge bg-primary text-white">
                                                                        {{ \Carbon\Carbon::parse($date)->isToday() ? 'Today' : (\Carbon\Carbon::parse($date)->isYesterday() ? 'Yesterday' : \Carbon\Carbon::parse($date)->format('d M Y')) }}
                                                                    </span>
                                                                </div>

                                                                @foreach ($messagesForDate as $message)
                                                                    @php
                                                                        $side =
                                                                            $message->sender_id == $customerZegoId
                                                                                ? 'left'
                                                                                : 'right';
                                                                        $sender = $users[$message->sender_id] ?? null;
                                                                    @endphp
                                                                    <li class="chat-list {{ $side }}">
                                                                        <div class="conversation-list">
                                                                            <div class="chat-avatar">
                                                                                <img src="{{ $sender->profile_picture }}"
                                                                                    onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                                                                    alt="Profile Picture" />
                                                                            </div>
                                                                            <div class="user-chat-content">
                                                                                <div class="ctext-wrap">
                                                                                    @if ($message->message_types === 'text')
                                                                                        <div class="ctext-wrap-content">
                                                                                            <p class="mb-0 ctext-content">
                                                                                                {{ $message->message }}</p>
                                                                                        </div>
                                                                                    @elseif ($message->message_types === 'audio')
                                                                                        <div class="message-audio mb-0">
                                                                                            <audio controls>
                                                                                                <source
                                                                                                    src="{{ $message->audio_path }}"
                                                                                                    type="audio/mpeg">
                                                                                                Your browser does not
                                                                                                support the audio element.
                                                                                            </audio>
                                                                                        </div>
                                                                                    @else
                                                                                        <div class="message-img mb-0">
                                                                                            <div class="message-img-list">
                                                                                                <div>
                                                                                                    <img src="{{ $message->image_path }}"
                                                                                                        alt=""
                                                                                                        class="rounded border" />
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="conversation-name">
                                                                                    <span
                                                                                        class="name">{{ $sender->first_name . ' ' . $sender->last_name }}</span>
                                                                                    <small
                                                                                        class="text-muted time small-text">
                                                                                        {{ \Carbon\Carbon::parse($message->created_at)->format('h:i A') }}
                                                                                    </small>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </li>
                                                                @endforeach
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="simplebar-placeholder" style="width: 974px; height: 642px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end chat-wrapper -->

    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        $(document).ready(function() {
            var chatContainer = $('#chat-conversation .simplebar-content-wrapper');
            chatContainer.scrollTop(chatContainer[0].scrollHeight);
        });
    </script>
@endsection
