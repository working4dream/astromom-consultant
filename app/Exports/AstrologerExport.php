<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AstrologerExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $astrologers = User::query();
        if (request()->filled('full_name')) {
            $full_name = request()->full_name;
            $astrologers->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$full_name}%"]);
        }
        if (request()->filled('email')) {
            $email = request()->email;
            $astrologers->where('email', 'LIKE', "%{$email}%");
        }
        if (request()->filled('mobile_number')) {
            $mobile_number = request()->mobile_number;
            $astrologers->where('mobile_number', 'LIKE', "%{$mobile_number}%");
        }
        if (request()->filled('experience_comparator') && request()->filled('experience')) {
            $experience_comparator = request()->experience_comparator;
            $experience = request()->experience;
            switch ($experience_comparator) {
                case 'above':
                    $astrologers->where('experience', '>', (int)$experience);
                    break;
                case 'equal':
                    $astrologers->where('experience', '=', (int)$experience);
                    break;
                case 'below':
                    $astrologers->where('experience', '<', (int)$experience);
                    break;
            }
        }
        if (request()->filled('rating_comparator') && request()->filled('rating')) {
            $rating = (float) request()->rating;
        
            $astrologers->leftJoin('astrologer_ratings', 'users.id', '=', 'astrologer_ratings.astrologer_id')
                        ->select('users.*')
                        ->selectRaw('AVG(astrologer_ratings.ratings) as avg_rating')
                        ->groupBy('users.id');
            
            switch (request()->rating_comparator) {
                case 'above':
                    $astrologers->having('avg_rating', '>', $rating);
                    break;
                case 'equal':
                    $astrologers->having('avg_rating', '=', $rating);
                    break;
                case 'below':
                    $astrologers->having('avg_rating', '<', $rating);
                    break;
            }
        }

       return $astrologers = $astrologers->role('astrologer')->with(['country','state','city'])->orderByDesc('id')->get();
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
            $order->consultancy_area,
            $order->hourly_rate,
            $order->description,
            $order->expertise,
            $order->philosophy,
            $order->language,
            $order->response_time,
            $order->start_time,
            $order->end_time,
            $order->experience,
            $order->status,
            $order->last_logged_in_at
        ];
    }
    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Mobile Number',
            'About',
            'Gender',
            'DOB',
            'Professional Title',
            'Social Links',
            'Country',
            'State',
            'City',
            'Address',
            'Postal Code',
            'Consultancy Area',
            'Hourly Rate',
            'Description',
            'Expertises',
            'Philosophy',
            'Language',
            'Response Time',
            'Start Time',
            'End Time',
            'Experience',
            'Status',
            'Last Logged in'
        ];
    }
}
