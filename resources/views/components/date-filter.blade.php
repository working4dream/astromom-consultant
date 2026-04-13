@props(['action' => ''])
@php
    $defaultStart = \Carbon\Carbon::now(app_display_timezone())->startOfMonth()->format('d M, Y');
    $defaultEnd = \Carbon\Carbon::now(app_display_timezone())->endOfMonth()->format('d M, Y');
    $selectedRange = request('date_range') ?? "$defaultStart to $defaultEnd";
@endphp
<div class="mt-3 mt-lg-0">
    <form action="{{ $action }}" method="GET" id="dateFilterForm">
        <div class="row g-3 mb-0 align-items-center">
            <div class="col-sm-auto">
                <div class="input-group">
                    <input type="text" class="form-control border-0 minimal-border dash-filter-picker shadow flatpickr-input"
                           id="dateRange"
                           name="date_range"
                           data-provider="flatpickr"
                           data-range-date="true"
                           data-date-format="d M, Y"
                           placeholder="Select Date Range"
                           value="{{ $selectedRange }}"
                           readonly>
                    <div class="input-group-text bg-primary border-primary text-white">
                        <i class="ri-calendar-2-line"></i>
                    </div>
                    <a href="{{ $action }}" class="btn btn-soft-secondary pl-2"><i
                        class="ri-refresh-line"></i></a>
                </div>
            </div>
        </div>
    </form>
</div>

@section('script')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let form = document.getElementById("dateFilterForm");

            flatpickr("#dateRange", {
                mode: "range",
                dateFormat: "d M, Y",
                onClose: function() {
                    form.submit();
                }
            });
        });

    </script>
@endsection
