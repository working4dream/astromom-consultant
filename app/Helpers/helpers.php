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

if (! function_exists('app_display_timezone')) {
    function app_display_timezone(): string
    {
        return config('app.current_display_timezone')
            ?? config('app.display_timezone')
            ?? env('APP_DISPLAY_TIMEZONE', 'Asia/Kolkata');
    }
}

/** Instant timestamps (created_at, etc.): show in viewer timezone. */
if (! function_exists('user_tz_format')) {
    function user_tz_format($value, string $format = 'd-M-Y H:i'): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return \Carbon\Carbon::parse($value)->timezone(app_display_timezone())->format($format);
    }
}

/** Calendar date fields (appointment date, DOB): no timezone shift. */
if (! function_exists('fmt_date')) {
    function fmt_date($value, string $format = 'd-M-Y'): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return \Carbon\Carbon::parse($value)->format($format);
    }
}

/** Group by calendar appointment date vs "today" in user timezone (date column, not created_at). */
if (! function_exists('appointment_day_label')) {
    function appointment_day_label($appointmentDate, string $fallbackFormat = 'd-M-Y'): string
    {
        if ($appointmentDate === null || $appointmentDate === '') {
            return '';
        }
        $ad = \Carbon\Carbon::parse($appointmentDate)->format('Y-m-d');
        $today = \Carbon\Carbon::now(app_display_timezone())->format('Y-m-d');
        $yesterday = \Carbon\Carbon::now(app_display_timezone())->subDay()->format('Y-m-d');
        if ($ad === $today) {
            return 'Today';
        }
        if ($ad === $yesterday) {
            return 'Yesterday';
        }

        return \Carbon\Carbon::parse($appointmentDate)->format($fallbackFormat);
    }
}

/** Group orders/notifications by "Today" / "Yesterday" / date in user timezone. */
if (! function_exists('group_day_label')) {
    function group_day_label(\Carbon\Carbon $utcDate): string
    {
        $d = $utcDate->copy()->timezone(app_display_timezone());
        if ($d->isToday()) {
            return 'Today';
        }
        if ($d->isYesterday()) {
            return 'Yesterday';
        }

        return $d->format('d-M-Y');
    }
}

/** Parse daterange from filters (e.g. "01 Jan, 2025 - 31 Jan, 2025") to UTC bounds for DB queries. */
if (! function_exists('parse_daterange_string_to_utc')) {
    function parse_daterange_string_to_utc(?string $dateRange, string $separator = ' - '): ?array
    {
        if ($dateRange === null || trim($dateRange) === '') {
            return null;
        }
        $dates = explode($separator, $dateRange);
        $tz = app_display_timezone();
        try {
            if (count($dates) === 2) {
                $start = \Carbon\Carbon::createFromFormat('d M, Y', trim($dates[0]), $tz)->startOfDay()->utc();
                $end = \Carbon\Carbon::createFromFormat('d M, Y', trim($dates[1]), $tz)->endOfDay()->utc();

                return [$start, $end];
            }
            if (count($dates) === 1 && trim($dates[0]) !== '') {
                $single = \Carbon\Carbon::createFromFormat('d M, Y', trim($dates[0]), $tz);

                return [$single->copy()->startOfDay()->utc(), $single->copy()->endOfDay()->utc()];
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }
}

if (! function_exists('default_month_range_utc')) {
    function default_month_range_utc(): array
    {
        $tz = app_display_timezone();
        $start = \Carbon\Carbon::now($tz)->startOfMonth()->startOfDay()->utc();
        $end = \Carbon\Carbon::now($tz)->endOfDay()->utc();

        return [$start, $end];
    }
}
