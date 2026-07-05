@extends('layouts.app')
@section('title','سجل الدخول')
@section('content')
<div class="grid sm:grid-cols-3 gap-3 mb-5">
  <div class="bg-white rounded-2xl border border-line shadow-soft p-4">
    <div class="text-xs text-muted">المتصلون الآن</div>
    <div class="ff-display font-extrabold text-2xl text-emerald-600 tnum mt-1">{{ $online }}</div>
  </div>
  <div class="bg-white rounded-2xl border border-line shadow-soft p-4">
    <div class="text-xs text-muted">إجمالي المستخدمين</div>
    <div class="ff-display font-extrabold text-2xl text-ink tnum mt-1">{{ $users->count() }}</div>
  </div>
  <div class="bg-white rounded-2xl border border-line shadow-soft p-4">
    <div class="text-xs text-muted">عمليات الدخول (آخر ١٠٠)</div>
    <div class="ff-display font-extrabold text-2xl text-brand tnum mt-1">{{ $logs->count() }}</div>
  </div>
</div>

<h3 class="ff-display font-bold mb-2">حالة المستخدمين</h3>
<div class="bg-white rounded-2xl border border-line shadow-soft overflow-x-auto mb-6">
  <table class="w-full text-sm min-w-[600px]">
    <thead><tr class="text-right text-muted text-xs border-b border-line bg-canvas/60">
      <th class="p-4 font-semibold">المستخدم</th><th class="p-4 font-semibold">الحالة</th>
      <th class="p-4 font-semibold">آخر دخول</th><th class="p-4 font-semibold">آخر نشاط</th></tr></thead>
    <tbody>
    @foreach($users as $u)
      <tr class="border-b border-line/60 hover:bg-canvas/60">
        <td class="p-4"><div class="flex items-center gap-3">
          <div class="relative">
            <div class="w-9 h-9 rounded-full grid place-items-center text-white font-bold ff-display" style="background:{{ $avatarColor($u->id) }}">{{ mb_substr($u->name,0,1) }}</div>
            @if($u->isOnline())<span class="absolute -bottom-0.5 -left-0.5 w-3 h-3 rounded-full bg-emerald-500 border-2 border-white"></span>@endif
          </div>
          <div><div class="font-semibold">{{ $u->name }}</div><div class="text-[11px] text-muted">{{ $roleLabels[$u->primaryRole()] ?? $u->primaryRole() }}</div></div>
        </div></td>
        <td class="p-4">
          @if($u->isOnline())<span class="inline-flex items-center gap-1.5 text-emerald-700 text-xs font-bold"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>متصل</span>
          @else<span class="inline-flex items-center gap-1.5 text-muted text-xs font-semibold"><span class="w-2 h-2 rounded-full bg-gray-300"></span>غير متصل</span>@endif
        </td>
        <td class="p-4 text-muted text-xs">{{ $u->last_login_at ? $u->last_login_at->format('Y-m-d H:i') : '—' }}</td>
        <td class="p-4 text-muted text-xs">{{ $u->last_seen_at ? $u->last_seen_at->diffForHumans() : '—' }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>

<h3 class="ff-display font-bold mb-2">آخر عمليات الدخول</h3>
<div class="bg-white rounded-2xl border border-line shadow-soft overflow-x-auto">
  <table class="w-full text-sm min-w-[640px]">
    <thead><tr class="text-right text-muted text-xs border-b border-line bg-canvas/60">
      <th class="p-4 font-semibold">المستخدم</th><th class="p-4 font-semibold">التاريخ والوقت</th>
      <th class="p-4 font-semibold">IP</th><th class="p-4 font-semibold">الجهاز</th></tr></thead>
    <tbody>
    @forelse($logs as $log)
      <tr class="border-b border-line/60 hover:bg-canvas/60">
        <td class="p-4 font-semibold whitespace-nowrap">{{ optional($log->user)->name ?? '—' }}</td>
        <td class="p-4 text-muted tnum whitespace-nowrap">{{ $log->logged_in_at?->format('Y-m-d H:i') }}</td>
        <td class="p-4 text-muted tnum whitespace-nowrap">{{ $log->ip ?? '—' }}</td>
        <td class="p-4 text-muted text-[11px] max-w-[280px] truncate">{{ $log->user_agent ?? '—' }}</td>
      </tr>
    @empty
      <tr><td colspan="4" class="p-8 text-center text-muted">لا توجد عمليات دخول مسجّلة بعد.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
@endsection
