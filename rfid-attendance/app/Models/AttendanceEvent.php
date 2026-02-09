<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceEvent extends Model
{
    protected $fillable = [
        'device_id',
        'uid',
        'student_user_id',
        'scanned_at',
        'action',
        'result',
        'message',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }
}

