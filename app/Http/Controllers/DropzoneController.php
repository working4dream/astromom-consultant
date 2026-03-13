<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Media;
use App\Models\Banner;
use App\Traits\AwsS3Trait;
use Illuminate\Http\Request;
use App\Jobs\UploadFileToS3Job;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class DropzoneController extends Controller
{
    use AwsS3Trait;
    public function uploadFileAWS(Request $request)
    {
        $allowedFiles = [
            'Astrologer' => 'astrologers/profile-picture',
            'AstrologerCutOut' => 'astrologers/cutout-image',
            'Customer' => 'customers/profile-picture',
            'Banner' => 'banners',
        ];
        
        foreach ($allowedFiles as $inputName => $uploadPath) {
            if ($request->hasFile($inputName)) {
                $file = $request->file($inputName);
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $this->uploadFileToS3($file, $uploadPath, $inputName);

                if (auth()->check()) {
                    $mediaProfile = new Media();
                    $mediaProfile->user_id = auth()->id();
                    $mediaProfile->name = $fileName;
                    $mediaProfile->type = $inputName;
                    $mediaProfile->path = $filePath;
                    $mediaProfile->save();
                }

                $fullUrl = Storage::disk('s3')->temporaryUrl($filePath, now()->addMinutes(60));

                return response()->json([
                    'message' => 'File uploaded successfully!',
                    'file_path' => $filePath,
                    'full_url' => $fullUrl
                ]);
            }
        }
        

        return response()->json(['message' => 'No file uploaded'], 400);
    }

    public function deleteUploadedFile(Request $request)
    {
        $filePath = $request->input('filepath');
        if (!$filePath) {
            return response()->json(['error' => 'File path not provided'], 400);
        }

//        $this->deleteFileFromS3($filePath);
        if($request->id){
            if ($request->model === 'Astrologer' || $request->model === 'AstrologerCutOut') {
                User::where('id', $request->id)->update([
                    $request->filename => NULL,
                ]);
            } elseif ($request->model === 'Customer') {
                User::where('id', $request->id)->update([
                    'profile_picture' => NULL,
                ]);
            } elseif ($request->model === 'Banner') {
                $banner = Banner::where('id', $request->id)->first();
                if ($banner->type === 1) {
                    $banner->update([
                        'customer_banner' => NULL,
                    ]);
                } elseif ($banner->type === 2) {
                    $banner->update([
                        'expert_banner' => NULL,
                    ]);
                }
            }
        }
        return response()->json(['success' => 'File deleted successfully']);
    }

    public function deleteExistingFile(Request $request){
        $filePath = $request->input('filepath');
        $videoPath = $request->input('videoPath');
        if ($videoPath) {
            $this->deleteFileFromS3($videoPath);
        }
        $this->deleteFileFromS3($filePath);
        if ($request->input('id')) {
            $media = Media::find($request->input('id'));
            if ($media) {
                $media->delete();
            }
        }
        return response()->json(['success' => 'File deleted successfully']);
    }
}
