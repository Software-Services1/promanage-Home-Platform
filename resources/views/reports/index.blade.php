@extends('layouts.app')
@section('title','التقارير')
@section('content')
@php
  $tabs = ['points'=>'أداء ونقاط','content'=>'خطة المحتوى','tasks'=>'المهام','maint'=>'صيانة الموقع','attendance'=>'الحضور'];
@endphp
<div class="flex items-center gap-2 mb-3">
  <span class="inline-flex items-center gap-2 bg-brand/10 text-brand text-sm font-bold px-3 py-1.5 rounded-xl">
    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
    تقارير {{ $monthLabel($month) }}</span>
</div>
<div class="flex flex-wrap items-center gap-2 mb-4">
  @foreach($tabs as $k=>$l)
    <a href="{{ route('reports.index',['tab'=>$k,'user'=>$userId]) }}" class="px-3.5 py-2 rounded-xl text-sm font-semibold {{ $tab===$k ? 'bg-brand text-white':'bg-white border border-line text-muted' }}">{{ $l }}</a>
  @endforeach
  <div class="flex-1"></div>
  <form method="GET" class="flex items-center gap-2">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <select name="user" onchange="this.form.submit()" class="bg-white border border-line rounded-xl px-3 py-2 text-sm shadow-soft">
      <option value="">كل الموظفين</option>
      @foreach($employees as $emp)<option value="{{ $emp->id }}" @selected($userId==$emp->id)>{{ $emp->name }}</option>@endforeach
    </select>
  </form>
  <a href="{{ route('reports.export',['tab'=>$tab,'user'=>$userId]) }}" class="inline-flex items-center gap-2 bg-white border border-line text-ink text-sm font-bold px-3.5 py-2.5 rounded-xl hover:border-brand/40">
    <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg> تصدير CSV</a>
</div>

<div class="bg-white rounded-2xl border border-line shadow-soft overflow-x-auto p-2">
  <table class="w-full text-sm">
    <thead><tr class="text-right text-muted text-xs border-b border-line">
      @foreach($data['head'] as $h)<th class="p-3 font-semibold">{{ $h }}</th>@endforeach
    </tr></thead>
    <tbody>
    @forelse($data['rows'] as $row)
      <tr class="border-b border-line/60">
        @foreach($row as $cell)<td class="p-3 {{ is_numeric($cell) ? 'tnum':'' }}">{{ $cell }}</td>@endforeach
      </tr>
    @empty
      <tr><td class="p-6 text-center text-muted" colspan="{{ count($data['head']) }}">لا توجد بيانات في {{ $monthLabel($month) }}.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<p class="text-xs text-muted mt-3">لا تتضمّن التقارير والتصدير أي بيانات رواتب — هذه مقصورة على واجهة الرواتب السرّية للمشرف العام.</p>
@endsection
