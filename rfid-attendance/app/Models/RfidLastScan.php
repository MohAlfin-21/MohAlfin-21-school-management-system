<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfidLastScan extends Model
{
    protected $fillable = [
        'device_id',
        'uid',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
