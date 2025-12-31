<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'agency_id', 'description', 'location', 'attachments', 'status'/* ['new', 'in_progress', 'resolved', 'rejected'] */, 'reference_number', 'assigned_employee_id', 'assigned_at'];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function citizen()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agency()
    {
        return $this->belongsTo(GovernmentAgency::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function history()
    {
        return $this->hasMany(ComplaintHistory::class);
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(User::class, 'assigned_employee_id');
    }
}