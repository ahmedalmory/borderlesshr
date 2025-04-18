<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessResumeUpload;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class JobApplicationController extends Controller
{
    public function apply(Request $request, $jobId)
    {
        $validator = Validator::make($request->all(), [
            'cover_letter' => 'required|string',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $job = Job::where('id', $jobId)
            ->whereNotNull('published_at')
            ->firstOrFail();

        $existingApplication = JobApplication::where('job_id', $jobId)
            ->where('candidate_id', $request->user()->id)
            ->first();

        if ($existingApplication) {
            return response()->json([
                'message' => 'You have already applied for this job'
            ], 422);
        }

        $resumePlaceholder = 'pending_' . Str::uuid();

        $application = JobApplication::create([
            'job_id' => $jobId,
            'candidate_id' => $request->user()->id,
            'cover_letter' => $request->cover_letter,
            'resume' => $resumePlaceholder,
            'status' => 'pending',
        ]);

        $file = $request->file('resume');
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = 'resumes/' . $fileName;

        ProcessResumeUpload::dispatch($file, $application->id, $filePath);

        return response()->json([
            'message' => 'Application submitted successfully',
            'application' => $application
        ], 201);
    }

    public function candidateApplications(Request $request)
    {
        $applications = JobApplication::with('job.company')
            ->where('candidate_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'applications' => $applications
        ]);
    }

    public function jobApplications(Request $request, $jobId)
    {
        $job = Job::where('id', $jobId)
            ->where('company_id', $request->user()->id)
            ->firstOrFail();

        $applications = JobApplication::with('candidate')
            ->where('job_id', $jobId)
            ->latest()
            ->paginate(10);

        return response()->json([
            'job' => $job,
            'applications' => $applications
        ]);
    }
}
