<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Services\App\TenantService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function __construct(
        private readonly TenantService $tenantService
    ) {}

    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $this->tenantService->canCreate($user)) {
            return redirect()->route('app.dashboard')
                ->with('error', 'Yeni tenant oluşturma izniniz yok veya tenant limitine ulaştınız.');
        }

        return Inertia::render('app/Tenant/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        try {
            $tenant = $this->tenantService->create(
                $user,
                $validated,
                $request->ip() ?? '127.0.0.1',
                $request->userAgent() ?? 'unknown'
            );

            $this->tenantService->switchTenant($user, $tenant->id);

            return redirect()->route('app.dashboard')
                ->with('success', 'Yeni tenant başarıyla oluşturuldu.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('app.dashboard')
                ->with('error', $e->getMessage());
        }
    }

    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'string'],
        ]);

        $user = $request->user();
        $tenant = $this->tenantService->switchTenant($user, $validated['tenant_id']);

        if (! $tenant) {
            return redirect()->back()
                ->with('error', 'Geçersiz tenant seçimi.');
        }

        return redirect()->route('app.dashboard')
            ->with('success', $tenant->account['title'] . ' hesabına geçiş yapıldı.');
    }
}
