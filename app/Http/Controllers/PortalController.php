<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PortalController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('portal/index', [
            'user' => $request->user(),
        ]);
    }
}
