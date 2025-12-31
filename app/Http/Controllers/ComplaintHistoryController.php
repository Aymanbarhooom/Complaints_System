<?php

namespace App\Http\Controllers;

use App\Models\ComplaintHistory;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintHistoryController extends Controller
{
    public function index($complaintId)
{
    $complaint = Complaint::findOrFail($complaintId);
    
    // Authorization (نحسنه لاحقاً مع Middleware)
    $user = auth()->user();
    if ($user->role === 'citizen' && $complaint->user_id !== $user->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    // ✅ جلب History مع العلاقات (ORM)
    $history = ComplaintHistory::where('complaint_id', $complaintId)
        ->with(['user:id,firstName,lastName,role']) // علاقات ORM
        ->orderBy('created_at', 'desc')
        ->paginate(20); // Pagination للأداء
    
    return response()->json([
        'complaint' => $complaint->only(['id', 'reference_number', 'status']),
        'history' => $history
    ]);
}
}
