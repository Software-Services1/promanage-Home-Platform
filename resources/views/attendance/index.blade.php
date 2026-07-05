@extends('layouts.app')
@section('title','الحضور والإجازات')
@section('content')
<div x-data="{ add:false }">
  <div class="flex items-center gap-2 mb-3">
    <span class="inline-flex items-center gap-2 bg-brand/10 text-brand text-sm font-bold px-3 py-1.5 rounded-xl">
      <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
      حضور {{ $monthLabel($month) }}</span>
  </div>
  <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
    <div class="text-sm text-muted">سياسة: بحد أقصى يومين شهرياً • ما زاد = خصم الأجر اليومي</div>
    <button x-on:click="add=true" class="inline-flex items-center gap-2 bg-brand hover:bg-brandd text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-lift">
      <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> طلب إجازة</button>
  </div>

  <div class="grid lg:grid-cols-3 gap-5">
    <div class="bg-white rounded-2xl border border-line shadow-soft p-5 lg:col-span-2">
      <h3 class="ff-display font-bold mb-3">طلبات الإجازات</h3>
      <div class="space-y-2">
        @forelse($leaves as $l)
          <div class="flex items-center gap-3 p-3 rounded-xl border border-line">
            <div class="w-9 h-9 rounded-full grid place-items-center text-white font-bold ff-display" style="background:{{ $avatarColor($l->user_id) }}">{{ mb_substr($l->user->name,0,1) }}</div>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-semibold">{{ $l->user->name }} • {{ $l->days }} يوم</div>
              <div class="text-[11px] text-muted">{{ $l->from_date->format('Y-m-d') }}@if($l->to_date->ne($l->from_date)) ← {{ $l->to_date->format('Y-m-d') }}@endif • {{ $l->reason }}</div>
            </div>
            @if($l->status==='pending')
              @if($canManage)
                <div class="flex gap-1">
                  <form method="POST" action="{{ route('attendance.status',$l) }}">@csrf<input type="hidden" name="status" value="approved">
                    <button class="px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold hover:bg-emerald-100">قبول</button></form>
                  <form method="POST" action="{{ route('attendance.status',$l) }}">@csrf<input type="hidden" name="status" value="rejected">
                    <button class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-xs font-bold hover:bg-rose-100">رفض</button></form>
                </div>
              @else<span class="text-xs text-golddk font-semibold">قيد المراجعة</span>@endif
            @else
              <span class="text-xs font-semibold {{ $l->status==='approved' ? 'text-emerald-700':'text-gray-400' }}">{{ $l->status==='approved' ? 'مقبول':'مرفوض' }}</span>
            @endif
          </div>
        @empty
          <div class="text-sm text-muted py-8 text-center">لا توجد طلبات في {{ $monthLabel($month) }}.</div>
        @endforelse
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-line shadow-soft p-5">
      <h3 class="ff-display font-bold mb-3">رصيد الشهر</h3>
      <div class="space-y-3">
        @foreach($team as $u)
          @php $extra = max(0, $u->leave_days - 2); @endphp
          <div>
            <div class="flex justify-between text-sm mb-1"><span>{{ $u->name }}</span><b class="tnum {{ $extra ? 'text-rose-600':'' }}">{{ $u->leave_days }}/2 @if($extra)(+{{ $extra }})@endif</b></div>
            <div class="h-2 rounded-full bg-canvas overflow-hidden"><div class="h-full rounded-full {{ $extra ? 'bg-rose-400':'bg-brand' }}" style="width:{{ min(100, $u->leave_days/2*100) }}%"></div></div>
          </div>
        @endforeach
      </div>
      <p class="text-xs text-muted mt-4">الأيام الزائدة تُحوّل آلياً إلى خصم في تقرير الرواتب لدى المشرف العام.</p>
    </div>
  </div>

  <div x-cloak x-show="add" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="add=false"></div>
    <form method="POST" action="{{ route('attendance.store') }}" class="relative bg-white rounded-2xl shadow-lift w-full max-w-md p-5 space-y-3">@csrf
      <h3 class="ff-display font-bold text-lg">طلب إجازة</h3>
      @if($canManage)
        <div><label class="text-xs font-semibold text-muted mb-1 block">الموظف</label>
          <select name="user_id" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm">@foreach($team as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></div>
      @else
        <input type="hidden" name="user_id" value="{{ auth()->id() }}">
      @endif
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-xs font-semibold text-muted mb-1 block">من</label>
          <input name="from_date" type="date" value="{{ $month }}-01" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
        <div><label class="text-xs font-semibold text-muted mb-1 block">إلى</label>
          <input name="to_date" type="date" value="{{ $month }}-01" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum"></div>
      </div>
      <div><label class="text-xs font-semibold text-muted mb-1 block">السبب</label>
        <input name="reason" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
      <div class="flex gap-2 justify-end pt-1">
        <button type="button" x-on:click="add=false" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">إرسال</button>
      </div>
    </form>
  </div>
</div>
@endsection
