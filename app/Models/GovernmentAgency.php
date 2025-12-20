<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GovernmentAgency extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function employees() {
        return $this->hasMany(User::class, 'agency_id');
    }

    public function complaints() {
        return $this->hasMany(Complaint::class, 'agency_id');
    }
}

