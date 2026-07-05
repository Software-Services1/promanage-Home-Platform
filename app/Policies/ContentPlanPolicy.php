<?php

namespace App\Policies;

use App\Models\ContentPlan;
use App\Models\User;

class ContentPlanPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool { return true; }

    public function view(User $user, ContentPlan $plan): bool
    {
        return $user->can('view all content') || $plan->assigned_to === $user->id;
    }

    public function create(User $user): bool { return $user->can('create content'); }

    public function update(User $user, ContentPlan $plan): bool { return $user->can('update content'); }

    public function approve(User $user): bool { return $user->can('approve content'); }

    public function delete(User $user, ContentPlan $plan): bool { return $user->can('delete content'); }


    /** رفع التصميم: من يملك صلاحية الرفع وهو مدير محتوى أو صاحب العنصر. */
    public function uploadDesign(User $user, ContentPlan $plan): bool
    {
        return $user->can('upload design') && ($user->can('view all content') || $plan->assigned_to === $user->id);
    }
}
