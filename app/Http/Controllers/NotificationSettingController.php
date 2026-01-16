<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateNotificationSettingRequest;
use App\Models\NotificationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationSettingController extends Controller
{
    /**
     * Show the form for editing notification settings
     */
    public function edit(): View
    {
        $settings = NotificationSetting::getSettings();

        return view('settings.notifications', compact('settings'));
    }

    /**
     * Update notification settings
     */
    public function update(UpdateNotificationSettingRequest $request): RedirectResponse
    {
        $settings = NotificationSetting::getSettings();
        $settings->update($request->validated());

        return redirect()->route('settings.notifications.edit')
            ->with('success', 'Configurações atualizadas com sucesso!');
    }
}
