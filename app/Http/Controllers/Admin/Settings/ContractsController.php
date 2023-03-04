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
        $input=$request->all();
        Contract::create($input);

        return Redirect::route('admin.settings.contracts')->with('status', 'contract-updated');
    }

    public function edit(Request $request): View
    {
        return view('admin.settings.contracts.edit');
    }

}
