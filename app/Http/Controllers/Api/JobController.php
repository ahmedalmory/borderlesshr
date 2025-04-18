<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'jobs_' . md5(json_encode($request->all()));

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $query = Job::with('company')
            ->whereNotNull('published_at');

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('is_remote') && $request->is_remote !== null) {
            $query->where('is_remote', $request->is_remote === 'true' || $request->is_remote === '1');
        }

        if ($request->has('keyword') && !empty($request->keyword)) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        $jobs = $query->latest()->paginate(10);

        $response = $jobs->toArray();

        Cache::put($cacheKey, $response, now()->addMinutes(5));

        return response()->json($response);
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'salary_range' => 'nullable|string|max:255',
            'is_remote' => 'required|boolean',
            'published_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $job = Job::create([
            'company_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'salary_range' => $request->salary_range,
            'is_remote' => $request->is_remote,
            'published_at' => $request->published_at ?? now(),
        ]);

        return response()->json([
            'message' => 'Job created successfully',
            'job' => $job
        ], 201);
    }

    public function show($id)
    {
        $job = Job::with('company')->findOrFail($id);
        
        return response()->json([
            'job' => $job
        ]);
    }

    public function edit(string $id)
    {
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'salary_range' => 'nullable|string|max:255',
            'is_remote' => 'required|boolean',
            'published_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $job = Job::where('id', $id)
            ->where('company_id', $request->user()->id)
            ->firstOrFail();

        $job->update([
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'salary_range' => $request->salary_range,
            'is_remote' => $request->is_remote,
            'published_at' => $request->published_at,
        ]);

        return response()->json([
            'message' => 'Job updated successfully',
            'job' => $job
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $job = Job::where('id', $id)
            ->where('company_id', $request->user()->id)
            ->firstOrFail();

        $job->delete();

        return response()->json([
            'message' => 'Job deleted successfully'
        ]);
    }

    public function companyJobs(Request $request)
    {
        $jobs = Job::where('company_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'jobs' => $jobs
        ]);
    }
}
