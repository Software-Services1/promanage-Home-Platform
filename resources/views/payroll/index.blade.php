@extends('layouts.app')
@section('title','الرواتب والمكافآت')
@section('content')
@php $money = fn($n)=> number_format(round($n)).' ر.س'; @endphp
<div class="bg-ink text-white rounded-2xl p-5 mb-5 flex items-center gap-4">
  <div class="w-12 h-12 rounded-xl bg-white/10 grid place-items-center">
    <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
  <div><div class="ff-display font-bold">{{ ($canAll ?? false) ? 'الرواتب والمكافآت' : 'راتبي' }} — {{ $monthLabel($month) }}</div>
    <div class="text-sm text-white/60">{{ ($canAll ?? false) ? 'بيانات سرّية — لا تظهر إلا لمن يملك صلاحية الاطلاع على الرواتب.' : 'تفاصيل راتبك لهذا الشهر — لا يمكنك الاطلاع على رواتب الآخرين.' }}</div></div>
  <div class="mr-auto text-left"><div class="text-xs text-white/50">{{ ($canAll ?? false) ? 'إجمالي الصافي' : 'صافي راتبي' }}</div>
    <div class="ff-display font-extrabold text-2xl tnum">{{ $money($totalNet) }}</div></div>
</div>

<div class="bg-white rounded-2xl border border-line shadow-soft overflow-x-auto">
  <table class="w-full text-sm">
    <thead><tr class="text-right text-muted text-xs border-b border-line bg-canvas/60">
      <th class="p-4 font-semibold">الموظف</th><th class="p-4 font-semibold">الراتب</th>
      <th class="p-4 font-semibold">النقاط/التارجت</th><th class="p-4 font-semibold">مكافأة 5%</th>
      <th class="p-4 font-semibold">خصم التارجت</th><th class="p-4 font-semibold">خصم الإجازات</th>
      <th class="p-4 font-semibold">الصافي</th></tr></thead>
    <tbody>
    @foreach($rows as $r)
      @php $u = $r['user']; $p = $r['pay']; @endphp
      <tr class="border-b border-line/60 hover:bg-canvas/60">
        <td class="p-4"><div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-full grid place-items-center text-white text-xs font-bold ff-display" style="background:{{ $avatarColor($u->id) }}">{{ mb_substr($u->name,0,1) }}</div>{{ $u->name }}</div></td>
        <td class="p-4 tnum">{{ $money($p['salary']) }}</td>
        <td class="p-4 tnum"><span class="{{ $p['met_target'] ? 'text-emerald-600':'text-rose-600' }} font-semibold">{{ $p['points'] }}</span> / {{ $p['target'] ?: '—' }}</td>
        <td class="p-4 tnum {{ $p['bonus'] ? 'text-emerald-600 font-semibold':'text-muted' }}">{{ $p['bonus'] ? '+'.$money($p['bonus']) : '—' }}</td>
        <td class="p-4 tnum {{ $p['target_deduction'] ? 'text-rose-600 font-semibold':'text-muted' }}">{{ $p['target_deduction'] ? '−'.$money($p['target_deduction']) : '—' }}</td>
        <td class="p-4 tnum {{ $p['leave_deduction'] ? 'text-rose-600 font-semibold':'text-muted' }}">{{ $p['leave_deduction'] ? '−'.$money($p['leave_deduction']).' ('.$p['extra_leave'].'ي)' : '—' }}</td>
        <td class="p-4 tnum"><b class="text-ink">{{ $money($p['net']) }}</b></td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
<div class="grid sm:grid-cols-2 gap-3 mt-4 text-xs text-muted">
  <div class="bg-white border border-line rounded-xl p-3">المكافأة = 5% من الراتب عند تجاوز التارجت.</div>
  <div class="bg-white border border-line rounded-xl p-3">الخصومات: عدم تحقيق التارجت (≤20%) + الأجر اليومي عن كل يوم إجازة زائد عن يومين.</div>
</div>
@endsection
