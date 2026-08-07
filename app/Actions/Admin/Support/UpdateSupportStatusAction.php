<?php

namespace App\Actions\Admin\Support;

use App\Models\ContactMessage;

class UpdateSupportStatusAction
{
    public function run(ContactMessage $support, array $data): ContactMessage
    {
        $support->update([
            'status'      => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? null,
        ]);

        return $support;
    }
}
