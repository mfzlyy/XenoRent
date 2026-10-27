<?php

namespace App\Presentation\Http\Controllers;

use Illuminate\Contracts\View\View;

class MediaGuideController extends Controller
{
    public function index(): View
    {
        return view('media-guide');
    }
}
