<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\AstrologerEarning;
use App\Models\AstrologerWithdrawal;
use App\Models\Status;

class ReportController extends Controller
{
    public function earningReport(Request $request)
    {
        $start_date = null;
        $end_date = null;
        
        if ($request->filled('date_range')) {
            $parsed = parse_daterange_string_to_utc($request->date_range, ' - ');
            if ($parsed) {
                [$start_date, $end_date] = $parsed;
            }
        }
        
        // Subquery to get latest earning IDs per astrologer
        $sub = \DB::table('astrologer_earnings')
            ->selectRaw('MAX(id) as id')
            ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
                $query->whereBetween('created_at', [$start_date, $end_date]);
            })
            ->groupBy('astrologer_id');
        
        // Main earning reports query
        $earningReports = AstrologerEarning::select('*')
            ->selectSub(function ($query) use ($start_date, $end_date) {
                $query->from('astrologer_earnings as ae2')
                    ->selectRaw('SUM(amount)')
                    ->whereColumn('ae2.astrologer_id', 'astrologer_earnings.astrologer_id')
                    ->where('status', 1);
                if ($start_date && $end_date) {
                    $query->whereBetween('ae2.created_at', [$start_date, $end_date]);
                }
            }, 'total_earning')
            ->selectSub(function ($query) use ($start_date, $end_date) {
                $query->from('astrologer_withdrawals as aw')
                    ->selectRaw('SUM(amount)')
                    ->whereColumn('aw.astrologer_id', 'astrologer_earnings.astrologer_id')
                    ->where('status', 28);
                if ($start_date && $end_date) {
                    $query->whereBetween('aw.created_at', [$start_date, $end_date]);
                }
            }, 'paid_amount')
            ->with(['astrologer'])
            ->whereIn('id', $sub)
            ->whereHas('astrologer', function ($query) use ($request) {
                if ($request->filled('full_name')) {
                    $fullName = trim($request->full_name);
        
                    if (str_contains($fullName, ' ')) {
                        $query->whereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$fullName}%"])
                              ->orWhere('users.first_name', 'LIKE', "%{$fullName}%");
                    } else {
                        $query->where(function ($q) use ($fullName) {
                            $q->where('users.first_name', 'LIKE', "%{$fullName}%")
                              ->orWhere('users.last_name', 'LIKE', "%{$fullName}%");
                        });
                    }
                }
            })
            ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
                $query->whereBetween('created_at', [$start_date, $end_date]);
            })
            ->orderByDesc('total_earning')
            ->paginate(20);
        
        // Total calculations with date filtering
        $totalAmountQuery = AstrologerEarning::where('status', 1);
        $totalPaidAmountQuery = AstrologerWithdrawal::where('status', 28);
        
        if ($start_date && $end_date) {
            $totalAmountQuery->whereBetween('created_at', [$start_date, $end_date]);
            $totalPaidAmountQuery->whereBetween('created_at', [$start_date, $end_date]);
        }
        
        $totalAmount = $totalAmountQuery->sum('amount');
        $totalPaidAmount = $totalPaidAmountQuery->sum('amount');
        $totalDueAmount = $totalAmount - $totalPaidAmount;
        return view('reports.earning-report', compact(
            'earningReports', 
            'totalAmount', 
            'totalPaidAmount', 
            'totalDueAmount',
        ));
    }

    public function earningReportShow(Request $request, AstrologerEarning $earning)
    {
        [$start_date, $end_date] = default_month_range_utc();

        if ($request->has('date_range') && !empty($request->date_range)) {
            $parsed = parse_daterange_string_to_utc($request->date_range, ' to ');
            if ($parsed) {
                [$start_date, $end_date] = $parsed;
            }
        }
        $orders = Order::where('astrologer_id', $earning->astrologer_id)
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->where('typeable_type', 'App\Models\Appointment')
                            ->orderByDesc('created_at')
                            ->paginate(20);
        $totalAmount = AstrologerEarning::where('astrologer_id', $earning->astrologer_id)
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->sum('amount');
        $totalPaidAmount = AstrologerWithdrawal::where('status', 28)
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->where('astrologer_id', $earning->astrologer_id)
                            ->sum('amount');
        $totalDueAmount = $totalAmount - $totalPaidAmount;
        return view('reports.earning-report-show', compact(
            'earning',
            'orders', 
            'totalAmount', 
            'totalPaidAmount', 
            'totalDueAmount',
            'start_date',
            'end_date',
        ));
    }

    public function accountReport(Request $request)
    {
        [$start_date, $end_date] = default_month_range_utc();

        if ($request->has('date_range') && !empty($request->date_range)) {
            $parsed = parse_daterange_string_to_utc($request->date_range, ' - ');
            if ($parsed) {
                [$start_date, $end_date] = $parsed;
            }
        }

        $statuses = Status::where('type','order_status')->get();
        $orders = Order::query();
        if($request->filled('order_type')){
            $order_types =request()->order_type;
            $orders->where(function($q) use($order_types) { 
                foreach($order_types as $type){
                    $q->orWhere('typeable_type', 'LIKE', "%{$type}%");
                }
            });
        }
        $request->filled('order_no') ? $orders->where('order_id', 'LIKE', "%{$request->order_no}%") : null;

        if ($request->filled('full_name')) {
            $fullName = trim($request->full_name);
        
            $orders->whereHas('customer', function ($query) use ($fullName) {
                if (str_contains($fullName, ' ')) {
                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$fullName}%"])
                            ->orWhere('users.first_name', 'LIKE', "%{$fullName}%");
                } else {
                    $query->where(function ($q) use ($fullName) {
                        $q->where('first_name', 'LIKE', "%{$fullName}%")
                          ->orWhere('last_name', 'LIKE', "%{$fullName}%");
                    });
                }
            });
        }        
        if ($request->filled('mobile_number')) {
            $mobile_number = trim($request->mobile_number);
        
            $orders->whereHas('customer', function ($query) use ($mobile_number) {
                $query->where('mobile_number', 'LIKE', "%{$mobile_number}%");
            });
        }        


        $request->filled('order_status') ? $orders->where('order_status', 'LIKE', "%{$request->order_status}%") : null;
        $orders = $orders->with('typeable','customer','coupon')
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->orderByDesc('id')
                    ->paginate(request('items') ?? 10)
                    ->withQueryString();

        $pendingOrders = Order::where('order_status', 6)->whereBetween('created_at', [$start_date, $end_date])->count();
        $completedOrders = Order::where('order_status', 7)->whereBetween('created_at', [$start_date, $end_date])->count();
        $cancelledOrders = Order::where('order_status', 8)->whereBetween('created_at', [$start_date, $end_date])->count();
        $totalRevenue = Order::where('order_status', 7)->whereBetween('created_at', [$start_date, $end_date])->sum('total_price');
        return view('reports.account-report', compact('orders', 'statuses', 'pendingOrders', 'completedOrders', 'cancelledOrders', 'totalRevenue'));
    }
}
