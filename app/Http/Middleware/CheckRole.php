<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $user = User::find($request->user()->id); // Get the authenticated user
        if (!$user->projects()->wherePivot('role', $role)->exists()) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        return $next($request);
    }
}
