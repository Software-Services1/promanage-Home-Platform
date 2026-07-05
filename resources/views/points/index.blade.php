@extends('layouts.app')
@section('title','النقاط والتارجت')
@section('content')
@php $roleLbl = ['supervisor'=>'مشرف المحتوى','designer'=>'مصمم جرافيك','editor'=>'مونتير ومصمم']; @endphp
<div class="flex items-center gap-2 mb-4 flex-wrap">
  <span class="inline-flex items-center gap-2 bg-brand/10 text-brand text-sm font-bold px-3 py-1.5 rounded-xl">
    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/></svg>
    نقاط {{ $monthLabel($month) }}</span>
  <span class="text-xs text-muted">النقاط والتارجت تُحتسب لكل شهر على حدة</span>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5 mb-5">
  @foreach($team as $u)
    @php
      $tgt = (int)$u->target; $p = $u->points;
      $pct = $tgt>0 ? min(100, round($p/$tgt*100)) : 0;
      $circ = 2*3.14159*46; $dash = $circ*(1-$pct/100);
      $over = $tgt>0 && $p>$tgt;
    @endphp
    <div class="bg-white rounded-2xl border border-line shadow-soft p-5">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-full grid place-items-center text-white font-bold ff-display" style="background:{{ $avatarColor($u->id) }}">{{ mb_substr($u->name,0,1) }}</div>
        <div><div class="font-bold">{{ $u->name }}</div><div class="text-[11px] text-muted">{{ $roleLbl[$u->primaryRole()] ?? '' }}</div></div>
        @if($over)<span class="mr-auto text-xs bg-gold/15 text-golddk px-2 py-1 rounded-lg font-bold">تجاوز</span>@endif
      </div>
      <div class="flex items-center gap-4">
        <div class="relative w-28 h-28 shrink-0">
          <svg viewBox="0 0 112 112" class="w-28 h-28 -rotate-90">
            <circle cx="56" cy="56" r="46" fill="none" stroke="#E6E4F0" stroke-width="11"/>
            <circle cx="56" cy="56" r="46" fill="none" stroke="{{ $over ? '#F0A53A' : '#5B4BDB' }}" stroke-width="11" stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $dash }}"/>
          </svg>
          <div class="absolute inset-0 grid place-items-center"><div class="text-center"><div class="ff-display font-extrabold text-xl tnum">{{ $p }}</div><div class="text-[10px] text-muted">من {{ $tgt ?: '—' }}</div></div></div>
        </div>
        <div class="flex-1 space-y-1.5 text-sm">
          @if(isset($u->breakdown))
            <div class="flex justify-between"><span class="text-muted">مباشرة</span><b class="tnum">{{ $u->breakdown['direct'] }}</b></div>
            <div class="flex justify-between"><span class="text-muted">من الإشراف ({{ rtrim(rtrim(number_format($u->breakdown['share'],2),'0'),'.') }}%)</span><b class="tnum text-brand">{{ $u->breakdown['from_designers'] }}</b></div>
            <div class="flex justify-between border-t border-line pt-1"><span class="text-muted">الإجمالي</span><b class="tnum">{{ $u->breakdown['total'] }}</b></div>
          @else
            <div class="flex justify-between"><span class="text-muted">المحقّق</span><b class="tnum">{{ $p }}</b></div>
            <div class="flex justify-between"><span class="text-muted">المستهدف</span><b class="tnum">{{ $tgt }}</b></div>
            <div class="flex justify-between"><span class="text-muted">النسبة</span><b class="tnum {{ $over ? 'text-golddk':'text-brand' }}">{{ $pct }}%</b></div>
          @endif
        </div>
      </div>
    </div>
  @endforeach
</div>

<div class="bg-white rounded-2xl border border-line shadow-soft p-5">
  <h3 class="ff-display font-bold mb-4">قيمة النقاط لكل نوع عمل</h3>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2">
    @foreach($taskTypes as $t)
      <div class="flex items-center justify-between bg-canvas rounded-xl px-3.5 py-2.5">
        <span class="text-sm">{{ $t['label'] }}@isset($t['bonus'])<span class="text-[10px] text-golddk"> +{{ $t['bonus'] }}</span>@endisset</span>
        <b class="tnum text-brand">{{ $t['points'] }}</b>
      </div>
    @endforeach
    @foreach($maintTypes as $t)
      <div class="flex items-center justify-between bg-canvas rounded-xl px-3.5 py-2.5">
        <span class="text-sm">{{ $t['label'] }}</span><b class="tnum text-brand">{{ $t['points'] }}</b>
      </div>
    @endforeach
  </div>
  <div class="mt-4 bg-brand/5 rounded-xl p-4 text-sm text-brand/90">
    <b>معادلة نقاط الإشراف:</b> إجمالي المشرف = نقاطه المباشرة + (نسبته الخاصة ٪ × القاعدة).
    @if(\App\Models\Setting::get('supervisor_credit_mode','auto')==='assigned')
      الوضع الحالي: <b>حسب الإسناد</b> — القاعدة هي نقاط المهام المُسنَدة لكل مشرف كمتابِع.
    @else
      الوضع الحالي: <b>توزيع تلقائي</b> — القاعدة هي مجموع نقاط المصممين ({{ $designersTotal }}).
    @endif
    تُضبط النسبة لكل مشرف من «إدارة المستخدمين»، والوضع من «إعدادات النظام».
  </div>
</div>
@endsection
