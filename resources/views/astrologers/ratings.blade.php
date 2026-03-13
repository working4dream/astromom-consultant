@extends('layouts.master')
@section('css')
    <style>
        .testimonial-card {
            background: #ffffff;
            color: black;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 400px;
        }

        .testimonial-text {
            font-size: 1rem;
        }

        .testimonial-footer {
            display: flex;
            align-items: center;
            margin-top: 15px;
        }
    </style>
@endsection
@section('title')
    Expert Ratings
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
            Ratings
        @endslot
    @endcomponent
    <div class="row" data-masonry='{"percentPosition": true }'>
        @foreach ($reviews as $review)
            <div class="col-md-3 pb-4">
                <div class="testimonial-card">
                    <p class="testimonial-text">{{ $review->review }}</p>
                    <div class="testimonial-footer d-flex">
                        <img class="rounded-circle avatar-sm material-shadow me-2" src="{{ $review?->user->profile_picture }}"
                            onerror="this.src='{{ asset('build/images/users/no-user.png') }}';" alt="User">
                        <div class="d-flex flex-column">
                            <strong>{{ $review?->user->full_name }}</strong>
                            <div class="basic-rater{{ $review->id }}"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        {{ $reviews->appends(request()->query())->links() }}
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/masonry-layout/masonry.pkgd.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/rater-js/index.js') }}"></script>
    <script>
        var reviews = @json(
            $reviews->map(function ($review) {
                $rating = $review->ratings;
                return [
                    'id' => $review->id,
                    'rating' => (float) $rating,
                ];
            }));

        reviews.forEach(review => {
            var basicRating = raterJs({
                starSize: 16,
                rating: parseFloat(review.rating),
                element: document.querySelector(".basic-rater" + review.id),
                readOnly: true
            });
        });
    </script>
@endsection
