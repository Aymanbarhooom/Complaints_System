<?php

namespace App\Http\Controllers;
namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintHistory;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
     public function allComplaints()
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(Complaint::all());
    }
    // المواطن ينشئ شكوى جديدة
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'citizen') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'agency_id' => 'required|exists:government_agencies,id',
            'description' => 'required|string',
            'location' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xlsx',
        ]);

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('complaints', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        $complaint = Complaint::create([
            'citizen_id' => auth()->id(),
            'agency_id' => $validated['agency_id'],
            'description' => $validated['description'],
            'location' => $validated['location'] ?? null,
            'attachments' => count($attachments) > 0 ? $attachments : null,
            'reference_number' => strtoupper(uniqid('CMP-')),
        ]);

        return response()->json(['message' => 'Complaint submitted', 'complaint' => $complaint], 201);
    }

    // عرض الشكاوى حسب الدور
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'citizen') {
            $complaints = Complaint::where('citizen_id', $user->id)->get();
        } elseif ($user->role === 'employee') {
            $complaints = Complaint::where('agency_id', $user->agency_id)->get();
        } else {
            $complaints = Complaint::all(); // admin
        }

        return response()->json($complaints);
    }

    // فتح شكوى للمعالجة (كما اتفقنا)
    public function open($id)
    {
        $user = auth()->user();
        $complaint = Complaint::findOrFail($id);

        if ($user->role !== 'employee') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($complaint->agency_id !== $user->agency_id) {
            return response()->json(['error' => 'Not your agency'], 403);
        }

        if ($complaint->status === 'new') {
            $old = $complaint->status;
            $complaint->update([
                'status' => 'in_progress',
            ]);

            ComplaintHistory::create([
                'complaint_id' => $complaint->id,
                'user_id' => $user->id,
                'action' => 'status_changed',
                'old_value' => $old,
                'new_value' => 'in_progress',
            ]);

            return response()->json(['message' => 'Complaint opened for processing']);
        }

        if ($complaint->status === 'in_progress') {
            return response()->json(['message' => 'Complaint already under processing'], 423);
        }

        return response()->json(['message' => 'Complaint closed, read-only']);
    }

    // تعديل الحالة إلى منجزة أو مرفوضة
    public function updateStatus(Request $request, $id)
    {
        $user = auth()->user();
        $complaint = Complaint::findOrFail($id);

        if ($user->role !== 'employee') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:resolved,rejected,in_progress,new',
        ]);

        $old = $complaint->status;
        $complaint->update(['status' => $validated['status']]);

        ComplaintHistory::create([
            'complaint_id' => $complaint->id,
            'user_id' => $user->id,
            'action' => 'status_changed',
            'old_value' => $old,
            'new_value' => $validated['status'],
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
