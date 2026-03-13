<?php

namespace App\Traits;

use FFMpeg;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

trait AwsS3Trait
{
    /**
     * Upload a file to AWS S3.
     *
     * @param string $filePath Local file path
     * @param string $s3Path Destination path in S3
     * @param string|null $disk Custom disk (optional)
     * @return string|bool URL of the uploaded file or false on failure
     */
    public function uploadFileToS3($file, $folder = '', $imageType = null, $disk = 's3')
    {
        $mimeType = $file->getClientMimeType();
        $fileExtension = $file->getClientOriginalExtension();
        $uploaded = '';

        if (str_starts_with($mimeType, 'image/')) {
            $filePath = $folder . '/' . Str::uuid() . '.webp';

            $image = Image::read($file);

            if ($imageType === 'Customer') {
                $image->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            $webpImage = $image->encodeByExtension('webp', quality: 80);

            $uploaded = Storage::disk($disk)->put($filePath, (string) $webpImage);
        } else {
            $filePath = $folder . '/' . Str::uuid() . '.' . $fileExtension;
            $uploaded = Storage::disk($disk)->put($filePath, file_get_contents($file));
        }

        return $uploaded ? $filePath : false;
    }

    /**
     * Delete a file from AWS S3.
     *
     * @param string $s3Path File path in S3
     * @param string|null $disk Custom disk (optional)
     * @return bool True if deleted successfully, false otherwise
     */
    public function deleteFileFromS3($s3Path, $disk = 's3')
    {
        $parsedUrl = parse_url($s3Path);
        $filePath = $parsedUrl['path'];
        $filePath = ltrim($filePath, '/');
        try {
            return Storage::disk($disk)->delete($filePath);
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('S3 Deletion Error: ' . $e->getMessage());
            return false;
        }
    }



    /**
     * Download a file from AWS S3.
     *
     * @param string $s3Path File path in S3
     * @param string $localPath Destination local path
     * @param string|null $disk Custom disk (optional)
     * @return bool True if downloaded successfully, false otherwise
     */
    public function downloadFileFromS3($s3Path, $localPath, $disk = 's3')
    {
        try {
            $content = Storage::disk($disk)->get($s3Path);
            return file_put_contents($localPath, $content) !== false;
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('S3 Download Error: ' . $e->getMessage());
            return false;
        }
    }

    public function generateSignedUrl(string $path, int $expiration = 18000): string
    {
        if (empty($path)) {
            return '';
        }
        try {
            return Storage::disk('s3')->temporaryUrl(
                $path,
                now()->addSeconds($expiration)
            );
        } catch (\Throwable $e) {
            \Log::error('S3 Signed URL Error', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return '';
        }
    }
    public function cloneFile($oldPath,$directory){
        $oldname =basename($oldPath);
        $newname = Str::uuid().'.'.pathinfo($oldPath, PATHINFO_EXTENSION);
        $newKey = $directory . $newname;
        Storage::disk('s3')->copy($oldPath, $newKey);
        return  $newKey;
    }

    function generateAndUploadThumbnail($s3VideoPath, $thumbnailName, $timeToCapture = 2)
    {
        if (!File::exists(storage_path('app/temp'))) {
            File::makeDirectory(storage_path('app/temp'), 0755, true);
        }
        
        $tempVideoPath = storage_path('app/temp/'. time() . '_' .'temp_video.mp4');
        $videoContent = Storage::disk('s3')->get($s3VideoPath);
        file_put_contents($tempVideoPath, $videoContent);

        $tempThumbnailPath = storage_path("app/temp/{$thumbnailName}");
        
        $ffmpeg = FFMpeg\FFMpeg::create([
            'ffmpeg.binaries'  => env('FFMPEG_PATH'),
            'ffprobe.binaries' => env('FFPROBE_PATH'),
        ]);
        $video = $ffmpeg->open($tempVideoPath);
        
        $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($timeToCapture));
        $frame->save($tempThumbnailPath);

        $thumbnailPath = 'media/videos/thumbnails/' . $thumbnailName;
        Storage::disk('s3')->put($thumbnailPath, file_get_contents($tempThumbnailPath));

        unlink($tempVideoPath);
        unlink($tempThumbnailPath);

        return $thumbnailPath;
    }
}
