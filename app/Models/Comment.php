<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

     protected $fillable = [
        'complaint_id',
        'citizen_id',
        'content',
    ];

    public function citizen() {
        return $this->belongsTo(User::class, 'citizen_id');
    }
    public function complaint() {
        return $this->belongsTo(Complaint::class, 'complaint_id');
    }
}
