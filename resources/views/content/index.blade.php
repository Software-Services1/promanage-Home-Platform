@extends('layouts.app')
@section('title','خطة المحتوى')
@section('content')
@php
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
@endphp
<div x-data="{ add:false, reject:false, edit:null, view:null, form:{}, vrow:{} }">
  <div class="flex items-center gap-2 mb-3 flex-wrap">
    <span class="inline-flex items-center gap-2 bg-brand/10 text-brand text-sm font-bold px-3 py-1.5 rounded-xl">
      <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 4h18v17H3zM3 9h18M8 2v4M16 2v4"/></svg>
      خطة {{ $monthLabel($month) }}</span>
    <span class="text-xs text-muted">{{ $monthCount }} عنصر</span>
  </div>

  <div x-data="{ filters: {{ ($filters['company'] || $filters['date'] || $filters['assigned_to'] || $filters['work_type'] || $platform!=='all') ? 'true':'false' }} }" class="mb-4">
    <div class="flex flex-wrap items-center gap-2 mb-3">
      <button type="button" x-on:click="filters=!filters" class="inline-flex items-center gap-2 bg-white border border-line rounded-xl px-3.5 py-2.5 text-sm font-semibold text-ink shadow-soft hover:border-brand/40">
        <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 5h18l-7 8v6l-4-2v-4z"/></svg>
        الفلاتر
        @php $active = collect($filters)->filter(fn($v)=>$v!==null && $v!=='')->count() + ($platform!=='all'?1:0); @endphp
        @if($active)<span class="bg-brand text-white text-[10px] rounded-full w-5 h-5 grid place-items-center tnum">{{ $active }}</span>@endif
      </button>
      <div class="flex-1"></div>
      @if($canApprove && $monthCount>0)
        <label class="hidden sm:flex items-center gap-1.5 text-xs text-muted bg-white border border-line rounded-xl px-3 py-2.5 cursor-pointer">
          <input type="checkbox" class="accent-brand w-4 h-4" onclick="document.querySelectorAll('.rowchk').forEach(c=>c.checked=this.checked)"> تحديد الكل</label>
        <button type="button" onclick="bulkContent('approved')" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-3.5 py-2.5 rounded-xl">اعتماد المحدّد</button>
        <button type="button" onclick="bulkContent('rejected')" class="bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold px-3.5 py-2.5 rounded-xl">رفض المحدّد</button>
      @endif
      @if($canCreate)<button x-on:click="{{ $addSeed }}" class="inline-flex items-center gap-2 bg-brand hover:bg-brandd text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-lift">
        <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> عنصر جديد</button>@endif
    </div>

    <form method="GET" x-show="filters" x-cloak x-transition class="bg-white rounded-2xl border border-line shadow-soft p-4 grid grid-cols-2 md:grid-cols-5 gap-3">
      <div class="col-span-2 md:col-span-1"><label class="text-[11px] font-semibold text-muted mb-1 block">اسم الشركة</label>
        <input name="company" value="{{ $filters['company'] }}" placeholder="بحث..." class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm"></div>
      <div><label class="text-[11px] font-semibold text-muted mb-1 block">التاريخ</label>
        <input name="date" type="date" value="{{ $filters['date'] }}" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm tnum"></div>
      <div><label class="text-[11px] font-semibold text-muted mb-1 block">المصمم</label>
        <select name="assigned_to" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm">
          <option value="">الكل</option>
          @foreach($assignees as $a)<option value="{{ $a->id }}" @selected($filters['assigned_to']==$a->id)>{{ $a->name }}</option>@endforeach
        </select></div>
      <div><label class="text-[11px] font-semibold text-muted mb-1 block">نوع العمل</label>
        <select name="work_type" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm">
          <option value="">الكل</option>
          @foreach($workTypes as $wt)<option value="{{ $wt->key }}" @selected($filters['work_type']===$wt->key)>{{ $wt->label }}</option>@endforeach
        </select></div>
      <div><label class="text-[11px] font-semibold text-muted mb-1 block">المنصة</label>
        <select name="platform" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm">
          <option value="all">الكل</option>
          @foreach($platforms as $pf)<option @selected($platform===$pf)>{{ $pf }}</option>@endforeach
        </select></div>
      <div class="col-span-2 md:col-span-5 flex gap-2 justify-end">
        <a href="{{ route('content.index') }}" class="px-4 py-2 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">تصفير</a>
        <button class="px-5 py-2 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">تطبيق</button>
      </div>
    </form>
  </div>

  @if($monthCount===0)
    <div class="bg-white rounded-2xl border border-line shadow-soft grid place-items-center text-center py-16 px-6">
      <div class="w-14 h-14 rounded-2xl bg-brand/10 grid place-items-center text-brand mb-3">
        <svg viewBox="0 0 24 24" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 4h18v17H3zM3 9h18M8 2v4M16 2v4"/></svg></div>
      <div class="ff-display font-bold">لا توجد خطة لشهر {{ $monthLabel($month) }} بعد</div>
      <div class="text-muted text-sm mt-1 mb-4">ابدأ بإضافة أول عنصر، أو انتقل لشهر آخر من الأعلى.</div>
      @if($canCreate)<button x-on:click="{{ $addSeed }}" class="bg-brand hover:bg-brandd text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-lift">إضافة أول عنصر</button>@else<span class="text-xs text-muted">لا توجد عناصر مسندة إليك في هذا الشهر.</span>@endif
    </div>
  @else
    @php $byStatus = collect($rows)->groupBy('status'); @endphp
    <div class="flex gap-3 overflow-x-auto pb-3 -mx-1 px-1 snap-x">
      @foreach($statuses as $st)
        @php $items = $byStatus[$st] ?? collect(); @endphp
        <div class="w-[280px] sm:w-[300px] shrink-0 snap-start">
          <div class="flex items-center justify-between mb-2.5 px-1">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full {{ $statusDot[$st] ?? 'bg-gray-300' }}"></span>
              <h4 class="ff-display font-bold text-sm">{{ $st }}</h4>
            </div>
            <span class="text-xs bg-white border border-line rounded-full px-2 py-0.5 tnum text-muted">{{ $items->count() }}</span>
          </div>
          <div class="space-y-3 bg-canvas/50 rounded-2xl p-2 min-h-[80px] border border-line/60">
            @forelse($items as $p)
              @include('content._card')
            @empty
              <div class="text-center text-[11px] text-muted py-6">—</div>
            @endforelse
          </div>
        </div>
      @endforeach
    </div>
  @endif

  <!-- نافذة إضافة صف -->
  <div x-cloak x-show="add" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="add=false"></div>
    <form method="POST" action="{{ route('content.store') }}" enctype="multipart/form-data" class="relative bg-white rounded-2xl shadow-lift w-full max-w-lg p-5 space-y-3 max-h-[90vh] overflow-y-auto">@csrf
      <h3 class="ff-display font-bold text-lg">عنصر جديد في خطة {{ $monthLabel($month) }}</h3>
      @include('content._fields', ['edit'=>false])
      <div class="flex gap-2 justify-end pt-1">
        <button type="button" x-on:click="add=false" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">إضافة</button>
      </div>
    </form>
  </div>

  <!-- نافذة تعديل صف -->
  @if($canUpdate)
  <div x-cloak x-show="edit!==null" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="edit=null"></div>
    <form method="POST" x-bind:action="'/content/'+form.id" enctype="multipart/form-data" class="relative bg-white rounded-2xl shadow-lift w-full max-w-lg p-5 space-y-3 max-h-[90vh] overflow-y-auto">@csrf @method('PUT')
      <h3 class="ff-display font-bold text-lg">تعديل العنصر</h3>
      @include('content._fields', ['edit'=>true])
      <div class="flex gap-2 justify-end pt-1">
        <button type="button" x-on:click="edit=null" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">حفظ</button>
      </div>
    </form>
  </div>
  @endif

  <!-- نافذة استعراض/مساهمة (للمصمم والمونتير) -->
  @if($canContribute && ! $canUpdate)
  <div x-cloak x-show="view!==null" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="view=null"></div>
    <form method="POST" x-bind:action="'/content/'+vrow.id+'/contribute'" enctype="multipart/form-data" class="relative bg-white rounded-2xl shadow-lift w-full max-w-lg p-5 space-y-3 max-h-[90vh] overflow-y-auto">@csrf
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
      @can('add content note')
        <div><label class="text-xs font-semibold text-muted mb-1 block">ملاحظة</label>
          <textarea name="notes" rows="2" x-model="vrow.notes" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm resize-none"></textarea></div>
      @endcan
      <template x-if="vrow.owner">
        <div class="space-y-3">
          @can('update content status')
            <div><label class="text-xs font-semibold text-muted mb-1 block">حالة التصميم</label>
              <select name="status" x-model="vrow.status" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">
                @foreach(['قيد التصميم','يحتاج تعديل','جاهز للنشر'] as $s)<option>{{ $s }}</option>@endforeach
              </select></div>
          @endcan
          @can('upload design')
            <div><label class="text-xs font-semibold text-muted mb-1 block">رفع/تعديل التصميم</label>
              <input name="design" type="file" accept="image/*" class="w-full text-xs file:bg-brand file:text-white file:border-0 file:rounded-lg file:px-3 file:py-2 file:ml-2 file:cursor-pointer bg-canvas border border-line rounded-xl px-2 py-2"></div>
          @endcan
        </div>
      </template>
      <div class="flex gap-2 justify-end pt-1">
        <button type="button" x-on:click="view=null" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إغلاق</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">حفظ</button>
      </div>
    </form>
  </div>
  @endif

  <!-- نافذة عدم الموافقة (كامل الشهر) -->
  <div x-cloak x-show="reject" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="reject=false"></div>
    <form method="POST" action="{{ route('content.reject') }}" class="relative bg-white rounded-2xl shadow-lift w-full max-w-md p-5 space-y-3">@csrf
      <h3 class="ff-display font-bold text-lg">سبب عدم الموافقة</h3>
      <textarea name="approval_note" rows="3" required class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm resize-none"></textarea>
      <div class="flex gap-2 justify-end">
        <button type="button" x-on:click="reject=false" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-rose-600 text-white hover:bg-rose-700">تأكيد</button>
      </div>
    </form>
  </div>
</div>

<form id="rowActionForm" method="POST" class="hidden">@csrf</form>
<script>
  const _csrf = '{{ csrf_token() }}';
  function bulkContent(state){
    const ids=[...document.querySelectorAll('.rowchk:checked')].map(c=>c.value);
    if(!ids.length){ alert('اختر عناصر أولاً بالضغط على مربّع الكارت'); return; }
    const f=document.createElement('form'); f.method='POST'; f.action='{{ route('content.bulkApprove') }}';
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
@endsection
