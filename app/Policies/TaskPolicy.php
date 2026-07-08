<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool { return true; }

    /** أحد المشاركين في المهمة أو من يملك رؤية الكل. */
    private function isAssignee(User $user, Task $task): bool
    {
        if ($task->user_id === $user->id) {
            return true;
        }
        return $task->relationLoaded('assignees')
            ? $task->assignees->contains('id', $user->id)
            : $task->assignees()->where('users.id', $user->id)->exists();
    }

    public function view(User $user, Task $task): bool
    {
        return $user->can('view all tasks') || $this->isAssignee($user, $task) || $task->supervisor_id === $user->id;
    }

    public function create(User $user): bool { return $user->can('create tasks'); }

    public function update(User $user, Task $task): bool { return $user->can('update tasks'); }

    public function delete(User $user, Task $task): bool { return $user->can('delete tasks'); }

    /** تحديث حالة التنفيذ — لأيّ مشارك في المهمة، وللمدراء بكل الحالات. */
    public function updateStatus(User $user, Task $task): bool
    {
        return $user->can('update tasks') || $this->isAssignee($user, $task);
    }
}
