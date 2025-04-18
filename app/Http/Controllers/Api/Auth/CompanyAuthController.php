<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CompanyAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:companies',
            'password' => 'required|string|min:8|confirmed',
            'website' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $company = Company::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'website' => $request->website,
            'description' => $request->description,
        ]);

        $token = $company->createToken('CompanyToken')->accessToken;

        return response()->json([
            'message' => 'Company registered successfully',
            'company' => $company,
            'token' => $token
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

        $credentials = $request->only('email', 'password');

        if (!Auth::guard('company')->attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $company = Auth::guard('company')->user();
        $token = $company->createToken('CompanyToken')->accessToken;

        return response()->json([
            'message' => 'Company logged in successfully',
            'company' => $company,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Company logged out successfully'
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'company' => $request->user()
        ]);
    }
}
