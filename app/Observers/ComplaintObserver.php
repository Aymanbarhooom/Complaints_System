<?php


namespace App\Observers;

use App\Models\Complaint;
use App\Models\ComplaintHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ComplaintObserver
{
    
    public function creating(Complaint $complaint)
    {
        // توليد رقم مرجعي فريد تلقائياً
        if (empty($complaint->reference_number)) {
            $complaint->reference_number = 'CMP-' . strtoupper(Str::random(8)) . '-' . time();
        }

        // تعيين الحالة الافتراضية
        if (empty($complaint->status)) {
            $complaint->status = 'new';
        }
    }

   
    public function created(Complaint $complaint)
    {
        ComplaintHistory::create([
            'complaint_id' => $complaint->id,
            'user_id' => auth()->id() ?? $complaint->user_id,
            'action' => 'created',
            'old_value' => null,
            'new_value' => json_encode([
                'status' => $complaint->status,
                'description' => Str::limit($complaint->description, 100),
                'agency_id' => $complaint->agency_id,
            ]),
        ]);

        Log::info("Complaint created", [
            'complaint_id' => $complaint->id,
            'reference' => $complaint->reference_number,
            'user_id' => $complaint->user_id,
        ]);
    }

   
    public function updating(Complaint $complaint)
    {
    }

    
    public function updated(Complaint $complaint)
    {
        // جلب التغييرات فقط
        $changes = $complaint->getChanges();
        
        // إذا لم يكن فيه تغييرات فعلية، لا تسجل
        if (empty($changes) || (count($changes) === 1 && isset($changes['updated_at']))) {
            return;
        }

        // إزالة updated_at من التغييرات
        unset($changes['updated_at']);

        foreach ($changes as $field => $newValue) {
            $oldValue = $complaint->getOriginal($field);

            // تسجيل كل تغيير بشكل منفصل
            ComplaintHistory::create([
                'complaint_id' => $complaint->id,
                'user_id' => auth()->id(),
                'action' => 'updated_' . $field,
                'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
                'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
            ]);
        }

        Log::info("Complaint updated", [
            'complaint_id' => $complaint->id,
            'changes' => $changes,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * قبل الحذف
     */
    public function deleting(Complaint $complaint)
    {
        // منع حذف الشكاوى قيد المعالجة
        if ($complaint->status === 'in_progress') {
            throw new \Exception('Cannot delete complaint in progress');
        }

        Log::warning("Complaint being deleted", [
            'complaint_id' => $complaint->id,
            'reference' => $complaint->reference_number,
        ]);
    }

    /**
     * بعد الحذف
     */
    public function deleted(Complaint $complaint)
    {
        // حذف البيانات المرتبطة
        $complaint->comments()->delete();
        $complaint->notifications()->delete();
        
        // تسجيل الحذف في History
        ComplaintHistory::create([
            'complaint_id' => $complaint->id,
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'old_value' => json_encode($complaint->toArray()),
            'new_value' => null,
        ]);
    }

    /**
     * عند استعادة الشكوى (Soft Delete)
     */
    public function restored(Complaint $complaint)
    {
        ComplaintHistory::create([
            'complaint_id' => $complaint->id,
            'user_id' => auth()->id(),
            'action' => 'restored',
            'old_value' => null,
            'new_value' => 'Complaint restored',
        ]);
    }
}
