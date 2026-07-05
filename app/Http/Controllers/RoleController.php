<?php

namespace App\Http\Controllers;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('id')->get();
        $permissions = Permission::orderBy('name')->get();
        return view('roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:50', Rule::unique('roles', 'name')],
            'permissions'   => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return back()->with('ok', 'تم إنشاء الدور.');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'permissions'   => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        // حماية: لا يُجرّد دور admin من صلاحياته
        if ($role->name === 'admin') {
            return back()->with('ok', 'دور المشرف العام محميّ ولا تُعدّل صلاحياته.');
        }

        $role->syncPermissions($data['permissions'] ?? []);
        app()['cache']->forget(config('permission.cache.key'));

        return back()->with('ok', "تم تحديث صلاحيات دور «{$role->name}».");
    }

    public function destroy(Role $role)
    {
        $protected = array_merge(['admin'], array_keys(RolePermissionSeeder::ROLE_MAP));
        if (in_array($role->name, $protected) || $role->users()->exists()) {
            return back()->with('ok', 'لا يمكن حذف دور أساسي أو دور مُسنَد لمستخدمين.');
        }
        $role->delete();
        return back()->with('ok', 'تم حذف الدور.');
    }
}
