<?php $__env->startSection('title','إدارة المستخدمين'); ?>
<?php $__env->startSection('content'); ?>
<?php $roleOptions = ['admin'=>'مشرف عام','supervisor'=>'مشرف المحتوى','designer'=>'مصمم جرافيك','editor'=>'مونتير ومصمم']; ?>
<div x-data="{ open:false, mode:'create', form:{} }">
  <div class="flex items-center justify-between mb-4">
    <div class="text-sm text-muted"><?php echo e($users->count()); ?> موظفين</div>
    <button x-on:click="mode='create'; form={role:'designer',salary:0,target:0,supervisor_share:30,is_active:true}; open=true"
            class="inline-flex items-center gap-2 bg-brand hover:bg-brandd text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-lift">
      <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> إضافة موظف
    </button>
  </div>

  <div class="bg-white rounded-2xl border border-line shadow-soft overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-right text-muted text-xs border-b border-line bg-canvas/60">
        <th class="p-4 font-semibold">الموظف</th><th class="p-4 font-semibold">الدور</th>
        <th class="p-4 font-semibold">التارجت</th><th class="p-4 font-semibold">تاريخ الانضمام</th>
        <th class="p-4 font-semibold">الحالة</th><th class="p-4"></th></tr></thead>
      <tbody>
      <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="border-b border-line/60 hover:bg-canvas/60">
          <td class="p-4">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full grid place-items-center text-white font-bold ff-display" style="background:<?php echo e($avatarColor($u->id)); ?>"><?php echo e(mb_substr($u->name,0,1)); ?></div>
              <div><div class="font-semibold"><?php echo e($u->name); ?></div><div class="text-[11px] text-muted"><?php echo e($u->email); ?></div></div>
            </div>
          </td>
          <td class="p-4"><span class="text-xs font-semibold bg-violet-50 text-brand px-2 py-1 rounded-lg"><?php echo e($roleOptions[$u->primaryRole()] ?? '—'); ?></span></td>
          <td class="p-4 tnum text-muted"><?php echo e($u->target ?: '—'); ?></td>
          <td class="p-4 tnum text-muted"><?php echo e(optional($u->join_date)->format('Y-m-d') ?? '—'); ?></td>
          <td class="p-4">
            <?php if($u->is_active): ?><span class="text-emerald-700 text-xs font-semibold">نشط</span>
            <?php else: ?><span class="text-rose-500 text-xs font-semibold">معطّل</span><?php endif; ?>
          </td>
          <td class="p-4">
            <div class="flex items-center gap-1 justify-end">
              <button x-on:click="mode='edit'; form={ id:<?php echo e($u->id); ?>, name:<?php echo \Illuminate\Support\Js::from($u->name)->toHtml() ?>, email:<?php echo \Illuminate\Support\Js::from($u->email)->toHtml() ?>, role:<?php echo \Illuminate\Support\Js::from($u->primaryRole())->toHtml() ?>, salary:<?php echo e((float)$u->salary); ?>, target:<?php echo e((int)$u->target); ?>, supervisor_share:<?php echo e((float)$u->supervisor_share); ?>, join_date:<?php echo \Illuminate\Support\Js::from(optional($u->join_date)->format('Y-m-d'))->toHtml() ?> }; open=true"
                      class="w-8 h-8 rounded-lg grid place-items-center text-muted hover:text-brand hover:bg-violet-50" title="تعديل">
                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></button>
              <form method="POST" action="<?php echo e(route('users.reset',$u)); ?>"><?php echo csrf_field(); ?>
                <button class="w-8 h-8 rounded-lg grid place-items-center text-muted hover:text-golddk hover:bg-amber-50" title="إعادة كلمة المرور">
                  <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5"/></svg></button>
              </form>
              <form method="POST" action="<?php echo e(route('users.toggle',$u)); ?>"><?php echo csrf_field(); ?>
                <button class="w-8 h-8 rounded-lg grid place-items-center text-muted hover:text-rose-600 hover:bg-rose-50" title="<?php echo e($u->is_active ? 'تعطيل':'تفعيل'); ?>">
                  <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M18.36 6.64a9 9 0 1 1-12.73 0M12 2v10"/></svg></button>
              </form>
              <?php if($u->id !== auth()->id()): ?>
                <form method="POST" action="<?php echo e(route('users.destroy',$u)); ?>" onsubmit="return confirm('حذف المستخدم <?php echo e($u->name); ?> نهائياً؟')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                  <button class="w-8 h-8 rounded-lg grid place-items-center text-muted hover:text-rose-700 hover:bg-rose-100" title="حذف">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg></button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>

  <!-- نافذة الإضافة/التعديل -->
  <div x-cloak x-show="open" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="open=false"></div>
    <form method="POST" x-bind:action="mode==='create' ? '<?php echo e(route('users.store')); ?>' : '/users/'+form.id"
          class="relative bg-white rounded-2xl shadow-lift w-full max-w-md p-5 space-y-4">
      <?php echo csrf_field(); ?>
      <template x-if="mode==='edit'"><input type="hidden" name="_method" value="PUT"></template>
      <h3 class="ff-display font-bold text-lg" x-text="mode==='create' ? 'إضافة موظف' : 'تعديل موظف'"></h3>
      <div><label class="text-xs font-semibold text-muted mb-1.5 block">الاسم</label>
        <input name="name" x-model="form.name" required class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
      <div><label class="text-xs font-semibold text-muted mb-1.5 block">البريد</label>
        <input name="email" type="email" x-model="form.email" required class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
      <div><label class="text-xs font-semibold text-muted mb-1.5 block">كلمة المرور <span class="text-muted" x-show="mode==='edit'">(اتركها فارغة لعدم التغيير)</span></label>
        <input name="password" type="password" x-bind:required="mode==='create'" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-xs font-semibold text-muted mb-1.5 block">الدور</label>
          <select name="role" x-model="form.role" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">
            <?php $__currentLoopData = $roleOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>"><?php echo e($v); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select></div>
        <div><label class="text-xs font-semibold text-muted mb-1.5 block">التارجت الشهري</label>
          <input name="target" type="number" x-model="form.target" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
        <div x-show="['supervisor','manager'].includes(form.role)">
          <label class="text-xs font-semibold text-muted mb-1.5 block">نسبة نقاط الإشراف ٪</label>
          <input name="supervisor_share" type="number" step="0.01" min="0" max="100" x-model="form.supervisor_share" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
        <div><label class="text-xs font-semibold text-muted mb-1.5 block">الراتب (سرّي)</label>
          <input name="salary" type="number" step="0.01" x-model="form.salary" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
        <div><label class="text-xs font-semibold text-muted mb-1.5 block">تاريخ الانضمام</label>
          <input name="join_date" type="date" x-model="form.join_date" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
      </div>
      <div class="flex gap-2 justify-end pt-2">
        <button type="button" x-on:click="open=false" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">حفظ</button>
      </div>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\markting\promanage\resources\views/users/index.blade.php ENDPATH**/ ?>