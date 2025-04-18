<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CandidateAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:candidates',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $candidate = Candidate::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'bio' => $request->bio,
        ]);

        // Correct way to get the token
        $tokenResult = $candidate->createToken('CandidateAuthToken');
        $token = $tokenResult->accessToken;

        return response()->json([
            'message' => 'Candidate registered successfully',
            'candidate' => $candidate,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $tokenResult->token->expires_at
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $candidate = Candidate::where('email', $request->email)->first();

        if (!$candidate || !Hash::check($request->password, $candidate->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Correct way to get the token
        $tokenResult = $candidate->createToken('CandidateAuthToken');
        $token = $tokenResult->accessToken;

        return response()->json([
            'message' => 'Candidate logged in successfully',
            'candidate' => $candidate,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $tokenResult->token->expires_at
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'candidate' => $request->user()
        ]);
    }
}