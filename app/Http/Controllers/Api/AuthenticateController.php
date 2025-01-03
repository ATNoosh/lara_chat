<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthenticateController extends Controller
{
    use ApiResponse;
    public function login(LoginRequest $loginRequest): Response
    {
        $loginUserData = $loginRequest->validated();
        $user = User::where('email', $loginUserData['email'])->first();
        if (!$user || !Hash::check($loginUserData['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid Credentials'
            ], 401);
        }
        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;

        return $this->success(__('User loggined successfully!', [
            'access_token' => $token,
        ]));
    }

    public function register()
    {
        
    }
}
