<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use APP\DAO\Interfaces\ComplaintDAOInterface;
use App\Models\Comment;
use App\Models\Complaint;
use App\Models\ComplaintHistory;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected ComplaintDAOInterface $complaintDAO;

    public function __construct(ComplaintDAOInterface $complaintDAO)
    {
        $this->complaintDAO = $complaintDAO;
    }

    public function store(Request $request, $complaintId)
{
    $user = auth()->user();
    $complaint = Complaint::findOrFail($complaintId);

    if ($user->role === 'citizen' && $complaint->user_id !== $user->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    if ($user->role === 'employee' && $complaint->agency_id !== $user->agency_id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $validated = $request->validate([
        'content' => 'required|string'
    ]);

    $comment = Comment::create([
        'complaint_id' => $complaint->id,
        'user_id'      => $user->id,
        'content'      => $validated['content'],
    ]);

    ComplaintHistory::create([
        'complaint_id' => $complaint->id,
        'user_id'      => $user->id,
        'action'       => 'comment_added',
        'old_value'    => null,
        'new_value'    => $validated['content'],
    ]);

    // ======================
    // Notification
    // ======================
    $notifyUserId = $user->role === 'citizen'
        ? User::where('agency_id', $complaint->agency_id)
              ->where('role', 'employee')
              ->value('id')
        : $complaint->user_id;

    if ($notifyUserId) {
        Notification::create([
            'user_id'      => $notifyUserId,
            'complaint_id' => $complaint->id,
            'title'        => 'New Comment',
            'message'      => 'A new comment has been added to your complaint.',
            'is_read'      => false,
        ]);
    }

    return response()->json([
        'message' => 'Comment added successfully',
        'comment' => $comment,
        'status'  => 201
    ], 201);
}

public function index($id)
{
    $user = auth()->user();
    $complaint = Complaint::findOrFail($id);

    // ======================
    // Authorization
    // ======================
    if ($user->role === 'citizen' && $complaint->user_id !== $user->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    if ($user->role === 'employee' && $complaint->agency_id !== $user->agency_id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    if ($user->role === 'admin') {
        // admin allowed
    }

    // ======================
    // Get comments
    // ======================
    $comments = Comment::where('complaint_id', $complaint->id)
        ->with('user:id,firstName,lastName,role') // معلومات صاحب التعليق
        ->orderBy('created_at', 'asc')
        ->get();

    return response()->json([
        'comments' => $comments,
        'status'   => 200
    ]);
}

}
