<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'device' => ['required', 'string', 'min:3'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $user = User::where('username', $request    ->username)->first();

        if (!$user || !Hash::check($request->password, $user->password))
            return response()->json([
                'error' => 'User not found or incorrect password'
            ], 401);
        
        $user->tokens()->where('name', $request->device)->delete();

        $token = $user->createToken($request->device)->plainTextToken;

        return response()->json([
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->json('User logged out', 200);
    }

    public function token(Request $request) {
        $response = [
            'id' => $request->user()->id,
            'permissions' => $request->user()->permissions()->pluck('name'),
            'username' => $request->user()->username,
        ];

        return $response;
    }
}
