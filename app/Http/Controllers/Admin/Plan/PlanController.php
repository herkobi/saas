<?php

namespace App\Http\Controllers\Admin\Plan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        return view('admin.plan.index');
    }

    public function free(): View
    {
        return view('admin.plan.free');
    }

    public function create(): View
    {
        return view('admin.plan.create');
    }
}
