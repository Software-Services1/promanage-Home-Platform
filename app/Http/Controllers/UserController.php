<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Models\MaintenanceItem;
use App\Models\Leave;
use App\Models\ContentPlan;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->orderBy('id')->get();
        return view('users.index', compact('users'));
    }

    public function store(UserRequest $request)
    {
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'salary'    => $request->salary ?? 0,
            'target'    => $request->target ?? 0,
            'supervisor_share' => $request->supervisor_share ?? 30,
            'join_date' => $request->join_date,
            'is_active' => true,
        ]);
        $user->syncRoles([$request->role]);

        return back()->with('ok', 'تمت إضافة الموظف.');
    }

    public function update(UserRequest $request, User $user)
    {
        $user->update([
            'name'      => $request->name,
            'email'     => $request->email,
            'salary'    => $request->salary ?? $user->salary,
            'target'    => $request->target ?? 0,
            'supervisor_share' => $request->supervisor_share ?? $user->supervisor_share,
            'join_date' => $request->join_date,
        ]);
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }
        $user->syncRoles([$request->role]);

        return back()->with('ok', 'تم حفظ التعديلات.');
    }

    public function toggle(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);
        return back()->with('ok', $user->is_active ? 'تم تفعيل الحساب.' : 'تم تعطيل الحساب.');
    }

    public function resetPassword(User $user)
    {
        $user->update(['password' => Hash::make('password')]);
        return back()->with('ok', "تمت إعادة كلمة مرور {$user->name} إلى الافتراضية.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('ok', 'لا يمكنك حذف حسابك الحالي.');
        }
        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return back()->with('ok', 'لا يمكن حذف آخر مشرف عام في النظام.');
        }
        if (Task::where('user_id', $user->id)->exists() || ContentPlan::where('assigned_to', $user->id)->exists()) {
            return back()->with('ok', 'لا يمكن حذف مستخدم لديه مهام أو عناصر محتوى مُسنَدة إليه — أعد إسنادها أولاً.');
        }

        // فكّ ارتباطات الإشراف وحذف السجلّات التابعة الآمنة
        Task::where('supervisor_id', $user->id)->update(['supervisor_id' => null]);
        ContentPlan::where('supervisor_id', $user->id)->update(['supervisor_id' => null]);
        Leave::where('user_id', $user->id)->delete();
        MaintenanceItem::where('user_id', $user->id)->delete();

        $user->delete();

        return back()->with('ok', 'تم حذف المستخدم.');
    }
}
