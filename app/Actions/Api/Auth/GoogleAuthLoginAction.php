<?php

namespace App\Actions\Api\Auth;

use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GoogleAuthLoginAction
{
    private const INVALID_GOOGLE_TOKEN_MESSAGE = 'Invalid Google token.';
    private const ALLOWED_GOOGLE_ISSUERS = [
        'accounts.google.com',
        'https://accounts.google.com',
    ];

    public function run(string $idToken, ?string $deviceName = null): array
    {
        $allowedClientIds = $this->allowedGoogleClientIds();
        if (empty($allowedClientIds)) {
            throw new RuntimeException('Google sign-in is not configured.', 503);
        }

        try {
            $client = new GoogleClient;
            $payload = $client->verifyIdToken($idToken);
        } catch (Throwable $e) {
            Log::warning('Google ID token verification failed', ['message' => $e->getMessage()]);
            throw new RuntimeException(self::INVALID_GOOGLE_TOKEN_MESSAGE, 401);
        }

        if (! is_array($payload) || ! $this->isValidGooglePayload($payload, $allowedClientIds)) {
            throw new RuntimeException(self::INVALID_GOOGLE_TOKEN_MESSAGE, 401);
        }

        try {
            $email = strtolower((string) $payload['email']);
            $user = User::where('email', $email)->first();

            if ($user) {
                if (! $user->is_active) {
                    throw new RuntimeException('Your account has been deactivated.', 403);
                }

                $user->fill([
                    'google_id' => $payload['sub'] ?? $user->google_id,
                    'avatar' => $payload['picture'] ?? $user->avatar,
                ]);

                if (! $user->email_verified) {
                    $user->email_verified = true;
                    $user->email_verified_at = now();
                }

                $user->save();
            } else {
                $user = User::create([
                    'username' => $this->uniqueUsernameForGoogle($payload, $email),
                    'email' => $email,
                    'google_id' => $payload['sub'] ?? null,
                    'avatar' => $payload['picture'] ?? null,
                    'password' => Str::random(32),
                    'agree_terms' => true,
                    'email_verified' => true,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]);
            }

            $deviceNameStr = trim((string) ($deviceName ?? 'google_auth'));
            $token = $user->createToken($deviceNameStr !== '' ? $deviceNameStr : 'google_auth')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Wrong number of segments')
                || str_contains($msg, 'JWT')
                || str_contains($msg, 'jwt')) {
                throw new RuntimeException(self::INVALID_GOOGLE_TOKEN_MESSAGE, 401);
            }

            Log::error('Google login failed', ['message' => $msg]);
            throw new RuntimeException('Google sign-in failed. Please try again.', 500);
        }
    }

    private function uniqueUsernameForGoogle(array $payload, string $email): string
    {
        $fromName = Str::slug((string) ($payload['name'] ?? ''));
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
        if (! preg_match('/\p{L}/u', $candidate)) {
            $candidate = Str::limit('user_'.$candidate, User::USERNAME_MAX_LENGTH, '');
        }
        $n = 0;
        while (User::withTrashed()->where('username', $candidate)->exists()) {
            $n++;
            $suffix = (string) $n;
            $candidate = Str::limit($base, User::USERNAME_MAX_LENGTH - strlen($suffix), '').$suffix;
        }

        return Str::limit($candidate, User::USERNAME_MAX_LENGTH, '');
    }

    private function allowedGoogleClientIds(): array
    {
        $clientIds = config('services.google.client_ids', []);
        if (! is_array($clientIds)) {
            $clientIds = [];
        }

        $legacyClientId = config('services.google.client_id');
        if (is_string($legacyClientId) && trim($legacyClientId) !== '') {
            $clientIds[] = $legacyClientId;
        }

        return array_values(array_unique(array_filter(array_map(
            static fn ($id) => is_string($id) ? trim($id) : '',
            $clientIds
        ))));
    }

    private function isValidGooglePayload(array $payload, array $allowedClientIds): bool
    {
        $audience = (string) ($payload['aud'] ?? '');
        $issuer = (string) ($payload['iss'] ?? '');
        $email = (string) ($payload['email'] ?? '');
        $emailVerified = filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $subject = (string) ($payload['sub'] ?? '');
        $expiry = (int) ($payload['exp'] ?? 0);

        return $audience !== ''
            && in_array($audience, $allowedClientIds, true)
            && in_array($issuer, self::ALLOWED_GOOGLE_ISSUERS, true)
            && $email !== ''
            && $emailVerified
            && $subject !== ''
            && $expiry > now()->timestamp;
    }
}
