<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'user_id', 
        'action', 
        'ip_address', 
        'user_agent', 
        'request_data', 
        'response_code', 
        'duration'
    ];

    public $timestamps = true;

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*public function byUser($userId)
    {
        return static::where('user_id',$userId->get());
    }*/

    // Scopes
    public function scopeErrors($query)
    {
        return $query->where('response_code', '>=', 400);
    }

    public function scopeSlow($query, $threshold = 1000)
    {
        return $query->where('duration', '>', $threshold);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
