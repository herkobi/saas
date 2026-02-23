<?php

/**
 * Setting Controller
 *
 * Handles system settings management operations
 * including viewing, updating settings and file uploads.
 *
 * @package    App\Http\Controllers\Panel\Settings
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Settings;

use App\Services\Panel\Settings\SettingService;
use App\Events\PanelSettingUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Settings\UpdateSettingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Setting Controller
 *
 * Manages system settings through the settings service.
 */
class SettingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param SettingService $settingService
     */
    public function __construct(
        private readonly SettingService $settingService
    ) {}

    /**
     * Display settings.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('panel/Settings/General/Index', [
            'settings' => $this->settingService->all(),
        ]);
    }

    /**
     * Update settings.
     *
     * @param UpdateSettingRequest $request
     * @return RedirectResponse
     */
    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        $oldData = $this->settingService->all();
        $validated = $request->validated();

        // Handle file uploads
        $fileFields = ['logo_light', 'logo_dark', 'favicon'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if exists
                if (isset($oldData[$field]) && $oldData[$field]) {
                    Storage::disk('public')->delete($oldData[$field]);
                }

                // Store new file
                $path = $request->file($field)->store('settings', 'public');
                $validated[$field] = $path;
            } else {
                // Keep existing value if no new file uploaded
                unset($validated[$field]);
            }
        }

        $this->settingService->updateMany($validated);

        PanelSettingUpdated::dispatch(
            $oldData,
            $validated,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Ayarlar güncellendi.');
    }

    /**
     * Delete a file setting.
     *
     * @param string $key
     * @return RedirectResponse
     */
    public function deleteFile(string $key): RedirectResponse
    {
        $allowedKeys = ['logo_light', 'logo_dark', 'favicon'];

        if (!in_array($key, $allowedKeys)) {
            return back()->with('error', 'Geçersiz dosya anahtarı.');
        }

        $filePath = $this->settingService->get($key);

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        $this->settingService->set($key, null);

        return back()->with('success', 'Dosya silindi.');
    }
}
