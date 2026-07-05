<?php $__env->startSection('title','خطة المحتوى'); ?>
<?php $__env->startSection('content'); ?>
<?php
  $statusColor = [
    'فكرة'=>'bg-gray-100 text-gray-600','قيد التصميم'=>'bg-violet-50 text-brand','يحتاج تعديل'=>'bg-rose-50 text-rose-600',
    'جاهز للنشر'=>'bg-emerald-50 text-emerald-700','مجدول في النشر التلقائي'=>'bg-sky-50 text-sky-700',
    'تم النشر'=>'bg-emerald-100 text-emerald-800','مؤجل'=>'bg-amber-50 text-golddk','ملغي'=>'bg-gray-100 text-gray-400'];
  $statusDot = [
    'فكرة'=>'bg-gray-300','قيد التصميم'=>'bg-brand','يحتاج تعديل'=>'bg-rose-500','جاهز للنشر'=>'bg-emerald-500',
    'مجدول في النشر التلقائي'=>'bg-sky-500','تم النشر'=>'bg-emerald-600','مؤجل'=>'bg-amber-500','ملغي'=>'bg-gray-300'];
  $approvalLabel = ['draft'=>'مسودة','pending'=>'بانتظار الاعتماد','approved'=>'معتمد','rejected'=>'مرفوض'];
  $approvalColor = ['draft'=>'bg-gray-100 text-gray-600','pending'=>'bg-amber-50 text-golddk','approved'=>'bg-emerald-50 text-emerald-700','rejected'=>'bg-rose-50 text-rose-600'];
  $canApprove = auth()->user()->can('approve content');
  $canCreate  = auth()->user()->can('create content');
  $canUpdate  = auth()->user()->can('update content');
  $canDelete  = auth()->user()->can('delete content');
  $canContribute = auth()->user()->can('add content note') || auth()->user()->can('update content status') || auth()->user()->can('upload design');
  $addSeed = "form={ platform:'إنستقرام', company_name:'', plan_date:'".$month."-01', plan_time:'19:00', content_type:'تعليمي', post_type:'منشور', status:'فكرة', assigned_to:null, design_content:'', design_text:'', caption:'', post_text:'', reference_link:'', notes:'', work_type:'', supervisor_id:null }; add=true";
