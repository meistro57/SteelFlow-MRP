<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

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
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
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

        // Persist Azure SSO logins across browser sessions just like the standard "Remember me" flow
        Auth::login($user, remember: true);

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
