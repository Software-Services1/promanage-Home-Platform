<?php

namespace Tests\Feature;

use App\Models\ContentPlan;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use App\Services\PointsService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TaskTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointsFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(TaskTypeSeeder::class);
    }

    /** البند 2: المهمة الإبداعية تُضيف نقاطاً حتى للأنواع بلا بونص مخصّص. */
    public function test_creative_bonus_applies_to_all_types(): void
    {
        $svc = app(PointsService::class);
        Setting::put('creative_bonus_pct', 50);

        $d = User::factory()->create(); $d->assignRole('designer');

        // كاروسيل = 5 نقاط، بلا بونص مخصّص → إبداعي يضيف 50% = 7.5
        $t = Task::create(['title'=>'A','type'=>'carousel','stage'=>'منشور','user_id'=>$d->id,
            'is_creative'=>true,'due_date'=>now()->startOfMonth()->toDateString()]);
        $t->assignees()->sync([$d->id => ['type'=>'carousel']]);
        $t->load('assignees');

        $this->assertSame(7.5, $svc->taskPointsForUser($t, $d->id));

        // ملف مشروع لديه بونص مخصّص 3 → 7+3 = 10 (يبقى كما هو)
        $t2 = Task::create(['title'=>'B','type'=>'project_file','stage'=>'منشور','user_id'=>$d->id,
            'is_creative'=>true,'due_date'=>now()->startOfMonth()->toDateString()]);
        $t2->assignees()->sync([$d->id => ['type'=>'project_file']]);
        $t2->load('assignees');
        $this->assertSame(10.0, $svc->taskPointsForUser($t2, $d->id));
    }

    /** البند 1: نقاط خطة المحتوى تُضاف للتارجت عند بلوغ العنصر حالة مُعتمَدة. */
    public function test_content_points_credit_on_completed_status(): void
    {
        $svc = app(PointsService::class);
        $month = now()->format('Y-m');

        $d = User::factory()->create(); $d->assignRole('designer');

        $plan = ContentPlan::create([
            'platform'=>'إنستقرام','company_name'=>'إيزي هوم','plan_date'=>now()->startOfMonth()->toDateString(),
            'content_type'=>'تعليمي','post_type'=>'منشور','status'=>'قيد التصميم','work_type'=>'carousel',
            'assigned_to'=>$d->id,
        ]);
        $plan->load('designers');

        // قيد التصميم → لا نقاط
        $this->assertSame(0.0, $svc->directPoints($d, $month));

        // الحالة تصبح «تم النشر» → تُحتسب النقاط دون الحاجة لزر التسليم
        $plan->update(['status' => 'تم النشر']);
        $this->assertSame(5.0, app(PointsService::class)->directPoints($d->fresh(), $month));
    }
}
