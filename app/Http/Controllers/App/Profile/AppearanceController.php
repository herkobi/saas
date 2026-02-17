<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\Profile;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AppearanceController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('app/Profile/Appearance');
    }
}
