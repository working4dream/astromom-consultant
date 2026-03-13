<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

if (!function_exists('generateS3Url')) {
    function generateS3Url(string $path, int $expiration = 18000): string
    {
        $disk = Storage::disk('s3');
        return $disk->temporaryUrl($path, now()->addSeconds($expiration));
    }
}
if (!function_exists('fileExistsInS3')) {
    function fileExistsInS3($s3Path, $disk = 's3')
    {
        if (empty($s3Path)) {
            return false;
        }
        try {
            if (filter_var($s3Path, FILTER_VALIDATE_URL)) {
                $parsedUrl = parse_url($s3Path);
                $filePath = ltrim($parsedUrl['path'] ?? '', '/');
                $filePath = urldecode($filePath);
            } else {
                $filePath = ltrim($s3Path, '/');
            }
            Storage::disk($disk)->temporaryUrl(
                $filePath,
                now()->addSeconds(5)
            );
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}


if (!function_exists('getMediaGlobaly')) {
    function getMediaGlobaly($type=null, $perPage = 12)
    {
        $query = \App\Models\Media::orderByDesc('id');

        if ($type) {
            switch ($type) {
                case 'videoFile':
                case 'video_view':
                    $query->where('type', 'videoFile');
                    break;
                case 'imageFile':
                case 'image_view':
                    $query->where('type', 'imageFile');
                    break;
                case 'Category':
                    $query->where('type', 'Category');
                    break;
                case 'Astrologer':
                    $query->where('type', 'Astrologer');
                    break;
                case 'AstrologerCutOut':
                    $query->where('type', 'AstrologerCutOut');
                    break;
                case 'Customer':
                    $query->where('type', 'Customer');
                    break;
                case 'Banner':
                    $query->where('type', 'Banner');
                    break;
                default:
                    $query->where('type', '!=', 'videoFile');
                    break;
            }
        }

        return $query->paginate($perPage);
    }
}
if (!function_exists('getGST')) {
    function getGST()
    {
        $gst = Setting::where('name', 'gst')->first();
        return $gst->data / 100;
    }
}
if (!function_exists('getPrimaryColor')) {
    function getPrimaryColor()
    {
        $color = Setting::where('name', 'primary_color')->first()->data ?? '#211324';
        return $color;
    }
}
