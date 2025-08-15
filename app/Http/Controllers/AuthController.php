<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
    
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255'
                     ],
            'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'unique:users,email',
                       ],
            'password' => [
                    'required',
                    'string',
                    'min:6',
                    'regex:/[A-Z]/',      
                    'regex:/[a-z]/',       
                    'regex:/[0-9]/',      
                    'regex:/[@$!%*?&]/',   
                    'confirmed',          
                        ],      
        ]);

        $user = User::create([
            'name'     => $validatedData['name'],
            'email'    => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // Tạo token API
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng ký thành công',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required']
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Email hoặc mật khẩu không đúng'], 401);
        }

        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Đăng xuất thành công']);
    }
}
