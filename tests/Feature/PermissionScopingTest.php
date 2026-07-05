<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TaskTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(TaskTypeSeeder::class);
    }

    private function user(string $role): User
    {
        $u = User::factory()->create();
        $u->assignRole($role);
        return $u;
    }

    public function test_designer_sees_only_own_tasks(): void
    {
        $d1 = $this->user('designer');
        $d2 = $this->user('designer');
        Task::create(['title' => 't1', 'type' => 'carousel', 'stage' => 'تصميم', 'user_id' => $d1->id, 'due_date' => now()->toDateString()]);
        Task::create(['title' => 't2', 'type' => 'carousel', 'stage' => 'تصميم', 'user_id' => $d2->id, 'due_date' => now()->toDateString()]);

        $this->assertCount(1, Task::visibleTo($d1)->get());
        $this->assertCount(2, Task::visibleTo($this->user('supervisor'))->get());
    }

    public function test_employee_cannot_edit_others_task_via_direct_request(): void
    {
        $d1 = $this->user('designer');
        $d2 = $this->user('designer');
        $task = Task::create(['title' => 't', 'type' => 'carousel', 'stage' => 'تصميم', 'user_id' => $d2->id, 'due_date' => now()->toDateString()]);

        $this->actingAs($d1)
            ->put(route('tasks.update', $task), ['title' => 'X', 'type' => 'carousel', 'stage' => 'تصميم', 'user_id' => $d2->id, 'due_date' => now()->toDateString()])
            ->assertForbidden();
    }

    public function test_owner_can_update_own_status_within_allowed_stages(): void
    {
        $d = $this->user('designer');
        $task = Task::create(['title' => 't', 'type' => 'carousel', 'stage' => 'تصميم', 'user_id' => $d->id, 'due_date' => now()->toDateString()]);

        $this->actingAs($d)->post(route('tasks.status', $task), ['stage' => 'جاهز'])->assertRedirect();
        $this->assertSame('جاهز', $task->fresh()->stage);

        // حالة غير مسموحة لغير المدير
        $this->actingAs($d)->post(route('tasks.status', $task), ['stage' => 'منشور'])->assertSessionHasErrors('stage');
    }

    public function test_designer_cannot_open_roles_screen(): void
    {
        $this->actingAs($this->user('designer'))->get(route('roles.index'))->assertForbidden();
        $this->actingAs($this->user('admin'))->get(route('roles.index'))->assertOk();
    }
}
