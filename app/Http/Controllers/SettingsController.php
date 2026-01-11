<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        return Inertia::render('Settings/Index');
    }

    /**
     * Update user settings (theme, layout, etc.)
     */
    public function update(UpdateSettingsRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

        $settings = array_merge($user->settings ?? [], $validated);

        $user->update(['settings' => $settings]);

        return back()->with('status', 'settings-updated');
    }
}
