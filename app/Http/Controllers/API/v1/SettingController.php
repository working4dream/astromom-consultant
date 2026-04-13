<?php

namespace App\Http\Controllers\API\v1;

use App\Models\User;
use App\Models\Banner;
use App\Models\Setting;
use App\Models\BannerClick;
use Illuminate\Http\Request;
use App\Models\AstrologerSchedule;
use App\Http\Controllers\Controller;
use App\Models\AstrologerBookNowPrice;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\API\BaseController;

class SettingController extends BaseController
{
    public function getCustomerConfig()
    {
        $user = auth('api')->user();
        $data = 
            [
                'is_test_payment' => env("IS_TEST_PAYMENT",false),
            ];
        return $this->sendResponse($data, 'Config retrived successfully.');
    }

    public function getAstrologerConfig()
    {
        $user = auth('api')->user();
        $isSchedule = AstrologerSchedule::where('astrologer_id',$user->id)->exists();
        $isPrices = AstrologerBookNowPrice::where('astrologer_id',$user->id)->exists();
        $isOnline = User::where('id',$user->id)->first()?->is_online;
        $data = 
            [
                'is_schedule_set' => $isSchedule,
                'is_price_set' => $isPrices,
                'is_online' => $isOnline,
                'is_test_payment' => env("IS_TEST_PAYMENT",false),
                'min_withdraw_amount' => env("MIN_WITHDRAW_AMOUNT",5000),
            ];
        return $this->sendResponse($data, 'Config retrived successfully.');
    }

    public function getPrices()
    {
        $data = Setting::whereIn('name', [
            'video_30_min_price',
            'video_30_max_price',
            'video_60_min_price',
            'video_60_max_price',
            'voice_30_min_price',
            'voice_30_max_price',
            'voice_60_min_price',
            'voice_60_max_price',
            'chat_min_price',
            'chat_max_price',
            'voice_min_price',
            'voice_max_price',
            'video_min_price',
            'video_max_price'
        ])->pluck('data', 'name')->map(function ($value) {
            return number_format((float)$value, 2, '.', '');
        });
        return $this->sendResponse($data, 'Prices Retrived successfully');
    }
    public function getBanner()
    {
        $user = auth('api')->user();
        $banners = "";
        $currentDate = date('Y-m-d');

        if ($user->hasRole('customer')) {
            $bannerQuery = Banner::where('type', 1)
                ->where('is_active', 1)
                ->where(function ($query) use ($currentDate) {
                    $query->where(function ($q) use ($currentDate) {
                        $q->where('start_date', '<=', $currentDate)
                          ->where('end_date', '>=', $currentDate);
                    })
                    ->orWhereNull('start_date')
                    ->orWhereNull('end_date');
                });
            $banners = $bannerQuery->orderByDesc('id')->get();
        } elseif ($user->hasRole('astrologer')) {
            $bannerQuery = Banner::where('type', 2)
                ->where('is_active', 1)
                ->where(function ($query) use ($currentDate) {
                    $query->where(function ($q) use ($currentDate) {
                        $q->where('start_date', '<=', $currentDate)
                          ->where('end_date', '>=', $currentDate);
                    })
                    ->orWhereNull('start_date')
                    ->orWhereNull('end_date');
                });
            $banners = $bannerQuery->orderByDesc('id')->get();
        }
        
        if ($banners->isNotEmpty()) {
            $data = $banners->map(function ($banner) use ($user) {
                return [
                    'id' => $banner->id ?? null,
                    'banner' => $user->hasRole('customer') ? $banner->customer_banner : $banner->expert_banner,
                    'link_type' => $banner->link_type ?? null,
                    'link' => $banner->link ?? null,
                ];
            });
        } else {
            $data = [];
        }
        return $this->sendResponse($data, 'Banner Retrived successfully');
    }
    public function bannerClick(Request $request) 
    {
        $request->validate([
            'banner_id' => 'required|exists:banners,id',
        ]);

        $bannerClick = BannerClick::where('user_id', auth('api')->user()->id)
                                  ->where('banner_id', $request->banner_id)
                                  ->first();

        if ($bannerClick) {
            $bannerClick->increment('click_count');
        } else {
            BannerClick::create([
                'user_id' => auth('api')->user()->id,
                'banner_id' => $request->banner_id,
                'click_count' => 1
            ]);
        }

        return response()->json(['message' => 'Click recorded successfully']);
    }

    public function getGst()
    {
        $data = Setting::whereIn('name', [
            'gst',
        ])->pluck('data', 'name')->map(function ($value) {
            return $value;
        });
        return $this->sendResponse($data, 'GST Retrived successfully');
    }

    public function isIOSReview()
    {
        $data = Setting::where('name', 'is_ios_review')->first();
        return $this->sendResponse(['is_ios_review' => $data?->data], 'IOS review retrived successfully');
    }

    public function getBranding()
    {
        $data = Setting::whereIn('name', [
            'brand_logo',
            'primary_color',
            'secondary_color',
        ])
        ->pluck('data', 'name')
        ->map(function ($value, $key) {
            if ($key === 'brand_logo' && !empty($value)) {
                $value = Storage::disk('s3')->temporaryUrl($value, now()->addMinutes(60));
            }
            if ($key === 'primary_color' && (empty($value) || $value === null)) {
                $value = '#211324';
            }
    
            if ($key === 'secondary_color' && (empty($value) || $value === null)) {
                $value = '#3A1C71';
            }
            return [
                'name' => $key,
                'value' => $value,
            ];
        })
        ->values();

        $data->push([
            'name' => 'currency_symbol',
            'value' => config('app.currency_symbol'),
        ]);

        return $this->sendResponse($data->values(), 'Branding retrieved successfully');
    }
    public function getFeatures()
    {
        $setting = Setting::where('name', 'features')->first();
        $features = $setting ? json_decode($setting->data, true) : [];
        return $this->sendResponse($features, 'Features retrieved successfully');
    }

}
