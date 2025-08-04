<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // استخدام Cache للتحقق من حالة المستخدم لتقليل الاستعلامات
            $userActiveStatus = Cache::remember("user_active_{$user->id}", 300, function () use ($user) {
                return $user->is_active;
            });
            
            if (!$userActiveStatus) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('filament.admin.auth.login')->withErrors([
                    'email' => 'Your account has been deactivated. Please contact administrator.',
                ]);
            }
        }

        return $next($request);
    }
}
