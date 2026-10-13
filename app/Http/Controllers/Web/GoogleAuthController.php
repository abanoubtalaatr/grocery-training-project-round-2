<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id')) {
            return redirect()->route('login')->withErrors([
                'login' => 'Google sign-in is not configured. Please try again later.',
            ]);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $email = strtolower($googleUser->getEmail());

            if (! $email) {
                return redirect()->route('login')->withErrors(['login' => 'Google authentication failed.']);
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                if (! $user->is_active) {
                    return redirect()->route('login')->withErrors(['login' => 'Your account has been deactivated.']);
                }

                $user->fill([
                    'google_id' => $googleUser->getId() ?? $user->google_id,
                    'avatar' => $googleUser->getAvatar() ?? $user->avatar,
                ]);

                if (! $user->email_verified) {
                    $user->email_verified = true;
                    $user->email_verified_at = now();
                }

                $user->save();
            } else {
                $user = User::create([
                    'username' => $this->uniqueUsernameForGoogle($googleUser->getName() ?? '', $email),
                    'email' => $email,
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => Str::random(32),
                    'agree_terms' => true,
                    'email_verified' => true,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]);
            }

            Auth::login($user, true);

            return redirect()->intended(route('home'));
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['login' => 'Google sign-in failed. Please try again.']);
        }
    }

    private function uniqueUsernameForGoogle(string $name, string $email): string
    {
        $fromName = Str::slug($name);
        $fromEmail = Str::slug(Str::before($email, '@'));
        $base = 'user';
        if ($fromName !== '') {
            $base = $fromName;
        } elseif ($fromEmail !== '') {
            $base = $fromEmail;
        }
        $base = Str::limit($base, User::USERNAME_MAX_LENGTH - 4, '');
        if ($base === '') {
            $base = 'user';
        }

        $candidate = Str::limit($base, User::USERNAME_MAX_LENGTH, '');
        $n = 0;
        while (User::withTrashed()->where('username', $candidate)->exists()) {
            $n++;
            $suffix = (string) $n;
            $candidate = Str::limit($base, User::USERNAME_MAX_LENGTH - strlen($suffix), '').$suffix;
        }

        return Str::limit($candidate, User::USERNAME_MAX_LENGTH, '');
    }
}
