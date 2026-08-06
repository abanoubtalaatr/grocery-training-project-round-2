<?php

namespace App\Actions\Api\Contact;

use App\Models\ContactMessage;

class GetContactStatisticsAction
{
    public function run(): array
    {
        $total = ContactMessage::count();
        $new = ContactMessage::new()->count();
        $read = ContactMessage::read()->count();
        $replied = ContactMessage::replied()->count();
        $spam = ContactMessage::spam()->count();

        $monthlyStats = ContactMessage::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            COUNT(*) as total,
            SUM(CASE WHEN status = "new" THEN 1 ELSE 0 END) as new,
            SUM(CASE WHEN status = "replied" THEN 1 ELSE 0 END) as replied
        ')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'total' => $total,
            'new' => $new,
            'read' => $read,
            'replied' => $replied,
            'spam' => $spam,
            'monthly_stats' => $monthlyStats,
        ];
    }
}
