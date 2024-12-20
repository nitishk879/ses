<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to the OAuth provider.
     */
    public function redirectToProvider(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle callback from the OAuth provider.
     */
    public function handleProviderCallback(string $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            $nameParts = explode(' ', $socialUser->getName());

            $firstname = $nameParts[0]; // First name
            $lastname = implode(' ', array_slice($nameParts, 1)); // Rest as last name
            // Find or create user
            $user = User::updateOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                ]
            );

            // Log the user in
            Auth::login($user);

            return redirect()->route('home');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Authentication failed!');
        }
    }
}
