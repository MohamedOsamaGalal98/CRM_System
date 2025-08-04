<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Filament\Notifications\Notification;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // التحقق من أن المستخدم مسجل دخول
        if ($user) {
            // التحقق من أن المستخدم غير نشط
            if (!$user->is_active) {
                Notification::make()
                    ->title('Account Deactivated')
                    ->body('Your account has been deactivated. Please contact an administrator.')
                    ->danger()
                    ->persistent()
                    ->send();
                
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect('/login')->with('error', 'Your account has been deactivated. Please contact an administrator.');
            }
            
            // التحقق من أن بريده الإلكتروني غير مؤكد
            if (is_null($user->email_verified_at)) {
                Notification::make()
                    ->title('Email Verification Required')
                    ->body('You must verify your email address before accessing the dashboard. Please contact an administrator.')
                    ->warning()
                    ->persistent()
                    ->send();
                
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect('/login')->with('error', 'Your email address must be verified before you can access the dashboard. Please contact an administrator.');
            }
        }
        
        return $next($request);
    }
}
