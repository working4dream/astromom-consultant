<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\User;
use App\Models\Order;
use App\Models\State;
use App\Models\Country;
use App\Traits\AwsS3Trait;
use App\Models\Appointment;
use Illuminate\Support\Str;
use App\Models\Notification;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use App\Models\ExpertProfile;
use App\Models\ExpertReferral;
use App\Models\AstrologerRating;

use App\Exports\AstrologerExport;
use App\Models\AppointmentRating;
use App\Models\AstrologerEarning;
use App\Models\AstrologerSchedule;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\AstrologerBookNowPrice;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Response;
use Spatie\Permission\Models\Permission;

class AstrologerController extends Controller
{
    use AwsS3Trait;

    public function index(Request $request)
    {
        $start_date = now()->startOfMonth()->startOfDay();
        $end_date = now()->endOfDay();

        if ($request->has('date_range') && ! empty($request->date_range)) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) === 2) {
                $start_date = \Carbon\Carbon::createFromFormat('d M, Y', trim($dates[0]))->startOfDay();
                $end_date = \Carbon\Carbon::createFromFormat('d M, Y', trim($dates[1]))->endOfDay();
            } elseif (count($dates) === 1) {
                $single_date = \Carbon\Carbon::createFromFormat('d M, Y', trim($dates[0]));
                $start_date = $single_date->copy()->startOfDay();
                $end_date = $single_date->copy()->endOfDay();
            }
        }

        $dateFiltered = $request->has('date_range') && ! empty($request->date_range);

        $astrologers = User::query()
            ->role('astrologer')
            ->withCount([
                'appointments' => function ($query) use ($start_date, $end_date, $dateFiltered) {
                    if ($dateFiltered) {
                        $query->whereBetween('created_at', [$start_date, $end_date]);
                    }
                },
            ]);

        if ($dateFiltered) {
            $astrologers->whereHas('appointments', function ($query) use ($start_date, $end_date) {
                $query->whereBetween('created_at', [$start_date, $end_date]);
            });
        }

        if ($request->filled('top_experts')) {
            $astrologers->orderByDesc('appointments_count')->limit(25);
        }

        if ($request->filled('full_name')) {
            $fullName = trim($request->full_name);
        
            if (str_contains($fullName, ' ')) {
                $astrologers->whereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$fullName}%"])
                            ->orWhere('users.first_name', 'LIKE', "%{$fullName}%");
            } else {
                $astrologers->where(function ($query) use ($fullName) {
                    $query->where('users.first_name', 'LIKE', "%{$fullName}%")
                          ->orWhere('users.last_name', 'LIKE', "%{$fullName}%");
                });
            }
        }
        
        if ($request->filled('email')) {
            $astrologers->where('email', 'LIKE', "%{$request->email}%");
        }
        if ($request->filled('mobile_number')) {
            $astrologers->where('mobile_number', 'LIKE', "%{$request->mobile_number}%");
        }
        if ($request->filled('experience_comparator') && $request->filled('experience')) {
            switch ($request->experience_comparator) {
                case 'above':
                    $astrologers->where('experience', '>', (int)$request->experience);
                    break;
                case 'equal':
                    $astrologers->where('experience', '=', (int)$request->experience);
                    break;
                case 'below':
                    $astrologers->where('experience', '<', (int)$request->experience);
                    break;
            }
        }
        if ($request->filled('rating_comparator') && $request->filled('rating')) {
            $rating = (float) $request->rating;

            $astrologers->withAggregate('ratings as avg_rating', 'ratings', 'avg');

            switch ($request->rating_comparator) {
                case 'above':
                    $astrologers->having('avg_rating', '>', $rating);
                    break;
                case 'equal':
                    $astrologers->havingRaw('ROUND(avg_rating, 4) = ?', [round($rating, 4)]);
                    break;
                case 'below':
                    $astrologers->having('avg_rating', '<', $rating);
                    break;
            }
        }
        if($request->has('active')) {
            if($request->active !=null){
             $astrologers->where('status', $request->active);
            }
        }
        if($request->has('expertise')){
            $expertises = $request->expertise;
            $astrologers->where(function($q) use($expertises) { 
                foreach ($expertises as $expertise) {
                    $q->orWhere('expertise', 'LIKE', "%{$expertise}%");
                }
           });
        }
        if($request->has('professional_title')){
            $professional_titles = $request->professional_title;
            $astrologers->where(function($q) use($professional_titles) { 
                foreach ($professional_titles as $professional_title) {
                    $q->orWhere('professional_title', 'LIKE', "%{$professional_title}%");
                }
           });
        }
        $astrologers = $request->filled('top_experts') ? 
            $astrologers->paginate(request('items') ?? 25)->withQueryString() :
            $astrologers->orderByDesc('id')->paginate(request('items') ?? 10)->withQueryString();
        $permissions = Permission::all();
        $roles = Role::get();
        return view('astrologers.index', compact('astrologers', 'permissions', 'roles', 'start_date', 'end_date'));
    }

    public function create()
    {
        $permissions = Permission::all();
        $countries = Country::all();
        $cities = $this->getCities();
        return view('astrologers.create', compact('permissions', 'countries', 'cities'));
    }
    public function show($id){
        $appointments = Order::where('astrologer_id', $id)->where('typeable_type','App\Models\Appointment')->orderByDesc('id')->paginate(10);
        $astrologer = User::with('country','state','city','appointments','ratings')->findOrFail($id);
        $reports = Order::where('astrologer_id', $id)->where('typeable_type','LIKE','%'.'report'.'%')->orderByDesc('id')->paginate(10);
        $appointmentCount = Order::where('astrologer_id', $id)->where('typeable_type','App\Models\Appointment')->count();
        $reportCount = Order::where('astrologer_id', $id)->where('typeable_type','LIKE','%'.'report'.'%')->count();
        $expertTotalEarnings = AstrologerEarning::where('astrologer_id',$id)->sum('amount');
        $lastActivity = UserActivity::where('user_id',$id)->latest()->first();
        $orders = Order::where('astrologer_id', $id)->orderByDesc('id')->paginate(10);
        $ordersCount = Order::where('astrologer_id', $id)->count();
        $notifications = Notification::where('user_id', $id)->orderByDesc('id')->paginate(10);
        $notificationsCount = Notification::where('user_id', $id)->count();
        return view('astrologers.show', compact(
            'astrologer',
            'appointments',
            'reports',
            'appointmentCount',
            'reportCount',
            'expertTotalEarnings',
            'lastActivity',
            'orders',
            'ordersCount',
            'notifications',
            'notificationsCount',
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'mobile_number' => 'required|string|unique:users,mobile_number|max:20',
            'professional_title' => 'required',
            'keywords' => 'required',
            'description' => 'required|max:205',
            'expertise' => 'required',
            'language' => 'required',
            'experience' => 'required',
            'city_id' => 'required',
        ]);

        $fullName = $request->first_name . ' ' . $request->last_name;
        $slug = Str::slug($fullName);
        $originalSlug = $slug;
        $i = 1;
        while (User::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $i++;
        }

        $password = strtolower(str_replace(' ', '', $request->first_name)) . '@123';
        $languageString = implode(',', $request->language);

        $astrologer = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'mobile_code' => 91,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($password),
            'gender' => $request->gender,
            'profile_picture' => $request->profile_picture,
            // 'cut_out_image' => $request->cutout_image,
            'professional_title' => implode(',', $request->professional_title),
            'keywords' => implode(',', $request->keywords),
            'description' => trim($request->description),
            'expertise' => implode(',',$request->expertise),
            'language' => $languageString,
            'status' => isset($request->status)?1:0,
            'experience' => $request->experience,
            'city_id' => $request->city_id,
            'slug' => $slug,
            'is_approved' => 1,
            'approved_id' => auth()->id(),
            'zego_user_id' => rand(1000, 9999) . time(),
        ]);
        $astrologer->assignRole('astrologer');
        if ($astrologer) {
            $this->createAstrologerSchedule($astrologer);
        }
        return redirect()->route('admin.experts.index',['tab'=>'approved'])->with("success", 'Astrologer Added successfully');
    }

    public function edit($id)
    {
        $astrologer = User::findOrFail($id);
        $countries = Country::all();
        $states = State::where('country_id', $astrologer->country_id)->get();
        $cities = $this->getCities();

        return view('astrologers.edit', compact('astrologer', 'cities', 'countries', 'states'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'mobile_number' => 'required|unique:users,mobile_number,' . $id,
            'professional_title' => 'required',
            'keywords' => 'required',
            'description' => 'required|max:205',
            'expertise' => 'required',
            'language' => 'required',
            'experience' => 'required',
            'city_id' => 'required',
        ]);

            $astrologer = User::findOrFail($id);
            $languageString = implode(',', $request->language);

        $astrologer->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'mobile_code' => 91,
            'mobile_number' => $request->mobile_number,
            'gender' => $request->gender,
            'profile_picture' => $request->profile_picture,
            'cut_out_image' => $request->cut_out_image,
            'professional_title' => implode(',', $request->professional_title),
            'keywords' => implode(',', $request->keywords),
            'description' => $request->description,
            'expertise' => implode(',',$request->expertise),
            'language' => $languageString,
            'experience' => $request->experience,
            'city_id' => $request->city_id,
            'status' => isset($request->status)?1:0,
            'is_approved' => 1,
            'approved_id' => auth()->id(),
        ]);

        return redirect()->route('admin.experts.index',['tab'=>'approved'])->with("success", 'Astrologer updated successfully');
    }

    public function destroy($id)
    {
        $admin = User::findOrFail($id);
        if ($admin) {
            $this->deleteFileFromS3($admin->profile_picture);
        }
        $admin->delete();
        return redirect()->route('admin.experts.index')->with('success', 'Astrologer deleted successfully');
    }

    public function State()
    {
        return $states = State::where('country_id', request('country_id'))->get();
    }

    public function City()
    {
        return $cities = City::where('state_id', request('state_id'))->get();
    }

    public function export()
    {
        return Excel::download(new AstrologerExport, 'experts.xlsx');
    }

    public function signup()
    {
        $cities = $this->getCities();
        return view('astrologer-signup',compact('cities'));
    }

    public function astrologer_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users,email,' . $request->id,
                function ($attribute, $value, $fail) {
                    if (!preg_match('/@[\w\-]+\.[\w\-]+$/', $value)) {
                        $fail('The email must contain a valid domain.');
                    }
                }
            ],
            'mobile_number' => 'required|unique:users,mobile_number,' . $request->id,
            'expertise' => 'required',
            'language' => 'required',
            'experience' => 'required',
            'professional_title' => 'required',
            'keywords' => 'required',
            'description' => 'required|max:205',
            'gender' => 'required',
            'city_id' => 'required',
        ]);

        $name_parts = explode(" ", $request->name, 2);
        $first_name = $name_parts[0];
        $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

        $languageString = implode(',', $request->language);

        $astrologer = User::updateOrCreate(['id' => $request->id], [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $request->email,
            'mobile_code' => (int)$request->mobile_code,
            'mobile_number' => $request->mobile_number,
            'description' => $request->description,
            'expertise' => implode(',', $request->expertise),
            'language' => $languageString,
            'experience' => $request->experience,
            'professional_title' =>implode(',', $request->professional_title),
            'keywords' =>implode(',', $request->keywords),
            'city_id' => $request->city_id,
            'profile_picture' => $request->profile_picture,
            // 'cut_out_image' => $request->cutout_image,
            'gender' => $request->gender,
        ]);
        if (is_null($astrologer->zego_user_id)) {
            $astrologer->zego_user_id = rand(1000, 9999) . time();
            $astrologer->save();
        }
        $astrologer->assignRole('astrologer');
        if ($astrologer) {
            $this->createAstrologerSchedule($astrologer);
        }
        // Customer Create
        // $customerEmail = preg_replace('/@/', '000@', $request->email, 1);
        // $customerMobileNumber = substr($request->mobile_number, 0, -3) . '000';
        // $customer = User::create([
        //     'first_name' => $first_name,
        //     'last_name' => $last_name,
        //     'email' => $customerEmail,
        //     'mobile_code' => (int)$request->mobile_code,
        //     'mobile_number' => $customerMobileNumber,
        //     'profile_picture' => $request->profile_picture,
        //     'dob' => "2000-01-02",
        //     'gender' => $request->gender,
        //     'city_id' => $request->city_id,
        // ]);
    
        // $customer->assignRole('customer');
        return redirect()->route('astrologer.success')->with("success", 'Registered successfully');
    }

    public function success()
    {
        if (!session()->has('success')) {
            return redirect()->route('astrologer.signup');
        }
        return view('astrologer_success');
    }

    public function updateApprovalStatus(Request $request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found!'], 404);
        }
    
        $user->update([
            'is_approved' => $request->is_approved,
            'approved_id' => auth()->id(),
            'reject_reason' => $request->reason,
        ]);
    
        return response()->json(['message' => 'Status updated successfully!']);
    }

    public function downloadImage($id)
    {
        $astrologer = User::findOrFail($id);

        $imageUrl = $astrologer->cut_out_image ?? $astrologer->profile_picture;
        if (!$imageUrl) {
            return back()->with('error', 'No image found.');
        }
        $response = Http::get($imageUrl);

        if ($response->failed()) {
            return back()->with('error', 'Failed to fetch image.');
        }
        $pathInfo = pathinfo(parse_url($imageUrl, PHP_URL_PATH)); 
        $extension = isset($pathInfo['extension']) ? $pathInfo['extension'] : 'jpg';

        $fileName = 'downloads/' . $astrologer->first_name . '_' . $astrologer->last_name. '_' . uniqid() . '.' . $extension;
        Storage::disk('public')->put($fileName, $response->body());

        $filePath = storage_path("app/public/$fileName");
        return Response::download($filePath, basename($filePath))->deleteFileAfterSend(true);
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
    public function profilepicture()
    {
        return view('expert_profile.expert_profile_picture_form');
    }
    public function expertProfileStore(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:expert_profiles',
            'mobile_number' => 'required',
        ]);

        $astrologer = ExpertProfile::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'profile_picture' => $request->profile_picture,
        ]);
        return redirect()->route('astrologer.success')->with("success", 'Saved successfully');
    }

    public function expertProfileIndex()
    {
        $expertProfiles = ExpertProfile::orderByDesc('id')->paginate(20);
        return view('expert_profile.index', compact('expertProfiles'));
    }

    public function downloadExpertProfileImage($id)
    {
        $astrologer = ExpertProfile::findOrFail($id);

        $imageUrl = $astrologer->profile_picture;
        if (!$imageUrl) {
            return back()->with('error', 'No image found.');
        }
        $response = Http::get($imageUrl);

        if ($response->failed()) {
            return back()->with('error', 'Failed to fetch image.');
        }
        $pathInfo = pathinfo(parse_url($imageUrl, PHP_URL_PATH)); 
        $extension = isset($pathInfo['extension']) ? $pathInfo['extension'] : 'jpg';

        $fileName = 'downloads/' . $astrologer->first_name . '_' . $astrologer->last_name. '_' . uniqid() . '.' . $extension;
        Storage::disk('public')->put($fileName, $response->body());

        $filePath = storage_path("app/public/$fileName");
        return Response::download($filePath, basename($filePath))->deleteFileAfterSend(true);
    }
    public function deleteExpertProfile($id)
    {
        $expertProfile = ExpertProfile::find($id);

        if (!$expertProfile) {
            return response()->json(['success' => false, 'message' => 'Expert profile not found.'], 404);
        }

        if ($expertProfile) {
            $this->deleteFileFromS3($expertProfile->profile_picture);
        }

        // Delete the expert profile from database
        $expertProfile->delete();

        return response()->json(['success' => true, 'message' => 'Expert profile deleted successfully.']);
    }
    private function createAstrologerSchedule($astrologer)
    {
        $scheduleData = [
            ["day" => "Monday", "time_periods" => [["start_time" => "07:00", "end_time" => "23:00"]]],
            ["day" => "Tuesday", "time_periods" => [["start_time" => "07:00", "end_time" => "23:00"]]],
            ["day" => "Wednesday", "time_periods" => [["start_time" => "07:00", "end_time" => "23:00"]]],
            ["day" => "Thursday", "time_periods" => [["start_time" => "07:00", "end_time" => "23:00"]]],
            ["day" => "Friday", "time_periods" => [["start_time" => "07:00", "end_time" => "23:00"]]],
            ["day" => "Saturday", "time_periods" => [["start_time" => "07:00", "end_time" => "23:00"]]],
            ["day" => "Sunday", "time_periods" => [["start_time" => "07:00", "end_time" => "23:00"]]],
        ];

        AstrologerSchedule::create([
            'astrologer_id' => $astrologer->id,
            'future_days' => 5,
            'duration_minutes' => 0,
            'schedule' => json_encode($scheduleData),
            'not_available_days' => null,
            'is_availability' => 1,
            'video_call_price_30min' => 1999,
            'video_call_price_60min' => 2999,
            'audio_call_price_30min' => 999,
            'audio_call_price_60min' => 1999,
        ]);

        AstrologerBookNowPrice::create([
            'astrologer_id' => $astrologer->id,
            'chat_price' => 15,
            'voice_price' => 25,
            'video_price' => 30,
        ]);
    }
    public function updateStatus(Request $request)
    {
        $astrologer = User::findOrFail($request->astrologer_id);
        $astrologer->status = $request->status;
        $astrologer->save();

        return response()->json([
            'success' => true,
            'message' => 'Astrologer status updated successfully!'
        ]);
    }

    public function updateIsTopExpert(Request $request)
    {
        $astrologer = User::findOrFail($request->astrologer_id);
        $astrologer->is_top_expert = $request->is_top_expert;
        $astrologer->save();

        return response()->json([
            'success' => true,
            'message' => 'Astrologer updated successfully!'
        ]);
    }

    public function expertRatings($id)
    {
        $astrologerReviews = AstrologerRating::where('astrologer_id', $id)->get();
        $appointmentIds = Appointment::where('astrologer_id', $id)->pluck('id');

        $appointmentReviews = AppointmentRating::whereIn('appointment_id', $appointmentIds)
            ->with('appointment')
            ->orderByDesc('created_at')
            ->get()
            ->unique('user_id');

        $reviews = $astrologerReviews->merge($appointmentReviews);

        $reviews = $reviews->sortByDesc('created_at')->values();
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $reviews->forPage($currentPage, $perPage),
            $reviews->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('astrologers.ratings', ['reviews' => $paginated]);
    }

    public function shareProfile(Request $request)
    {
        $ref = $request->query('ref');
        $astrologer = ExpertReferral::where('referral_code', $ref)->first();
        if ($ref) {
            $astrologer = ExpertReferral::where('referral_code', $ref)->first();
            if ($astrologer) {
                $astrologer->increment('share_count');
            }
        }
        $userAgent = $request->header('User-Agent');
        $deepLink = $request->query('number');
        $deviceMappings = [
            'Android' => 'https://play.google.com/store/apps/details?id=in.subastro.app&referrer='. $astrologer->referral_code .'',
            'iPhone'  => 'https://google.com',
        ];
        $defaultRedirectUrl = url('/pmp');
        $deviceType = 'Other';

        foreach ($deviceMappings as $type => $url) {
            if (stripos($userAgent, $type) !== false) {
                $deviceType = $type;
                break;
            }
        }

        $redirectUrl = $deviceMappings[$deviceType] ?? $defaultRedirectUrl;
        if ($deviceType === 'Other') {
            return response()->view('deep-link-message', [
                'androidUrl' => $deviceMappings['Android'], 
                'iphoneUrl' => $deviceMappings['iPhone'],
                'deepLink' => $deepLink,
            ]);
        }

        return redirect()->to($redirectUrl);
    }
}
