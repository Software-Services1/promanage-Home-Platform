<?php

namespace Tests\Feature;

use App\Models\ContentPlan;
use App\Models\Setting;
use App\Models\User;
use App\Services\PointsService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TaskTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentPointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(TaskTypeSeeder::class);
    }

    public function test_content_row_points_credit_designer_and_supervisor(): void
    {
        $svc = app(PointsService::class);
        $month = now()->format('Y-m');

        $designer = User::factory()->create();
        $designer->assignRole('designer');
        $sup = User::factory()->create(['supervisor_share' => 50]);
        $sup->assignRole('supervisor');

        // عنصر منشور بنوع كاروسيل (5) — يُحتسب
        ContentPlan::create([
            'platform' => 'إنستقرام', 'plan_date' => now()->startOfMonth()->toDateString(),
            'content_type' => 'تعليمي', 'post_type' => 'منشور', 'work_type' => 'carousel',
            'status' => 'تم النشر', 'assigned_to' => $designer->id, 'supervisor_id' => $sup->id,
        ]);
        // عنصر ما زال فكرة — لا يُحتسب
        ContentPlan::create([
            'platform' => 'فيسبوك', 'plan_date' => now()->startOfMonth()->toDateString(),
            'content_type' => 'ترفيهي', 'post_type' => 'منشور', 'work_type' => 'post_approved',
            'status' => 'فكرة', 'assigned_to' => $designer->id,
        ]);

        $this->assertSame(5.0, $svc->directPoints($designer, $month));

        Setting::put('supervisor_credit_mode', 'assigned');
        $this->assertSame(2.5, $svc->supervisorCredit($sup, $month)); // 50% × 5
    }
}
