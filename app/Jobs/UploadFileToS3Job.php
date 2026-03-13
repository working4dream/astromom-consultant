<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class UploadFileToS3Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $s3Path;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $s3Path)
    {
        $this->filePath = $filePath;
        $this->s3Path = $s3Path;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!file_exists($this->filePath)) {
            \Log::error("File not found: " . $this->filePath);
            return;
        }
        $file = fopen($this->filePath, 'r');
        Storage::disk('s3')->put($this->s3Path, $file);
        fclose($file);

        unlink($this->filePath);

        \Log::info("File uploaded to S3: " . $this->s3Path);
    }
}
