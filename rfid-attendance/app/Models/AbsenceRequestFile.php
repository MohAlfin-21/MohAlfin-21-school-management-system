<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsenceRequestFile extends Model
{
    protected $fillable = [
        'absence_request_id',
        'path',
        'original_name',
        'mime',
        'size',
    ];

    public function absenceRequest()
    {
        return $this->belongsTo(AbsenceRequest::class);
    }
}

    