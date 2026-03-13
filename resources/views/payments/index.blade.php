@extends('layouts.master')
@section('title')
User Payments
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
User Payments
@endslot
@slot('title')
Manage Payments
@endslot
@endcomponent
<div class="row">
  
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <table id="payment-table" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 20%">Order Id</th>
                            <th>Transaction ID </th>
                            <th>Payment Method </th>
                            <th>Payment Date </th>
                            <th >Amount</th>
                            <th >Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                        <tr>
                            <td><a  href="{{ route('admin.orders.show', $payment->order_id) }}"> {{ $payment->order->order_id}}</a></td>
                            <td>{{ $payment->transaction_id }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>{{ $payment->payment_date }}</td>
                            <td>{{ $payment->amount }}</td>
                            <td>  @php
                                        $status = strtolower($payment->status?->name);
                                        $badgeClass = 'bg-primary';

                                        switch ($status) {
                                            case 'pending':
                                                $badgeClass = 'bg-warning';
                                                break;
                                            case 'successful':
                                                $badgeClass = 'bg-success';
                                                break;
                                            case 'failed':
                                                $badgeClass = 'bg-danger';
                                                break;
                                        }
                                    @endphp
                                <span class="badge {{ $badgeClass }} ">{{ $payment->status?->name }}</span></td>
                            
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $payments->appends(request()->query())->links() }}
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
            $('#payment-table').DataTable({
                searching: false,
                ordering: false,
                paging: false,
                bInfo: false,
                lengthChange: false
            });
        });
    </script>
@endsection
