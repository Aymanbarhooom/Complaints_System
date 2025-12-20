<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = ['citizen_id','agency_id','description','location','attachments','status','reference_number'];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function citizen() {
        return $this->belongsTo(User::class, 'citizen_id');
    }

    public function agency() {
        return $this->belongsTo(GovernmentAgency::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function history() {
        return $this->hasMany(ComplaintHistory::class);
    }
}

