<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\User;

class WithdrawRequestExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $index = 1;
        return User::role('astrologer')
            ->withSum('approvedEarnings as total_earnings', 'amount')
            ->withSum('approvedWithdrawals as total_withdrawals', 'amount')
            ->having('total_earnings', '>', 0)
            ->get()
            ->map(function ($user) use (&$index) {
                return [
                    'id' => $index++,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->mobile_number,
                    'total_amount' => (float) $user->total_earnings * 2,
                    'earning_amount' => (float) $user->total_earnings,
                    'withdrawals' => (float) ($user->total_withdrawals ?? 0),
                    'due_amount' => (float) ($user->total_earnings - $user->total_withdrawals),
                ];
            });
    }
    public function headings(): array
    {
        return ['#', 'Name', 'Email', 'Phone', 'Total Amount', 'Earning Amount', 'Withdrawals', 'Due Amount'];
    }
}
