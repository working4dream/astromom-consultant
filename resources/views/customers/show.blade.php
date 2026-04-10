@extends('layouts.master')
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection
@section('title')
    Customer Details
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('backarrow')
            {{ route('admin.customers.index') }}
        @endslot
        @slot('li_1')
            Customers
        @endslot
        @slot('title')
            Details
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-xxl-3">
            <div class="card">
                <div class="card-body p-4">
                    <div>
                        <div class="flex-shrink-0 avatar-md mx-auto">
                            <div class="avatar-title bg-light rounded">
                                <img src="{{ $customer->profile_picture }}"
                                    onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                    class="rounded-circle avatar-lg material-shadow">
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h5 class="mb-1">{{ $customer->full_name }}
                                <a class="btn btn-primary  btn-sm btn-icon waves-effect waves-light"
                                    href="{{ route('admin.customers.edit', $customer->id) }}">
                                    <i class="ri-pencil-fill fs-4"></i>
                                </a>
                            </h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0 table-borderless">
                                <tbody>
                                    <tr>
                                        <th><span class="fw-medium">Email</span></th>
                                        <td>{{ $customer->email }}</td>
                                    </tr>
                                    <tr>
                                        <th><span class="fw-medium">Mobile Number</span></th>
                                        <td>{{ $customer->mobile_number }}</td>
                                    </tr>
                                    <tr>
                                        <th><span class="fw-medium">Gender</span></th>
                                        <td>{{ $customer->gender }}</td>
                                    </tr>
                                    <tr>
                                        <th><span class="fw-medium">DOB</span></th>
                                        <td>{{ $customer->dob }}</td>
                                    </tr>
                                    <tr>
                                        <th><span class="fw-medium">City</span></th>
                                        <td>{{ $customer?->city?->name }}</td>
                                    </tr>
                                    <tr>
                                        <th><span class="fw-medium">Notes</span></th>
                                        <td>{{ $customer?->notes }}</td>
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
                                    <div class="d-flex justify-content-end mb-3">
                                        <button type="button" class="btn btn-primary waves-effect waves-light mb-3"
                                            data-bs-toggle="modal" data-bs-target="#createAppointmentModal">
                                            <i class="fa fa-plus" aria-hidden="true"></i> Create Appointment
                                        </button>
                                    </div>
                                    <div class="table-responsive table-card mb-3">
                                        <table class="table table-borderless align-middle mb-0">
                                            <thead class="table-light text-muted">
                                                <tr>
                                                    <th scope="col">Order Id</th>
                                                    <th scope="col">Appointment With</th>
                                                    <th scope="col">Type</th>
                                                    <th scope="col">Price</th>
                                                    <th scope="col">Date</th>
                                                    <th scope="col">Time</th>
                                                    <th scope="col">Duration</th>
                                                    <th scope="col">Reschedule</th>
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
                                                                    @if ($appointment->typeable?->astrologer?->profile_picture)
                                                                        <img src="{{ $appointment->typeable?->astrologer?->profile_picture }}"
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
                                                                        {{ $appointment->typeable?->astrologer?->full_name }}</h6>
                                                                    <small
                                                                        class="text-muted mb-0">{{ $appointment->typeable?->astrologer?->professional_title }}</small>
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
                                                        <td>{{ $currencySymbol }} {{ $appointment->total_price }}
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($appointment->typeable?->date)->format('d-M-Y') }}
                                                        </td>
                                                        <td>{{ $appointment->typeable?->time_period }}</td>
                                                        <td>{{ $appointment->typeable?->callLog?->call_time }}</td>
                                                        <td>
                                                            <a href="#"
                                                                class="btn btn-primary btn-icon waves-effect waves-light"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#rescheduleModal-{{ $appointment->id }}">
                                                                <i class="ri-history-line fs-4"></i>
                                                            </a>
                                                            <div class="modal fade" id="rescheduleModal-{{ $appointment->id }}"
                                                                tabindex="-1"
                                                                aria-labelledby="rescheduleModalLabel-{{ $appointment->id }}"
                                                                aria-hidden="true">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title"
                                                                                id="rescheduleModalLabel-{{ $appointment->id }}">
                                                                                Reschedule Appointment</h5>
                                                                            <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <form
                                                                                action="{{ route('admin.appointment.reschedule') }}"
                                                                                method="POST">
                                                                                @csrf
                                                                                <input type="hidden" name="appointment_id"
                                                                                    value="{{ $appointment->typeable?->id }}">
                                                                                <div class="mb-3">
                                                                                    <label for="start_time"
                                                                                        class="form-label">Start Time</label>
                                                                                    <input type="time" class="form-control"
                                                                                        id="startTimeReschedule" name="start_time"
                                                                                        required>
                                                                                </div>
                                                                                <div class="modal-footer px-0">
                                                                                    <button type="submit"
                                                                                        class="btn btn-primary">Save</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
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
    <!-- Create Appointment Modal -->
    <div class="modal fade" id="createAppointmentModal" tabindex="-1" aria-labelledby="createAppointmentLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.create-appointment') }}" method="POST">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createAppointmentLabel">Create Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="astrologer" class="form-label">Expert</label>
                            <select class="form-control" name="astrologer_id" id="astrologer_id" required>
                                <option value="">Select Expert</option>
                                @foreach ($astrologers as $astrologer)
                                    <option value="{{ $astrologer->id }}"
                                        {{ old('astrologer_id') == $astrologer->id ? 'selected' : '' }}>
                                        {{ $astrologer->first_name }}
                                        {{ $astrologer->last_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="appointmentDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="appointmentDate" name="date" required>
                        </div>

                        <div class="mb-3">
                            <label for="connectType" class="form-label">Connect Type</label>
                            <select class="form-select" id="connectType" name="connect_type" required>
                                <option value="" disabled selected>Select Type</option>
                                <option value="chat">Chat</option>
                                <option value="voice">Voice</option>
                                <option value="video">Video</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="duration" class="form-label">Duration (in minutes)</label>
                            <input type="number" class="form-control" id="duration" name="duration" required>
                        </div>

                        <div class="mb-3">
                            <label for="startTime" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="startTime" name="start_time"
                                data-provider="timepickr" required>
                        </div>

                        <div class="mb-3">
                            <label for="endTime" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="endTime" name="end_time"
                                data-provider="timepickr" required>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="gst" class="form-label">GST Price</label>
                            <input type="number" class="form-control" id="gst" name="gst" step="0.01"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="totalPrice" class="form-label">Total Price</label>
                            <input type="number" class="form-control" id="totalPrice" name="total_price" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="paymentId" class="form-label">Payment ID</label>
                            <input type="text" class="form-control" id="paymentId" name="payment_id">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="generateBtn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="generateSpinner"
                                role="status" aria-hidden="true"></span>
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            flatpickr("#appointmentDate", {
                dateFormat: "Y-m-d",
                allowInput: true
            });
            flatpickr("#startTime", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });

            flatpickr("#endTime", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });
            flatpickr("#startTimeReschedule", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });
            $('#astrologer_id').select2({
                placeholder: "Select Expert",
                dropdownParent: $('#createAppointmentModal') // agar modal mein hai
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const priceInput = document.getElementById('price');
            const gstInput = document.getElementById('gst');
            const totalInput = document.getElementById('totalPrice');

            function calculateTotal() {
                const price = parseFloat(priceInput.value) || 0;
                const gst = parseFloat(gstInput.value) || 0;
                totalInput.value = (price + gst).toFixed(2);
            }

            priceInput.addEventListener('input', calculateTotal);
            gstInput.addEventListener('input', calculateTotal);
        });
    </script>
@endsection
