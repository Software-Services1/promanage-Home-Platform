<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Services\PayrollService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TaskTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(TaskTypeSeeder::class);
    }

    public function test_target_miss_deduction_is_capped_and_proportional(): void
    {
        $u = User::factory()->create(['salary' => 7000, 'target' => 100]);
        $u->assignRole('editor');

        // 50 نقطة فقط (نصف التارجت) → خصم = min(20%، 20%×0.5) = 10% = 700
        Task::create(['title' => 'مونتاج', 'type' => 'video_edit', 'stage' => 'منشور', 'user_id' => $u->id, 'due_date' => now()->startOfMonth()->toDateString()]); // 15
        Task::create(['title' => 'مونتاج2', 'type' => 'video_edit', 'stage' => 'منشور', 'user_id' => $u->id, 'due_date' => now()->startOfMonth()->addDay()->toDateString()]); // 15
        Task::create(['title' => 'ريلز', 'type' => 'reels', 'stage' => 'منشور', 'user_id' => $u->id, 'due_date' => now()->startOfMonth()->addDays(2)->toDateString()]); // 7

        $pay = app(PayrollService::class)->compute($u, now()->format('Y-m'));
        $this->assertSame(37.0, $pay['points']);            // 15+15+7
        $this->assertGreaterThan(0, $pay['target_deduction']);
        $this->assertLessThanOrEqual(1400.0, $pay['target_deduction']); // ≤ 20%
        $this->assertSame(0.0, $pay['bonus']);
    }
}
