@extends('layouts.master')
@section('title')
User Refunds
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
User Refunds
@endslot
@slot('title')
Manage Refunds
@endslot
@endcomponent
<div class="row">
  
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <table id="refund-table" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 20%">Order Id</th>
                            <th>User </th>
                            <th>Reason </th>
                            <th >Amount</th>
                            <th >Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($refunds as $refund)
                        <tr>
                            <td><a  href="{{ route('admin.orders.show', $refund->order_id) }}">{{ $refund->order->order_id}}</a></td>
                            <td>{{ $refund->user->first_name }} {{ $refund->user->last_name }}</td>
                            <td>{{ $refund->reason }}</td>
                            <td>{{ $refund->amount }}</td>
                            <td>   @php
                                        $status = strtolower($refund->status?->name);
                                        $badgeClass = 'bg-primary';

                                        switch ($status) {
                                            case 'pending':
                                                $badgeClass = 'bg-warning';
                                                break;
                                            case 'approved':
                                                $badgeClass = 'bg-success';
                                                break;
                                            case 'rejected':
                                                $badgeClass = 'bg-danger';
                                                break;
                                        }
                                    @endphp
                                <span class="badge {{ $badgeClass }} ">{{ $refund->status?->name }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $refunds->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
<script>
        $(document).ready(function() {
            $('#refund-table').DataTable({
                searching: false,
                ordering: false,
                paging: false,
                bInfo: false,
                lengthChange: false
            });
        });
    </script>
@endsection
