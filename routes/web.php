<?php

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;

Route::get('/', function (Request $request): Redirector|RedirectResponse {

    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $targetUrl = Fortify::redirects('home');

    return redirect($targetUrl);

})->name('home');
