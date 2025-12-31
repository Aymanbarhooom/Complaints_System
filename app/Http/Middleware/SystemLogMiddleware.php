<?php


namespace App\Http\Middleware;

use App\Models\SystemLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SystemLogMiddleware
{
   
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $startTime;
        
        try {
            SystemLog::create([
                'user_id' => auth()->id(),
                'action' => $request->method() . ' ' . $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_data' => $this->sanitizeRequestData($request),
                'response_code' => $response->status(),
                'duration' => round($duration * 1000, 2), // بالمللي ثانية
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log system activity', [
                'error' => $e->getMessage()
            ]);
        }
        
        return $response;
    }
    
    /**
     * تنظيف البيانات الحساسة قبل التسجيل
     */
    private function sanitizeRequestData(Request $request)
    {
        $data = $request->except(['password', 'password_confirmation', 'token']);
        
        // إزالة الملفات الكبيرة
        if ($request->hasFile('attachments')) {
            $data['attachments'] = 'files_count:' . count($request->file('attachments'));
        }
        
        return json_encode($data);
    }
}