<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = [
        'name',
        'grade',
        'major',
        'homeroom_teacher_id',
    ];

    public function homeroomTeacher()
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    public function memberships()
    {
        return $this->hasMany(ClassroomMembership::class);
    }

    public function activeMemberships(?string $date = null)
    {
        $date ??= now()->toDateString();

        return $this->memberships()
            ->activeOn($date);
    }
}
