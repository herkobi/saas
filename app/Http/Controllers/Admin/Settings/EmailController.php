<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.email.index');
    }

}
