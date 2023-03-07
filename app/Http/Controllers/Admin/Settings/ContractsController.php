<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\ContractUpdateRequest;
use App\Models\Contract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ContractsController extends Controller
{
    public function index(): View
    {
        $contracts = Contract::all();
        return view('admin.settings.contracts.index', ['contracts' => $contracts]);
    }

    public function create(): View
    {
        return view('admin.settings.contracts.create');
    }

    public function store(ContractUpdateRequest $request): RedirectResponse
    {
        Contract::create($request->all());

        return Redirect::route('admin.settings.contracts')->with('status', 'contract-updated');
    }

    public function edit(Contract $contract)
    {
        return view('admin.settings.contracts.edit', compact('contract'));
    }

    public function update(ContractUpdateRequest $request, Contract $contract): RedirectResponse
    {
    
        return Redirect::route('admin.settings.contracts')->with('status', 'contract-updated');
    }

}
