@props(['action' => ''])
@php
    $defaultStart = now()->startOfMonth()->format('d M, Y');
    $defaultEnd = now()->endOfMonth()->format('d M, Y');
    $selectedRange = request('date_range') ?? "$defaultStart - $defaultEnd";
@endphp
<div class="mt-3 mt-lg-0">
    <form action="{{ $action }}" method="GET" id="dateFilterForm">
        <div class="row g-3 mb-0 align-items-center">
            <div class="col-sm-auto">
                <div class="input-group" style="min-width: 320px;">
                    <input type="text" name="date_range" class="form-control" id="daterange"
                           value="{{ $selectedRange }}"
                           readonly style="cursor:pointer;" />
                    <span class="input-group-text bg-primary text-white"><i class="ri-calendar-2-line"></i></span>
                    <a href="{{ $action }}" class="btn btn-soft-secondary"><i class="ri-refresh-line"></i></a>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
    $(function () {
        const form = $('#dateFilterForm');

        $('#daterange').daterangepicker({
            opens: 'left',
            autoUpdateInput: true,
            locale: {
                format: 'DD MMM, YYYY'
            },
            alwaysShowCalendars: true,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            }
        }, function(start, end) {
            $('#daterange').val(start.format('DD MMM, YYYY') + ' - ' + end.format('DD MMM, YYYY'));
            form.submit();
        });
    });
</script>
