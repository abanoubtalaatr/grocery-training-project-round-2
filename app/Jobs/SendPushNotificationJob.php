<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\FcmPushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public User $user,
        public string $title,
        public string $body,
        public array $data = []
    ) {}

    public function handle(): void
    {
        $settings = $this->user->notificationSettings;

        if (! $settings || ! $settings->push_notifications) {
            return;
        }

        if ($this->user->pushTokens()->count() === 0) {
            return;
        }

        $notification = new class($this->title, $this->body, $this->data) extends \Illuminate\Notifications\Notification {
            public function __construct(
                public string $title,
                public string $body,
                public array $data
            ) {}

            public function via(object $notifiable): array
            {
                return [FcmPushChannel::class];
            }

            public function toArray(object $notifiable): array
            {
                return array_merge([
                    'title' => $this->title,
                    'body' => $this->body,
                ], $this->data);
            }
        };

        $this->user->notify($notification);
    }

    public function failed(\Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error('SendPushNotificationJob failed: ' . $exception->getMessage());
    }
}
