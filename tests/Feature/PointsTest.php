<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Services\PointsService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TaskTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(TaskTypeSeeder::class);
    }

    private function designer(): User
    {
        $u = User::factory()->create(['target' => 100, 'salary' => 6500]);
        $u->assignRole('designer');
        return $u;
    }

    public function test_creative_project_file_gets_bonus(): void
    {
        $svc = app(PointsService::class);
        $t = new Task(['type' => 'project_file', 'stage' => 'منشور', 'is_creative' => true, 'is_late' => false]);
        $this->assertSame(10.0, $svc->taskPoints($t)); // 7 + 3
    }

    public function test_late_delivery_halves_points(): void
    {
        $svc = app(PointsService::class);
        $t = new Task(['type' => 'carousel', 'stage' => 'منشور', 'is_creative' => false, 'is_late' => true]);
        $this->assertSame(2.5, $svc->taskPoints($t)); // 5 / 2
    }

    public function test_reels_idea_counts_only_when_published(): void
    {
        $svc = app(PointsService::class);
        $draft = new Task(['type' => 'reels_idea', 'stage' => 'تصميم', 'is_late' => false]);
        $done  = new Task(['type' => 'reels_idea', 'stage' => 'منشور', 'is_late' => false]);
        $this->assertSame(0.0, $svc->taskPoints($draft));
        $this->assertSame(3.0, $svc->taskPoints($done));
    }

    public function test_monthly_total_is_isolated_per_month(): void
    {
        $svc = app(PointsService::class);
        $d = $this->designer();
        $cur  = now()->format('Y-m');
        $prev = now()->subMonth()->format('Y-m');

        Task::create(['title' => 'أ', 'type' => 'carousel', 'stage' => 'منشور', 'user_id' => $d->id, 'due_date' => now()->startOfMonth()->toDateString()]);
        Task::create(['title' => 'ب', 'type' => 'post_story', 'stage' => 'منشور', 'user_id' => $d->id, 'due_date' => now()->subMonth()->startOfMonth()->toDateString()]);

        $this->assertSame(5.0, $svc->totalPoints($d, $cur));   // الكاروسيل فقط
        $this->assertSame(3.0, $svc->totalPoints($d, $prev));  // البوست فقط
    }
}
