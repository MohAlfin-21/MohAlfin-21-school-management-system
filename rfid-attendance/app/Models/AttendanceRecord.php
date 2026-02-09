<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'date',
        'student_user_id',
        'classroom_id',
        'check_in_at',
        'check_out_at',
        'check_in_method',
        'check_out_method',
        'check_out_type',
        'status',
        'note',
        'early_checkout_reason',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
        ];
    }

    public function scopeForDate($query, string $date)
    {
        $dateValue = CarbonImmutable::parse($date)->startOfDay();

        return $query->where('date', $dateValue);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
