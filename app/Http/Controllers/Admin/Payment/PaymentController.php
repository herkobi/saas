<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        return view('admin.payment.index');
    }

    public function open(): View
    {
        return view('admin.payment.open');
    }

    public function approve(): View
    {
        return view('admin.payment.approve');
    }

    public function closed(): View
    {
        return view('admin.payment.closed');
    }
}
