<?php $__env->startSection('title','إعدادات النظام'); ?>
<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('settings.update')); ?>" class="max-w-3xl space-y-5"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

  <div class="bg-white rounded-2xl border border-line shadow-soft p-5 md:p-6">
    <h3 class="ff-display font-bold text-lg mb-1">احتساب نقاط الإشراف</h3>
    <p class="text-sm text-muted mb-4">نسبة كل مشرف تُحدَّد من «إدارة المستخدمين».</p>

    <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer mb-3 transition <?php echo e($mode==='auto' ? 'border-brand bg-violet-50' : 'border-line hover:border-brand/40'); ?>">
      <input type="radio" name="supervisor_credit_mode" value="auto" <?php if($mode==='auto'): echo 'checked'; endif; ?> class="accent-brand w-5 h-5 mt-0.5">
      <div><div class="font-bold text-ink">توزيع تلقائي</div>
        <div class="text-sm text-muted mt-0.5">نسبة المشرف ٪ × إجمالي نقاط جميع المصممين.</div></div>
    </label>
    <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition <?php echo e($mode==='assigned' ? 'border-brand bg-violet-50' : 'border-line hover:border-brand/40'); ?>">
      <input type="radio" name="supervisor_credit_mode" value="assigned" <?php if($mode==='assigned'): echo 'checked'; endif; ?> class="accent-brand w-5 h-5 mt-0.5">
      <div><div class="font-bold text-ink">حسب الإسناد</div>
        <div class="text-sm text-muted mt-0.5">نسبة المشرف ٪ × نقاط المهام/العناصر المُسنَدة إليه كمتابِع.</div></div>
    </label>
  </div>

  <div class="bg-white rounded-2xl border border-line shadow-soft p-5 md:p-6">
    <h3 class="ff-display font-bold text-lg mb-1">حقول كارت خطة المحتوى</h3>
    <p class="text-sm text-muted mb-4">اختر البيانات التي تظهر على وجه كل كارت في خطة المحتوى.</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
      <?php $__currentLoopData = $allCardFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <label class="flex items-center gap-2 text-sm bg-canvas rounded-xl px-3 py-2.5 cursor-pointer border border-transparent hover:border-brand/30 transition">
          <input type="checkbox" name="content_card_fields[]" value="<?php echo e($key); ?>" <?php if(in_array($key,$cardFields)): echo 'checked'; endif; ?> class="accent-brand w-4 h-4">
          <?php echo e($label); ?>

        </label>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-line shadow-soft p-5 md:p-6">
    <h3 class="ff-display font-bold text-lg mb-1">المكافآت والخصومات</h3>
    <p class="text-sm text-muted mb-4">اضبط نِسَب المكافأة والخصم وعتبة النقاط المطلوبة.</p>
    <div class="grid sm:grid-cols-3 gap-4">
      <div>
        <label class="text-xs font-semibold text-muted mb-1.5 block">نسبة المكافأة ٪</label>
        <input name="payroll_bonus_pct" type="number" step="0.1" min="0" max="100" value="<?php echo e($payroll['bonus_pct']); ?>" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum">
        <p class="text-[11px] text-muted mt-1">من الراتب عند تجاوز العتبة.</p>
      </div>
      <div>
        <label class="text-xs font-semibold text-muted mb-1.5 block">حد أقصى للخصم ٪</label>
        <input name="payroll_deduction_pct" type="number" step="0.1" min="0" max="100" value="<?php echo e($payroll['deduction_pct']); ?>" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum">
        <p class="text-[11px] text-muted mt-1">عند عدم تحقيق العتبة (متناسب).</p>
      </div>
      <div>
        <label class="text-xs font-semibold text-muted mb-1.5 block">عتبة النقاط</label>
        <input name="payroll_threshold" type="number" min="0" value="<?php echo e($payroll['threshold']); ?>" placeholder="تارجت كل موظف" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum">
        <p class="text-[11px] text-muted mt-1">اتركها فارغة لاستخدام تارجت كل موظف.</p>
      </div>
    </div>
    <div class="mt-4 pt-4 border-t border-line">
      <label class="text-xs font-semibold text-muted mb-1.5 block">نسبة مكافأة الإبداع ٪</label>
      <input name="creative_bonus_pct" type="number" step="1" min="0" max="200" value="<?php echo e($payroll['creative_pct']); ?>" class="w-full sm:w-1/3 bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum">
      <p class="text-[11px] text-muted mt-1">تُضاف لنقاط أي مهمة مُعلَّمة «إبداعية» (تُطبَّق على الأنواع التي لا تملك بونص مخصّصاً).</p>
    </div>
  </div>

  <div class="flex justify-end">
    <button class="px-5 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd shadow-lift">حفظ الإعدادات</button>
  </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\markting\promanage\resources\views/settings/index.blade.php ENDPATH**/ ?>