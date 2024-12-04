<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SocialiteController extends Controller
{
    /**
     * Redirect to Google for authentication.
     */
    public function googleLogin()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google authentication and user login/registration.
     */
    public function googleAuthentication()
    {
        try {
            // Pag retrieve sa google information
            $googleUser = Socialite::driver('google')->user();

            // E check if nag exist naba ni nga google ID
            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // If nag exist na, log them in
                Auth::login($user);
            } else {
                // Check if a user exists with the same email
                $user = User::where('email', $googleUser->email)->first();

                if ($user) {
                    // Update the existing user's Google ID
                    $user->update(['google_id' => $googleUser->id]);
                } else {
                    // Create a new user
                    $user = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'password' => Hash::make('Password@1234'), // Assign a default password
                        'google_id' => $googleUser->id,
                    ]);
                }

                // Log in the new or updated user
                Auth::login($user);
            }

            // Redirect to the dashboard
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            // Handle errors
            return redirect()->route('login')->withErrors(['error' => 'Google login failed. Please try again.']);
        }
    }
}
