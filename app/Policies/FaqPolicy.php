<?php

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

class FaqPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Faq $faq): bool
    {
        return true; // Anyone can view active FAQs
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Faq $faq): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Faq $faq): bool
    {
        return $user->is_admin;
    }
}
