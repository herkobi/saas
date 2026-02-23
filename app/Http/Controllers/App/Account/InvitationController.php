<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\Account;

use App\Services\App\Account\InvitationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Account\InviteTeamMemberRequest;
use App\Traits\HasTenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    use HasTenantContext;

    public function __construct(
        private readonly InvitationService $invitationService
    ) {}

    public function index(): Response|RedirectResponse
    {
        $tenant = $this->currentTenant();

        if (! $tenant) {
            return redirect()
                ->route('dashboard')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Tenant bulunamadı.',
                ]);
        }

        $invitations = $this->invitationService->getPendingInvitations($tenant);

        return Inertia::render('app/Account/Users/Invitations', compact('invitations'));
    }

    public function invite(InviteTeamMemberRequest $request): RedirectResponse
    {
        $tenant = $this->currentTenant();

        if (! $tenant) {
            return redirect()
                ->route('dashboard')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Tenant bulunamadı.',
                ]);
        }

        try {
            $result = $this->invitationService->invite(
                $tenant,
                $request->validated('email'),
                (int) $request->validated('role'),
                $request->user(),
                $request->ip() ?? '127.0.0.1',
                $request->userAgent() ?? 'unknown'
            );

            $message = $result['type'] === 'directly_added'
                ? 'Kullanıcı hesaba eklendi.'
                : 'Davetiye gönderildi.';

            return redirect()
                ->back()
                ->with('toast', [
                    'type' => 'success',
                    'message' => $message,
                ]);
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->with('toast', [
                    'type' => 'error',
                    'message' => $e->getMessage(),
                ]);
        }
    }

    public function revoke(Request $request, string $invitationId): RedirectResponse
    {
        $tenant = $this->currentTenant();

        if (! $tenant) {
            return redirect()
                ->route('app.dashboard')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Tenant bulunamadı.',
                ]);
        }

        $invitation = $this->invitationService->findById($tenant, $invitationId);

        if (! $invitation) {
            return redirect()
                ->back()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Davetiye bulunamadı.',
                ]);
        }

        try {
            $this->invitationService->revokeInvitation(
                $tenant,
                $invitation,
                $request->user(),
                $request->ip() ?? '127.0.0.1',
                $request->userAgent() ?? 'unknown'
            );

            return redirect()
                ->back()
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Davetiye iptal edildi.',
                ]);
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->with('toast', [
                    'type' => 'error',
                    'message' => $e->getMessage(),
                ]);
        }
    }

    public function resend(Request $request, string $invitationId): RedirectResponse
    {
        $tenant = $this->currentTenant();

        if (! $tenant) {
            return redirect()
                ->route('dashboard')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Tenant bulunamadı.',
                ]);
        }

        $invitation = $this->invitationService->findById($tenant, $invitationId);

        if (! $invitation) {
            return redirect()
                ->back()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Davetiye bulunamadı.',
                ]);
        }

        try {
            $this->invitationService->resendInvitation($tenant, $invitation, $request->user());

            return redirect()
                ->back()
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Davetiye tekrar gönderildi.',
                ]);
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->with('toast', [
                    'type' => 'error',
                    'message' => $e->getMessage(),
                ]);
        }
    }
}
