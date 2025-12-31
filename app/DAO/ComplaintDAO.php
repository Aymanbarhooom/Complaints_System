<?php

namespace App\DAO;

use App\DAO\Interfaces\ComplaintDAOInterface;
use App\Models\Complaint;
use App\Models\ComplaintHistory;
use App\Models\Comment;
use App\Models\User;

class ComplaintDAO implements ComplaintDAOInterface
{
    
    public function findForUpdate(int $id):Complaint
    {
        return Complaint::lockForUpdate()->findOrFail($id);
    }

    
    public function assignToEmployee(Complaint $complaint, User $employee):void
    {
        if ($complaint->assigned_employee_id !== null) {
            throw new \Exception('Complaint already assigned');
        }

        if ($complaint->agency_id !== $employee->agency_id) {
            throw new \Exception('Unauthorized');
        }

        $complaint->update([
            'assigned_employee_id' => $employee->id,
            'assigned_at' => now(),
            'status' => 'in_progress'
        ]);

        ComplaintHistory::create([
            'complaint_id' => $complaint->id,
            'user_id' => $employee->id,
            'action' => 'assigned',
            'old_value' => null,
            'new_value' => 'in_progress',
        ]);
    }

    /**
     * Release complaint
     */
    public function release(Complaint $complaint, User $employee):void
    {
        if ($complaint->assigned_employee_id !== $employee->id) {
            throw new \Exception('You are not assigned to this complaint');
        }

        $oldStatus = $complaint->status;

        $complaint->update([
            'assigned_employee_id' => null,
            'assigned_at' => null,
            'status' => 'new'
        ]);

        ComplaintHistory::create([
            'complaint_id' => $complaint->id,
            'user_id' => $employee->id,
            'action' => 'released',
            'old_value' => $oldStatus,
            'new_value' => 'new',
        ]);
    }

    /**
     * Update complaint status
     */
    public function updateStatus(Complaint $complaint, User $employee, string $newStatus):void
    {
        if ($complaint->assigned_employee_id !== $employee->id) {
            throw new \Exception('Not assigned to this complaint');
        }

        $old = $complaint->status;

        $complaint->update([
            'status' => $newStatus
        ]);

        ComplaintHistory::create([
            'complaint_id' => $complaint->id,
            'user_id' => $employee->id,
            'action' => 'status_changed',
            'old_value' => $old,
            'new_value' => $newStatus,
        ]);
    }

    /**
     * Add comment to complaint
     */
    public function addComment(Complaint $complaint, User $user, string $content)
    {
        $comment = Comment::create([
            'complaint_id' => $complaint->id,
            'user_id' => $user->id,
            'content' => $content,
        ]);

        ComplaintHistory::create([
            'complaint_id' => $complaint->id,
            'user_id' => $user->id,
            'action' => 'comment_added',
            'old_value' => null,
            'new_value' => $content,
        ]);

        return $comment;
    }
}
