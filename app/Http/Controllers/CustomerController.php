<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Traits\AwsS3Trait;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use App\Exports\CustomerExport;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;



class CustomerController extends Controller
{
    use AwsS3Trait;
    public function index(Request $request) 
    {
        $customers = User::query();
        if ($request->filled('full_name')) {
            $fullName = trim($request->full_name);
        
            if (str_contains($fullName, ' ')) {
                $customers->whereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$fullName}%"])
                            ->orWhere('users.first_name', 'LIKE', "%{$fullName}%");
            } else {
                $customers->where(function ($query) use ($fullName) {
                    $query->where('users.first_name', 'LIKE', "%{$fullName}%")
                          ->orWhere('users.last_name', 'LIKE', "%{$fullName}%");
                });
            }
        }
        if ($request->filled('email')) {
            $customers->where('email', 'LIKE', "%{$request->email}%");
        }
        if ($request->filled('mobile_number')) {
            $customers->where('mobile_number', 'LIKE', "%{$request->mobile_number}%");
        }
        if ($request->filled('dob')) {
            $customers->where('dob', $request->dob);
        }
        if ($request->filled('city_id')) {
            $customers->where('city_id', 'LIKE', "%{$request->city_id}%");
        }
        $customers = $customers->role('customer')->orderByDesc('id')->paginate(request('items') ?? 20)->withQueryString();
        $cities = $this->getCities();
        return view('customers.index', compact('customers', 'cities'));
    }

    public function create() 
    {
        $cities = $this->getCities();
        return view('customers.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile_number' => 'required|unique:users,mobile_number',
            'gender' => 'required',
        ]);
        
        $password = strtolower(str_replace(' ', '', $request->first_name)) . '@123';
        $customer = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'mobile_code' => 91,
            'mobile_number' => $request->mobile_number,
            'profile_picture' => $request->profile_picture,
            'password' => Hash::make($password),
            'dob' => $request->dob,
            'gender' => $request->gender,
            'city_id' => $request->city_id,
            'notes' => $request->notes
        ]);
    
        $role = Role::findByName('customer');
        $customer->assignRole($role);
        return redirect()->route('admin.customers.index')->with("success", 'Customer created successfully');
    }

    public function show(User $customer)
    {
        $appointments = Order::where('customer_id', $customer->id)->where('typeable_type','App\Models\Appointment')->orderByDesc('id')->paginate(10);
        $reports = Order::where('customer_id', $customer->id)->where('typeable_type','LIKE','%'.'report'.'%')->orderByDesc('id')->paginate(10);
        $appointmentCount = Order::where('customer_id', $customer->id)->where('typeable_type','App\Models\Appointment')->count();
        $reportCount = Order::where('customer_id', $customer->id)->where('typeable_type','LIKE','%'.'report'.'%')->count();
        $astrologers = User::role('astrologer')->get();
        $lastActivity = UserActivity::where('user_id',$customer->id)->latest()->first();
        $orders = Order::where('customer_id', $customer->id)->orderByDesc('id')->paginate(10);
        $ordersCount = Order::where('customer_id', $customer->id)->count();
        $notifications = Notification::where('user_id', $customer->id)->orderByDesc('id')->paginate(10);
        $notificationsCount = Notification::where('user_id', $customer->id)->count();
        return view('customers.show', compact(
            'customer',
            'appointments',
            'reports',
            'astrologers',
            'appointmentCount',
            'reportCount',
            'lastActivity',
            'orders',
            'ordersCount',
            'notifications',
            'notificationsCount',
        ));
    }

    public function edit(User $customer)
    {
        $cities = $this->getCities();
        return view('customers.edit', compact('customer','cities'));
    }

    public function update(User $customer, Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $customer->id,
            'mobile_number' => 'required|unique:users,mobile_number,' . $customer->id,
            'gender' => 'required',
        ]);

        $customer->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'profile_picture' => $request->profile_picture,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'city_id' => $request->city_id,
            'notes' => $request->notes
        ]);
    
        return redirect()->route('admin.customers.index')->with("success", 'Customer updated successfully');
    }

    public function destroy(User $customer)
    {
        if($customer){
            $this->deleteFileFromS3($customer->profile_picture);
          }
        $customer->delete();
        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted successfully');
    }

    public function getCities()
    {
        $cities = \DB::table('cities')
            ->join('states', 'cities.state_id', '=', 'states.id')
            ->join('countries', 'states.country_id', '=', 'countries.id')
            ->select('cities.*')
            ->orderBy('cities.name', 'asc')
            ->get();

        return $cities;
    }
    public function export(){
        return Excel::download(new CustomerExport, 'customers.xlsx');
    }
    public function appointmentReschedule(Request $request)
    {
        $appointment = Appointment::findOrFail($request->appointment_id);
        $startTime = Carbon::parse($request->start_time);
        $endTime = $startTime->copy()->addMinutes((int)$appointment->duration);
        $timePeriod = $startTime->format('H:i') . '-' . $endTime->format('H:i');
        $appointment->update([
            'date' => now()->toDateString(),
            'time_period' => $timePeriod,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'booking_status' => 15,
        ]);
        // Send Notification
        // For Customer
        $deviceTokensCustomer = json_decode($appointment->customer->device_token);
        $titleCustomer = "Appointment Rescheduled";
        $messageCustomer = "Your appointment with ". $appointment->astrologer->full_name ." is confirmed for ". Carbon::parse($appointment->date)->format('d-M-Y') ." at ". $startTime->format('H:i') .". Be ready for your session.";
        if (!empty($deviceTokensCustomer)) {
            $this->sendNotificationForAdmin($titleCustomer, $messageCustomer, $deviceTokensCustomer);
        }
        Notification::create([
            'user_id' => $appointment->customer->id,
            'title' => $titleCustomer,
            'subtitle' => $messageCustomer,
            'type' => 'general',
        ]);
        // For Expert
        $deviceTokens = json_decode($appointment->astrologer->device_token);
        $title = "Appointment Rescheduled";
        $message = "You have a new appointment with ". $appointment->customer->full_name ." on ". Carbon::parse($appointment->date)->format('d-M-Y') ." at ". $startTime->format('H:i') .". Please be prepared.";
        if (!empty($deviceTokens)) {
            $this->sendNotificationForAdmin($title, $message, $deviceTokens);
        }
        Notification::create([
            'user_id' => $appointment->astrologer->id,
            'title' => $title,
            'subtitle' => $message,
            'type' => 'general',
        ]);
        return redirect()->back()->with('success', 'Appointment rescheduled successfully');
    }

}
