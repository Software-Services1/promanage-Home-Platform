<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /** كتالوج الصلاحيات — مصدر موحّد يُربط بالأدوار. */
    public const PERMISSIONS = [
        'view all content', 'create content', 'update content', 'approve content', 'delete content',
        'upload design', 'update content status', 'add content note',
        'view all tasks', 'create tasks', 'update tasks', 'delete tasks',
        'view all leaves', 'approve leaves',
        'view all salaries',
        'manage users', 'manage maintenance', 'view reports',
        'manage roles', 'manage task types', 'manage settings',
    ];

    /** الصلاحيات لكل دور (admin يحصل على الكل تلقائياً). */
    public const ROLE_MAP = [
        'supervisor' => [
            'view all content', 'create content', 'update content', 'approve content', 'delete content',
            'view all tasks', 'create tasks', 'update tasks', 'delete tasks',
            'view all leaves', 'approve leaves', 'manage maintenance', 'view reports',
        ],
        // مدير: إشراف واسع دون الرواتب وإدارة المستخدمين/الأدوار
        'manager' => [
            'view all content', 'create content', 'update content', 'approve content', 'delete content',
            'view all tasks', 'create tasks', 'update tasks', 'delete tasks',
            'view all leaves', 'approve leaves', 'manage maintenance', 'view reports', 'manage task types',
        ],
        // المصمم/المونتير: يطّلع على كل الصفوف، يغيّر حالة ما أُسند إليه، يضيف ملاحظات، ويرفع/يعدّل التصاميم
        'designer'  => ['view all content', 'view all tasks', 'upload design', 'update content status', 'add content note'],
        'editor'    => ['view all content', 'view all tasks', 'upload design', 'update content status', 'add content note'],
        // كاتب محتوى: ينشئ ويحرّر عناصر الخطة
        'writer'    => ['view all content', 'create content', 'update content', 'add content note'],
        // ناشر محتوى: يرى كل المحتوى ويحدّث حالته (النشر)
        'publisher' => ['view all content', 'update content', 'update content status'],
        // موظف عام: لا صلاحيات واسعة — يرى ما يخصّه فقط
        'employee'  => [],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // admin — كل الصلاحيات
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        foreach (self::ROLE_MAP as $role => $perms) {
            $r = Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            $r->syncPermissions($perms);
        }

        Artisan::call('permission:cache-reset');
    }
}
