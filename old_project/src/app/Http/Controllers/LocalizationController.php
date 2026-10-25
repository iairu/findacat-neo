<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocalizationController extends Controller
{
    public function changeLanguage(Request $request)
    {
        $locale = $request->input('locale');
        session()->put('locale', $locale);
        return redirect()->back();
    }
}
