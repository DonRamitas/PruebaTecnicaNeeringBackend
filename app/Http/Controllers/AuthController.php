<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// Implementa JWT
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

// Controlador para la autenticación
class AuthController extends Controller
{
    // Petición de registro
    public function register(Request $request)
    {
        // Validadores de cada campo del registro
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'birthdate' => 'required|date|before_or_equal:' . now()->subYears(5)->toDateString(),
            'phone' => 'required|string',
            'password' => 'required|string|min:6|max:16',
        ]);

        // Si pasa las validación crea al usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'birthdate' => $request->birthdate,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Genera un token para el usuario
        $token = JWTAuth::fromUser($user);

        // Retorna al usuario junto a su token
        return response()->json(compact('user', 'token'), 201);
    }

    // Petición de Login
    public function login(Request $request)
    {
        // Recibe las credenciales ingresadas
        $credentials = $request->only('email', 'password');

        // Valida las credenciales con JWT
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // Retorna el token del usuario logeado
        return response()->json(compact('token'));
    }

    // Petición para cerrar sesión
    public function logout()
    {
        // Invalida del token del usuario
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    // Otorga un token nuevo
    public function refresh()
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());
        return response()->json(compact('token'));
    }

    // Retorna los datos del usuario logeado actualmente
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    
}
