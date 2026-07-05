@php
  $current = request()->route()->getName();
  $monthScoped = in_array(explode('.', $current)[0], ['content','tasks','points','payroll','attendance','reports','maintenance']);
  [$cy,$cm] = array_map('intval', explode('-', $activeMonth));
  $prev = sprintf('%04d-%02d', $cm==1?$cy-1:$cy, $cm==1?12:$cm-1);
  $next = sprintf('%04d-%02d', $cm==12?$cy+1:$cy, $cm==12?1:$cm+1);
  $titles = ['dashboard'=>'لوحة التحكم','users'=>'إدارة المستخدمين','content'=>'خطة المحتوى','tasks'=>'إدارة المهام','maintenance'=>'صيانة الموقع','points'=>'النقاط والتارجت','attendance'=>'الحضور والإجازات','payroll'=>'الرواتب والمكافآت','reports'=>'التقارير'];
  $pageKey = explode('.', $current)[0];
@endphp
<header class="sticky top-0 z-30 bg-canvas/85 backdrop-blur border-b border-line">
  <div class="px-5 md:px-8 h-[68px] flex items-center gap-4 max-w-[1500px] mx-auto">
    <button x-on:click="nav=true" class="lg:hidden w-10 h-10 rounded-xl bg-white border border-line grid place-items-center text-ink shadow-soft shrink-0" title="القائمة">
      <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <div class="min-w-0 shrink">
      <div class="ff-display font-extrabold text-lg sm:text-xl text-ink leading-none truncate">{{ $titles[$pageKey] ?? 'ProManage Flow' }}</div>
      <div class="text-xs text-muted mt-1 hidden sm:block">
        @if($monthScoped) {{ $monthLabel($activeMonth) }} — كل شهر مستقلّ @else أهلاً {{ explode(' ', auth()->user()->name)[0] }} @endif
      </div>
    </div>
    <div class="flex-1"></div>

    @if($monthScoped)
      <div x-data="{open:false}" class="relative block">
        <div class="flex items-center gap-0.5 bg-white rounded-xl border border-line px-1 sm:px-1.5 py-1 shadow-soft">
          <a href="{{ route($current, ['month'=>$prev]) }}" title="الشهر السابق" class="w-8 h-8 rounded-lg grid place-items-center text-muted hover:text-ink hover:bg-canvas">
            <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></a>
          <button x-on:click="open=!open" class="px-2 sm:px-3 text-[13px] sm:text-sm font-bold text-ink min-w-[86px] sm:min-w-[110px] text-center hover:text-brand truncate">{{ $monthLabel($activeMonth) }}</button>
          <a href="{{ route($current, ['month'=>$next]) }}" title="الشهر التالي" class="w-8 h-8 rounded-lg grid place-items-center text-muted hover:text-ink hover:bg-canvas">
            <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg></a>
        </div>
        <div x-cloak x-show="open" x-on:click.outside="open=false" x-transition
             class="absolute left-0 mt-2 w-64 bg-white rounded-2xl border border-line shadow-lift p-3 z-50">
          <div class="text-center ff-display font-extrabold mb-2 tnum">{{ $cy }}</div>
          <div class="grid grid-cols-3 gap-2">
            @foreach($arMonths as $i=>$nm)
              @php $ym = sprintf('%04d-%02d',$cy,$i+1); @endphp
              <a href="{{ route($current, ['month'=>$ym]) }}"
                 class="px-2 py-2.5 rounded-xl text-sm font-semibold text-center border transition {{ $ym===$activeMonth ? 'bg-brand text-white border-brand' : 'bg-canvas border-line hover:border-brand/40' }}">{{ $nm }}</a>
            @endforeach
          </div>
        </div>
      </div>
    @endif

    <div class="hidden md:flex items-center gap-2 bg-white rounded-xl border border-line px-3 py-2 shadow-soft">
      <span class="text-[11px] text-muted">دورك</span>
      <span class="text-sm font-bold text-ink">{{ $roleLabels[auth()->user()->primaryRole()] ?? '' }}</span>
    </div>

    @php $unread = auth()->user()->unreadNotifications; $recent = auth()->user()->notifications()->latest()->take(8)->get(); @endphp
    <div x-data="{ bell:false }" class="relative">
      <button x-on:click="bell=!bell" class="relative w-10 h-10 rounded-xl bg-white border border-line grid place-items-center text-ink shadow-soft hover:border-brand/40" title="الإشعارات">
        <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0"/></svg>
        @if($unread->count())<span class="absolute -top-1 -left-1 min-w-[18px] h-[18px] px-1 rounded-full bg-brand text-white text-[10px] font-bold grid place-items-center tnum">{{ $unread->count() > 9 ? '9+' : $unread->count() }}</span>@endif
      </button>
      <div x-cloak x-show="bell" x-on:click.outside="bell=false" x-transition
           class="absolute left-0 mt-2 w-80 bg-white rounded-2xl border border-line shadow-lift z-50 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-line">
          <div class="ff-display font-bold text-sm">الإشعارات</div>
          @if($unread->count())
            <form method="POST" action="{{ route('notifications.readAll') }}">@csrf
              <button class="text-[11px] text-brand font-semibold hover:underline">تعليم الكل كمقروء</button></form>
          @endif
        </div>
        <div class="max-h-80 overflow-y-auto">
          @forelse($recent as $note)
            <a href="{{ route('notifications.read', $note->id) }}"
               class="block px-4 py-3 border-b border-line/60 hover:bg-canvas transition {{ $note->read_at ? '' : 'bg-violet-50/50' }}">
              <div class="flex items-start gap-2">
                <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $note->read_at ? 'bg-transparent' : (($note->data['level'] ?? 'normal')==='high' ? 'bg-rose-500' : 'bg-brand') }}"></span>
                <div class="min-w-0">
                  <div class="text-sm font-semibold text-ink leading-snug">{{ $note->data['title'] ?? 'إشعار' }}</div>
                  <div class="text-[12px] text-muted leading-snug">{{ $note->data['message'] ?? '' }}</div>
                  <div class="text-[10px] text-muted mt-1">{{ $note->created_at->diffForHumans() }}</div>
                </div>
              </div>
            </a>
          @empty
            <div class="px-4 py-8 text-center text-sm text-muted">لا توجد إشعارات بعد.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</header>
