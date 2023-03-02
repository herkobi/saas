<?php

namespace App\Http\Controllers\User\Account\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        return view('user.account.payment.index');
    }
}
