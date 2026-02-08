<?php

/**
 * User Role Changed Mail
 *
 * Mailable class for user role change notifications.
 *
 * @package    App\Mail\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Mail\App\Account;

use App\Enums\TenantUserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for user role changed notification.
 */
class UserRoleChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The old role label.
     *
     * @var string
     */
    public string $oldRoleLabel;

    /**
     * The new role label.
     *
     * @var string
     */
    public string $newRoleLabel;

    /**
     * The tenant name.
     *
     * @var string
     */
    public string $tenantName;

    /**
     * Create a new message instance.
     *
     * @param User $user The user whose role changed
     * @param Tenant $tenant The tenant context
     * @param int|null $oldRole The previous role value
     * @param int $newRole The new role value
     */
    public function __construct(
        public readonly User $user,
        public readonly Tenant $tenant,
        public readonly ?int $oldRole,
        public readonly int $newRole
    ) {
        $this->oldRoleLabel = $oldRole !== null
            ? TenantUserRole::from($oldRole)->label()
            : 'Yok';
        $this->newRoleLabel = TenantUserRole::from($newRole)->label();
        $this->tenantName = $tenant->data['name'] ?? $tenant->code;
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rolünüz Değiştirildi',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.tenant.account.user-role-changed',
            with: [
                'user' => $this->user,
                'tenantName' => $this->tenantName,
                'oldRoleLabel' => $this->oldRoleLabel,
                'newRoleLabel' => $this->newRoleLabel,
                'dashboardUrl' => url('/app/dashboard'),
            ],
        );
    }
}
