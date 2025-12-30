<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function login()
    {
        return \Inertia\Inertia::render('Auth/Login');
    }

    /**
     * Redirect the user to the Microsoft authentication page.
     */
    public function redirectToProvider()
    {
        return Socialite::driver('azure')->redirect();
    }

    /**
     * Obtain the user information from Microsoft.
     */
    public function handleProviderCallback()
    {
        try {
            $socialUser = Socialite::driver('azure')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Authentication failed');
        }

        $user = User::updateOrCreate([
            'email' => $socialUser->getEmail(),
        ], [
            'name' => $socialUser->getName() ?? $socialUser->getNickname(),
            'azure_id' => $socialUser->getId(),
            'password' => bcrypt(Str::random(24)), // Random password for OAuth users
        ]);

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
