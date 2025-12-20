<?php

namespace App\Http\Controllers;

use App\Models\ComplaintHistory;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintHistoryController extends Controller
{
    public function index($complaintId)
    {
        // Get the complaint
        $complaint = Complaint::find($complaintId);
        
        if (!$complaint) {
            return response()->json(['error' => 'Complaint not found'], 404);
        }

        $user = auth()->user();

        if ($user->role === 'citizen') {
            if ($complaint->citizen_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } elseif ($user->role === 'employee') {
            if ($complaint->agency_id !== $user->agency_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } elseif ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $history = ComplaintHistory::where('complaint_id', $complaintId)
            ->with('complaint', 'complaint.citizen', 'complaint.agency')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'complaint_id' => $complaintId,
            'history' => $history
        ], 200);
    }
}
