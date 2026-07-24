<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class UserGuideController extends Controller
{
    /**
     * Display the system user guide and operational SOPs.
     */
    public function index(): Response
    {
        return Inertia::render('UserGuide/Index');
    }
}
