@php
  $nav = [
    ['id'=>'dashboard','label'=>'لوحة التحكم','route'=>'dashboard','roles'=>['admin','supervisor','designer','editor'],'icon'=>'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z'],
    ['id'=>'users','label'=>'إدارة المستخدمين','route'=>'users.index','can'=>'manage users','roles'=>['admin'],'icon'=>'M16 18a4 4 0 0 0-8 0M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6'],
    ['id'=>'content','label'=>'خطة المحتوى','route'=>'content.index','roles'=>['admin','supervisor','designer','editor'],'icon'=>'M3 4h18v17H3zM3 9h18M8 2v4M16 2v4'],
    ['id'=>'tasks','label'=>'إدارة المهام','route'=>'tasks.index','roles'=>['admin','supervisor','designer','editor'],'icon'=>'M9 11l3 3 8-8M4 12v6a2 2 0 0 0 2 2h12'],
    ['id'=>'maintenance','label'=>'صيانة الموقع','route'=>'maintenance.index','can'=>'manage maintenance','roles'=>['admin','supervisor'],'icon'=>'M14.7 6.3a4 4 0 0 0-5 5L4 17v3h3l5.7-5.7a4 4 0 0 0 5-5l-2.5 2.5-2.5-.7-.7-2.5z'],
    ['id'=>'points','label'=>'النقاط والتارجت','route'=>'points.index','roles'=>['admin','supervisor','designer','editor'],'icon'=>'M12 4a8 8 0 1 0 0 16 8 8 0 0 0 0-16zM12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z'],
    ['id'=>'attendance','label'=>'الحضور والإجازات','route'=>'attendance.index','roles'=>['admin','supervisor','designer','editor'],'icon'=>'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18zM12 7v5l3 2'],
    ['id'=>'payroll','label'=>'الرواتب والمكافآت','route'=>'payroll.index','roles'=>['admin','supervisor','designer','editor'],'icon'=>'M3 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2M3 7v10a2 2 0 0 0 2 2h13a2 2 0 0 0 2-2v-3M21 14h-4a2 2 0 0 1 0-4h4z'],
    ['id'=>'reports','label'=>'التقارير','route'=>'reports.index','can'=>'view reports','roles'=>['admin','supervisor'],'icon'=>'M4 20V10M10 20V4M16 20v-7M22 20H2'],
    ['id'=>'tasktypes','label'=>'أنواع المهام','route'=>'tasktypes.index','can'=>'manage task types','roles'=>['admin'],'icon'=>'M4 7h16M4 12h16M4 17h10'],
    ['id'=>'roles','label'=>'الأدوار والصلاحيات','route'=>'roles.index','can'=>'manage roles','roles'=>['admin'],'icon'=>'M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z'],
    ['id'=>'settings','label'=>'إعدادات النظام','route'=>'settings.index','can'=>'manage settings','roles'=>['admin'],'icon'=>'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8zM3 12h2M19 12h2M12 3v2M12 19v2'],
    ['id'=>'logins','label'=>'سجل الدخول','route'=>'logins.index','can'=>'manage users','roles'=>['admin'],'icon'=>'M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3'],
  ];
  $current = request()->route()->getName();
@endphp
<aside x-bind:class="nav ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
       class="w-[248px] shrink-0 bg-ink text-white/70 flex flex-col fixed lg:sticky top-0 right-0 h-screen z-40 transition-transform duration-300 lg:transition-none">
  <div class="px-5 pt-6 pb-5 border-b border-white/10">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-brand grid place-items-center text-white shadow-lift">
        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l8 4.5v9L12 21l-8-4.5v-9z"/><path d="M12 12v9M12 12 4 7.5M12 12l8-4.5"/></svg>
      </div>
      <div class="flex-1">
        <div class="ff-display font-extrabold text-white text-lg leading-tight">ProManage Flow</div>
        <div class="text-[11px] text-white/45">إدارة مهام التسويق</div>
      </div>
      <button x-on:click="nav=false" class="lg:hidden w-8 h-8 rounded-lg grid place-items-center text-white/50 hover:text-white hover:bg-white/10" title="إغلاق">
        <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
  </div>
  <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
    @foreach($nav as $n)
      @php $visible = isset($n['can']) ? auth()->user()->can($n['can']) : auth()->user()->hasAnyRole($n['roles']); @endphp
      @if($visible)
        @php $active = str_starts_with($current, explode('.', $n['route'])[0]); @endphp
        <a href="{{ route($n['route']) }}" x-on:click="nav=false"
           class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition hover:bg-white/5 {{ $active ? 'active' : '' }}">
          <span class="ic w-8 h-8 rounded-lg grid place-items-center bg-white/5 {{ $active ? '' : 'text-white/60' }}">
            <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $n['icon'] }}"/></svg>
          </span>
          <span>{{ $n['label'] }}</span>
        </a>
      @endif
    @endforeach
  </nav>
  <div class="p-3 border-t border-white/10">
    <div class="flex items-center gap-3 px-2 py-2 rounded-xl">
      <div class="w-9 h-9 rounded-full grid place-items-center text-white font-bold ff-display" style="background:{{ $avatarColor(auth()->id()) }}">{{ mb_substr(auth()->user()->name,0,1) }}</div>
      <div class="min-w-0 flex-1">
        <div class="text-white text-sm font-semibold truncate">{{ auth()->user()->name }}</div>
        <div class="text-[11px] text-white/45">{{ $roleLabels[auth()->user()->primaryRole()] ?? '' }}</div>
      </div>
      <form method="POST" action="{{ route('logout') }}">@csrf
        <button class="w-8 h-8 rounded-lg grid place-items-center text-white/50 hover:text-white hover:bg-white/10" title="خروج">
          <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        </button>
      </form>
    </div>
  </div>
</aside>
