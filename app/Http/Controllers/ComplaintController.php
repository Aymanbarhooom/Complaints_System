<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\DAO\Interfaces\ComplaintDAOInterface;
use App\Models\Complaint;
use App\Models\Notification;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{

    protected ComplaintDAOInterface $complaintDAO;

    public function __construct(ComplaintDAOInterface $complaintDAO)
    {
        $this->complaintDAO = $complaintDAO;
    }

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

        $user = auth()->user();

        $validated = $request->validate([
            'agency_id' => 'required|exists:government_agencies,id',
            'description' => 'required|string',
            'location' => 'nullable|string',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xlsx',
        ]);

        try {
            // =============================
            // بداية Transaction
            // =============================
            $complaint = DB::transaction(function () use ($request, $validated) {

                // 1️⃣ إنشاء الشكوى
                $complaint = Complaint::create([
                    'agency_id' => $validated['agency_id'],
                    'user_id' => auth()->id(),
                    'status' => 'new',
                    'description' => $validated['description'],
                    'location' => $validated['location'] ?? null,
                    // reference_number سيتم توليده في Observer
                ]);

                // 2️⃣ رفع الملفات المرفقة
                $attachmentsData = [];
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        // رفع الملف
                        $path = $file->store('complaints/' . $complaint->id, 'public');

                        $attachmentsData[] = [
                            'name' => $file->getClientOriginalName(),
                            'path' => $path,
                            'type' => $file->getClientMimeType(),
                            'size' => $file->getSize(),
                        ];
                    }

                    // حفظ المرفقات في الشكوى
                    $complaint->update(['attachments' => $attachmentsData]);
                }

                // 3️⃣ إنشاء إشعار لموظفي الجهة
                $employees = User::where('agency_id', $validated['agency_id'])
                    ->where('role', 'employee')
                    ->get();

                foreach ($employees as $employee) {
                    Notification::create([
                        'user_id' => $employee->id,
                        'complaint_id' => $complaint->id,
                        'title' => 'New Complaint Received',
                        'message' => "New complaint #{$complaint->reference_number} has been submitted.",
                        'is_read' => false,
                    ]);
                }

                // 4️⃣ إرسال إشعار للمواطن بالتأكيد
                Notification::create([
                    'user_id' => auth()->id(),
                    'complaint_id' => $complaint->id,
                    'title' => 'Complaint Submitted Successfully',
                    'message' => "Your complaint has been submitted with reference number: {$complaint->reference_number}",
                    'is_read' => false,
                ]);

                // ✅ إذا وصلنا هنا = كل شي نجح
                return $complaint;
            });
            // =============================
            // نهاية Transaction
            // =============================

            // Log النجاح
            Log::info('Complaint created successfully', [
                'complaint_id' => $complaint->id,
                'reference' => $complaint->reference_number,
                'user_id' => auth()->id(),
            ]);

            // إرجاع النتيجة
            return response()->json([
                'message' => 'Complaint submitted successfully',
                'complaint' => $complaint->load('agency:id,name'),
                'reference_number' => $complaint->reference_number,
                'status' => 201
            ], 201);
        } catch (\Exception $e) {
            // ❌ Rollback تلقائي - كل شي يرجع كأنو ما صار

            // حذف الملفات المرفوعة (إذا وصلت لهذه المرحلة)
            if (isset($attachmentsData)) {
                foreach ($attachmentsData as $attachment) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }

            // Log الخطأ
            Log::error('Failed to create complaint', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Failed to submit complaint. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'status' => 500
            ], 500);
        }
    }

    // عرض الشكاوى حسب الدور
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'citizen') {
            $complaints = Complaint::where('user_id', $user->id)->get();
        } elseif ($user->role === 'employee') {
            $complaints = Complaint::where('agency_id', $user->agency_id)->get();
        } else {
            $complaints = Complaint::all(); // admin
        }

        return response()->json($complaints);
    }

    public function assignToMe($id)
    {
        $user = auth()->user();

        if ($user->role !== 'employee') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            DB::transaction(function () use ($id, $user) {
                $complaint = $this->complaintDAO->findForUpdate($id);
                $this->complaintDAO->assignToEmployee($complaint, $user);

                SystemLog::create([
                    'user_id' => $user->id,
                    'action' => 'assigned_complaint',
                    'ip_address' => request()->ip(),
                ]);
            });

            return response()->json(['message' => 'Complaint assigned'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }


    public function release($id)
    {
        $user = auth()->user();

        try {
            DB::transaction(function () use ($id, $user) {
                $complaint = $this->complaintDAO->findForUpdate($id);
                $this->complaintDAO->release($complaint, $user);

                SystemLog::create([
                    'user_id' => $user->id,
                    'action' => 'released_complaint',
                    'ip_address' => request()->ip(),
                ]);
            });

            return response()->json(['message' => 'Complaint released'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    } 

    public function updateStatus(Request $request, $id)
{
    $user = auth()->user();

    $validated = $request->validate([
        'status' => 'required|in:new,in_progress,resolved,rejected'
    ]);

    try {
        DB::transaction(function () use ($id, $user, $validated) {
            $complaint = $this->complaintDAO->findForUpdate($id);
            $this->complaintDAO->updateStatus(
                $complaint,
                $user,
                $validated['status']
            );

            SystemLog::create([
                'user_id' => $user->id,
                'action' => 'updated_status',
                'ip_address' => request()->ip(),
            ]);
        });

        return response()->json(['message' => 'Status updated'], 200);

    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 409);
    }
}


}
