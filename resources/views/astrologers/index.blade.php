@extends('layouts.master')
@section('title')
    Experts
@endsection
@section('css')
@endsection
@section('content')
    @component('components.breadcrumb')
        @section('add-route')
            {{ route('admin.experts.create') }}
        @endsection
        @slot('li_1')
            Experts
        @endslot
        @slot('title')
            Manage Experts
        @endslot
    @endcomponent
    <div class="row">
        @if ($astrologers->total() === 0)
            @include('components.add-button')
        @endif
        <div class="col-xxl-12">
            <div class="card">
                <div class="card-body">
                    @include('astrologers.active')
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="viewAstrologerModal" tabindex="-1" aria-labelledby="viewAstrologerLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAstrologerLabel">Expert Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card" id="contact-view-detail">
                        <div class="card-body text-center">
                            <div class="position-relative d-inline-block">
                                <img id="astrologerAvatar" src="assets/images/users/avatar-10.jpg" alt=""
                                    class="avatar-lg rounded-circle img-thumbnail material-shadow">
                                <span class="contact-active position-absolute rounded-circle bg-success">
                                    <span class="visually-hidden"></span>
                                </span>
                            </div>
                            <h5 class="mt-4 mb-1" id="astrologerName"></h5>
                            {{-- <div>
                                <div id="modalAstrologerRating" class="basic-rater"></div>
                                <span id="modalAverageRating"></span> (<span id="modalTotalReviews"></span> Reviews)
                            </div> --}}
                        </div>
                        <div class="card-body">
                            <h6 class="text-muted text-uppercase fw-semibold mb-3">Personal Information</h6>
                            <p class="text-muted mb-4" id="astrologerDescription"></p>
                            <div class="table-responsive table-card">
                                <table class="table table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="fw-medium" scope="row">Email ID</td>
                                            <td id="astrologerEmail"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Phone No</td>
                                            <td id="astrologerPhone"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Gender</td>
                                            <td id="astrologerGender"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Expert In</td>
                                            <td id="astrologerProfessionalTitle"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Keywords</td>
                                            <td id="astrologerKeywords"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Experience</td>
                                            <td id="astrologerExperience"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Language</td>
                                            <td id="astrologerLanguage"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Expertise</td>
                                            <td id="astrologerExpertise"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Appointments</td>
                                            <td id="totalAppointment"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-circle-fill" aria-hidden="true"></i>
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="{{ URL::asset('build/libs/rater-js/index.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#astrologers-table').DataTable({
                searching: false,
                ordering: true,
                paging: false,
                bInfo: false,
                lengthChange: false
            });
        });
    </script>
    @php
    $astrologersData = $astrologers->map(function ($astrologer) {
        $astrologerTotalRatings = \App\Models\AstrologerRating::where('astrologer_id', $astrologer->id)->count();
        $astrologerAvgRating = $astrologerTotalRatings > 0
            ? \App\Models\AstrologerRating::where('astrologer_id', $astrologer->id)->avg('ratings')
            : 0;
    
        $appointmentIds = \App\Models\Appointment::where('astrologer_id', $astrologer->id)->pluck('id');
        $appointmentTotalRatings = \App\Models\AppointmentRating::whereIn('appointment_id', $appointmentIds)->count();
        $appointmentAvgRating = $appointmentTotalRatings > 0
            ? \App\Models\AppointmentRating::whereIn('appointment_id', $appointmentIds)->avg('ratings')
            : 0;
    
        $totalRatings = $astrologerTotalRatings + $appointmentTotalRatings;
        $combinedAvgRating = $totalRatings > 0
            ? (($astrologerAvgRating * $astrologerTotalRatings) + ($appointmentAvgRating * $appointmentTotalRatings)) / $totalRatings
            : 0;
    
        return [
            'id' => $astrologer->id,
            'rating' => round($combinedAvgRating, 1),
        ];
    });
    @endphp
    <script>
        var astrologers = @json($astrologersData);

        astrologers.forEach(astrologer => {
            var basicRating = raterJs({
                starSize: 16,
                rating: astrologer.rating,
                element: document.querySelector(".basic-rater" + astrologer.id),
                readOnly: true
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.view-astrologer-btn').click(function() {
                let astrologerId = $(this).data('id');
                let totalRatings = $(this).data('total_ratings');
                let averageRating = $(this).data('average_rating');

                $('#astrologerName').text($(this).data('name'));
                $('#astrologerEmail').text($(this).data('email'));
                $('#astrologerPhone').text($(this).data('phone'));
                $('#astrologerAvatar').attr('src', $(this).data('avatar'));
                $('#astrologerDescription').text($(this).data('description'));
                $('#astrologerGender').text($(this).data('gender'));
                $('#astrologerExperience').text($(this).data('experience'));
                $('#totalAppointment').text($(this).data('total_appointment'));

                // Expertise
                let expertise = $(this).data('expertise').split(',');
                let expertiseHtml = '';
                expertise.forEach(a => {
                    expertiseHtml +=
                        `<span class="badge bg-primary-subtle text-primary">${a.trim()}</span> `;
                });
                $('#astrologerExpertise').html(expertiseHtml);
                // Language
                let language = $(this).data('language').split(',');
                let languagesHtml = '';
                language.forEach(a => {
                    languagesHtml +=
                        `<span class="badge bg-primary-subtle text-primary">${a.trim()}</span> `;
                });
                $('#astrologerLanguage').html(languagesHtml);
                // Professional_title
                let ProfessionalTitle = $(this).data('professional_title').split(',');
                let ProfessionalTitlesHtml = '';
                ProfessionalTitle.forEach(a => {
                    ProfessionalTitlesHtml +=
                        `<span class="badge bg-primary-subtle text-primary">${a.trim()}</span> `;
                });
                $('#astrologerProfessionalTitle').html(ProfessionalTitlesHtml);
                // Keywords
                let Keyword = $(this).data('keywords').split(',');
                let KeywordsHtml = '';
                Keyword.forEach(a => {
                    KeywordsHtml +=
                        `<span class="badge bg-primary-subtle text-primary">${a.trim()}</span> `;
                });
                $('#astrologerKeywords').html(KeywordsHtml);

                // Set Ratings
                $('#modalAverageRating').text(averageRating);
                $('#modalTotalReviews').text(totalRatings);
                $("#modalAstrologerRating").empty();
                raterJs({
                    starSize: 16,
                    rating: averageRating,
                    element: document.querySelector("#modalAstrologerRating"),
                    readOnly: true
                });
                // Download Button
                let downloadRoute = "{{ route('admin.experts.download', ':id') }}".replace(':id',
                    astrologerId);
                let downloadButton = document.querySelector("#downloadAstrologerProfile");
                if (downloadButton) {
                    downloadButton.href = downloadRoute;
                }
            });
        });
    </script>
@endsection
