<?php

// Middleware que hace de intermediario para acceder a rutas protegidas

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends Middleware
{
    // Envía error 401 cuando el token JWT del usuario no es válido o no existe
    protected function unauthenticated($request, array $guards)
    {
        abort(response()->json(['error' => 'Unauthorized'], 401));
    }
}
