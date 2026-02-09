<?php

namespace App\Http\Controllers;

use App\Models\AbsenceRequestFile;
use Illuminate\Support\Facades\Storage;

class AbsenceRequestFileController extends Controller
{
    public function download(AbsenceRequestFile $file)
    {
        $this->authorize('view', $file->absenceRequest);

        return Storage::disk('local')->download($file->path, $file->original_name);
    }
}

