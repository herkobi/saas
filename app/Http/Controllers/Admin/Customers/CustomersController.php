<?php

namespace App\Http\Controllers\Admin\Customers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomersController extends Controller
{
    public function index(): View
    {
        return view('admin.customers.index');
    }
}
