<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AstrologerWithdrawal;
use Yajra\DataTables\Facades\DataTables;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        return view('index');
    }

    public function dashboard(Request $request)
    {
        $start_date = now()->startOfMonth()->startOfDay();
        $end_date = now()->endOfDay();

        if ($request->has('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) === 2) {
                $start_date = \Carbon\Carbon::createFromFormat('d M, Y', trim($dates[0]))->startOfDay();
                $end_date = \Carbon\Carbon::createFromFormat('d M, Y', trim($dates[1]))->endOfDay();
            }
        }

        // Top Summary Cards (Quick Stats)
        $totalSales = Order::whereBetween('created_at', [$start_date, $end_date])
            ->where('order_status', 7)
            ->sum('total_price');
        $totalOrders = Order::whereBetween('created_at', [$start_date, $end_date])
            ->where('order_status', 7)
            ->count();
        $totalPaidOrders = Order::whereBetween('created_at', [$start_date, $end_date])
            ->where('order_status', 7)
            ->where(function ($query) {
                $query->where('payment_id', 'like', 'pay_%')
                    ->orWhere('payment_id', 'like', 'MOJO%');
            })
            ->count();
        $totalFreeOrders = Order::whereBetween('created_at', [$start_date, $end_date])
            ->where('order_status', 7)
            ->where(function ($query) {
                $query->where('payment_id', 'like', 'free_%');
            })
            ->count();
        $totalCustomers = User::role('customer')->whereBetween('created_at', [$start_date, $end_date])->count();
        $totalExperts = User::role('astrologer')->whereBetween('created_at', [$start_date, $end_date])->count();
        $appointmentsBookedCount = Order::whereBetween('created_at', [$start_date, $end_date])
            ->where('typeable_type', 'App\Models\Appointment')
            ->where('order_status', 7)
            ->count();
        $appointmentsBookedAmount = Order::whereBetween('created_at', [$start_date, $end_date])
            ->where('typeable_type', 'App\Models\Appointment')
            ->where('order_status', 7)
            ->sum('total_price');
        $activeFreeChatUsers = User::role('customer')
            ->whereHas('customerAppointments', function ($query) {
                $query->where('payment_id', 'freeChat');
            })
            ->whereBetween('created_at', [$start_date, $end_date])
            ->count();
        $pendingWithdrawalRequests = AstrologerWithdrawal::where('status', 27)
            ->whereBetween('created_at', [$start_date, $end_date])
            ->count();
        $couponsRedeemed = Order::whereBetween('created_at', [$start_date, $end_date])
            ->where('order_status', 7)
            ->where('coupon_id', '>', 0)
            ->count();
        $totalAppointmentsMinutes = \DB::table('call_logs as cl')
            ->join('appointments as a', 'a.id', '=', 'cl.appointment_id')
            ->whereBetween('cl.created_at', [$start_date, $end_date])
            ->select(
                'a.connect_type',
                \DB::raw('FLOOR(SUM(TIME_TO_SEC(STR_TO_DATE(cl.call_time, "%i:%s")))/60) as total_minutes')
            )
            ->groupBy('a.connect_type')
            ->get();

        // Orders Analytics
        $revenueByProductType = DB::table('orders')
            ->where('order_status', 7)
            ->select('typeable_type', DB::raw('SUM(total_price) as total_price_sum'))
            ->groupBy('typeable_type')
            ->orderByDesc('total_price_sum')
            ->get();
        // User Analytics
        $topExperts = User::role('astrologer')->withCount(['appointments' => function ($query) use ($start_date, $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }])->orderByDesc('appointments_count')->take(10)->get();
        // Earnings Analytics
        $totalPayouts = User::role('astrologer')
            ->withSum('approvedEarnings as total_earnings', 'amount')
            ->get()
            ->sum('total_earnings');
        $totalCompletedWithdrawals = AstrologerWithdrawal::where('status', 28)->sum('amount');
        // Notification Analytics
        return view('dashboard')->with(compact(
            'totalSales',
            'totalOrders',
            'totalPaidOrders',
            'totalFreeOrders',
            'totalCustomers',
            'totalExperts',
            'topExperts',
            'appointmentsBookedCount',
            'appointmentsBookedAmount',
            'activeFreeChatUsers',
            'pendingWithdrawalRequests',
            'couponsRedeemed',
            'totalAppointmentsMinutes',
            'revenueByProductType',
            'totalPayouts',
            'totalCompletedWithdrawals',
        ));
    }

    public function getSalesData($range)
    {
        if ($range === 'daily') {
            $startDate = Carbon::today()->subDays(15);
    
            $salesData = Order::where('order_status', 7)
                ->whereDate('created_at', '>=', $startDate)
                ->select(
                    DB::raw("DATE(created_at) as label"),
                    DB::raw("SUM(total_price) as total_sales")
                )
                ->groupBy(DB::raw("DATE(created_at)"))
                ->orderBy(DB::raw("DATE(created_at)"))
                ->get();
    
        } elseif ($range === 'weekly') {
            $startDate = Carbon::now()->subWeeks(7)->startOfWeek();
    
            $salesData = Order::where('order_status', 7)
            ->whereDate('created_at', '>=', $startDate) 
                ->select(
                    DB::raw("
                        CONCAT(
                            DATE_FORMAT(DATE_SUB(MIN(created_at), INTERVAL WEEKDAY(MIN(created_at)) DAY), '%d %b'),
                            ' - ',
                            DATE_FORMAT(DATE_ADD(DATE_SUB(MIN(created_at), INTERVAL WEEKDAY(MIN(created_at)) DAY), INTERVAL 6 DAY), '%d %b')
                        ) AS label
                    "),
                    DB::raw("SUM(total_price) AS total_sales")
                )
                ->groupBy(DB::raw("YEARWEEK(created_at, 1)"))
                ->orderBy(DB::raw("YEARWEEK(created_at, 1)"))
                ->get();
        } else {
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();
    
            $salesData = Order::where('order_status', 7)
                ->whereDate('created_at', '>=', $startDate)
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '%M %Y') as label"),
                    DB::raw("SUM(total_price) as total_sales")
                )
                ->groupBy(DB::raw("YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%M %Y')"))
                ->orderBy(DB::raw("YEAR(created_at)"))
                ->orderBy(DB::raw("MONTH(created_at)"))
                ->get();
        }
    
        return response()->json([
            'labels' => $salesData->pluck('label'),
            'data' => $salesData->pluck('total_sales')
        ]);
    }
    public function filterOrders(Request $request)
    {
        $status = $request->status ?? 7;
        $orders = Order::where('order_status', $status)
            ->select('orders.*')
            ->orderBy('created_at', 'desc');

        return DataTables::of($orders)
            ->addIndexColumn()
            ->addColumn('customer_name', function ($row) {
                return $row->customer ? $row->customer->first_name . ' ' . $row->customer->last_name : '';
            })
            ->addColumn('order_id_link', function ($row) {
                return '<a href="'.route('admin.orders.show', $row->id).'">#'.$row->order_id.'</a>';
            })
            ->addColumn('total_price', function ($row) {
                return '₹ '.number_format($row->total_price, 2);
            })
            ->addColumn('typeable_type', function ($row) {
                return class_basename($row->typeable_type);
            })
            ->addColumn('status', function ($row) {
                if ($row->order_status == 7) {
                    return '<span class="badge bg-success">Paid</span>';
                } elseif ($row->order_status == 6) {
                    return '<span class="badge bg-warning">Pending</span>';
                } elseif ($row->order_status == 8) {
                    return '<span class="badge bg-danger">Cancelled</span>';
                }
                return '';
            })
            ->addColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d');
            })
            ->rawColumns(['status', 'order_id_link'])
            ->make(true);

    }
    public function freeChatUsage(Request $request){
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $orders = DB::table('orders')
            ->whereDate('created_at', '>=', $startDate)
            ->selectRaw("DATE_FORMAT(created_at, '%b %Y') as month_label, COUNT(*) as total")
            ->where('payment_id', 'freeChat')
            ->where('order_status', 7)
            ->groupBy('month_label')
            ->orderByRaw("MIN(created_at)")
            ->get();

        $labels = $orders->pluck('month_label');
        $data = $orders->pluck('total');

        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }
    public function getLatestCustomers()
    {
        $users = User::role('customer')
            ->latest()
            ->select('users.*');

        return DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('name', function ($row) {
                return $row->first_name . ' ' . $row->last_name;
            })
            ->addColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d H:i:s');
            })
            ->addColumn('contact_details', function ($row) {
                return $row->email . '<br>+' . $row->mobile_code . ' ' . $row->mobile_number;
            })
            ->addColumn('city', function ($row) {
                return $row->city?->name;
            })
            ->rawColumns(['contact_details'])
            ->make(true);
    }
}
