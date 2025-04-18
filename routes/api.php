<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\CompanyAuthController;
use App\Http\Controllers\Api\Auth\CandidateAuthController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\JobApplicationController;

Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/{id}', [JobController::class, 'show']);

Route::prefix('company')->group(function () {
    Route::post('/register', [CompanyAuthController::class, 'register']);
    Route::post('/login', [CompanyAuthController::class, 'login']);
    
    Route::middleware('auth:company')->group(function () {
        Route::post('/logout', [CompanyAuthController::class, 'logout']);
        Route::get('/profile', [CompanyAuthController::class, 'profile']);
        
        Route::get('/jobs', [JobController::class, 'companyJobs']);
        Route::post('/jobs', [JobController::class, 'store']);
        Route::put('/jobs/{id}', [JobController::class, 'update']);
        Route::delete('/jobs/{id}', [JobController::class, 'destroy']);
        
        Route::get('/jobs/{jobId}/applications', [JobApplicationController::class, 'jobApplications']);
        
        Route::get('/dashboard', function () {
            $company = auth()->user();
            $jobCount = $company->jobs()->count();
            $applicationCount = $company->applications()->count();
            
            return response()->json([
                'job_count' => $jobCount,
                'application_count' => $applicationCount
            ]);
        });
    });
});

Route::prefix('candidate')->group(function () {
    Route::post('/register', [CandidateAuthController::class, 'register']);
    Route::post('/login', [CandidateAuthController::class, 'login']);
    
    Route::middleware('auth:candidate')->group(function () {
        Route::post('/logout', [CandidateAuthController::class, 'logout']);
        Route::get('/profile', [CandidateAuthController::class, 'profile']);
        
        Route::post('/jobs/{jobId}/apply', [JobApplicationController::class, 'apply']);
        Route::get('/applications', [JobApplicationController::class, 'candidateApplications']);
        
        Route::get('/dashboard', function () {
            $candidate = auth()->user();
            $applicationCount = $candidate->applications()->count();
            
            return response()->json([
                'application_count' => $applicationCount
            ]);
        });
    });
}); 