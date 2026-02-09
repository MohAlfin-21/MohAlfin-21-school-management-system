<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherProfile extends Model
{
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'nip',
        'full_name_with_title',
        'phone_wa',
        'photo_path',
        'public_bio',
        'subjects_text',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

