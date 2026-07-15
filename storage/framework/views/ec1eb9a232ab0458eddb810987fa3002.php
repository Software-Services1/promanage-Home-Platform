<?php $__env->startSection('title','إدارة المهام'); ?>
<?php $__env->startSection('content'); ?>
<?php
  $stageColor = [
    'فكرة'=>'bg-gray-100 text-gray-600','خطة'=>'bg-slate-100 text-slate-600','تصميم'=>'bg-violet-50 text-brand',
    'تنفيذ'=>'bg-indigo-50 text-indigo-600','مراجعة'=>'bg-sky-50 text-sky-700','جاهز'=>'bg-amber-50 text-golddk','منشور'=>'bg-emerald-50 text-emerald-700'];
  $canManageTasks = auth()->user()->can('update tasks');
  $canCreateTasks = auth()->user()->can('create tasks');
?>
<div x-data="{ add:false, edit:null, viewT:null, form:{}, vt:{}, aAdd:[{user_id:'',type:''}] }">
  <div x-data="boardFilter({perPage:15, contains:['user']})">
  <div class="flex items-center gap-2 mb-4 flex-wrap">
    <span class="inline-flex items-center gap-2 bg-brand/10 text-brand text-sm font-bold px-3 py-1.5 rounded-xl">
      <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M9 11l3 3 8-8"/></svg>
      مهام <?php echo e($monthLabel($month)); ?></span>
    <span class="text-xs text-muted"><span class="tnum" x-text="total"></span> مهمة — الأحدث أولاً</span>
    <div class="flex-1"></div>
    <button type="button" x-on:click.stop="flt=!flt" class="inline-flex items-center gap-2 bg-white border border-line rounded-xl px-3.5 py-2.5 text-sm font-semibold text-ink shadow-soft hover:border-brand/40">
      <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 5h18l-7 8v6l-4-2v-4z"/></svg> الفلاتر
      <span x-show="Object.values(q).some(v=>v)" class="bg-brand text-white text-[10px] rounded-full min-w-5 h-5 px-1 grid place-items-center tnum" x-text="Object.values(q).filter(v=>v).length"></span>
    </button>
    <?php if($canCreateTasks): ?><button x-on:click="aAdd=[{user_id:'',type:''}]; add=true" class="inline-flex items-center gap-2 bg-brand hover:bg-brandd text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-lift">
      <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> مهمة جديدة</button><?php endif; ?>
  </div>

  <div x-show="flt" x-cloak x-transition x-on:click.outside="flt=false" class="relative bg-white rounded-2xl border border-line shadow-soft p-4 pt-11 grid grid-cols-2 md:grid-cols-6 gap-3 mb-4">
    <button type="button" x-on:click="flt=false" title="إغلاق" class="absolute top-2.5 left-2.5 w-8 h-8 rounded-lg grid place-items-center text-muted hover:text-rose-600 hover:bg-rose-50">
      <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
    <div><label class="text-[11px] font-semibold text-muted mb-1 block">النوع</label>
      <select x-model="q.type" x-on:change="onFilter()" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm">
        <option value="">الكل</option>
        <?php $__currentLoopData = $taskTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($tt->label); ?>"><?php echo e($tt->label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select></div>
    <div><label class="text-[11px] font-semibold text-muted mb-1 block">المرحلة</label>
      <select x-model="q.stage" x-on:change="onFilter()" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm">
        <option value="">الكل</option>
        <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option><?php echo e($s); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select></div>
    <div><label class="text-[11px] font-semibold text-muted mb-1 block">المشرف</label>
      <select x-model="q.sup" x-on:change="onFilter()" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm">
        <option value="">الكل</option>
        <?php $__currentLoopData = $supervisors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sv->name); ?>"><?php echo e($sv->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select></div>
    <div><label class="text-[11px] font-semibold text-muted mb-1 block">المصمم</label>
      <select x-model="q.user" x-on:change="onFilter()" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm">
        <option value="">الكل</option>
        <?php $__currentLoopData = $assignees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($a->name); ?>"><?php echo e($a->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select></div>
    <div><label class="text-[11px] font-semibold text-muted mb-1 block">تاريخ التسليم</label>
      <input x-model="q.date" x-on:change="onFilter()" type="date" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm tnum"></div>
    <div><label class="text-[11px] font-semibold text-muted mb-1 block">إبداعية</label>
      <select x-model="q.creative" x-on:change="onFilter()" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm">
        <option value="">الكل</option>
        <option value="true">نعم</option>
        <option value="false">لا</option>
      </select></div>
    <div class="col-span-2 md:col-span-6 flex gap-2 justify-end">
      <button type="button" x-on:click="reset()" class="px-4 py-2 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">تصفير</button>
    </div>
  </div>

  <?php if($tasks->isEmpty()): ?>
    <div class="bg-white rounded-2xl border border-line shadow-soft grid place-items-center text-center py-16 px-6">
      <div class="w-14 h-14 rounded-2xl bg-brand/10 grid place-items-center text-brand mb-3">
        <svg viewBox="0 0 24 24" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 11l3 3 8-8"/></svg></div>
      <div class="ff-display font-bold">لا توجد مهام مطابقة</div>
      <div class="text-muted text-sm mt-1">جرّب تعديل الفلاتر أو أضف مهمة جديدة.</div>
    </div>
  <?php else: ?>
  <div class="bg-white rounded-2xl border border-line shadow-soft overflow-x-auto">
    <table class="w-full text-sm min-w-[900px]">
      <thead><tr class="text-right text-muted text-xs border-b border-line bg-canvas/60">
        <th class="p-4 font-semibold">المهمة</th>
        <th class="p-4 font-semibold">النوع</th>
        <th class="p-4 font-semibold">المرحلة</th>
        <th class="p-4 font-semibold">المشرف</th>
        <th class="p-4 font-semibold">المصمم</th>
        <th class="p-4 font-semibold">تاريخ التسليم</th>
        <th class="p-4 font-semibold">إبداعية</th>
        <th class="p-4 font-semibold">النقاط</th>
        <th class="p-4"></th>
      </tr></thead>
      <tbody>
      <?php $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $asgNames = $t->assignees->pluck('name')->join('، '); ?>
        <tr data-row
            data-type="<?php echo e($t->typeLabel()); ?>"
            data-stage="<?php echo e($t->stage); ?>"
            data-sup="<?php echo e(optional($t->supervisor)->name); ?>"
            data-user="<?php echo e($asgNames ?: optional($t->user)->name); ?>"
            data-date="<?php echo e(optional($t->due_date)->format('Y-m-d')); ?>"
            data-creative="<?php echo e($t->is_creative ? 'true' : 'false'); ?>"
            class="border-b border-line/60 hover:bg-canvas/60 align-middle">
          <td class="p-4">
            <div class="font-semibold text-ink"><?php echo e($t->title); ?></div>
            <?php if($t->description): ?><div class="text-[11px] text-muted truncate max-w-[240px]"><?php echo e(\Illuminate\Support\Str::limit($t->description, 60)); ?></div><?php endif; ?>
          </td>
          <td class="p-4 text-muted whitespace-nowrap"><?php echo e($t->typeLabel()); ?></td>
          <td class="p-4"><span class="text-xs font-semibold px-2 py-1 rounded-lg <?php echo e($stageColor[$t->stage] ?? 'bg-gray-100'); ?>"><?php echo e($t->stage); ?></span></td>
          <td class="p-4 text-muted whitespace-nowrap"><?php echo e(optional($t->supervisor)->name ?? '—'); ?></td>
          <td class="p-4 whitespace-nowrap"><?php echo e($asgNames ?: optional($t->user)->name ?? '—'); ?></td>
          <td class="p-4 tnum text-muted whitespace-nowrap"><?php echo e(optional($t->due_date)->format('Y-m-d') ?? '—'); ?>

            <?php if($t->is_late): ?><span class="block text-[10px] text-rose-600 font-semibold">متأخر</span><?php endif; ?>
          </td>
          <td class="p-4">
            <?php if($t->is_creative): ?><span class="inline-flex items-center gap-1 text-[11px] font-bold text-golddk bg-amber-50 px-2 py-1 rounded-lg">
              <svg viewBox="0 0 24 24" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l2.4 7.4H22l-6 4.4 2.3 7.2-6.3-4.6-6.3 4.6L7.9 13.8 2 9.4h7.6z"/></svg> نعم</span>
            <?php else: ?><span class="text-muted text-xs">—</span><?php endif; ?>
          </td>
          <td class="p-4"><span class="bg-violet-50 text-brand px-2 py-1 rounded-lg text-xs font-bold tnum"><?php echo e($t->computed_points); ?></span></td>
          <td class="p-4">
            <div class="flex items-center gap-1 justify-end">
              <?php if($canManageTasks): ?>
                <button type="button" class="w-8 h-8 rounded-lg grid place-items-center text-muted hover:text-brand hover:bg-violet-50" title="تعديل"
                  x-on:click="edit=<?php echo e($t->id); ?>; form={ id:<?php echo e($t->id); ?>, title:<?php echo \Illuminate\Support\Js::from($t->title)->toHtml() ?>, description:<?php echo \Illuminate\Support\Js::from($t->description)->toHtml() ?>, stage:<?php echo \Illuminate\Support\Js::from($t->stage)->toHtml() ?>, supervisor_id:<?php echo e($t->supervisor_id ?? 'null'); ?>, due_date:<?php echo \Illuminate\Support\Js::from(optional($t->due_date)->format('Y-m-d'))->toHtml() ?>, is_late:<?php echo e($t->is_late?'true':'false'); ?>, is_creative:<?php echo e($t->is_creative?'true':'false'); ?>, assignees:<?php echo \Illuminate\Support\Js::from($t->assignees->map(fn($a)=>['user_id'=>(string)$a->id,'type'=>$a->pivot->type ?: $t->type])->values())->toHtml() ?> }">
                  <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></button>
              <?php else: ?>
                <button type="button" class="w-8 h-8 rounded-lg grid place-items-center text-muted hover:text-brand hover:bg-violet-50" title="استعراض"
                  x-on:click="viewT=<?php echo e($t->id); ?>; vt={ title:<?php echo \Illuminate\Support\Js::from($t->title)->toHtml() ?>, description:<?php echo \Illuminate\Support\Js::from($t->description)->toHtml() ?>, type:<?php echo \Illuminate\Support\Js::from($t->typeLabel())->toHtml() ?>, stage:<?php echo \Illuminate\Support\Js::from($t->stage)->toHtml() ?>, assignee:<?php echo \Illuminate\Support\Js::from($asgNames ?: optional($t->user)->name)->toHtml() ?>, due:<?php echo \Illuminate\Support\Js::from(optional($t->due_date)->format('Y-m-d'))->toHtml() ?>, points:'<?php echo e($t->computed_points); ?>', late:<?php echo e($t->is_late?'true':'false'); ?>, creative:<?php echo e($t->is_creative?'true':'false'); ?> }">
                  <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg></button>
              <?php endif; ?>
              <?php if(! $canManageTasks && $t->assignees->contains('id', auth()->id())): ?>
                <form method="POST" action="<?php echo e(route('tasks.status',$t)); ?>"><?php echo csrf_field(); ?>
                  <select name="stage" onchange="this.form.submit()" class="text-xs bg-canvas border border-line rounded-lg px-2 py-1.5 outline-none focus:border-brand">
                    <?php $__currentLoopData = ['تصميم','تنفيذ','مراجعة','جاهز']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php if($t->stage===$o): echo 'selected'; endif; ?>><?php echo e($o); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
    <div x-show="total===0" class="text-center text-sm text-muted py-10">لا توجد مهام مطابقة للفلاتر.</div>
  </div>
  <?php echo $__env->make('partials.pager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php endif; ?>
  </div>

  <!-- نافذة استعراض تفاصيل المهمة -->
  <div x-cloak x-show="viewT!==null" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="viewT=null"></div>
    <div class="relative bg-white rounded-2xl shadow-lift w-full max-w-md p-5 space-y-3">
      <h3 class="ff-display font-bold text-lg" x-text="vt.title"></h3>
      <div class="text-sm text-muted" x-show="vt.description" x-text="vt.description"></div>
      <div class="grid grid-cols-2 gap-3 text-sm bg-canvas rounded-xl p-3">
        <div><span class="text-muted text-[11px] block">النوع</span><b x-text="vt.type"></b></div>
        <div><span class="text-muted text-[11px] block">المرحلة</span><b x-text="vt.stage"></b></div>
        <div><span class="text-muted text-[11px] block">الموظف</span><b x-text="vt.assignee"></b></div>
        <div><span class="text-muted text-[11px] block">تاريخ التسليم</span><b class="tnum" x-text="vt.due"></b></div>
        <div><span class="text-muted text-[11px] block">النقاط</span><b class="tnum" x-text="vt.points"></b></div>
        <div><span class="text-muted text-[11px] block">الحالة</span><b x-text="(vt.late?'متأخر ':'')+(vt.creative?'إبداعي':'')||'—'"></b></div>
      </div>
      <div class="flex justify-end"><button type="button" x-on:click="viewT=null" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إغلاق</button></div>
    </div>
  </div>

  <!-- نافذة إضافة مهمة -->
  <div x-cloak x-show="add" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="add=false"></div>
    <form method="POST" action="<?php echo e(route('tasks.store')); ?>" class="relative bg-white rounded-2xl shadow-lift w-full max-w-md p-5 space-y-3 max-h-[90vh] overflow-y-auto"><?php echo csrf_field(); ?>
      <h3 class="ff-display font-bold text-lg">مهمة جديدة — <?php echo e($monthLabel($month)); ?></h3>
      <div><label class="text-xs font-semibold text-muted mb-1 block">العنوان</label>
        <input name="title" required class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
      <div><label class="text-xs font-semibold text-muted mb-1 block">الوصف</label>
        <textarea name="description" rows="3" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm resize-none"></textarea></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-xs font-semibold text-muted mb-1 block">المرحلة</label>
          <select name="stage" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"><?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option><?php echo e($s); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div><label class="text-xs font-semibold text-muted mb-1 block">تاريخ التسليم</label>
          <input name="due_date" type="date" value="<?php echo e($month); ?>-01" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
        <div class="col-span-2"><label class="text-xs font-semibold text-muted mb-1 block">مشرف متابِع (اختياري)</label>
          <select name="supervisor_id" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">
            <option value="">— بدون —</option>
            <?php $__currentLoopData = $supervisors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sv->id); ?>"><?php echo e($sv->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select></div>
      </div>
      <div class="bg-canvas/60 rounded-xl p-3 border border-line">
        <label class="text-xs font-bold text-ink mb-2 block">المصمّمون ونوع عمل كلٍّ منهم</label>
        <div class="space-y-2">
          <template x-for="(row,i) in aAdd" :key="i">
            <div class="flex gap-2 items-center">
              <select :name="`assignees[${i}][user_id]`" x-model="row.user_id" required class="flex-1 min-w-0 bg-white border border-line rounded-lg px-2 py-2 text-sm">
                <option value="">المصمم</option>
                <?php $__currentLoopData = $assignees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($a->id); ?>"><?php echo e($a->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
              <select :name="`assignees[${i}][type]`" x-model="row.type" required class="flex-1 min-w-0 bg-white border border-line rounded-lg px-2 py-2 text-sm">
                <option value="">نوع العمل</option>
                <?php $__currentLoopData = $taskTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($tt->key); ?>"><?php echo e($tt->label); ?> (<?php echo e($tt->points); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
              <button type="button" x-show="aAdd.length>1" x-on:click="aAdd.splice(i,1)" class="w-8 h-8 shrink-0 rounded-lg grid place-items-center text-rose-500 hover:bg-rose-50">
                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
            </div>
          </template>
        </div>
        <button type="button" x-on:click="aAdd.push({user_id:'',type:''})" class="text-brand text-xs font-bold mt-2 inline-flex items-center gap-1">
          <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> إضافة مصمّم</button>
      </div>
      <div class="flex gap-4">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_late" value="1" class="accent-brand w-4 h-4"> متأخر</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_creative" value="1" class="accent-gold w-4 h-4"> إبداعي</label>
      </div>
      <div class="flex gap-2 justify-end pt-1">
        <button type="button" x-on:click="add=false" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">إضافة</button>
      </div>
    </form>
  </div>

  <!-- نافذة تعديل مهمة (للمدراء) -->
  <?php if($canManageTasks): ?>
  <div x-cloak x-show="edit!==null" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="edit=null"></div>
    <form method="POST" x-bind:action="'/tasks/'+form.id" class="relative bg-white rounded-2xl shadow-lift w-full max-w-md p-5 space-y-3 max-h-[90vh] overflow-y-auto"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
      <h3 class="ff-display font-bold text-lg">تعديل المهمة</h3>
      <div><label class="text-xs font-semibold text-muted mb-1 block">العنوان</label>
        <input name="title" x-model="form.title" required class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
      <div><label class="text-xs font-semibold text-muted mb-1 block">الوصف</label>
        <textarea name="description" x-model="form.description" rows="3" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm resize-none"></textarea></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-xs font-semibold text-muted mb-1 block">المرحلة</label>
          <select name="stage" x-model="form.stage" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"><?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option><?php echo e($s); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div><label class="text-xs font-semibold text-muted mb-1 block">تاريخ التسليم</label>
          <input name="due_date" type="date" x-model="form.due_date" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
        <div class="col-span-2"><label class="text-xs font-semibold text-muted mb-1 block">مشرف متابِع</label>
          <select name="supervisor_id" x-model="form.supervisor_id" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">
            <option value="">— بدون —</option>
            <?php $__currentLoopData = $supervisors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sv->id); ?>"><?php echo e($sv->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select></div>
      </div>
      <div class="bg-canvas/60 rounded-xl p-3 border border-line">
        <label class="text-xs font-bold text-ink mb-2 block">المصمّمون ونوع عمل كلٍّ منهم</label>
        <div class="space-y-2">
          <template x-for="(row,i) in (form.assignees || [])" :key="i">
            <div class="flex gap-2 items-center">
              <select :name="`assignees[${i}][user_id]`" x-model="row.user_id" required class="flex-1 min-w-0 bg-white border border-line rounded-lg px-2 py-2 text-sm">
                <option value="">المصمم</option>
                <?php $__currentLoopData = $assignees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($a->id); ?>"><?php echo e($a->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
              <select :name="`assignees[${i}][type]`" x-model="row.type" required class="flex-1 min-w-0 bg-white border border-line rounded-lg px-2 py-2 text-sm">
                <option value="">نوع العمل</option>
                <?php $__currentLoopData = $taskTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($tt->key); ?>"><?php echo e($tt->label); ?> (<?php echo e($tt->points); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
              <button type="button" x-show="form.assignees.length>1" x-on:click="form.assignees.splice(i,1)" class="w-8 h-8 shrink-0 rounded-lg grid place-items-center text-rose-500 hover:bg-rose-50">
                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
            </div>
          </template>
        </div>
        <button type="button" x-on:click="form.assignees=(form.assignees||[]); form.assignees.push({user_id:'',type:''})" class="text-brand text-xs font-bold mt-2 inline-flex items-center gap-1">
          <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> إضافة مصمّم</button>
      </div>
      <div class="flex gap-4">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_late" value="1" x-model="form.is_late" class="accent-brand w-4 h-4"> متأخر</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_creative" value="1" x-model="form.is_creative" class="accent-gold w-4 h-4"> إبداعي</label>
      </div>
      <div class="flex items-center justify-between pt-1">
        <button type="button" x-on:click="if(confirm('حذف هذه المهمة؟')){ document.getElementById('del-task').submit(); }" class="text-sm font-semibold text-rose-600 hover:underline">حذف</button>
        <div class="flex gap-2">
          <button type="button" x-on:click="edit=null" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
          <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">حفظ</button>
        </div>
      </div>
    </form>
    <form id="del-task" method="POST" x-bind:action="'/tasks/'+form.id" class="hidden"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?></form>
  </div>
  <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\markting\promanage\resources\views/tasks/index.blade.php ENDPATH**/ ?>