@extends('layouts.app')
@section('title','إدارة المهام')
@section('content')
@php
  $stageColor = [
    'فكرة'=>'bg-gray-100 text-gray-600','خطة'=>'bg-slate-100 text-slate-600','تصميم'=>'bg-violet-50 text-brand',
    'تنفيذ'=>'bg-indigo-50 text-indigo-600','مراجعة'=>'bg-sky-50 text-sky-700','جاهز'=>'bg-amber-50 text-golddk','منشور'=>'bg-emerald-50 text-emerald-700'];
  $canManageTasks = auth()->user()->can('update tasks');
  $canCreateTasks = auth()->user()->can('create tasks');
@endphp
<div x-data="{ add:false, edit:null, viewT:null, form:{}, vt:{}, flt: {{ collect($filters)->filter(fn($v)=>$v!==null && $v!=='')->count() ? 'true':'false' }} }">
  <div class="flex items-center gap-2 mb-4 flex-wrap">
    <span class="inline-flex items-center gap-2 bg-brand/10 text-brand text-sm font-bold px-3 py-1.5 rounded-xl">
      <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M9 11l3 3 8-8"/></svg>
      مهام {{ $monthLabel($month) }}</span>
    @if($canManageTasks)<span class="text-xs text-muted hidden sm:inline">اسحب البطاقة بين الأعمدة لتغيير مرحلتها</span>@endif
    <div class="flex-1"></div>
    <button type="button" x-on:click="flt=!flt" class="inline-flex items-center gap-2 bg-white border border-line rounded-xl px-3.5 py-2.5 text-sm font-semibold text-ink shadow-soft hover:border-brand/40">
      <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 5h18l-7 8v6l-4-2v-4z"/></svg> الفلاتر
      @php $fcount = collect($filters)->filter(fn($v)=>$v!==null && $v!=='')->count(); @endphp
      @if($fcount)<span class="bg-brand text-white text-[10px] rounded-full w-5 h-5 grid place-items-center tnum">{{ $fcount }}</span>@endif
    </button>
    @if($canCreateTasks)<button x-on:click="add=true" class="inline-flex items-center gap-2 bg-brand hover:bg-brandd text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-lift">
      <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> مهمة جديدة</button>@endif
  </div>

  <form method="GET" x-show="flt" x-cloak x-transition class="bg-white rounded-2xl border border-line shadow-soft p-4 grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div><label class="text-[11px] font-semibold text-muted mb-1 block">الموظف</label>
      <select name="user_id" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm">
        <option value="">الكل</option>
        @foreach($assignees as $a)<option value="{{ $a->id }}" @selected($filters['user_id']==$a->id)>{{ $a->name }}</option>@endforeach
      </select></div>
    <div><label class="text-[11px] font-semibold text-muted mb-1 block">النوع</label>
      <select name="type" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm">
        <option value="">الكل</option>
        @foreach($taskTypes as $tt)<option value="{{ $tt->key }}" @selected($filters['type']===$tt->key)>{{ $tt->label }}</option>@endforeach
      </select></div>
    <div><label class="text-[11px] font-semibold text-muted mb-1 block">المرحلة</label>
      <select name="stage" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm">
        <option value="">الكل</option>
        @foreach($stages as $s)<option @selected($filters['stage']===$s)>{{ $s }}</option>@endforeach
      </select></div>
    <div><label class="text-[11px] font-semibold text-muted mb-1 block">تاريخ التسليم</label>
      <input name="date" type="date" value="{{ $filters['date'] }}" class="w-full bg-canvas border border-line rounded-xl px-3 py-2 text-sm tnum"></div>
    <div class="col-span-2 md:col-span-4 flex gap-2 justify-end">
      <a href="{{ route('tasks.index') }}" class="px-4 py-2 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">تصفير</a>
      <button class="px-5 py-2 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">تطبيق</button>
    </div>
  </form>

  <div class="flex gap-3 overflow-x-auto pb-2">
    @foreach($groups as $stage => $items)
      <div class="bg-canvas/60 rounded-2xl border border-line p-3 w-[280px] shrink-0">
        <div class="flex items-center justify-between mb-3 px-1">
          <h4 class="ff-display font-bold text-sm">{{ $stage }}</h4>
          <span class="text-xs bg-white border border-line rounded-full px-2 py-0.5 tnum text-muted">{{ count($items) }}</span>
        </div>
        <div class="space-y-2 min-h-[40px]" data-stage-list data-stage="{{ $stage }}">
          @foreach($items as $t)
            <div class="bg-white rounded-xl border border-line p-3 shadow-soft {{ $canManageTasks ? 'cursor-grab active:cursor-grabbing' : '' }}" data-id="{{ $t->id }}">
              <div class="flex items-start justify-between gap-2">
                <div class="text-sm font-semibold leading-snug">{{ $t->title }}</div>
                <span class="text-[10px] font-bold tnum text-brand bg-violet-50 rounded-lg px-1.5 py-0.5 shrink-0">{{ $t->computed_points }}</span>
              </div>
              @if($t->description)<div class="text-[11px] text-muted mt-1 line-clamp-2">{{ \Illuminate\Support\Str::limit($t->description, 90) }}</div>@endif
              <div class="text-[11px] text-muted mt-1.5">{{ $t->typeLabel() }} • {{ $t->user->name }}</div>
              @if($t->supervisor)<div class="text-[10px] text-brand mt-0.5 inline-flex items-center gap-1"><svg viewBox="0 0 24 24" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 18a4 4 0 0 0-8 0M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg>متابعة: {{ $t->supervisor->name }}</div>@endif
              @if(! $canManageTasks)
                <button type="button" class="text-[10px] text-brand font-semibold mt-1"
                  x-on:click="viewT={{ $t->id }}; vt={ title:@js($t->title), description:@js($t->description), type:@js($t->typeLabel()), stage:@js($t->stage), assignee:@js($t->user->name), due:@js(optional($t->due_date)->format('Y-m-d')), points:'{{ $t->computed_points }}', late:{{ $t->is_late?'true':'false' }}, creative:{{ $t->is_creative?'true':'false' }} }">استعراض التفاصيل</button>
              @endif
              <div class="flex items-center gap-2 mt-2 flex-wrap">
                <span class="text-[10px] px-2 py-0.5 rounded-md {{ $stageColor[$stage] ?? 'bg-gray-100' }}">{{ $t->stage }}</span>
                @if($t->is_late)<span class="text-[10px] px-2 py-0.5 rounded-md bg-rose-50 text-rose-600">متأخر</span>@endif
                @if($t->is_creative)<span class="text-[10px] px-2 py-0.5 rounded-md bg-amber-50 text-golddk">إبداعي</span>@endif
                @if($canManageTasks)
                  <button type="button" class="text-[10px] text-brand font-semibold mr-auto"
                    x-on:click="edit={{ $t->id }}; form={ id:{{ $t->id }}, title:@js($t->title), description:@js($t->description), type:@js($t->type), stage:@js($t->stage), user_id:{{ $t->user_id }}, supervisor_id:{{ $t->supervisor_id ?? "null" }}, due_date:@js(optional($t->due_date)->format('Y-m-d')), is_late:{{ $t->is_late?'true':'false' }}, is_creative:{{ $t->is_creative?'true':'false' }} }">تعديل</button>
                @endif
              </div>
              @if(! $canManageTasks && $t->user_id === auth()->id())
                <form method="POST" action="{{ route('tasks.status',$t) }}" class="mt-2">@csrf
                  <select name="stage" onchange="this.form.submit()" class="w-full text-xs bg-canvas border border-line rounded-lg px-2 py-1.5 outline-none focus:border-brand">
                    @foreach(['تصميم','تنفيذ','مراجعة','جاهز'] as $o)<option @selected($t->stage===$o)>{{ $o }}</option>@endforeach
                  </select>
                </form>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    @endforeach
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
    <form method="POST" action="{{ route('tasks.store') }}" class="relative bg-white rounded-2xl shadow-lift w-full max-w-md p-5 space-y-3 max-h-[90vh] overflow-y-auto">@csrf
      <h3 class="ff-display font-bold text-lg">مهمة جديدة — {{ $monthLabel($month) }}</h3>
      <div><label class="text-xs font-semibold text-muted mb-1 block">العنوان</label>
        <input name="title" required class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
      <div><label class="text-xs font-semibold text-muted mb-1 block">الوصف</label>
        <textarea name="description" rows="3" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm resize-none"></textarea></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-xs font-semibold text-muted mb-1 block">النوع</label>
          <select name="type" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">
            @foreach($taskTypes as $tt)<option value="{{ $tt->key }}">{{ $tt->label }} ({{ $tt->points }})</option>@endforeach
          </select></div>
        <div><label class="text-xs font-semibold text-muted mb-1 block">المرحلة</label>
          <select name="stage" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">@foreach($stages as $s)<option>{{ $s }}</option>@endforeach</select></div>
        <div><label class="text-xs font-semibold text-muted mb-1 block">الموظف</label>
          <select name="user_id" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">@foreach($assignees as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
        <div><label class="text-xs font-semibold text-muted mb-1 block">مشرف متابِع (اختياري)</label>
          <select name="supervisor_id" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">
            <option value="">— بدون —</option>
            @foreach($supervisors as $sv)<option value="{{ $sv->id }}">{{ $sv->name }}</option>@endforeach
          </select></div>
        <div><label class="text-xs font-semibold text-muted mb-1 block">تاريخ التسليم</label>
          <input name="due_date" type="date" value="{{ $month }}-01" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
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
  @if($canManageTasks)
  <div x-cloak x-show="edit!==null" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="edit=null"></div>
    <form method="POST" x-bind:action="'/tasks/'+form.id" class="relative bg-white rounded-2xl shadow-lift w-full max-w-md p-5 space-y-3 max-h-[90vh] overflow-y-auto">@csrf @method('PUT')
      <h3 class="ff-display font-bold text-lg">تعديل المهمة</h3>
      <div><label class="text-xs font-semibold text-muted mb-1 block">العنوان</label>
        <input name="title" x-model="form.title" required class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
      <div><label class="text-xs font-semibold text-muted mb-1 block">الوصف</label>
        <textarea name="description" x-model="form.description" rows="3" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm resize-none"></textarea></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-xs font-semibold text-muted mb-1 block">النوع</label>
          <select name="type" x-model="form.type" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">
            @foreach($taskTypes as $tt)<option value="{{ $tt->key }}">{{ $tt->label }} ({{ $tt->points }})</option>@endforeach
          </select></div>
        <div><label class="text-xs font-semibold text-muted mb-1 block">المرحلة</label>
          <select name="stage" x-model="form.stage" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">@foreach($stages as $s)<option>{{ $s }}</option>@endforeach</select></div>
        <div><label class="text-xs font-semibold text-muted mb-1 block">الموظف</label>
          <select name="user_id" x-model="form.user_id" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">@foreach($assignees as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
        <div><label class="text-xs font-semibold text-muted mb-1 block">مشرف متابِع</label>
          <select name="supervisor_id" x-model="form.supervisor_id" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">
            <option value="">— بدون —</option>
            @foreach($supervisors as $sv)<option value="{{ $sv->id }}">{{ $sv->name }}</option>@endforeach
          </select></div>
        <div><label class="text-xs font-semibold text-muted mb-1 block">تاريخ التسليم</label>
          <input name="due_date" type="date" x-model="form.due_date" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
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
    <form id="del-task" method="POST" x-bind:action="'/tasks/'+form.id" class="hidden">@csrf @method('DELETE')</form>
  </div>
  @endif
</div>

@if($canManageTasks)
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
  window.addEventListener('load', function () {
    if (typeof Sortable === 'undefined') return;
    document.querySelectorAll('[data-stage-list]').forEach(function (list) {
      new Sortable(list, {
        group: 'tasks', animation: 150, ghostClass: 'opacity-40',
        onEnd: function (evt) {
          var stage = evt.to.getAttribute('data-stage');
          if (evt.from.getAttribute('data-stage') === stage) return;
          var id = evt.item.getAttribute('data-id');
          var token = document.querySelector('meta[name=csrf-token]').content;
          var f = document.createElement('form');
          f.method = 'POST'; f.action = '/tasks/' + id + '/status';
          f.innerHTML = '<input type="hidden" name="_token" value="' + token + '">' +
                        '<input type="hidden" name="stage" value="' + stage + '">';
          document.body.appendChild(f); f.submit();
        }
      });
    });
  });
</script>
@endif
@endsection
