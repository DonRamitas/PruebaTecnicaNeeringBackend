<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'birthdate' => 'required|date|before_or_equal:' . now()->subYears(5)->toDateString(), // opcionalmente puedes validar edad mínima
            'phone' => 'required|string',
            'password' => 'required|string|min:6|max:16',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'birthdate' => $request->birthdate,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
    }


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        return response()->json(compact('token'));
    }

    public function me()
    {
        return response()->json(auth()->user());
    }
}
