<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'ProManage Flow')</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
tailwind.config = { theme: { extend: {
  fontFamily: { display: ['Cairo','sans-serif'], body: ['Tajawal','sans-serif'] },
  colors: { ink:'#191824', ink2:'#262430', brand:'#5B4BDB', brandd:'#4334C2', gold:'#F0A53A', golddk:'#C9832A', canvas:'#F3F2F9', line:'#E6E4F0', muted:'#6C6A7C' },
  boxShadow: { soft:'0 1px 2px rgba(25,24,36,.04), 0 8px 24px -12px rgba(25,24,36,.15)', lift:'0 12px 40px -16px rgba(91,75,219,.35)' }
}}}
</script>
<style>
  html,body{background:#F3F2F9;font-family:'Tajawal',sans-serif;color:#191824}
  .ff-display{font-family:'Cairo',sans-serif}
  .tnum{font-variant-numeric:tabular-nums}
  ::-webkit-scrollbar{width:10px;height:10px}::-webkit-scrollbar-thumb{background:#cfcce0;border-radius:99px;border:2px solid #F3F2F9}
  .nav-item.active{background:#fff;color:#191824}.nav-item.active .ic{background:#5B4BDB;color:#fff}
  [x-cloak]{display:none!important}
</style>
@php
  $arMonths = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
  $monthLabel = function($ym) use($arMonths){ [$y,$m]=explode('-',$ym); return $arMonths[(int)$m-1].' '.$y; };
  $activeMonth = session('active_month', now()->format('Y-m'));
  $roleLabels = ['admin'=>'مشرف عام','supervisor'=>'مشرف المحتوى','designer'=>'مصمم جرافيك','editor'=>'مونتير ومصمم'];
  $avatarColor = fn($id)=> ['#191824','#5B4BDB','#059669','#0284c7'][$id % 4];
@endphp
</head>
<body class="min-h-screen">
@auth
<div x-data="{ nav:false }" class="flex flex-row-reverse min-h-screen">
  <!-- المحتوى -->
  <main class="flex-1 min-w-0 flex flex-col">
    @include('partials.topbar')
    <div class="flex-1 px-5 md:px-8 py-6 max-w-[1500px] w-full mx-auto">
      @if(session('ok'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3500)" x-transition
             class="mb-4 bg-ink text-white text-sm font-semibold px-4 py-3 rounded-xl shadow-lift flex items-center gap-2">
          <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
          {{ session('ok') }}
        </div>
      @endif
      @if($errors->any())
        <div class="mb-4 bg-rose-50 text-rose-700 text-sm px-4 py-3 rounded-xl border border-rose-200">
          @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
      @endif
      @yield('content')
    </div>
  </main>

  <!-- الشريط الجانبي (يمين الشاشة) -->
  @include('partials.sidebar')

  <!-- طبقة تعتيم للجوال عند فتح القائمة -->
  <div x-cloak x-show="nav" x-on:click="nav=false" x-transition.opacity class="fixed inset-0 bg-ink/40 z-30 lg:hidden"></div>
</div>
@else
  @yield('content')
@endauth
</body>
</html>
