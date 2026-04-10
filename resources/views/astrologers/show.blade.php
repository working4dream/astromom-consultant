@extends('layouts.master')
@section('title')
    Expert Details
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('backarrow')
            {{ route('admin.experts.index') }}
        @endslot
        @slot('li_1')
            Experts
        @endslot
        @slot('title')
            Details
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body d-flex gap-3 align-items-center">
                    <div class="avatar-sm">
                        <div class="avatar-title border bg-primary-subtle border-primary border-opacity-25 rounded-2">
                            <i class="ri-wallet-3-line text-primary fs-2"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fs-15">{{ $currencySymbol }} {{ number_format($expertTotalEarnings, 2) }}</h5>
                        <p class="mb-0 text-muted">Total Earnings</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xxl-3">
            <div class="card" id="contact-view-detail">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block">
                        <img id="astrologerAvatar" src="{{ $astrologer->profile_picture }}"
                            onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                            class="avatar-lg rounded-circle img-thumbnail material-shadow">
                        <span class="contact-active position-absolute rounded-circle bg-success">
                            <span class="visually-hidden"></span>
                        </span>
                    </div>
                    <h5 class="mt-4 mb-1" id="astrologerName">
                        {{ $astrologer->full_name }}
                        <a class="btn btn-primary btn-sm btn-icon waves-effect waves-light"
                            href="{{ route('admin.experts.edit', $astrologer->id) }}">
                            <i class="ri-pencil-fill fs-4"></i>
                        </a>
                    </h5>
                    <div>
                        @php
                            $astrologerTotalRatings = \App\Models\AstrologerRating::where(
                                'astrologer_id',
                                $astrologer->id,
                            )->count();
                            $astrologerAvgRating =
                                $astrologerTotalRatings > 0
                                    ? \App\Models\AstrologerRating::where('astrologer_id', $astrologer->id)->avg(
                                        'ratings',
                                    )
                                    : 0;

                            $appointmentIds = \App\Models\Appointment::where('astrologer_id', $astrologer->id)->pluck(
                                'id',
                            );
                            $appointmentTotalRatings = \App\Models\AppointmentRating::whereIn(
                                'appointment_id',
                                $appointmentIds,
                            )->count();
                            $appointmentAvgRating =
                                $appointmentTotalRatings > 0
                                    ? \App\Models\AppointmentRating::whereIn('appointment_id', $appointmentIds)->avg(
                                        'ratings',
                                    )
                                    : 0;

                            $totalRatings = $astrologerTotalRatings + $appointmentTotalRatings;
                            $combinedAvgRating =
                                $totalRatings > 0
                                    ? round(
                                        ($astrologerAvgRating * $astrologerTotalRatings +
                                            $appointmentAvgRating * $appointmentTotalRatings) /
                                            $totalRatings,
                                        1,
                                    )
                                    : 0;
                        @endphp
                        <div style="">
                            <a href="{{ route('admin.experts.ratings', $astrologer->id) }}">
                                <div id="basic-rater" dir="ltr"></div>
                                {{ $combinedAvgRating }} ({{ $totalRatings }} Reviews)
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold mb-3">Personal Information</h6>
                    <p class="text-muted mb-4" id="astrologerDescription">
                        {{ $astrologer->description }}
                    </p>
                    <div class="table-responsive table-card">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-medium" scope="row">Email ID</td>
                                    <td id="astrologerEmail"> {{ $astrologer->email }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Phone No</td>
                                    <td id="astrologerPhone">{{ $astrologer->mobile_number }} </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Gender</td>
                                    <td>{{ $astrologer->gender }} </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Expert In</td>
                                    <td>
                                        @if ($astrologer->professional_title)
                                            @foreach (explode(',', $astrologer->professional_title) as $ex)
                                                <span class="badge bg-primary-subtle text-primary">
                                                    {{ $ex }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Keywords</td>
                                    <td>
                                        @if ($astrologer->keywords)
                                            @foreach (explode(',', $astrologer->keywords) as $keyword)
                                                <span class="badge bg-primary-subtle text-primary">
                                                    {{ $keyword }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Experience</td>
                                    <td>{{ $astrologer->experience }} </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Language</td>
                                    <td id="astrologerLanguage">
                                        @if ($astrologer->language)
                                            @foreach (explode(',', $astrologer->language) as $lang)
                                                <span class="badge bg-primary-subtle text-primary">
                                                    {{ $lang }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Expertise</td>
                                    <td id="astrologerExpertise">
                                        @if ($astrologer->expertise)
                                            @foreach (explode(',', $astrologer->expertise) as $ex)
                                                <span class="badge bg-primary-subtle text-primary">
                                                    {{ $ex }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Appointments</td>
                                    <td id="totalAppointment">
                                        {{ $astrologer->appointments?->count() }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr>
                    <h6 class="text-muted text-uppercase fw-semibold mb-3">Last Activity</h6>
                    <div class="table-responsive table-card">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-medium" scope="row">IP Address</td>
                                    <td id="astrologerEmail"> {{ $lastActivity?->ip_address }} </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Device Name</td>
                                    <td id="astrologerEmail"> {{ $lastActivity?->device_name }} </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Last Activity</td>
                                    <td id="astrologerEmail"> {{ $lastActivity?->last_activity }} </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-9">
            <div class="row">
                <div class="col-xxl-12">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link {{ request('tab') == 'appointments' ? 'active' : '' }}"
                                            href="?tab=appointments" role="tab">
                                            Appointments ({{ $appointmentCount }})
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link {{ request('tab') == 'orders' ? 'active' : '' }}" href="?tab=orders"
                                            role="tab">
                                            Orders ({{ $ordersCount ?? 0 }})
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link {{ request('tab') == 'notifications' ? 'active' : '' }}" href="?tab=notifications"
                                            role="tab">
                                            Notifications ({{ $notificationsCount ?? 0 }})
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane fade {{ request('tab') == 'appointments' ? 'show active' : '' }}"
                                    id="appointments" role="tabpanel">
                                    <div class="table-responsive table-card pb-2">
                                        <table class="table table-borderless align-middle mb-0">
                                            <thead class="table-light text-muted">
                                                <tr>
                                                    <th scope="col">Order Id</th>
                                                    <th scope="col">Appointment With</th>
                                                    <th scope="col">Type</th>
                                                    <th scope="col">Order Price</th>
                                                    <th scope="col">Earning Price</th>
                                                    <th scope="col">Date</th>
                                                    <th scope="col">Time</th>
                                                    <th scope="col">Duration</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($appointments as $appointment)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('admin.orders.show', $appointment->id) }}">
                                                                #{{ $appointment->order_id }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-sm">
                                                                    @if ($appointment->typeable?->customer?->profile_picture)
                                                                        <img src="{{ $appointment->typeable?->customer?->profile_picture }}"
                                                                            alt=""
                                                                            class="rounded-circle avatar-sm material-shadow">
                                                                    @else
                                                                        <img src="{{ URL::asset('build/images/users/no-user.png') }}"
                                                                            alt=""
                                                                            class="rounded-circle avatar-sm material-shadow">
                                                                    @endif
                                                                </div>
                                                                <div class="ms-3 flex-grow-1">
                                                                    <h6 class="fs-15 mb-0">
                                                                        {{ $appointment->typeable?->customer?->full_name }}</h6>
                                                                    <small
                                                                        class="text-muted mb-0">{{ $appointment->typeable?->customer?->professional_title }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if ($appointment->typeable?->connect_type === 'chat')
                                                                <a href="{{ route('admin.freeChatUsage.show', $appointment->id) }}"
                                                                    target="_blank">
                                                                    {{ ucfirst($appointment->typeable?->connect_type) }} <i
                                                                        class="ri-external-link-fill"></i>
                                                                </a>
                                                            @else
                                                                {{ ucfirst($appointment->typeable?->connect_type) }}
                                                            @endif
                                                        </td>
                                                        <td>{{ $currencySymbol }} {{ number_format($appointment->total_price, 2) }}</td>
                                                        <td>{{ $currencySymbol }} {{ number_format($appointment->typeable?->earnings?->first()?->amount, 2) }}</td>
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($appointment->typeable?->date)->format('d-M-Y') }}
                                                        </td>
                                                        <td>{{ $appointment->typeable?->time_period }}</td>
                                                        <td>{{ $appointment->typeable?->callLog?->call_time ? $appointment->typeable?->callLog?->call_time : '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    {{ $appointments->appends(request()->query())->links() }}
                                </div>
                                <div class="tab-pane fade {{ request('tab') == 'orders' ? 'show active' : '' }}" id="orders"
                                    role="tabpanel">
                                    <div class="table-responsive table-card mb-3">
                                        <table class="table table-borderless align-middle mb-0">
                                            <thead class="table-light text-muted">
                                                <tr>
                                                    <th scope="col">Order Id</th>
                                                    <th scope="col">Total Price </th>
                                                    <th scope="col">Order Type </th>
                                                    <th scope="col">Order Status </th>
                                                    <th scope="col">Order Date </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($orders as $order)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('admin.orders.show', $order->id) }}">
                                                                #{{ $order->order_id }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $currencySymbol }} {{ $order->total_price }}</td>
                                                        <td>{{ explode('\\', $order->typeable_type)[count(explode('\\', $order->typeable_type)) - 1] }}
                                                        </td>
                                                        <td>
                                                            @php
                                                                $status = strtolower($order->status?->name);
                                                                $badgeClass = 'bg-primary';
        
                                                                switch ($status) {
                                                                    case 'pending':
                                                                        $badgeClass = 'bg-warning';
                                                                        break;
                                                                    case 'completed':
                                                                        $badgeClass = 'bg-success';
                                                                        break;
                                                                    case 'cancelled':
                                                                        $badgeClass = 'bg-danger';
                                                                        break;
                                                                }
                                                            @endphp
                                                            <span
                                                                class="badge {{ $badgeClass }} ">{{ $order->status?->name }}</span>
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d-M-Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    {{ $orders->appends(request()->query())->links() }}
                                </div>
                                <div class="tab-pane fade {{ request('tab') == 'notifications' ? 'show active' : '' }}" id="notifications"
                                    role="tabpanel">
                                    @forelse($notifications as $notification)
                                        <div class="border-bottom pb-2 mb-3">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="mb-1">{{ $notification->title }}</h6>
                                                    <p class="mb-1">{{ $notification->subtitle }}</p>
                                                </div>
                                                <div class="text-end text-muted" style="white-space: nowrap;">
                                                    <small>
                                                        {{ \Carbon\Carbon::parse($notification->created_at)->format('d M Y, h:i A') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted">No notifications available.</p>
                                    @endforelse
                                    {{ $notifications->appends(request()->query())->links() }}
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
    <script src="{{ URL::asset('build/libs/rater-js/index.js') }}"></script>

    <script>
        if (document.querySelector('#basic-rater'))
            var basicRating = raterJs({
                starSize: 18,
                rating: {{ $combinedAvgRating }},
                element: document.querySelector("#basic-rater"),
                readOnly: true
            });
    </script>
@endsection
