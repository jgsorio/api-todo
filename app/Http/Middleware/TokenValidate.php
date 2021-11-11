<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class TokenValidate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('token');
        if (!$token) {
            return response()->json(['errors' => 'Token não está presente', 'data' => ''], 403);
        }

        $user = User::where('token', $token)->first();
        if (!$user) {
            return response()->json(['errors' => 'Token inválido', 'data' => ''], 403);
        }
        return $next($request);
    }
}
