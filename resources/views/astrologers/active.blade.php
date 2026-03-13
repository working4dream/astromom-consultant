<div class="card-body">
    <x-spinner></x-spinner>
    <div id="expert-list">
        <table id="astrologers-table" class="table table-bordered dt-responsive nowrap table-striped align-middle"
            style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Contact Details</th>
                    <th>Expertise</th>
                    <th>Total Duration <br>(hh:mm:ss)</th>
                    <th>Active</th>
                    <th>Last logged in</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($astrologers as $key => $astrologer)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center position-relative">
                                <div class="position-relative">
                                    <img src="{{ $astrologer->profile_picture }}"
                                        onerror="this.src='{{ asset('build/images/users/no-user.png') }}';"
                                        class="rounded-circle avatar-sm me-2" alt="Profile Picture" />
                                </div>
                                <div>
                                    <div>
                                        <a
                                            href="{{ route('admin.experts.show', $astrologer->id) . '?tab=appointments' }}">
                                            {{ $astrologer->first_name }} {{ $astrologer->last_name }}
                                            ({{ $astrologer->appointments_count ?? 0 }})
                                        </a>
                                    </div>
                                    <div class="text-muted">
                                        {{ explode(',', $astrologer->professional_title)[0] }}
                                    </div>
                                    @php
                                        $astrologerTotalRatings = \App\Models\AstrologerRating::where(
                                            'astrologer_id',
                                            $astrologer->id,
                                        )->count();
                                        $astrologerAvgRating =
                                            $astrologerTotalRatings > 0
                                                ? \App\Models\AstrologerRating::where(
                                                    'astrologer_id',
                                                    $astrologer->id,
                                                )->avg('ratings')
                                                : 0;

                                        $appointmentIds = \App\Models\Appointment::where(
                                            'astrologer_id',
                                            $astrologer->id,
                                        )->pluck('id');
                                        $appointmentTotalRatings = \App\Models\AppointmentRating::whereIn(
                                            'appointment_id',
                                            $appointmentIds,
                                        )->count();
                                        $appointmentAvgRating =
                                            $appointmentTotalRatings > 0
                                                ? \App\Models\AppointmentRating::whereIn(
                                                    'appointment_id',
                                                    $appointmentIds,
                                                )->avg('ratings')
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
                                    {{-- <div>
                                        <div style="display:flex">
                                            <a href="{{ route('admin.experts.ratings', $astrologer->id) }}">
                                                <div class="basic-rater{{ $astrologer->id }}"></div>&nbsp;
                                                {{ $combinedAvgRating }} ({{ $totalRatings }} Reviews)
                                            </a>
                                        </div>
                                    </div> --}}
                                </div>
                            </div>
                        </td>
                        <td>
                            {{ $astrologer->email }} <br>
                            {{ $astrologer->mobile_number }} <br>
                            {{ $astrologer->experience }}
                        </td>
                        <td>
                            <div class="d-flex flex-wrap">
                                @foreach (explode(',', $astrologer->expertise) as $expertise)
                                    <span
                                        class="badge bg-primary-subtle text-primary me-2 mb-2">{{ trim($expertise) }}</span>
                                @endforeach
                            </div>
                        </td>
                        @php
                            $totalSeconds = 0;

                            if (request()->has('date_range')) {
                                $appointments = $astrologer->appointments->whereBetween('created_at', [
                                    $start_date,
                                    $end_date,
                                ]);
                            } else {
                                $appointments = $astrologer->appointments;
                            }
                            foreach ($appointments as $appointment) {
                                if ($appointment->callLog) {
                                    if ($appointment->callLog->call_time) {
                                        [$min, $sec] = explode(':', $appointment->callLog->call_time);
                                        $totalSeconds += (int) $min * 60 + (int) $sec;
                                    }
                                }
                            }

                            $hours = floor($totalSeconds / 3600);
                            $minutes = floor(($totalSeconds % 3600) / 60);
                            $seconds = $totalSeconds % 60;
                        @endphp

                        <td>
                            {{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input toggle-status" type="checkbox"
                                    data-id="{{ $astrologer->id }}" {{ $astrologer->status === 1 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td>
                            @if ($astrologer->last_logged_in_at)
                                {{ \Carbon\Carbon::parse($astrologer->last_logged_in_at)->format('d-m-Y H:i:s') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item edit-item-btn"
                                            href="{{ route('admin.experts.edit', $astrologer->id) }}">
                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i>
                                            Edit</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item edit-item-btn view-astrologer-btn" href="#"
                                            data-id="{{ $astrologer->id }}" data-name="{{ $astrologer->full_name }}"
                                            data-email="{{ $astrologer->email }}"
                                            data-phone="{{ $astrologer->mobile_number }}"
                                            data-avatar="{{ $astrologer->profile_picture }}"
                                            data-professional_title="{{ $astrologer->professional_title }}"
                                            data-description="{{ $astrologer->description }}"
                                            data-gender="{{ $astrologer->gender }}"
                                            data-keywords="{{ $astrologer->keywords }}"
                                            data-experience="{{ $astrologer->experience }}"
                                            data-language="{{ $astrologer->language }}"
                                            data-short_bio="{{ $astrologer->short_bio }}"
                                            data-expertise="{{ $astrologer->expertise }}"
                                            data-total_ratings="{{ $totalRatings }}"
                                            data-average_rating="{{ $combinedAvgRating }}"
                                            data-is_approved="{{ $astrologer->is_approved }}"
                                            data-total_appointment="{{ $astrologer->appointments->count() }}"
                                            data-bs-toggle="modal" data-bs-target="#viewAstrologerModal">
                                            <i class="ri-eye-fill align-bottom me-2 text-muted"></i> View
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if (!$astrologers->hasPages() && $astrologers->total() > 0)
            <p class="small text-muted">
                Showing
                <span class="fw-semibold">{{ $astrologers->firstItem() }}</span>
                to
                <span class="fw-semibold">{{ $astrologers->lastItem() }}</span>
                of
                <span class="fw-semibold">{{ $astrologers->total() }}</span>
                results
            </p>
        @endif
        {{ $astrologers->appends(request()->query())->links() }}
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('.toggle-status').on('change', function() {
            var astrologerId = $(this).data('id');
            var status = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: '{{ route('admin.experts.updateAstrologerStatus') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    astrologer_id: astrologerId,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });
    });
</script>
