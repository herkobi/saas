<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\GatewayUpdateRequest;
use App\Models\Gateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class GatewayController extends Controller
{
    public function index(): View
    {
        $gateways = Gateway::all();
        return view('admin.settings.gateway.index', ['gateways' => $gateways]);
    }

    public function create(): View
    {
        return view('admin.settings.gateway.create');
    }

    public function store(GatewayUpdateRequest $request): RedirectResponse
    {
        $input=$request->all();
        Gateway::create($input);

        return Redirect::route('admin.settings.gateways')->with('status', 'gateway-updated');
    }

    public function edit(Request $request): View
    {
        return view('admin.settings.gateway.edit');
    }

}
