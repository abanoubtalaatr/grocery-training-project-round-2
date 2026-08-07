<?php

namespace App\Services;

use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactService
{
    /**
     * Submit contact message and send notifications.
     */
    public function submitMessage(array $data, string $ipAddress, string $userAgent): ?ContactMessage
    {
        if ($this->isSpam($data['message'] ?? '')) {
            return null;
        }

        $data['ip_address'] = $ipAddress;
        $data['user_agent'] = $userAgent;

        $contactMessage = ContactMessage::create($data);

        $this->sendEmails($contactMessage);

        return $contactMessage;
    }

    /**
     * Get paginated contact messages based on filters.
     */
    public function getFilteredMessages(array $filters): LengthAwarePaginator
    {
        $query = ContactMessage::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%")
                    ->orWhere('message', 'LIKE', "%{$search}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $perPage = $filters['per_page'] ?? 20;

        return $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
    }

    /**
     * Show contact message and mark as read if new.
     */
    public function showMessage(ContactMessage $contactMessage): ContactMessage
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->markAsRead();
        }

        return $contactMessage;
    }

    /**
     * Update message status.
     */
    public function updateStatus(ContactMessage $contactMessage, array $data): ContactMessage
    {
        $contactMessage->update([
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? null,
        ]);

        return $contactMessage;
    }

    /**
     * Delete a contact message.
     */
    public function deleteMessage(ContactMessage $contactMessage): bool
    {
        return $contactMessage->delete();
    }

    /**
     * Get dashboard contact statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total' => ContactMessage::count(),
            'new' => ContactMessage::new()->count(),
            'read' => ContactMessage::read()->count(),
            'replied' => ContactMessage::replied()->count(),
            'spam' => ContactMessage::spam()->count(),
            'monthly_stats' => ContactMessage::selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as total,
                SUM(CASE WHEN status = "new" THEN 1 ELSE 0 END) as new,
                SUM(CASE WHEN status = "replied" THEN 1 ELSE 0 END) as replied
            ')
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get(),
        ];
    }

    /**
     * Spam detection logic.
     */
    private function isSpam(string $message): bool
    {
        $spamKeywords = [
            'viagra', 'casino', 'loan', 'debt', 'free money',
            'work from home', 'make money fast', 'click here',
        ];

        $message = strtolower($message);

        foreach ($spamKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle sending email notifications.
     */
    private function sendEmails(ContactMessage $contactMessage): void
    {
        try {
            Mail::to(config('mail.admin_email', 'admin@example.com'))
                ->send(new ContactMessageReceived($contactMessage));

            Mail::to($contactMessage->email)
                ->send(new ContactAutoReply($contactMessage));
        } catch (\Exception $e) {
            Log::error('Failed to send contact email: ' . $e->getMessage());
        }
    }
}