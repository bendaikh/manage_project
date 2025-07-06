<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Support multiple roles separated by comma, e.g. role:admin,manager
        $roleArray = array_map('trim', explode(',', $roles));

        // Use helper on User model to check any role match
        if (!auth()->user()->hasAnyRole($roleArray)) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
