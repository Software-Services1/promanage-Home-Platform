<?php

namespace App\Policies;

use App\Models\Leave;
use App\Models\User;

class LeavePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Leave $leave): bool
    {
        return $user->can('view all leaves') || $leave->user_id === $user->id;
    }

    public function create(User $user): bool { return true; } // أي موظف يطلب لنفسه

    public function approve(User $user, Leave $leave): bool { return $user->can('approve leaves'); }
}
