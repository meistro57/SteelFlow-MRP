<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Update user settings (theme, layout, etc.)
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $settings = array_merge($user->settings ?? [], $request->only(['theme', 'layout_density', 'sidebar_collapsed']));
        
        $user->update(['settings' => $settings]);

        return back()->with('status', 'settings-updated');
    }
}
