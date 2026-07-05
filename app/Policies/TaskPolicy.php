<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /** المدير العام يتجاوز كل الفحوص. */
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool { return true; } // مُقيّد بالاستعلام

    public function view(User $user, Task $task): bool
    {
        return $user->can('view all tasks') || $task->user_id === $user->id;
    }

    public function create(User $user): bool { return $user->can('create tasks'); }

    /** التعديل الكامل للبيانات الأساسية — للمدراء فقط. */
    public function update(User $user, Task $task): bool { return $user->can('update tasks'); }

    public function delete(User $user, Task $task): bool { return $user->can('delete tasks'); }

    /** تحديث حالة التنفيذ — يسمح به لصاحب المهمة أيضاً. */
    public function updateStatus(User $user, Task $task): bool
    {
        return $user->can('update tasks') || $task->user_id === $user->id;
    }
}
