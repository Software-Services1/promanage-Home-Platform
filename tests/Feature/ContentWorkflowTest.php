<?php

namespace Tests\Feature;

use App\Models\ContentPlan;
use App\Models\User;
use App\Services\PointsService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TaskTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(TaskTypeSeeder::class);
    }

    public function test_ordered_handoff_and_points_per_designer(): void
    {
        $svc = app(PointsService::class);
        $month = now()->format('Y-m');

        $d1 = User::factory()->create(); $d1->assignRole('designer');
        $d2 = User::factory()->create(); $d2->assignRole('editor');

        $plan = ContentPlan::create([
            'platform' => 'إنستقرام', 'plan_date' => now()->startOfMonth()->toDateString(),
            'content_type' => 'تعليمي', 'post_type' => 'منشور', 'status' => 'قيد التصميم',
            'approval_state' => 'approved',
        ]);
        $plan->designers()->attach($d1->id, ['position' => 1, 'work_type' => 'carousel', 'step_status' => 'قيد العمل']);
        $plan->designers()->attach($d2->id, ['position' => 2, 'work_type' => 'video_edit', 'step_status' => 'بانتظار الدور']);
        $plan->load('designers');

        // الدور الأول لـ d1، ولا نقاط قبل الإكمال
        $this->assertSame($d1->id, $plan->currentDesigner()->id);
        $this->assertSame(0.0, $svc->directPoints($d1, $month));

        // d1 يُكمل ويُسلّم
        $this->actingAs($d1)->post(route('content.advance', $plan))->assertRedirect();
        $plan->refresh()->load('designers');
        $this->assertSame($d2->id, $plan->currentDesigner()->id);
        $this->assertSame(5.0, app(PointsService::class)->directPoints($d1, $month)); // كاروسيل

        // d2 يُكمل
        $this->actingAs($d2)->post(route('content.advance', $plan))->assertRedirect();
        $this->assertSame(15.0, app(PointsService::class)->directPoints($d2, $month)); // مونتاج
        $this->assertSame(20.0, app(PointsService::class)->contentPoints($plan->fresh()->load('designers')));
    }
}
