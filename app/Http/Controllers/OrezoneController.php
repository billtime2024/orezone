<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class OrezoneController extends Controller
{
    public function landing()
    {
        return Inertia::render('landing/index');
    }

    public function coming_soon()
    {
        return Inertia::render('pages/coming-soon');
    }
}
