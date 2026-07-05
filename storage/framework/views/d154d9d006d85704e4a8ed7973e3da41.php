<?php $__env->startSection('title','الأدوار والصلاحيات'); ?>
<?php $__env->startSection('content'); ?>
<?php
  $permLabels = [
    'view all content'=>'رؤية كل المحتوى','create content'=>'إنشاء محتوى','update content'=>'تعديل المحتوى','approve content'=>'اعتماد المحتوى','delete content'=>'حذف المحتوى','upload design'=>'رفع التصاميم','update content status'=>'تغيير حالة المحتوى','add content note'=>'إضافة ملاحظة',
    'view all tasks'=>'رؤية كل المهام','create tasks'=>'إنشاء المهام','update tasks'=>'تعديل المهام','delete tasks'=>'حذف المهام',
    'view all leaves'=>'رؤية كل الإجازات','approve leaves'=>'اعتماد الإجازات','view all salaries'=>'رؤية كل الرواتب',
    'manage users'=>'إدارة المستخدمين','manage maintenance'=>'إدارة الصيانة','view reports'=>'عرض التقارير',
    'manage roles'=>'إدارة الأدوار','manage task types'=>'إدارة أنواع المهام',
  ];
  $roleLabelsMap = ['admin'=>'مشرف عام','manager'=>'مدير','supervisor'=>'مشرف','designer'=>'مصمم','editor'=>'مونتير','writer'=>'كاتب محتوى','publisher'=>'ناشر محتوى','employee'=>'موظف'];
?>
<div x-data="{ create:false, edit:null }">
  <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
    <div class="text-sm text-muted"><?php echo e($roles->count()); ?> أدوار • <?php echo e($permissions->count()); ?> صلاحية</div>
    <button x-on:click="create=true" class="inline-flex items-center gap-2 bg-brand hover:bg-brandd text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-lift">
      <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> دور جديد</button>
  </div>

  <div class="grid md:grid-cols-2 gap-4">
    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="bg-white rounded-2xl border border-line shadow-soft p-5">
        <div class="flex items-center justify-between mb-3">
          <div>
            <div class="ff-display font-bold text-lg"><?php echo e($roleLabelsMap[$role->name] ?? $role->name); ?></div>
            <div class="text-[11px] text-muted"><?php echo e($role->name); ?> • <?php echo e($role->permissions->count()); ?> صلاحية</div>
          </div>
          <div class="flex gap-1">
            <button x-on:click="edit=<?php echo e($role->id); ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-violet-50 text-brand hover:bg-violet-100">تعديل الصلاحيات</button>
            <?php if($role->name!=='admin'): ?>
              <form method="POST" action="<?php echo e(route('roles.destroy',$role)); ?>" onsubmit="return confirm('حذف هذا الدور؟')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-600 hover:bg-rose-100">حذف</button></form>
            <?php endif; ?>
          </div>
        </div>
        <div class="flex flex-wrap gap-1.5">
          <?php $__empty_1 = true; $__currentLoopData = $role->permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <span class="text-[11px] bg-canvas border border-line rounded-lg px-2 py-1"><?php echo e($permLabels[$p->name] ?? $p->name); ?></span>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <span class="text-xs text-muted">لا توجد صلاحيات (يرى ما يخصّه فقط).</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- نافذة تعديل صلاحيات الدور -->
      <div x-cloak x-show="edit===<?php echo e($role->id); ?>" class="fixed inset-0 z-50 grid place-items-center p-4">
        <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="edit=null"></div>
        <form method="POST" action="<?php echo e(route('roles.update',$role)); ?>" class="relative bg-white rounded-2xl shadow-lift w-full max-w-lg p-5 max-h-[90vh] overflow-y-auto"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
          <h3 class="ff-display font-bold text-lg mb-1">صلاحيات «<?php echo e($roleLabelsMap[$role->name] ?? $role->name); ?>»</h3>
          <?php if($role->name==='admin'): ?>
            <p class="text-sm text-muted">دور المشرف العام يملك كل الصلاحيات ولا يُعدّل.</p>
          <?php else: ?>
            <p class="text-xs text-muted mb-3">فعّل ما يملكه هذا الدور.</p>
            <div class="grid sm:grid-cols-2 gap-2">
              <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="flex items-center gap-2 text-sm bg-canvas rounded-xl px-3 py-2 cursor-pointer">
                  <input type="checkbox" name="permissions[]" value="<?php echo e($perm->name); ?>" class="accent-brand w-4 h-4"
                    <?php if($role->permissions->contains('name',$perm->name)): echo 'checked'; endif; ?>>
                  <?php echo e($permLabels[$perm->name] ?? $perm->name); ?>

                </label>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
          <?php endif; ?>
          <div class="flex gap-2 justify-end pt-4">
            <button type="button" x-on:click="edit=null" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إغلاق</button>
            <?php if($role->name!=='admin'): ?><button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">حفظ</button><?php endif; ?>
          </div>
        </form>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>

  <!-- نافذة إنشاء دور -->
  <div x-cloak x-show="create" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="create=false"></div>
    <form method="POST" action="<?php echo e(route('roles.store')); ?>" class="relative bg-white rounded-2xl shadow-lift w-full max-w-lg p-5 max-h-[90vh] overflow-y-auto"><?php echo csrf_field(); ?>
      <h3 class="ff-display font-bold text-lg mb-3">دور جديد</h3>
      <div class="mb-3"><label class="text-xs font-semibold text-muted mb-1 block">اسم الدور (بالإنجليزية، بدون مسافات)</label>
        <input name="name" required pattern="[a-z_]+" placeholder="content_lead" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
      <div class="grid sm:grid-cols-2 gap-2">
        <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <label class="flex items-center gap-2 text-sm bg-canvas rounded-xl px-3 py-2 cursor-pointer">
            <input type="checkbox" name="permissions[]" value="<?php echo e($perm->name); ?>" class="accent-brand w-4 h-4">
            <?php echo e($permLabels[$perm->name] ?? $perm->name); ?>

          </label>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <div class="flex gap-2 justify-end pt-4">
        <button type="button" x-on:click="create=false" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">إنشاء</button>
      </div>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\markting\promanage\resources\views/roles/index.blade.php ENDPATH**/ ?>