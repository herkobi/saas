<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Contracts\App\Account\InvitationServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InvitationAcceptController extends Controller
{
    public function __construct(
        private readonly InvitationServiceInterface $invitationService
    ) {}

    public function show(string $token): Response|RedirectResponse
    {
        $invitation = $this->invitationService->findByToken($token);

        if (! $invitation || ! $invitation->isPending() || $invitation->isExpired()) {
            return redirect()
                ->route('login')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Davetiye geçersiz veya süresi dolmuş.',
                ]);
        }

        if (Auth::check()) {
            return Inertia::render('app/Invitation/Accept', compact('invitation', 'token'));
        }

        return redirect()
            ->route('register', ['invitation' => $token]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        try {
            $this->invitationService->acceptInvitation($token, $request->user());

            return redirect()
                ->route('app.dashboard')
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Davetiye kabul edildi. Hesaba katıldınız.',
                ]);
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('app.dashboard')
                ->with('toast', [
                    'type' => 'error',
                    'message' => $e->getMessage(),
                ]);
        }
    }
}
