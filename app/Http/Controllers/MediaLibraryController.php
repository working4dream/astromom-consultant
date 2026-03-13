<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\AwsS3Trait;

class MediaLibraryController extends Controller
{
    use AwsS3Trait;
    public function index()
    {
        return view('media_library.index');
    }
    public function getMedia(Request $request)
    {
        $perPage = 12;
        $routeType = match ($request->route_type) {
            'video' => 'videoFile',
            'image' => 'imageFile',
            default => $request->route_type,
        };
        $media = getMediaGlobaly($routeType, $perPage);

        $mediaFiles = [];
        foreach ($media as $file) {

            $fileUrl = generateS3Url($file->path);
            if (fileExistsInS3($fileUrl)) {
                $mediaFiles[] = [
                    'id' => $file->id,
                    'url' => $fileUrl,
                    'path' => $file->path,
                    'video_path' => $file->video_path,
                    'type' => $file->type,
                    'name' => $file->name,
                ];
            }
        }

        return response()->json([
            'data' => $mediaFiles,
            'has_more' => $media->hasMorePages(),
            'next_page' => $media->nextPageUrl(),
        ]);
    }
    public function getMediaLibrary(Request $request)
    {
        $perPage = $request->input('per_page', 12);
        $page = $request->input('page', 1);
        $type = $request->input('type', null);

        $media = getMediaGlobaly($type, $perPage);

        $mediaFiles = [];
        foreach ($media as $file) {
            $fileUrl = generateS3Url($file->path);
            if (fileExistsInS3($fileUrl)) {
                $mediaFiles[] = [
                    'id' => $file->id,
                    'url' => $fileUrl,
                    'path' => $file->path,
                    'video_path' => $file->video_path,
                    'type' => $file->type,
                    'name' => $file->name,
                ];
            }
        }

        return response()->json([
            'data' => $mediaFiles,
            'has_more' => $media->hasMorePages(),
            'next_page' => $media->currentPage() + 1,
        ]);
    }

    public function deleteMultipleFiles(Request $request)
    {
        $fileIds = $request->input('fileIds');

        if (empty($fileIds)) {
            return response()->json(['success' => false, 'message' => 'No files selected.']);
        }

        foreach ($fileIds as $fileId) {
            $file = Media::find($fileId);
            Storage::disk('s3')->delete($file->path);
            if ($file->video_path) {
                $this->deleteFileFromS3($file->video_path);
            }
            if ($file) {
                $file->delete();
            }
        }
        return response()->json(['success' => true]);
    }

}
