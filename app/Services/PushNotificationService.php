<?php

namespace App\Services;

use App\Models\PushToken;
use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private $messaging = null;

    public function getMessaging()
    {
        if ($this->messaging !== null) {
            return $this->messaging;
        }

        $credentialsPath = config('services.firebase.credentials');

        if (! $credentialsPath || ! file_exists($credentialsPath)) {
            return null;
        }

        try {
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();

            return $this->messaging;
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase messaging: ' . $e->getMessage());

            return null;
        }
    }

    public function sendToUser(User $user, string $title, string $body, array $data = []): int
    {
        $messaging = $this->getMessaging();
        if (! $messaging) {
            return 0;
        }

        $tokens = $user->pushTokens()->get();
        $sentCount = 0;

        foreach ($tokens as $pushToken) {
            try {
                $message = CloudMessage::withTarget('token', $pushToken->token)
                    ->withNotification(Notification::create($title, $body))
                    ->withData($data);

                $messaging->send($message);
                $pushToken->touchLastUsed();
                $sentCount++;
            } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
                Log::warning('Invalid push token, removing: ' . $pushToken->token . ' - ' . $e->getMessage());
                $pushToken->delete();
            } catch (\Exception $e) {
                Log::error('Push notification failed for token: ' . $e->getMessage());
            }
        }

        return $sentCount;
    }

    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        $messaging = $this->getMessaging();
        if (! $messaging) {
            return false;
        }

        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $messaging->send($message);

            return true;
        } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
            Log::warning('Invalid push token: ' . $e->getMessage());
            PushToken::where('token', $token)->delete();

            return false;
        } catch (\Exception $e) {
            Log::error('Push notification send failed: ' . $e->getMessage());

            return false;
        }
    }

    public function isConfigured(): bool
    {
        $credentialsPath = config('services.firebase.credentials');

        return $credentialsPath && file_exists($credentialsPath);
    }
}
