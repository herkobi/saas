<?php

namespace App\Http\Controllers\User\Account\Plan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        return view('user.account.plan.index');
    }
}
