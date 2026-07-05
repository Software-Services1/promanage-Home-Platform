<?php $__env->startSection('title','أنواع المهام'); ?>
<?php $__env->startSection('content'); ?>
<?php $cats = ['design'=>'تصميم','video'=>'مونتاج','idea'=>'فكرة','sup'=>'إشراف','general'=>'عام']; ?>
<div x-data="{ open:false, mode:'create', form:{} }">
  <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
    <div class="text-sm text-muted"><?php echo e($types->count()); ?> نوع — تُحتسب نقاطها تلقائياً في النظام</div>
    <button x-on:click="mode='create'; form={points:5,bonus:0,category:'general',counts_when_published:false,is_active:true}; open=true"
            class="inline-flex items-center gap-2 bg-brand hover:bg-brandd text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-lift">
      <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> نوع جديد</button>
  </div>

  <div class="bg-white rounded-2xl border border-line shadow-soft overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-right text-muted text-xs border-b border-line bg-canvas/60">
        <th class="p-4 font-semibold">النوع</th><th class="p-4 font-semibold">التصنيف</th>
        <th class="p-4 font-semibold">النقاط</th><th class="p-4 font-semibold">إضافي</th>
        <th class="p-4 font-semibold">عند النشر فقط</th><th class="p-4 font-semibold">الحالة</th>
        <th class="p-4 font-semibold">مستخدم</th><th class="p-4"></th></tr></thead>
      <tbody>
      <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="border-b border-line/60 hover:bg-canvas/60">
          <td class="p-4"><div class="font-semibold"><?php echo e($t->label); ?></div><div class="text-[11px] text-muted"><?php echo e($t->key); ?></div></td>
          <td class="p-4 text-muted"><?php echo e($cats[$t->category] ?? $t->category); ?></td>
          <td class="p-4"><span class="bg-violet-50 text-brand px-2 py-1 rounded-lg text-xs font-bold tnum"><?php echo e($t->points); ?></span></td>
          <td class="p-4 tnum text-muted"><?php echo e($t->bonus ?: '—'); ?></td>
          <td class="p-4"><?php echo e($t->counts_when_published ? 'نعم' : '—'); ?></td>
          <td class="p-4"><?php if($t->is_active): ?><span class="text-emerald-700 text-xs font-semibold">مفعّل</span><?php else: ?><span class="text-gray-400 text-xs font-semibold">معطّل</span><?php endif; ?></td>
          <td class="p-4 tnum text-muted"><?php echo e($usage[$t->key] ?? 0); ?></td>
          <td class="p-4">
            <div class="flex gap-1 justify-end">
              <button x-on:click="mode='edit'; form={ id:<?php echo e($t->id); ?>, key:<?php echo \Illuminate\Support\Js::from($t->key)->toHtml() ?>, label:<?php echo \Illuminate\Support\Js::from($t->label)->toHtml() ?>, points:<?php echo e($t->points); ?>, bonus:<?php echo e($t->bonus); ?>, category:<?php echo \Illuminate\Support\Js::from($t->category)->toHtml() ?>, counts_when_published:<?php echo e($t->counts_when_published ? 'true':'false'); ?>, is_active:<?php echo e($t->is_active ? 'true':'false'); ?> }; open=true"
                      class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-violet-50 text-brand hover:bg-violet-100">تعديل</button>
              <?php if(($usage[$t->key] ?? 0) === 0): ?>
                <form method="POST" action="<?php echo e(route('tasktypes.destroy',$t)); ?>" onsubmit="return confirm('حذف هذا النوع؟')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                  <button class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-600 hover:bg-rose-100">حذف</button></form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>

  <!-- نافذة إضافة/تعديل -->
  <div x-cloak x-show="open" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="open=false"></div>
    <form method="POST" x-bind:action="mode==='create' ? '<?php echo e(route('tasktypes.store')); ?>' : '/task-types/'+form.id"
          class="relative bg-white rounded-2xl shadow-lift w-full max-w-md p-5 space-y-3"><?php echo csrf_field(); ?>
      <template x-if="mode==='edit'"><input type="hidden" name="_method" value="PUT"></template>
      <h3 class="ff-display font-bold text-lg" x-text="mode==='create' ? 'نوع مهمة جديد' : 'تعديل النوع'"></h3>
      <div><label class="text-xs font-semibold text-muted mb-1 block">الاسم</label>
        <input name="label" x-model="form.label" required class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-xs font-semibold text-muted mb-1 block">النقاط</label>
          <input name="points" type="number" x-model="form.points" required class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
        <div><label class="text-xs font-semibold text-muted mb-1 block">نقاط إضافية (إبداع)</label>
          <input name="bonus" type="number" x-model="form.bonus" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
        <div class="col-span-2"><label class="text-xs font-semibold text-muted mb-1 block">التصنيف</label>
          <select name="category" x-model="form.category" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">
            <?php $__currentLoopData = $cats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>"><?php echo e($v); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select></div>
      </div>
      <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="counts_when_published" value="1" x-model="form.counts_when_published" class="accent-brand w-4 h-4"> لا تُحتسب النقاط إلا عند النشر</label>
      <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="accent-brand w-4 h-4"> مفعّل</label>
      <div class="flex gap-2 justify-end pt-1">
        <button type="button" x-on:click="open=false" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">حفظ</button>
      </div>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\markting\promanage\resources\views/tasktypes/index.blade.php ENDPATH**/ ?>