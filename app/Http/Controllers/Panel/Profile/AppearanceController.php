<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Profile;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AppearanceController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('panel/Profile/Appearance');
    }
}
