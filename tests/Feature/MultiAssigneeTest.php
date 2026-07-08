<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Services\PointsService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TaskTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiAssigneeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(TaskTypeSeeder::class);
    }

    public function test_each_designer_gets_points_by_their_own_work_type(): void
    {
        $svc = app(PointsService::class);
        $month = now()->format('Y-m');

        $d1 = User::factory()->create(); $d1->assignRole('designer'); // كاروسيل 5
        $d2 = User::factory()->create(); $d2->assignRole('editor');   // مونتاج 15

        $task = Task::create([
            'title' => 'مهمة مشتركة', 'type' => 'carousel', 'stage' => 'منشور',
            'user_id' => $d1->id, 'due_date' => now()->startOfMonth()->toDateString(),
        ]);
        // مزامنة مصمّمَين بنوعين مختلفين
        $task->assignees()->sync([
            $d1->id => ['type' => 'carousel'],   // 5
            $d2->id => ['type' => 'video_edit'], // 15
        ]);
        $task->load('assignees');

        $this->assertSame(5.0, $svc->taskPointsForUser($task, $d1->id));
        $this->assertSame(15.0, $svc->taskPointsForUser($task, $d2->id));
        $this->assertSame(20.0, $svc->taskPoints($task)); // مجموع المشاركين

        $this->assertSame(5.0, $svc->directPoints($d1, $month));
        $this->assertSame(15.0, $svc->directPoints($d2, $month));
    }
}
