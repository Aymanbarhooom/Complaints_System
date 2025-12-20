<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Complaint;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, $complaintId)
    {
        $user = auth()->user();
        $complaint = Complaint::findOrFail($complaintId);

        if ($user->role === 'citizen' && $complaint->citizen_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'employee' && $complaint->agency_id !== $user->agency_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate(['content' => 'required|string']);

        $comment = Comment::create([
            'complaint_id' => $complaint->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
        ]);

        return response()->json(['message' => 'Comment added', 'comment' => $comment]);
    }
}
