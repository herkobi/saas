<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\ContractUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
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

    public function store(ContractUpdateRequest $request): RedirectResponse
    {
        $request->fill($request->validated());

        $request->save();

        return Redirect::route('admin.settings.contracts.edit')->with('status', 'contract-updated');
    }

    public function edit(Request $request): View
    {
        return view('admin.settings.contracts.edit');
    }

}
