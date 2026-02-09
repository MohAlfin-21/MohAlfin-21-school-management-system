<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'name',
        'location',
        'token_hash',
        'token_plain',
        'is_active',
        'last_seen_at',
    ];

    protected $hidden = [
        'token_plain',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
            'token_plain' => 'encrypted',
        ];
    }

    public function attendanceEvents()
    {
        return $this->hasMany(AttendanceEvent::class);
    }

    public function lastScan()
    {
        return $this->hasOne(RfidLastScan::class);
    }
}
