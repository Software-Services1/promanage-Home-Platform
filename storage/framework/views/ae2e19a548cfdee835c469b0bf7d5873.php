<?php $__env->startSection('title','النقاط والتارجت'); ?>
<?php $__env->startSection('content'); ?>
<?php $roleLbl = ['supervisor'=>'مشرف المحتوى','designer'=>'مصمم جرافيك','editor'=>'مونتير ومصمم']; ?>
<div class="flex items-center gap-2 mb-4 flex-wrap">
  <span class="inline-flex items-center gap-2 bg-brand/10 text-brand text-sm font-bold px-3 py-1.5 rounded-xl">
    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/></svg>
    نقاط <?php echo e($monthLabel($month)); ?></span>
  <span class="text-xs text-muted">النقاط والتارجت تُحتسب لكل شهر على حدة</span>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5 mb-5">
  <?php $__currentLoopData = $team; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
      $tgt = (int)$u->target; $p = $u->points;
      $pct = $tgt>0 ? min(100, round($p/$tgt*100)) : 0;
      $circ = 2*3.14159*46; $dash = $circ*(1-$pct/100);
      $over = $tgt>0 && $p>$tgt;
    ?>
    <div class="bg-white rounded-2xl border border-line shadow-soft p-5">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-full grid place-items-center text-white font-bold ff-display" style="background:<?php echo e($avatarColor($u->id)); ?>"><?php echo e(mb_substr($u->name,0,1)); ?></div>
        <div><div class="font-bold"><?php echo e($u->name); ?></div><div class="text-[11px] text-muted"><?php echo e($roleLbl[$u->primaryRole()] ?? ''); ?></div></div>
        <?php if($over): ?><span class="mr-auto text-xs bg-gold/15 text-golddk px-2 py-1 rounded-lg font-bold">تجاوز</span><?php endif; ?>
      </div>
      <div class="flex items-center gap-4">
        <div class="relative w-28 h-28 shrink-0">
          <svg viewBox="0 0 112 112" class="w-28 h-28 -rotate-90">
            <circle cx="56" cy="56" r="46" fill="none" stroke="#E6E4F0" stroke-width="11"/>
            <circle cx="56" cy="56" r="46" fill="none" stroke="<?php echo e($over ? '#F0A53A' : '#5B4BDB'); ?>" stroke-width="11" stroke-linecap="round" stroke-dasharray="<?php echo e($circ); ?>" stroke-dashoffset="<?php echo e($dash); ?>"/>
          </svg>
          <div class="absolute inset-0 grid place-items-center"><div class="text-center"><div class="ff-display font-extrabold text-xl tnum"><?php echo e($p); ?></div><div class="text-[10px] text-muted">من <?php echo e($tgt ?: '—'); ?></div></div></div>
        </div>
        <div class="flex-1 space-y-1.5 text-sm">
          <?php if(isset($u->breakdown)): ?>
            <div class="flex justify-between"><span class="text-muted">مباشرة</span><b class="tnum"><?php echo e($u->breakdown['direct']); ?></b></div>
            <div class="flex justify-between"><span class="text-muted">من الإشراف (<?php echo e(rtrim(rtrim(number_format($u->breakdown['share'],2),'0'),'.')); ?>%)</span><b class="tnum text-brand"><?php echo e($u->breakdown['from_designers']); ?></b></div>
            <div class="flex justify-between border-t border-line pt-1"><span class="text-muted">الإجمالي</span><b class="tnum"><?php echo e($u->breakdown['total']); ?></b></div>
          <?php else: ?>
            <div class="flex justify-between"><span class="text-muted">المحقّق</span><b class="tnum"><?php echo e($p); ?></b></div>
            <div class="flex justify-between"><span class="text-muted">المستهدف</span><b class="tnum"><?php echo e($tgt); ?></b></div>
            <div class="flex justify-between"><span class="text-muted">النسبة</span><b class="tnum <?php echo e($over ? 'text-golddk':'text-brand'); ?>"><?php echo e($pct); ?>%</b></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="bg-white rounded-2xl border border-line shadow-soft p-5">
  <h3 class="ff-display font-bold mb-4">قيمة النقاط لكل نوع عمل</h3>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2">
    <?php $__currentLoopData = $taskTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="flex items-center justify-between bg-canvas rounded-xl px-3.5 py-2.5">
        <span class="text-sm"><?php echo e($t['label']); ?><?php if(isset($t['bonus'])): ?><span class="text-[10px] text-golddk"> +<?php echo e($t['bonus']); ?></span><?php endif; ?></span>
        <b class="tnum text-brand"><?php echo e($t['points']); ?></b>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php $__currentLoopData = $maintTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="flex items-center justify-between bg-canvas rounded-xl px-3.5 py-2.5">
        <span class="text-sm"><?php echo e($t['label']); ?></span><b class="tnum text-brand"><?php echo e($t['points']); ?></b>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
  <div class="mt-4 bg-brand/5 rounded-xl p-4 text-sm text-brand/90">
    <b>معادلة نقاط الإشراف:</b> إجمالي المشرف = نقاطه المباشرة + (نسبته الخاصة ٪ × القاعدة).
    <?php if(\App\Models\Setting::get('supervisor_credit_mode','auto')==='assigned'): ?>
      الوضع الحالي: <b>حسب الإسناد</b> — القاعدة هي نقاط المهام المُسنَدة لكل مشرف كمتابِع.
    <?php else: ?>
      الوضع الحالي: <b>توزيع تلقائي</b> — القاعدة هي مجموع نقاط المصممين (<?php echo e($designersTotal); ?>).
    <?php endif; ?>
    تُضبط النسبة لكل مشرف من «إدارة المستخدمين»، والوضع من «إعدادات النظام».
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\markting\promanage\resources\views/points/index.blade.php ENDPATH**/ ?>