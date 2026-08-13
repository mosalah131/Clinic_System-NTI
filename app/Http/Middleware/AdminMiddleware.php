<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * The security guard standing in front of every /admin page.
 *
 * It runs BEFORE the controller and asks two questions:
 *   1. Is anybody logged in?          -> if not, back to the login page
 *   2. Is that person an admin?       -> if not, 403 Forbidden
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (! Auth::user()->isAdmin()) {
            abort(403, 'Only an administrator can open this page.');
        }

        return $next($request);
    }
}
