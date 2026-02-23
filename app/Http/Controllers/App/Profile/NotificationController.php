<?php

/**
 * Tenant Notification Controller
 *
 * This controller handles notification-related operations for tenant users,
 * including listing, marking as read, and viewing archived notifications.
 *
 * @package    App\Http\Controllers\App\Profile
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\App\Profile;

use App\Services\Shared\NotificationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Profile\Notification\MarkAsReadRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for tenant notification operations.
 *
 * Manages notification listing, reading, and archiving
 * for tenant users.
 */
class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param NotificationService $notificationService Service for notification operations
     */
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    /**
     * Display a listing of the user's notifications.
     *
     * @return Response
     */
    public function index(): Response
    {
        $notifications = $this->notificationService->getPaginatedNotifications(Auth::user());
        $unreadCount = $this->notificationService->getUnreadCount(Auth::user());

        return Inertia::render('app/Profile/Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Display a listing of the user's archived notifications.
     *
     * @return Response
     */
    public function archived(): Response
    {
        $archivedNotifications = $this->notificationService->getPaginatedArchivedNotifications(Auth::user());

        return Inertia::render('app/Profile/Notifications/Archived', [
            'archivedNotifications' => $archivedNotifications,
        ]);
    }

    /**
     * Mark a notification as read.
     *
     * @param MarkAsReadRequest $request
     * @return RedirectResponse
     */
    public function markAsRead(MarkAsReadRequest $request): RedirectResponse
    {
        $this->notificationService->markAsRead(
            Auth::user(),
            $request->validated('notification_id')
        );

        return back();
    }

    /**
     * Mark all notifications as read.
     *
     * @return RedirectResponse
     */
    public function markAllAsRead(): RedirectResponse
    {
        $this->notificationService->markAllAsRead(Auth::user());

        return back()->with('success', 'Tüm bildirimler okundu olarak işaretlendi.');
    }
}
