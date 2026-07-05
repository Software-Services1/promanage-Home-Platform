<?php $__env->startSection('title','لوحة التحكم — ProManage Flow'); ?>
<?php $__env->startSection('content'); ?>
<?php
  $me = auth()->user();
  $tgt = (int) $me->target;
  $pct = $tgt > 0 ? min(100, round($pts / $tgt * 100)) : 0;
  $circ = 2 * 3.14159 * 52;
  $dash = $circ * (1 - $pct/100);
?>

<!-- فترة التقرير -->
<form method="GET" class="bg-white rounded-2xl border border-line shadow-soft p-4 flex flex-wrap items-end gap-3 mb-4">
  <div class="flex items-center gap-2 text-brand">
    <span class="w-9 h-9 rounded-xl bg-violet-50 grid place-items-center">
      <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 4h18v17H3zM3 9h18M8 2v4M16 2v4"/></svg></span>
    <div><div class="text-sm font-bold text-ink leading-none">فترة التقرير</div><div class="text-[11px] text-muted mt-1">حدّد المدى الزمني للملخّص</div></div>
  </div>
  <div><label class="text-[11px] font-semibold text-muted mb-1 block">من</label>
    <input type="date" name="from" value="<?php echo e($from); ?>" class="bg-canvas border border-line rounded-xl px-3 py-2 text-sm tnum"></div>
  <div><label class="text-[11px] font-semibold text-muted mb-1 block">إلى</label>
    <input type="date" name="to" value="<?php echo e($to); ?>" class="bg-canvas border border-line rounded-xl px-3 py-2 text-sm tnum"></div>
  <button class="bg-brand hover:bg-brandd text-white text-sm font-bold px-4 py-2 rounded-xl">تطبيق</button>
  <div class="text-xs text-muted mr-auto self-center">من <b class="text-ink tnum"><?php echo e($from); ?></b> إلى <b class="text-ink tnum"><?php echo e($to); ?></b></div>
</form>

<!-- بطاقات إحصائية -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
  <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="bg-white rounded-2xl border border-line shadow-soft p-4">
      <div class="ff-display font-extrabold text-2xl tnum text-ink leading-none"><?php echo e($s['value']); ?></div>
      <div class="text-xs text-muted mt-2"><?php echo e($s['label']); ?></div>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="grid lg:grid-cols-3 gap-5">
  <!-- التارجت -->
  <div class="bg-white rounded-2xl border border-line shadow-soft p-5">
    <h3 class="ff-display font-bold text-ink mb-3">نقاط الفترة مقابل التارجت</h3>
    <div class="flex items-center gap-5">
      <div class="relative w-32 h-32 shrink-0">
        <svg viewBox="0 0 120 120" class="w-32 h-32 -rotate-90">
          <circle cx="60" cy="60" r="52" fill="none" stroke="#E6E4F0" stroke-width="12"/>
          <circle cx="60" cy="60" r="52" fill="none" stroke="#5B4BDB" stroke-width="12" stroke-linecap="round"
                  stroke-dasharray="<?php echo e($circ); ?>" stroke-dashoffset="<?php echo e($dash); ?>"/>
        </svg>
        <div class="absolute inset-0 grid place-items-center">
          <div class="text-center"><div class="ff-display font-extrabold text-2xl tnum"><?php echo e($pts); ?></div><div class="text-[11px] text-muted">نقطة</div></div>
        </div>
      </div>
      <div class="space-y-2 text-sm flex-1">
        <?php if($breakdown): ?>
          <div class="flex justify-between"><span class="text-muted">نقاط مباشرة</span><b class="tnum"><?php echo e($breakdown['direct']); ?></b></div>
          <div class="flex justify-between"><span class="text-muted">من المصممين 30%</span><b class="tnum text-brand"><?php echo e($breakdown['from_designers']); ?></b></div>
          <div class="flex justify-between border-t border-line pt-2"><span class="font-semibold">الإجمالي</span><b class="tnum"><?php echo e($pts); ?></b></div>
        <?php else: ?>
          <div class="flex justify-between"><span class="text-muted">نقاط الفترة</span><b class="tnum"><?php echo e($pts); ?></b></div>
          <div class="flex justify-between"><span class="text-muted">التارجت الشهري</span><b class="tnum"><?php echo e($tgt ?: '—'); ?></b></div>
          <div class="flex justify-between"><span class="text-muted">المتبقّي</span><b class="tnum <?php echo e($pts >= $tgt ? 'text-emerald-600':'text-golddk'); ?>"><?php echo e(max(0, $tgt - $pts)); ?></b></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- المهام -->
  <div class="bg-white rounded-2xl border border-line shadow-soft p-5 lg:col-span-2">
    <div class="flex items-center justify-between mb-3">
      <h3 class="ff-display font-bold text-ink"><?php echo e($isAdmin ? 'مهام الفترة' : 'مهامي خلال الفترة'); ?></h3>
      <a href="<?php echo e(route('tasks.index')); ?>" class="text-brand text-sm font-bold hover:underline">عرض الكل</a>
    </div>
    <div class="space-y-2">
      <?php $__empty_1 = true; $__currentLoopData = $recentTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-canvas transition">
          <div class="w-9 h-9 rounded-lg bg-violet-50 text-brand grid place-items-center shrink-0">
            <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M9 11l3 3 8-8"/></svg></div>
          <div class="min-w-0 flex-1"><div class="text-sm font-semibold truncate"><?php echo e($t->title); ?></div>
            <div class="text-[11px] text-muted"><?php echo e($t->typeLabel()); ?> • <?php echo e($t->user->name); ?></div></div>
          <span class="text-[11px] font-semibold px-2 py-1 rounded-lg bg-canvas text-muted"><?php echo e($t->stage); ?></span>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="text-sm text-muted py-8 text-center">لا توجد مهام في هذه الفترة.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\markting\promanage\resources\views/dashboard.blade.php ENDPATH**/ ?>