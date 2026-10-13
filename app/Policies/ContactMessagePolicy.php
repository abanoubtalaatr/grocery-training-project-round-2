<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

class ContactMessagePolicy
{
    /**
     * Determine whether the user can view any contact messages.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['support_agent', 'admin']);
    }

    /**
     * Determine whether the user can view the contact message.
     */
    public function view(User $user, ContactMessage $contactMessage): bool
    {
        return $user->hasRole(['support_agent', 'admin'])
            || ($contactMessage->user_id !== null && $contactMessage->user_id === $user->id);
    }

    /**
     * Determine whether the user can update the contact message.
     */
    public function update(User $user, ContactMessage $contactMessage): bool
    {
        return $user->hasRole(['support_agent', 'admin']);
    }

    /**
     * Determine whether the user can delete the contact message.
     */
    public function delete(User $user, ContactMessage $contactMessage): bool
    {
        return $user->hasRole(['support_agent', 'admin']);
    }
}
