<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ShowImpersonationBanner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // التحقق من وجود جلسة انتحال هوية في صفحات Filament فقط
        if (session('impersonator_id') && $request->is('admin*')) {
            $originalUser = User::find(session('impersonator_id'));
            $currentUser = Auth::user();
            
            if ($originalUser && $currentUser) {
                // إضافة JavaScript لعرض تنبيه في أعلى الصفحة
                $banner = '
                <div id="impersonation-banner" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    background-color: #f59e0b;
                    color: white;
                    padding: 10px;
                    text-align: center;
                    z-index: 9999;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    font-weight: 600;
                ">
                    🎭 أنت تستخدم انتحال الهوية - حساب: ' . htmlspecialchars($currentUser->name) . ' | المدير الأصلي: ' . htmlspecialchars($originalUser->name) . '
                    <a href="/admin/stop-impersonation" style="
                        background-color: #dc2626;
                        color: white;
                        padding: 5px 10px;
                        margin-left: 10px;
                        text-decoration: none;
                        border-radius: 4px;
                        font-size: 12px;
                    ">إيقاف انتحال الهوية</a>
                </div>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const banner = document.getElementById("impersonation-banner");
                        if (banner) {
                            document.body.style.paddingTop = "60px";
                        }
                    });
                </script>';
                
                // إدراج البانر في بداية body
                $content = $response->getContent();
                if (strpos($content, '<body') !== false) {
                    $content = preg_replace('/(<body[^>]*>)/', '$1' . $banner, $content);
                    $response->setContent($content);
                }
            }
        }

        return $response;
    }
}
