<?php

namespace App\Services;

use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    /**
     * Generate a new OTP
     */
    public function generate(string $identifier, string $type): string
    {
        $identifier = $this->normalizeIdentifier($identifier);

        Otp::where('identifier', $identifier)
            ->where('type', $type)
            ->where('is_used', false)
            ->delete();

        $otpCode = $this->generateOtpCode();

        Otp::create([
            'identifier' => $identifier,
            'otp' => Hash::make($otpCode),
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes(config('otp.expiry_minutes', 3)),
        ]);

        return $otpCode;
    }

    /**
     * Verify an OTP
     */
    public function verify(string $identifier, string $otpCode, string $type): bool
    {
        $identifier = $this->normalizeIdentifier($identifier);
        $otpCode = $this->normalizeOtpInput($otpCode);

        $otp = $this->findValidOtp($identifier, $otpCode, $type);

        if (! $otp) {
            return false;
        }

        $otp->markAsUsed();

        return true;
    }

    /**
     * Check if OTP exists and is valid
     */
    public function isValid(string $identifier, string $otpCode, string $type): bool
    {
        $identifier = $this->normalizeIdentifier($identifier);
        $otpCode = $this->normalizeOtpInput($otpCode);

        return $this->findValidOtp($identifier, $otpCode, $type) !== null;
    }

    /**
     * Find a valid OTP by checking hashed values.
     * Since generate() deletes old OTPs first, there should be very few valid OTPs per identifier/type.
     */
    private function findValidOtp(string $identifier, string $otpCode, string $type): ?Otp
    {
        $validOtps = Otp::where('identifier', $identifier)
            ->where('type', $type)
            ->valid()
            ->get();

        foreach ($validOtps as $otp) {
            if (Hash::check($otpCode, $otp->otp)) {
                return $otp;
            }
        }

        return null;
    }

    /**
     * Generate a random numeric OTP of configured length.
     */
    private function generateOtpCode(): string
    {
        $fixed = config('otp.fixed_code');
        if (is_string($fixed) && $fixed !== '' && app()->environment('local', 'testing')) {
            return $fixed;
        }

        $length = max(4, min(8, (int) config('otp.length', 4)));

        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= (string) random_int(0, 9);
        }

        return $code;
    }

    /**
     * Normalize identifier so email/OTP lookups match across requests.
     */
    private function normalizeIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return strtolower($identifier);
        }

        return preg_replace('/\s+/', '', $identifier);
    }

    /**
     * Normalize OTP from JSON/form (digits only; strips spaces and separators).
     */
    private function normalizeOtpInput(string|int $otpCode): string
    {
        return preg_replace('/\D/', '', (string) $otpCode);
    }

    /**
     * Clean up expired OTPs
     */
    public function cleanupExpired(): int
    {
        return Otp::where('expires_at', '<', Carbon::now())->delete();
    }
}
