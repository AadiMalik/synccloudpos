<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'    => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('username', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid username or password',
            ], 401);
        }

        $user = Auth::user();
        $tokenResult = $user->createToken('Personal Access Token');

        // 🔹 Convert keys to snake_case if needed
        $data = $user->toArray();

        // 🔹 Add extra fields as in original JS code
        $data['store_id']        = $user->location_id ?? 0;
        $data['employee_number'] = $user->id;
        $data['user_type']       = $user->tilType ?? null; // optional field
        $data['name']            = $user->username ?? $user->name;

        return response()->json([
            'Status'     => '0',
            'error_code' => null,
            'message'    => 'Success',
            'Response'   => [
                'access_token' => [
                    'access_token' => $tokenResult->accessToken,
                ],
                'data' => $data,
            ],
        ], 200);
    }

    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'    => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Auth::guard('web')->attempt($request->only('username', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Delete old tokens (optional)
        $user->tokens()->delete();

        // Create new access token (1 week expiry)
        $tokenResult = $user->createToken('Access Token');
        $token = $tokenResult->token;
        $token->expires_at = now()->addWeek();
        $token->save();

        return response()->json([
            'Status' => '0',
            'error_code' => null,
            'message' => 'Success',
            'Response' => [
                'access_token' => $tokenResult->accessToken,
                'refresh_token' => '1'
            ]
        ]);
    }

    public function tokenData(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $tokenResult = $user->createToken('Short Access Token');
        $tokenResult->token->expires_at = now()->addHour();
        $tokenResult->token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'expires_at' => $tokenResult->token->expires_at
        ]);
    }

    public function showData($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            return response()->json(['user' => $user], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}
