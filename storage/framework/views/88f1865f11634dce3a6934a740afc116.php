<?php $__env->startSection('title','التقارير'); ?>
<?php $__env->startSection('content'); ?>
<?php
  $tabs = ['points'=>'أداء ونقاط','content'=>'خطة المحتوى','tasks'=>'المهام','maint'=>'صيانة الموقع','attendance'=>'الحضور'];
?>
<div class="flex items-center gap-2 mb-3">
  <span class="inline-flex items-center gap-2 bg-brand/10 text-brand text-sm font-bold px-3 py-1.5 rounded-xl">
    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
    تقارير <?php echo e($monthLabel($month)); ?></span>
</div>
<div class="flex flex-wrap items-center gap-2 mb-4">
  <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <a href="<?php echo e(route('reports.index',['tab'=>$k,'user'=>$userId])); ?>" class="px-3.5 py-2 rounded-xl text-sm font-semibold <?php echo e($tab===$k ? 'bg-brand text-white':'bg-white border border-line text-muted'); ?>"><?php echo e($l); ?></a>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <div class="flex-1"></div>
  <form method="GET" class="flex items-center gap-2">
    <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
    <select name="user" onchange="this.form.submit()" class="bg-white border border-line rounded-xl px-3 py-2 text-sm shadow-soft">
      <option value="">كل الموظفين</option>
      <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($emp->id); ?>" <?php if($userId==$emp->id): echo 'selected'; endif; ?>><?php echo e($emp->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
  </form>
  <a href="<?php echo e(route('reports.export',['tab'=>$tab,'user'=>$userId])); ?>" class="inline-flex items-center gap-2 bg-white border border-line text-ink text-sm font-bold px-3.5 py-2.5 rounded-xl hover:border-brand/40">
    <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg> تصدير CSV</a>
</div>

<div class="bg-white rounded-2xl border border-line shadow-soft overflow-x-auto p-2">
  <table class="w-full text-sm">
    <thead><tr class="text-right text-muted text-xs border-b border-line">
      <?php $__currentLoopData = $data['head']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><th class="p-3 font-semibold"><?php echo e($h); ?></th><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $data['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr class="border-b border-line/60">
        <?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><td class="p-3 <?php echo e(is_numeric($cell) ? 'tnum':''); ?>"><?php echo e($cell); ?></td><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td class="p-6 text-center text-muted" colspan="<?php echo e(count($data['head'])); ?>">لا توجد بيانات في <?php echo e($monthLabel($month)); ?>.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
<p class="text-xs text-muted mt-3">لا تتضمّن التقارير والتصدير أي بيانات رواتب — هذه مقصورة على واجهة الرواتب السرّية للمشرف العام.</p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\markting\promanage\resources\views/reports/index.blade.php ENDPATH**/ ?>