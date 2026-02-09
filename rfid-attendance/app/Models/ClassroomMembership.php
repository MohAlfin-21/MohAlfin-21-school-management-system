<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class ClassroomMembership extends Model
{
    protected $fillable = [
        'classroom_id',
        'student_user_id',
        'is_secretary',
        'active_from',
        'active_to',
    ];

    protected function casts(): array
    {
        return [
            'is_secretary' => 'boolean',
            'active_from' => 'date',
            'active_to' => 'date',
        ];
    }

    public function scopeActiveOn($query, string $date)
    {
        $dateValue = CarbonImmutable::parse($date)->startOfDay();

        return $query
            ->where('active_from', '<=', $dateValue)
            ->where(function ($query) use ($dateValue) {
                $query->whereNull('active_to')->orWhere('active_to', '>=', $dateValue);
            });
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }
}
