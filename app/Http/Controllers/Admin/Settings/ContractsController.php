<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.contracts.index');
    }

    public function create(): View
    {
        return view('admin.settings.contracts.create');
    }

}
