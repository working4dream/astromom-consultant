<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Traits\AwsS3Trait;

class SettingsController extends Controller
{
    use AwsS3Trait;
    public function index()
    {
        return view('settings.layout');
    }
    public function updatePrice(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            if(in_array($key,['service_types','languages','video_30_min_price','video_30_max_price','video_60_min_price','video_60_max_price',
            'voice_30_min_price','voice_30_max_price','voice_60_min_price','voice_60_max_price',
            'chat_min_price','chat_max_price','voice_min_price','voice_max_price','video_min_price','video_max_price',
            'specialization','expertise','keywords','name_correction','name_correction_exclusive',
            'relationship_comparability', 'appointment_gst_type', 'free_chat_status', 'free_chat_limit'])) {
           Setting::updateOrCreate(['name' => $key], ['name' => $key,'data' => $value]);
        }
      }
        return redirect()->back()->with('success', 'Data updated successfully');
    }
    public function storeBanner(Request $request)
    {
        $request->validate([
            'url' => 'required_if:link_type,url|nullable|url',
            'date_range' => 'required',
        ]);

        $start_date = null;
        $end_date = null;
        
        if ($request->filled('date_range')) { 
            $dates = explode(' to ', $request->date_range);
            if (count($dates) === 2) {
                try {
                    $tz = app_display_timezone();
                    $start_date = \Carbon\Carbon::createFromFormat('d M, Y', trim($dates[0]), $tz)->format('Y-m-d');
                    $end_date = \Carbon\Carbon::createFromFormat('d M, Y', trim($dates[1]), $tz)->format('Y-m-d');
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors(['date_range' => 'Invalid date format. Please select a valid date range.']);
                }
            } else {
                return redirect()->back()->withErrors(['date_range' => 'Date range must contain both start and end dates.']);
            }
        }

        $link = $request->link_type === 'url' ? $request->url : $request->link;

        $banner = Banner::create([
            'customer_banner' => $request->customer_banner,
            'expert_banner' => $request->expert_banner,
            'link_type' => $request->link_type,
            'link' => $link,
            'type' => $request->type,
            'is_active' => $request->is_active === 'on' ? true : false,
            'start_date' => $start_date ?? null,
            'end_date' => $end_date ?? null,
            'date_range' => $request->date_range ?? null,
        ]);
        return redirect()->back()->with('success', 'Banner saved successfully');
    }
    public function updateBanner(Request $request)
    {
        $link = $request->link_type === 'url' ? $request->url : $request->link;
        if ($request->filled('date_range')) { 
            $dates = explode(' to ', $request->date_range);
            if (count($dates) === 2) {
                try {
                    $tz = app_display_timezone();
                    $start_date = \Carbon\Carbon::createFromFormat('d M, Y', trim($dates[0]), $tz)->format('Y-m-d');
                    $end_date = \Carbon\Carbon::createFromFormat('d M, Y', trim($dates[1]), $tz)->format('Y-m-d');
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors(['date_range' => 'Invalid date format. Please select a valid date range.']);
                }
            } else {
                return redirect()->back()->withErrors(['date_range' => 'Date range must contain both start and end dates.']);
            }
        }
        $banner = Banner::findOrFail($request->banner_id);
        $banner->link_type = $request->link_type;
        $banner->link = $link;
        $banner->date_range = $request->date_range;
        $banner->is_active = $request->has('is_active');
        $banner->start_date = $start_date ?? null;
        $banner->end_date = $end_date ?? null;
        $banner->save();

        return redirect()->back()->with('success', 'Banner updated successfully');
    }
    public function deleteBanner($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();

        return response()->json(['success' => 'Banner deleted successfully!']);
    }
    public function updateAppSettings(Request $request)
    {
        $request->validate([
            'is_ios_review' => 'required|in:true,false',
            'features'      => 'required|array',
        ]);
        $features = [];
        foreach ($request->features as $index => $featureName) {
            $features[] = [
                'name'    => $featureName,
                'enabled' => isset($request->enabled[$index]) ? true : false,
            ];
        }
        Setting::updateOrCreate(['name' => 'is_ios_review'], ['name' => 'is_ios_review', 'data' => $request->is_ios_review == 'true' ? 'true' : 'false']);
        Setting::updateOrCreate(
            ['name' => 'features'],
            ['name' => 'features', 'data' => json_encode($features)]
        );
        return redirect()->back()->with('success', 'App settings updated successfully');
    }

    public function updateBranding(Request $request)
    {
        $request->validate([
            'primary_color' => 'required|string',
            'secondary_color' => 'required|string',
        ]);
        if ($request->hasFile('brand_logo')) {

            $file = $request->file('brand_logo');
            $uploadedUrl = $this->uploadFileToS3($file, 'branding/logo');

            if ($uploadedUrl) {
                Setting::updateOrCreate(
                    ['name' => 'brand_logo'],
                    ['data' => $uploadedUrl]
                );
            }
        }

        Setting::updateOrCreate(['name' => 'primary_color'], ['data' => $request->primary_color]);
        Setting::updateOrCreate(['name' => 'secondary_color'], ['data' => $request->secondary_color]);

        return redirect()->back()->with('success', 'Branding updated successfully');
    }

    public function deleteBrandLogo()
    {
        $brandLogo = Setting::where('name', 'brand_logo')->first();
        if($brandLogo) {
            $this->deleteFileFromS3($brandLogo->data);
            $brandLogo->data = null;
            $brandLogo->save();
        }
        return response()->json(['success' => 'Brand logo deleted successfully']);
    } 
}
