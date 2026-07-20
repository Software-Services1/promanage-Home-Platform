<?php

namespace Tests\Feature;

use App\Models\TaskType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTypeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $u = User::factory()->create();
        $u->assignRole('admin');
        return $u;
    }

    /** الحالة التي كانت تُنتج خطأ 500: تسمية عربية بلا مفتاح، ومكافأة فارغة، ومربّعات غير محدَّدة. */
    public function test_creates_type_with_arabic_label_and_no_key(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('tasktypes.store'), [
            'label'  => 'تصميم بوست إعلاني',
            'points' => 8,
            // bonus / category / checkboxes غير مُرسَلة إطلاقاً
        ])->assertRedirect()->assertSessionHasNoErrors();

        $type = TaskType::firstWhere('label', 'تصميم بوست إعلاني');
        $this->assertNotNull($type);
        $this->assertNotSame('', $type->key);
        $this->assertSame(8, $type->points);
        $this->assertSame(0, $type->bonus);
        $this->assertSame('general', $type->category);
        $this->assertFalse($type->counts_when_published);
        $this->assertFalse($type->is_active); // لم يُرسَل المربّع
    }

    /** مفاتيح فريدة رغم تطابق التسميات العربية. */
    public function test_generates_unique_keys_for_duplicate_arabic_labels(): void
    {
        $admin = $this->admin();

        foreach ([1, 2] as $i) {
            $this->actingAs($admin)->post(route('tasktypes.store'), [
                'label' => 'ريلز', 'points' => 10, 'is_active' => 1,
            ])->assertRedirect();
        }

        $keys = TaskType::where('label', 'ريلز')->pluck('key');
        $this->assertCount(2, $keys);
        $this->assertCount(2, $keys->unique());
    }

    /** التعديل يسمح بتعطيل النوع (المربّع غير المحدَّد يعني false). */
    public function test_update_can_deactivate_type(): void
    {
        $admin = $this->admin();
        $type = TaskType::create([
            'key' => 'demo', 'label' => 'تجريبي', 'points' => 5,
            'bonus' => 0, 'category' => 'general', 'is_active' => true,
        ]);

        $this->actingAs($admin)->put(route('tasktypes.update', $type), [
            'label' => 'تجريبي', 'points' => 5, // is_active غير مُرسَل
        ])->assertRedirect();

        $this->assertFalse($type->fresh()->is_active);
        $this->assertSame('demo', $type->fresh()->key); // المفتاح ثابت
    }
}
