<?php

namespace App\Policies;

use App\Models\ScheduledPayment;
use App\Models\User;

class ScheduledPaymentPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ScheduledPayment $scheduledPayment): bool
    {
        return $user->id === $scheduledPayment->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ScheduledPayment $scheduledPayment): bool
    {
        return $user->id === $scheduledPayment->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScheduledPayment $scheduledPayment): bool
    {
        return $user->id === $scheduledPayment->user_id;
    }
}
