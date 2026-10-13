<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function toggle(Request $request, string $locale): RedirectResponse
    {
        if (in_array($locale, ['ar', 'en'], true)) {
            $request->session()->put('locale', $locale);
            app()->setLocale($locale);
        }

        return back();
    }
}
