<?php

namespace App\DAO\Interfaces;

use App\Models\Complaint;
use App\Models\User;

interface ComplaintDAOInterface {
    public function findForUpdate(int $id):Complaint;
    public function assignToEmployee(Complaint $complaint, User $employee):void;
    public function release(Complaint $complaint, User $employee):void;
    public function updateStatus(Complaint $complaint, User $employee, string $newStatus):void;
    public function addComment(Complaint $complaint, User $user, string $content);
}