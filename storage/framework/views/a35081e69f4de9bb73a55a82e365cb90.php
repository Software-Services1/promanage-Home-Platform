<?php $__env->startSection('title','الحضور والإجازات'); ?>
<?php $__env->startSection('content'); ?>
<div x-data="{ add:false }">
  <div class="flex items-center gap-2 mb-3">
    <span class="inline-flex items-center gap-2 bg-brand/10 text-brand text-sm font-bold px-3 py-1.5 rounded-xl">
      <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
      حضور <?php echo e($monthLabel($month)); ?></span>
  </div>
  <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
    <div class="text-sm text-muted">سياسة: بحد أقصى يومين شهرياً • ما زاد = خصم الأجر اليومي</div>
    <button x-on:click="add=true" class="inline-flex items-center gap-2 bg-brand hover:bg-brandd text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-lift">
      <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> طلب إجازة</button>
  </div>

  <div class="grid lg:grid-cols-3 gap-5">
    <div class="bg-white rounded-2xl border border-line shadow-soft p-5 lg:col-span-2">
      <h3 class="ff-display font-bold mb-3">طلبات الإجازات</h3>
      <div class="space-y-2">
        <?php $__empty_1 = true; $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <div class="flex items-center gap-3 p-3 rounded-xl border border-line">
            <div class="w-9 h-9 rounded-full grid place-items-center text-white font-bold ff-display" style="background:<?php echo e($avatarColor($l->user_id)); ?>"><?php echo e(mb_substr($l->user->name,0,1)); ?></div>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-semibold"><?php echo e($l->user->name); ?> • <?php echo e($l->days); ?> يوم</div>
              <div class="text-[11px] text-muted"><?php echo e($l->from_date->format('Y-m-d')); ?><?php if($l->to_date->ne($l->from_date)): ?> ← <?php echo e($l->to_date->format('Y-m-d')); ?><?php endif; ?> • <?php echo e($l->reason); ?></div>
            </div>
            <?php if($l->status==='pending'): ?>
              <?php if($canManage): ?>
                <div class="flex gap-1">
                  <form method="POST" action="<?php echo e(route('attendance.status',$l)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="status" value="approved">
                    <button class="px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold hover:bg-emerald-100">قبول</button></form>
                  <form method="POST" action="<?php echo e(route('attendance.status',$l)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="status" value="rejected">
                    <button class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-xs font-bold hover:bg-rose-100">رفض</button></form>
                </div>
              <?php else: ?><span class="text-xs text-golddk font-semibold">قيد المراجعة</span><?php endif; ?>
            <?php else: ?>
              <span class="text-xs font-semibold <?php echo e($l->status==='approved' ? 'text-emerald-700':'text-gray-400'); ?>"><?php echo e($l->status==='approved' ? 'مقبول':'مرفوض'); ?></span>
            <?php endif; ?>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <div class="text-sm text-muted py-8 text-center">لا توجد طلبات في <?php echo e($monthLabel($month)); ?>.</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-line shadow-soft p-5">
      <h3 class="ff-display font-bold mb-3">رصيد الشهر</h3>
      <div class="space-y-3">
        <?php $__currentLoopData = $team; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php $extra = max(0, $u->leave_days - 2); ?>
          <div>
            <div class="flex justify-between text-sm mb-1"><span><?php echo e($u->name); ?></span><b class="tnum <?php echo e($extra ? 'text-rose-600':''); ?>"><?php echo e($u->leave_days); ?>/2 <?php if($extra): ?>(+<?php echo e($extra); ?>)<?php endif; ?></b></div>
            <div class="h-2 rounded-full bg-canvas overflow-hidden"><div class="h-full rounded-full <?php echo e($extra ? 'bg-rose-400':'bg-brand'); ?>" style="width:<?php echo e(min(100, $u->leave_days/2*100)); ?>%"></div></div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <p class="text-xs text-muted mt-4">الأيام الزائدة تُحوّل آلياً إلى خصم في تقرير الرواتب لدى المشرف العام.</p>
    </div>
  </div>

  <div x-cloak x-show="add" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="add=false"></div>
    <form method="POST" action="<?php echo e(route('attendance.store')); ?>" class="relative bg-white rounded-2xl shadow-lift w-full max-w-md p-5 space-y-3"><?php echo csrf_field(); ?>
      <h3 class="ff-display font-bold text-lg">طلب إجازة</h3>
      <?php if($canManage): ?>
        <div><label class="text-xs font-semibold text-muted mb-1 block">الموظف</label>
          <select name="user_id" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"><?php $__currentLoopData = $team; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
      <?php else: ?>
        <input type="hidden" name="user_id" value="<?php echo e(auth()->id()); ?>">
      <?php endif; ?>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-xs font-semibold text-muted mb-1 block">من</label>
          <input name="from_date" type="date" value="<?php echo e($month); ?>-01" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
        <div><label class="text-xs font-semibold text-muted mb-1 block">إلى</label>
          <input name="to_date" type="date" value="<?php echo e($month); ?>-01" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
      </div>
      <div><label class="text-xs font-semibold text-muted mb-1 block">السبب</label>
        <input name="reason" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
      <div class="flex gap-2 justify-end pt-1">
        <button type="button" x-on:click="add=false" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">إرسال</button>
      </div>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\markting\promanage\resources\views/attendance/index.blade.php ENDPATH**/ ?>