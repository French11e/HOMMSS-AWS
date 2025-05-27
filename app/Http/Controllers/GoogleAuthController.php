<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;


class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle()
    {
        try {
            $google_user = Socialite::driver('google')->stateless()->user();

            // Log Google authentication attempt for security monitoring
            \Log::info('Google OAuth attempt', [
                'email' => $google_user->getEmail(),
                'name' => $google_user->getName(),
                'google_id' => $google_user->getId(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Check if user with this email already exists
            $user = User::where('email', $google_user->getEmail())->first();

            if ($user) {
                // If google_id is not yet linked, update it
                if (!$user->google_id) {
                    $user->google_id = $google_user->getId();
                    $user->save();

                    \Log::info('Google ID linked to existing user', [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);
                }
            } else {
                // If no user with this email, create a new one
                $user = User::create([
                    'name' => $google_user->getName(),
                    'email' => $google_user->getEmail(),
                    'google_id' => $google_user->getId(),
                    'utype' => 'USR', // Default user type
                    'email_verified_at' => now(), // Google accounts are pre-verified
                ]);

                \Log::info('New user created via Google OAuth', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            }

            Auth::login($user);

            // Redirect to intended page or home
            return redirect()->intended('/')->with('success', 'Successfully signed in with Google!');

        } catch (\Exception $e) {
            \Log::error('Google OAuth error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => request()->ip()
            ]);

            return redirect()->route('login')->with('error', 'Unable to sign in with Google. Please try again or use email/password.');
        }
    }
}
