<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;

class CheckActiveUser
{
    public function handle(Request $request, Closure $next)
    {
        // Check if this is a login attempt
        if ($request->is('admin/login') && $request->isMethod('post')) {
            $credentials = $request->only(['email', 'password']);
            
            // Check if user exists and is active
            $user = \App\Models\User::where('email', $credentials['email'])->first();
            
            if ($user && !$user->is_active) {
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact administrator.',
                ]);
            }
        }

        // Check if authenticated user is still active
        if (Auth::check() && !Auth::user()->is_active) {
            Auth::logout();
            return redirect()->route('filament.admin.auth.login')->withErrors([
                'email' => 'Your account has been deactivated. Please contact administrator.',
            ]);
        }

        return $next($request);
    }
}
