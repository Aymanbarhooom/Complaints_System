<?php

namespace App\Http\Controllers;

use App\Models\system_logs;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemLogController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = SystemLog::with('user:id,firstName,lastName,email')
                          ->orderBy('created_at', 'desc');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->has('status')) {
            if ($request->status === 'errors') {
                $query->errors();

            } elseif ($request->status === 'slow') {
                $query->slow(2000); 
            }
        }

        $logs = $query->paginate(50);

        return response()->json($logs);
    }

    public function statistics()
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_requests_today' => SystemLog::today()->count(),
            
            'errors_today' => SystemLog::today()->errors()->count(),
            
            'avg_response_time' => round(SystemLog::today()->avg('duration'), 2),
            
            'slowest_request' => SystemLog::today()
                ->orderBy('duration', 'desc')
                ->first(['action', 'duration']),
            
            'top_users' => SystemLog::today()
                ->select('user_id', DB::raw('count(*) as requests_count'))
                ->groupBy('user_id')
                ->with('user:id,firstName,lastName')
                ->orderBy('requests_count', 'desc')
                ->limit(5)
                ->get(),
            
            // أكثر الـ endpoints استخداماً
            'top_endpoints' => SystemLog::today()
                ->select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            
            // توزيع حالات الاستجابة
            'response_codes' => SystemLog::today()
                ->select('response_code', DB::raw('count(*) as count'))
                ->groupBy('response_code')
                ->get(),
        ];

        return response()->json($stats);
    }

    public function userActivity()
    {
        $user = auth()->user();
        
        

        $logs = SystemLog::byUser($user->id)
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);

        return response()->json($logs);
    }

    /**
     * حذف السجلات القديمة (تنظيف)
     */
    public function cleanup(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $days = $request->input('days', 30); // افتراضياً 30 يوم
        
        $deleted = SystemLog::where('created_at', '<', now()->subDays($days))
                            ->delete();

        return response()->json([
            'message' => "Deleted {$deleted} old log entries",
            'deleted_count' => $deleted
        ]);
    }
}