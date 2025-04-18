<?php

namespace App\Jobs;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessResumeUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file;

    protected $applicationId;

    protected $filePath;

    public function __construct($file, $applicationId, $filePath)
    {
        $this->file = $file;
        $this->applicationId = $applicationId;
        $this->filePath = $filePath;
    }

    public function handle(): void
    {
        try {
            $application = JobApplication::findOrFail($this->applicationId);
            
            Storage::disk('public')->put($this->filePath, file_get_contents($this->file));
            
            $application->update([
                'resume' => $this->filePath,
            ]);
            
            Log::info('Resume uploaded successfully for application #' . $this->applicationId);
        } catch (\Exception $e) {
            Log::error('Error processing resume upload: ' . $e->getMessage());
            $this->fail($e);
        }
    }
}
