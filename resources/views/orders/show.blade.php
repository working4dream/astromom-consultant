@extends('layouts.master')
@section('title')
    Order Details
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Ecommerce
        @endslot
        @slot('title')
            Orders Details
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-xl-9">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title flex-grow-1 mb-0">Order ID #{{ $order->order_id }}</h5>
                        <div class="flex-shrink-0 me-3">
                            Payment ID #{{ $order->payment_id }}
                        </div>
                        <div class="flex-shrink-0 ms-3">
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
                            <span class="badge {{ $badgeClass }} ">{{ $order->status?->name }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-card">
                        <table class="table table-nowrap align-middle table-borderless mb-0">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th scope="col">Item</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col" class="text-end">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        @if ($order->typeable_type === 'App\Models\Appointment')
                                            <div class="d-flex">
                                                <div class="flex-shrink p-1 ">
                                                    @if ($order->typeable?->astrologer->profile_picture)
                                                        <img src="{{ $order->typeable?->astrologer->profile_picture }}"
                                                            alt=""
                                                            class="avatar-md rounded material-shadow mx-auto d-block">
                                                    @else
                                                        <img src="{{ URL::asset('build/images/users/no-user.png') }}"
                                                            alt=""
                                                            class="avatar-md rounded material-shadow mx-auto d-block">
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1 ms-3 align-content-center">
                                                    <h6 class="fs-15 mb-0">{{ $order->typeable?->astrologer?->full_name }}
                                                        @if ($order->typeable?->connect_type === 'video')
                                                            <i class=" bx bxs-video"></i>
                                                        @elseif($order->typeable?->connect_type === 'voice')
                                                            <i class="ri-headphone-fill"></i>
                                                        @else
                                                            <i class="bx bxs-chat"></i>
                                                        @endif
                                                    </h6>
                                                    <small
                                                        class="text-muted mb-0 d-block">{{ $order->typeable?->astrologer->professional_title }}</small>
                                                    <small
                                                        class="text-muted mb-0">{{ fmt_date($order->typeable?->date, 'd-M-Y') }}
                                                        &#8226; </small>
                                                    <small
                                                        class="text-muted mb-0">{{ $order->typeable?->time_period }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 p-1">
                                                    <img src="{{ URL::asset('build/images/report.png') }}" alt=""
                                                        class="avatar-sm rounded material-shadow mx-auto d-block">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="fs-15 mb-0">
                                                        {{ explode('\\', $order->typeable_type)[count(explode('\\', $order->typeable_type)) - 1] }}
                                                    </h5>
                                                    <small class="text-muted mb-0 d-block">
                                                        <span class="fw-bold">Booked On: </span>
                                                        {{ user_tz_format($order->typeable?->created_at, 'd-M-Y') }}
                                                    </small>
                                                    <small class="text-muted mb-0 d-block">
                                                        <span class="fw-bold">Delivered On: </span>
                                                        {{ user_tz_format(\Carbon\Carbon::parse($order->typeable?->created_at)->addDays(3), 'd-M-Y') }}
                                                    </small>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $currencySymbol }} {{ $order->price }}</td>
                                    <td>1</td>
                                    <td class="fw-medium text-end">
                                        {{ $currencySymbol }} {{ $order->price }}
                                    </td>
                                </tr>
                                <tr class="border-top border-top-dashed">
                                    <td colspan="2"></td>
                                    <td colspan="2" class="fw-medium p-0">
                                        <table class="table table-borderless mb-0">
                                            <tbody>
                                                <tr>
                                                    <td>Sub Total :</td>
                                                    <td class="text-end">{{ $currencySymbol }} {{ $order->price }}
                                                    </td>
                                                </tr>
                                                @if ($order->discount)
                                                    <tr>
                                                        <td>Discount <span
                                                                class="text-muted">({{ $order->coupon?->code }})</span> :
                                                        </td>
                                                        <td class="text-end">-{{ $currencySymbol }}
                                                            {{ $order->discount }}</td>
                                                    </tr>
                                                @endif
                                                @if ($order->gst)
                                                    <tr>
                                                        <td>GST <span class="text-muted"></span> : </td>
                                                        <td class="text-end">{{ $currencySymbol }}
                                                            {{ $order->gst }}</td>
                                                    </tr>
                                                @endif
                                                <tr class="border-top border-top-dashed">
                                                    <th scope="row">Total :</th>
                                                    <th class="text-end">{{ $currencySymbol }}
                                                        {{ $order->total_price }}</th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            @php
                $isCustomer = $order->customer_id && $order->customer_id != 0;
                $profile = $isCustomer ? $order->customer : $order->astrologer;
                $route = $isCustomer
                    ? route('admin.customers.show', $order->customer_id) . '?tab=appointments'
                    : route('admin.experts.show', $order->astrologer_id) . '?tab=appointments';
                $label = $isCustomer ? 'Customer' : 'Astrologer';
            @endphp
            <div class="card">
                <div class="card-header">
                    <div class="d-flex">
                        <h5 class="card-title flex-grow-1 mb-0">{{ $label }} Details</h5>
                        <div class="flex-shrink-0">
                            <a href="{{ $route }}" class="link-secondary">View Profile</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 vstack gap-3">
                        <li>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <img src="{{ $profile?->profile_picture }}"
                                        onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                        class="avatar-sm rounded material-shadow">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fs-14 mb-1">{{ $profile?->first_name }} {{ $profile?->last_name }}</h6>
                                    <p class="text-muted mb-0">{{ $label }}</p>
                                </div>
                            </div>
                        </li>
                        <li><i class="ri-mail-line me-2 align-middle text-muted fs-16"></i>{{ $profile?->email }}</li>
                        <li><i class="ri-phone-line me-2 align-middle text-muted fs-16"></i>{{ $profile?->mobile_number }}
                        </li>
                    </ul>
                </div>
            </div>
            @if ($order->typeable_type === 'App\Models\Appointment')
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex">
                            <h5 class="card-title flex-grow-1 mb-0">Appointment Details</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 vstack gap-3">
                            @if ($order->typeable?->service_type)
                                <li><span class="fw-bold">Service Type:
                                    </span>{{ $order->typeable?->service_type }}</li>
                            @endif
                            <li><span class="fw-bold">Connect Type:
                                </span>{{ ucfirst($order->typeable?->connect_type) }}
                                @if ($order->payment_id === 'freeChat')
                                    <a href="{{ route('admin.freeChatUsage.show', $order->id) }}" target="_blank">
                                        (Free Chat) <i class="ri-external-link-fill"></i>
                                    </a>
                                @endif
                            </li>
                            <li><span class="fw-bold">Date:
                                </span>{{ fmt_date($order->typeable?->date, 'd-M-Y') }}
                            </li>
                            <li><span class="fw-bold">Time Period:
                                </span>{{ $order->typeable?->time_period }}</li>
                            <li><span class="fw-bold">Duration:
                                </span>{{ $order->typeable?->callLog?->call_time }}</li>
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
