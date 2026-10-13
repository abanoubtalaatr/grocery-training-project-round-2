<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Notifications\DeliverySlaBreachedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client as TwilioClient;

class NotificationService
{
    /**
     * Send OTP via email
     */
    public function sendOtpEmail(string $email, string $otp, string $type): bool
    {
        try {
            $subject = $this->getEmailSubject($type);
            $message = $this->getEmailMessage($otp, $type);

            Mail::raw($message, function ($mail) use ($email, $subject) {
                $mail->to($email)
                    ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send OTP via SMS using Twilio
     */
    public function sendOtpSms(string $phone, string $otp, string $type): bool
    {
        try {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.phone');

            if (! $sid || ! $token || ! $from) {
                Log::error('Twilio SMS configuration is incomplete. Cannot send OTP.');
                return false;
            }

            $message = $this->getSmsMessage($otp, $type);

            $client = $this->createTwilioClient($sid, $token);
            $client->messages->create($phone, [
                'from' => $from,
                'body' => $message,
            ]);

            Log::info('SMS OTP sent to phone ending in ' . substr($phone, -4) . ' (type: ' . $type . ')');

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send OTP SMS: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a Twilio client instance.
     */
    protected function createTwilioClient(string $sid, string $token): mixed
    {
        return new TwilioClient($sid, $token);
    }

    /**
     * Get email subject based on OTP type
     */
    private function getEmailSubject(string $type): string
    {
        return match ($type) {
            'password_reset' => 'Password Reset OTP',
            'password_change' => 'Password Change OTP',
            'email_verification' => 'Email Verification OTP',
            'phone_verification' => 'Phone Verification OTP',
            'mfa_login' => 'Login Verification OTP',
            default => 'Verification OTP',
        };
    }

    /**
     * Get email message based on OTP type
     */
    private function getEmailMessage(string $otp, string $type): string
    {
        $appName = config('app.name');
        $expiryMinutes = config('otp.expiry_minutes', 10);

        $typeText = match ($type) {
            'password_reset' => 'reset your password',
            'email_verification' => 'verify your email',
            'phone_verification' => 'verify your phone',
            'mfa_login' => 'complete your login',
            default => 'verify your account',
        };

        return "Your {$appName} OTP to {$typeText} is: {$otp}\n\n"
            . "This code will expire in {$expiryMinutes} minutes.\n\n"
            . "If you did not request this code, please ignore this email.";
    }

    /**
     * Get SMS message based on OTP type
     */
    private function getSmsMessage(string $otp, string $type): string
    {
        $appName = config('app.name');
        
        return "{$appName}: Your verification code is {$otp}. Valid for " . config('otp.expiry_minutes', 10) . " minutes.";
    }

    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail(string $email, string $username): bool
    {
        try {
            $appName = config('app.name');
            $message = "Welcome to {$appName}, {$username}!\n\n"
                . "Thank you for registering. We're excited to have you on board.\n\n"
                . "Start exploring our fresh meals and categories today!";

            Mail::raw($message, function ($mail) use ($email, $appName) {
                $mail->to($email)
                    ->subject("Welcome to {$appName}!");
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send order confirmation notification
     */
    public function sendOrderConfirmation(User $user, $order)
    {
        if ($this->shouldSendNotification($user, 'order_confirmation')) {
            // $user->notify(new OrderConfirmationNotification($order));
        }
    }

    /**
     * Send order shipped notification
     */
    public function sendOrderShipped(User $user, $order, $trackingInfo)
    {
        if ($this->shouldSendNotification($user, 'order_shipped')) {
            // $user->notify(new OrderShippedNotification($order, $trackingInfo));
        }
    }

    /**
     * Send delivery update notification
     */
    public function sendDeliveryUpdate(User $user, $order, $update)
    {
        if ($this->shouldSendNotification($user, 'delivery_updates')) {
            // $user->notify(new DeliveryUpdateNotification($order, $update));
        }
    }

    /**
     * Send delivery delay notification for breached delivery SLA.
     */
    public function sendDeliverySlaBreached(User $user, Order $order): bool
    {
        try {
            $user->initializeNotificationSettings();

            if (! $this->shouldSendNotification($user, 'delivery_updates')) {
                return false;
            }

            $user->notify(new DeliverySlaBreachedNotification($order));

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send delivery SLA breach notification: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Send weekly discounts notification
     */
    public function sendWeeklyDiscounts(User $user, $discounts)
    {
        if ($this->shouldSendNotification($user, 'weekly_discounts')) {
            // $user->notify(new WeeklyDiscountsNotification($discounts));
        }
    }

    /**
     * Check if notification should be sent based on user preferences
     */
    private function shouldSendNotification(User $user, string $notificationType): bool
    {
        $settings = $user->notificationSettings;
        
        if (!$settings) {
            return false;
        }

        // Check if the specific notification type is enabled
        if (!$settings->{$notificationType}) {
            return false;
        }

        // Check if at least one channel is enabled
        return $settings->email_notifications || $settings->push_notifications || $settings->sms_notifications;
    }

    /**
     * Bulk send notifications to multiple users
     */
    public function bulkSendPromotion(array $userIds, $promotion, string $type = 'seasonal_campaigns')
    {
        $users = User::with('notificationSettings')
            ->whereIn('id', $userIds)
            ->get();

        foreach ($users as $user) {
            if ($this->shouldSendNotification($user, $type)) {
                // Send appropriate notification based on type
                // $user->notify(...);
            }
        }
    }
}
