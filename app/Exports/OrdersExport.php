<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
         $orders = Order::query();
         if(request()->filled('order_type')){
            $order_types =request()->filled('order_type');
             foreach($order_types as $type){
                 $orders->orWhere('typeable_type', 'LIKE', "%{$type}%");
             }
         } 
         if(request()->filled('order_no')){
            $order_no=request()->order_no;
          $orders->where('order_id', 'LIKE', "%{$order_no}%");
         
         }
         $customer =request()->customer;
         request()->filled('customer') ? $orders->whereHas('customer', function($query) use($customer){
             $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$customer}%"]);
         }) : null;
         if(request()->filled('date')){
            $date = request()->date;
         $date = explode('to',$date);
         $start_date=$date[0];
         $to_date=$date[1];
         $orders->whereDate('created_at', '>=', $start_date);
         $orders->whereDate('created_at', '<=', $to_date);
         }
         if(request()->filled('order_status') ){
         $order_status =request()->filled('order_status');
         $orders->where('order_status', 'LIKE', "%{$order_status}%");
         }
        
        return $orders = $orders->with('typeable','customer','coupon','status')->orderByDesc('id')->get();

        
    }
    public function map($order): array
    {
        return [
            $order->order_id,
            $order->customer?->first_name.' '.$order->customer?->last_name,
            $order->astrologer?->first_name.' '.$order->astrologer?->last_name,
            $order->total_price, 
            $order->coupon?->name.' ('.$order->coupon?->code.')',
            $order->gst,
            $order->discount,
            $order->status->name
        ];
    }
    public function headings(): array
    {
        return [
            'OrderID',
            'Customer',
            'Astrologer',
            'Price',
            'Coupon',
            'GST',
            'Discount',
            'Status'
        ];
    }
}
