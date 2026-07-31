<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Sanctum personal access tokens alongside Passport's HasApiTokens.
 *
 * Sanctum's HasApiTokens trait cannot coexist with Passport's trait
 * (both define createToken / tokens / withAccessToken). This trait
 * provides Sanctum token creation + a place to hang the current token
 * for our custom sanctum.auth middleware.
 */
trait CreatesSanctumTokens
{
    protected ?PersonalAccessToken $currentSanctumToken = null;

    public function sanctumTokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    /**
     * @param  array<int, string>  $abilities
     */
    public function createSanctumToken(string $name, array $abilities = ['*']): NewAccessToken
    {
        $plainTextToken = Str::random(40);

        $token = $this->sanctumTokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
        ]);

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }

    public function withSanctumAccessToken(?PersonalAccessToken $accessToken): static
    {
        $this->currentSanctumToken = $accessToken;

        return $this;
    }

    public function currentSanctumToken(): ?PersonalAccessToken
    {
        return $this->currentSanctumToken;
    }
}
