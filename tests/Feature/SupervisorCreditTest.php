<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use App\Services\PointsService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TaskTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorCreditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(TaskTypeSeeder::class);
    }

    public function test_dynamic_share_and_credit_modes(): void
    {
        $svc = app(PointsService::class);
        $month = now()->format('Y-m');

        $sup = User::factory()->create(['supervisor_share' => 50]);
        $sup->assignRole('supervisor');
        $des = User::factory()->create();
        $des->assignRole('designer');

        // مهمة متابَعة من المشرف (5 نقاط) + مهمة غير متابَعة (3 نقاط)
        Task::create(['title' => 'A', 'type' => 'carousel', 'stage' => 'منشور', 'user_id' => $des->id, 'supervisor_id' => $sup->id, 'due_date' => now()->startOfMonth()->toDateString()]);
        Task::create(['title' => 'B', 'type' => 'post_story', 'stage' => 'منشور', 'user_id' => $des->id, 'due_date' => now()->startOfMonth()->addDay()->toDateString()]);

        // الوضع التلقائي: 50% × (5+3) = 4
        Setting::put('supervisor_credit_mode', 'auto');
        $this->assertSame(4.0, $svc->totalPoints($sup, $month));

        // وضع الإسناد: 50% × (5 فقط) = 2.5
        Setting::put('supervisor_credit_mode', 'assigned');
        $this->assertSame(2.5, $svc->totalPoints($sup, $month));
    }
}
