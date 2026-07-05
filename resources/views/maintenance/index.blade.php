@extends('layouts.app')
@section('title','صيانة الموقع')
@section('content')
<div x-data="{ open:false, mode:'create', form:{} }">
  <div class="flex items-center gap-2 mb-3">
    <span class="inline-flex items-center gap-2 bg-brand/10 text-brand text-sm font-bold px-3 py-1.5 rounded-xl">
      <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M14.7 6.3a4 4 0 0 0-5 5L4 17v3h3l5.7-5.7a4 4 0 0 0 5-5l-2.5 2.5-2.5-.7-.7-2.5z"/></svg>
      صيانة {{ $monthLabel($month) }}</span>
  </div>
  <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
    <div class="flex gap-2 text-xs flex-wrap">
      @foreach($types as $k=>$v)<span class="bg-white border border-line rounded-lg px-2.5 py-1.5"><b class="text-brand tnum">{{ $v['points'] }}</b> {{ $v['label'] }}</span>@endforeach
    </div>
    <button x-on:click="mode='create'; form={ type:'minor_design', work_date:'{{ $month }}-01', status:'قيد التنفيذ' }; open=true"
            class="inline-flex items-center gap-2 bg-brand hover:bg-brandd text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-lift">
      <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> عملية صيانة</button>
  </div>

  @if($rows->isEmpty())
    <div class="bg-white rounded-2xl border border-line shadow-soft text-center text-muted py-14">لا توجد عمليات صيانة في {{ $monthLabel($month) }}.</div>
  @else
    <div class="bg-white rounded-2xl border border-line shadow-soft overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-right text-muted text-xs border-b border-line bg-canvas/60">
          <th class="p-4 font-semibold">العملية</th><th class="p-4 font-semibold">النوع</th>
          <th class="p-4 font-semibold">المنفّذ</th><th class="p-4 font-semibold">التاريخ</th>
          <th class="p-4 font-semibold">النقاط</th><th class="p-4 font-semibold">الحالة</th><th class="p-4"></th></tr></thead>
        <tbody>
        @foreach($rows as $m)
          <tr class="border-b border-line/60 hover:bg-canvas/60">
            <td class="p-4 font-semibold">{{ $m->title }}</td>
            <td class="p-4 text-muted">{{ $m->typeLabel() }}</td>
            <td class="p-4">{{ $m->user->name }}</td>
            <td class="p-4 tnum text-muted">{{ $m->work_date->format('Y-m-d') }}</td>
            <td class="p-4"><span class="bg-gold/15 text-golddk px-2 py-1 rounded-lg text-xs font-bold tnum">{{ $m->points() }}</span></td>
            <td class="p-4">@if($m->status==='تم')<span class="text-emerald-700 text-xs font-semibold">تم</span>@else<span class="text-golddk text-xs font-semibold">قيد التنفيذ</span>@endif</td>
            <td class="p-4">
              <div class="flex items-center gap-1 justify-end">
                <button type="button" class="w-8 h-8 rounded-lg grid place-items-center text-muted hover:text-brand hover:bg-violet-50" title="تعديل"
                  x-on:click="mode='edit'; form={ id:{{ $m->id }}, title:@js($m->title), type:@js($m->type), work_date:@js($m->work_date->format('Y-m-d')), status:@js($m->status) }; open=true">
                  <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></button>
                <form method="POST" action="{{ route('maintenance.destroy',$m) }}" onsubmit="return confirm('حذف هذه العملية؟')">@csrf @method('DELETE')
                  <button class="w-8 h-8 rounded-lg grid place-items-center text-muted hover:text-rose-600 hover:bg-rose-50" title="حذف">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg></button>
                </form>
              </div>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  @endif
  <p class="text-xs text-muted mt-3">نقاط الصيانة تُحتسب لمشرف المحتوى عند اكتمال العملية (الحالة «تم»).</p>

  <!-- نافذة إضافة/تعديل -->
  <div x-cloak x-show="open" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="open=false"></div>
    <form method="POST" x-bind:action="mode==='create' ? '{{ route('maintenance.store') }}' : '/maintenance/'+form.id"
          class="relative bg-white rounded-2xl shadow-lift w-full max-w-md p-5 space-y-3">@csrf
      <template x-if="mode==='edit'"><input type="hidden" name="_method" value="PUT"></template>
      <h3 class="ff-display font-bold text-lg" x-text="mode==='create' ? 'عملية صيانة جديدة' : 'تعديل العملية'"></h3>
      <div><label class="text-xs font-semibold text-muted mb-1 block">العنوان</label>
        <input name="title" x-model="form.title" required class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-xs font-semibold text-muted mb-1 block">النوع</label>
          <select name="type" x-model="form.type" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">@foreach($types as $k=>$v)<option value="{{ $k }}">{{ $v['label'] }} ({{ $v['points'] }})</option>@endforeach</select></div>
        <div><label class="text-xs font-semibold text-muted mb-1 block">التاريخ</label>
          <input name="work_date" type="date" x-model="form.work_date" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
        <div class="col-span-2"><label class="text-xs font-semibold text-muted mb-1 block">الحالة</label>
          <select name="status" x-model="form.status" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">@foreach($statuses as $s)<option>{{ $s }}</option>@endforeach</select></div>
      </div>
      <div class="flex gap-2 justify-end pt-1">
        <button type="button" x-on:click="open=false" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">حفظ</button>
      </div>
    </form>
  </div>
</div>
@endsection
