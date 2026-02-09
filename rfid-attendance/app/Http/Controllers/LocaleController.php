<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(['id', 'en'])],
        ]);

        $locale = $validated['locale'];

        if ($request->user()) {
            $request->user()->forceFill(['locale' => $locale])->save();
        }

        $request->session()->put('locale', $locale);

        return back();
    }
}
