<div class="grid grid-cols-2 gap-3">
  <div class="col-span-2"><label class="text-xs font-semibold text-muted mb-1 block">اسم الشركة</label>
    <input name="company_name" x-model="form.company_name" placeholder="اسم العميل/الشركة" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
  <div><label class="text-xs font-semibold text-muted mb-1 block">المنصة</label>
    <select name="platform" x-model="form.platform" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">@foreach($platforms as $p)<option>{{ $p }}</option>@endforeach</select></div>
  <div><label class="text-xs font-semibold text-muted mb-1 block">التاريخ</label>
    <input name="plan_date" type="date" x-model="form.plan_date" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
  <div><label class="text-xs font-semibold text-muted mb-1 block">التوقيت</label>
    <input name="plan_time" type="time" x-model="form.plan_time" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
  <div><label class="text-xs font-semibold text-muted mb-1 block">نوع المحتوى</label>
    <select name="content_type" x-model="form.content_type" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">@foreach($contentTypes as $c)<option>{{ $c }}</option>@endforeach</select></div>
  <div><label class="text-xs font-semibold text-muted mb-1 block">نوع المنشور</label>
    <select name="post_type" x-model="form.post_type" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">@foreach($postTypes as $pt)<option>{{ $pt }}</option>@endforeach</select></div>
  <div><label class="text-xs font-semibold text-muted mb-1 block">الحالة</label>
    <select name="status" x-model="form.status" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">@foreach($statuses as $s)<option>{{ $s }}</option>@endforeach</select></div>
  <div class="col-span-2"><label class="text-xs font-semibold text-muted mb-1 block">المصمم المسؤول</label>
    <select name="assigned_to" x-model="form.assigned_to" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">@foreach($assignees as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
</div>
<div><label class="text-xs font-semibold text-muted mb-1 block">محتوى التصميم</label>
  <input name="design_content" x-model="form.design_content" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
<div><label class="text-xs font-semibold text-muted mb-1 block">نص التصميم</label>
  <input name="design_text" x-model="form.design_text" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
<div><label class="text-xs font-semibold text-muted mb-1 block">الكابشن</label>
  <textarea name="caption" x-model="form.caption" rows="2" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm resize-none"></textarea></div>
<div><label class="text-xs font-semibold text-muted mb-1 block">نص المنشور</label>
  <textarea name="post_text" x-model="form.post_text" rows="2" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm resize-none"></textarea></div>
<div><label class="text-xs font-semibold text-muted mb-1 block">ملاحظات</label>
  <input name="notes" x-model="form.notes" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
<div class="grid grid-cols-2 gap-3">
  <div><label class="text-xs font-semibold text-muted mb-1 block">رابط مرجعي</label>
    <input name="reference_link" type="url" x-model="form.reference_link" placeholder="https://" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
  <div><label class="text-xs font-semibold text-muted mb-1 block">صورة/ملف مرجعي</label>
    <input name="reference_file" type="file" class="w-full text-xs file:bg-brand file:text-white file:border-0 file:rounded-lg file:px-3 file:py-2 file:ml-2 file:cursor-pointer bg-canvas border border-line rounded-xl px-2 py-2"></div>
</div>
<div class="grid grid-cols-2 gap-3">
  <div><label class="text-xs font-semibold text-muted mb-1 block">نوع العمل (للنقاط)</label>
    <select name="work_type" x-model="form.work_type" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">
      <option value="">— بدون احتساب —</option>
      @foreach($workTypes as $wt)<option value="{{ $wt->key }}">{{ $wt->label }} ({{ $wt->points }})</option>@endforeach
    </select></div>
  <div><label class="text-xs font-semibold text-muted mb-1 block">مشرف متابِع</label>
    <select name="supervisor_id" x-model="form.supervisor_id" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">
      <option value="">— بدون —</option>
      @foreach($supervisors as $sv)<option value="{{ $sv->id }}">{{ $sv->name }}</option>@endforeach
    </select></div>
</div>
