<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $customers = User::query();
        if (request()->filled('full_name')) {
            $full_name =request()->full_name;
            $customers->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$full_name}%"]);
        }
        if (request()->filled('email')) {
            $email =request()->email;
            $customers->where('email', 'LIKE', "%{$email}%");
        }
        if (request()->filled('mobile_number')) {
            $mobile_number =request()->mobile_number;
            $customers->where('mobile_number', 'LIKE', "%{$mobile_number}%");
        }
        if (request()->filled('dob')) {
            $dob =request()->dob;
            $customers->where('dob',$dob);
        }
        if (request()->filled('city_id')) {
            $city_id = request()->city_id;
            $customers->where('city_id', 'LIKE', "%{$city_id}%");
        }
       return $customers = $customers->role('customer')->with(['country','state','city'])->orderByDesc('id')->get();
    }
    public function map($order): array
    {
        return [
            $order->first_name.' '.$order->last_name,
            $order->email, 
            $order->mobile_number,
            $order->about_me,
            $order->gender,
            $order->dob,
            $order->professional_title,
            $order->social_links,
            $order->country?->name,
            $order->state?->name,
            $order->city?->name,
            $order->address,
            $order->postal_code,

        ];
    }
    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Mobile Number',
            'Gender',
            'DOB',
            'Professional Title',
            'Social Links',
            'Country',
            'State',
            'City',
            'Address',
            'Postal Code',
        ];
    }
}