?>
<div x-data="{ add:false, reject:false, edit:null, view:null, form:{}, vrow:{} }">
  <div class="flex items-center gap-2 mb-3 flex-wrap">
    <span class="inline-flex items-center gap-2 bg-brand/10 text-brand text-sm font-bold px-3 py-1.5 rounded-xl">
      <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 4h18v17H3zM3 9h18M8 2v4M16 2v4"/></svg>
      خطة <?php echo e($monthLabel($month)); ?></span>
    <span class="text-xs text-muted"><?php echo e($monthCount); ?> عنصر</span>
  </div>

  <div x-data="{ filters: <?php echo e(($filters['company'] || $filters['date'] || $filters['assigned_to'] || $filters['work_type'] || $platform!=='all') ? 'true':'false'); ?> }" class="mb-4">
    <div class="flex flex-wrap items-center gap-2 mb-3">
      <button type="button" x-on:click="filters=!filters" class="inline-flex items-center gap-2 bg-white border border-line rounded-xl px-3.5 py-2.5 text-sm font-semibold text-ink shadow-soft hover:border-brand/40">
        <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 5h18l-7 8v6l-4-2v-4z"/></svg>
        الفلاتر
        <?php $active = collect($filters)->filter(fn($v)=>$v!==null && $v!=='')->count() + ($platform!=='all'?1:0); ?>
        <?php if($active): ?><span class="bg-brand text-white text-[10px] rounded-full w-5 h-5 grid place-items-center tnum"><?php echo e($active); ?></span><?php endif; ?>
      </button>
      <div class="flex-1"></div>
      <?php if($canApprove && $monthCount>0): ?>
        <label class="hidden sm:flex items-center gap-1.5 text-xs text-muted bg-white border border-line rounded-xl px-3 py-2.5 cursor-pointer">
          <input type="checkbox" class="accent-brand w-4 h-4" onclick="document.querySelectorAll('.rowchk').forEach(c=>c.checked=this.checked)"> تحديد الكل</label>
        <button type="button" onclick="bulkContent('approved')" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-3.5 py-2.5 rounded-xl">اعتماد المحدّد</button>
        <button type="button" onclick="bulkContent('rejected')" class="bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold px-3.5 py-2.5 rounded-xl">رفض المحدّد</button>
      <?php endif; ?>
      <?php if($canCreate): ?><button x-on:click="<?php echo e($addSeed); ?>" class="inline-flex items-center gap-2 bg-brand hover:bg-brandd text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-lift">
        <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> عنصر جديد</button><?php endif; ?>
    </div>

    <form method="GET" x-show="filters" x-cloak x-transition class="bg-white rounded-2xl border border-line shadow-soft p-4 grid grid-cols-2 md:grid-cols-5 gap-3">
      <div class="col-span-2 md:col-span-1"><label class="text-[11px] font-semibold text-muted mb-1 block">اسم الشركة</label>
        <input name="company" value="<?php echo e($filters['company']); ?>" placeholder="بحث..." class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm"></div>
      <div><label class="text-[11px] font-semibold text-muted mb-1 block">التاريخ</label>
        <input name="date" type="date" value="<?php echo e($filters['date']); ?>" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm tnum"></div>
      <div><label class="text-[11px] font-semibold text-muted mb-1 block">المصمم</label>
        <select name="assigned_to" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm">
          <option value="">الكل</option>
          <?php $__currentLoopData = $assignees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($a->id); ?>" <?php if($filters['assigned_to']==$a->id): echo 'selected'; endif; ?>><?php echo e($a->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select></div>
      <div><label class="text-[11px] font-semibold text-muted mb-1 block">نوع العمل</label>
        <select name="work_type" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm">
          <option value="">الكل</option>
          <?php $__currentLoopData = $workTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($wt->key); ?>" <?php if($filters['work_type']===$wt->key): echo 'selected'; endif; ?>><?php echo e($wt->label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select></div>
      <div><label class="text-[11px] font-semibold text-muted mb-1 block">المنصة</label>
        <select name="platform" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm">
          <option value="all">الكل</option>
          <?php $__currentLoopData = $platforms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php if($platform===$pf): echo 'selected'; endif; ?>><?php echo e($pf); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select></div>
      <div class="col-span-2 md:col-span-5 flex gap-2 justify-end">
        <a href="<?php echo e(route('content.index')); ?>" class="px-4 py-2 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">تصفير</a>
        <button class="px-5 py-2 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">تطبيق</button>
      </div>
    </form>
  </div>

  <?php if($monthCount===0): ?>
    <div class="bg-white rounded-2xl border border-line shadow-soft grid place-items-center text-center py-16 px-6">
      <div class="w-14 h-14 rounded-2xl bg-brand/10 grid place-items-center text-brand mb-3">
        <svg viewBox="0 0 24 24" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 4h18v17H3zM3 9h18M8 2v4M16 2v4"/></svg></div>
      <div class="ff-display font-bold">لا توجد خطة لشهر <?php echo e($monthLabel($month)); ?> بعد</div>
      <div class="text-muted text-sm mt-1 mb-4">ابدأ بإضافة أول عنصر، أو انتقل لشهر آخر من الأعلى.</div>
      <?php if($canCreate): ?><button x-on:click="<?php echo e($addSeed); ?>" class="bg-brand hover:bg-brandd text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-lift">إضافة أول عنصر</button><?php else: ?><span class="text-xs text-muted">لا توجد عناصر مسندة إليك في هذا الشهر.</span><?php endif; ?>
    </div>
  <?php else: ?>
    <?php $byStatus = collect($rows)->groupBy('status'); ?>
    <div class="flex gap-3 overflow-x-auto pb-3 -mx-1 px-1 snap-x">
      <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $items = $byStatus[$st] ?? collect(); ?>
        <div class="w-[280px] sm:w-[300px] shrink-0 snap-start">
          <div class="flex items-center justify-between mb-2.5 px-1">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full <?php echo e($statusDot[$st] ?? 'bg-gray-300'); ?>"></span>
              <h4 class="ff-display font-bold text-sm"><?php echo e($st); ?></h4>
            </div>
            <span class="text-xs bg-white border border-line rounded-full px-2 py-0.5 tnum text-muted"><?php echo e($items->count()); ?></span>
          </div>
          <div class="space-y-3 bg-canvas/50 rounded-2xl p-2 min-h-[80px] border border-line/60">
            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <?php echo $__env->make('content._card', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <div class="text-center text-[11px] text-muted py-6">—</div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  <?php endif; ?>

  <!-- نافذة إضافة صف -->
  <div x-cloak x-show="add" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="add=false"></div>
    <form method="POST" action="<?php echo e(route('content.store')); ?>" enctype="multipart/form-data" class="relative bg-white rounded-2xl shadow-lift w-full max-w-lg p-5 space-y-3 max-h-[90vh] overflow-y-auto"><?php echo csrf_field(); ?>
      <h3 class="ff-display font-bold text-lg">عنصر جديد في خطة <?php echo e($monthLabel($month)); ?></h3>
      <?php echo $__env->make('content._fields', ['edit'=>false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <div class="flex gap-2 justify-end pt-1">
        <button type="button" x-on:click="add=false" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">إضافة</button>
      </div>
    </form>
  </div>

  <!-- نافذة تعديل صف -->
  <?php if($canUpdate): ?>
  <div x-cloak x-show="edit!==null" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="edit=null"></div>
    <form method="POST" x-bind:action="'/content/'+form.id" enctype="multipart/form-data" class="relative bg-white rounded-2xl shadow-lift w-full max-w-lg p-5 space-y-3 max-h-[90vh] overflow-y-auto"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
      <h3 class="ff-display font-bold text-lg">تعديل العنصر</h3>
      <?php echo $__env->make('content._fields', ['edit'=>true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <div class="flex gap-2 justify-end pt-1">
        <button type="button" x-on:click="edit=null" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">حفظ</button>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <!-- نافذة استعراض/مساهمة (للمصمم والمونتير) -->
  <?php if($canContribute && ! $canUpdate): ?>
  <div x-cloak x-show="view!==null" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="view=null"></div>
    <form method="POST" x-bind:action="'/content/'+vrow.id+'/contribute'" enctype="multipart/form-data" class="relative bg-white rounded-2xl shadow-lift w-full max-w-lg p-5 space-y-3 max-h-[90vh] overflow-y-auto"><?php echo csrf_field(); ?>
      <h3 class="ff-display font-bold text-lg">تفاصيل العنصر</h3>
      <div class="grid grid-cols-2 gap-3 text-sm bg-canvas rounded-xl p-3">
        <div><span class="text-muted text-[11px] block">المنصة</span><b x-text="vrow.platform"></b></div>
        <div><span class="text-muted text-[11px] block">التاريخ</span><b class="tnum" x-text="vrow.date"></b></div>
        <div><span class="text-muted text-[11px] block">نوع المحتوى</span><b x-text="vrow.content_type"></b></div>
        <div><span class="text-muted text-[11px] block">نوع المنشور</span><b x-text="vrow.post_type"></b></div>
        <div class="col-span-2"><span class="text-muted text-[11px] block">المسؤول</span><b x-text="vrow.assignee || '—'"></b></div>
        <div class="col-span-2"><span class="text-muted text-[11px] block">محتوى التصميم</span><span x-text="vrow.design_content || '—'"></span></div>
        <div class="col-span-2"><span class="text-muted text-[11px] block">نص التصميم</span><span x-text="vrow.design_text || '—'"></span></div>
        <div class="col-span-2"><span class="text-muted text-[11px] block">الكابشن</span><span x-text="vrow.caption || '—'"></span></div>
        <div class="col-span-2"><span class="text-muted text-[11px] block">نص المنشور</span><span x-text="vrow.post_text || '—'"></span></div>
        <div class="col-span-2"><span class="text-muted text-[11px] block">رابط مرجعي</span><a x-bind:href="vrow.reference_link" target="_blank" class="text-brand" x-text="vrow.reference_link || '—'"></a></div>
      </div>
      <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('add content note')): ?>
        <div><label class="text-xs font-semibold text-muted mb-1 block">ملاحظة</label>
          <textarea name="notes" rows="2" x-model="vrow.notes" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm resize-none"></textarea></div>
      <?php endif; ?>
      <template x-if="vrow.owner">
        <div class="space-y-3">
          <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update content status')): ?>
            <div><label class="text-xs font-semibold text-muted mb-1 block">حالة التصميم</label>
              <select name="status" x-model="vrow.status" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">
                <?php $__currentLoopData = ['قيد التصميم','يحتاج تعديل','جاهز للنشر']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option><?php echo e($s); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select></div>
          <?php endif; ?>
          <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('upload design')): ?>
            <div><label class="text-xs font-semibold text-muted mb-1 block">رفع/تعديل التصميم</label>
              <input name="design" type="file" accept="image/*" class="w-full text-xs file:bg-brand file:text-white file:border-0 file:rounded-lg file:px-3 file:py-2 file:ml-2 file:cursor-pointer bg-canvas border border-line rounded-xl px-2 py-2"></div>
          <?php endif; ?>
        </div>
      </template>
      <div class="flex gap-2 justify-end pt-1">
        <button type="button" x-on:click="view=null" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إغلاق</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">حفظ</button>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <!-- نافذة عدم الموافقة (كامل الشهر) -->
  <div x-cloak x-show="reject" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="reject=false"></div>
    <form method="POST" action="<?php echo e(route('content.reject')); ?>" class="relative bg-white rounded-2xl shadow-lift w-full max-w-md p-5 space-y-3"><?php echo csrf_field(); ?>
      <h3 class="ff-display font-bold text-lg">سبب عدم الموافقة</h3>
      <textarea name="approval_note" rows="3" required class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm resize-none"></textarea>
      <div class="flex gap-2 justify-end">
        <button type="button" x-on:click="reject=false" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-rose-600 text-white hover:bg-rose-700">تأكيد</button>
      </div>
    </form>
  </div>
</div>

<form id="rowActionForm" method="POST" class="hidden"><?php echo csrf_field(); ?></form>
<script>
  const _csrf = '<?php echo e(csrf_token()); ?>';
  function bulkContent(state){
    const ids=[...document.querySelectorAll('.rowchk:checked')].map(c=>c.value);
    if(!ids.length){ alert('اختر عناصر أولاً بالضغط على مربّع الكارت'); return; }
    const f=document.createElement('form'); f.method='POST'; f.action='<?php echo e(route('content.bulkApprove')); ?>';
    let html='<input type="hidden" name="_token" value="'+_csrf+'"><input type="hidden" name="state" value="'+state+'">';
    ids.forEach(id=> html+='<input type="hidden" name="ids[]" value="'+id+'">');
    f.innerHTML=html; document.body.appendChild(f); f.submit();
  }
  function delRow(id){
    const f=document.createElement('form'); f.method='POST'; f.action='/content/'+id;
    f.innerHTML='<input type="hidden" name="_token" value="'+_csrf+'"><input type="hidden" name="_method" value="DELETE">';
    document.body.appendChild(f); f.submit();
  }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\markting\promanage\resources\views/content/index.blade.php ENDPATH**/ ?>