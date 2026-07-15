<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<title><?php echo $__env->yieldContent('title', 'ProManage Flow'); ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<script>
  // مكوّن موحّد: فلترة فورية + ترقيم صفحات في المتصفح (بلا إعادة تحميل)
  function boardFilter(opts){
    opts = opts || {};
    return {
      q:{}, page:1, perPage: opts.perPage || 12, contains: opts.contains || [], flt:false,
      total:0, pages:1, rows:[],
      init(){
        this.rows = Array.from(this.$root.querySelectorAll('[data-row]'));
        this.total = this.rows.length;
        this.apply();
      },
      rowMatches(el){
        for (const k of Object.keys(this.q)){
          const v = (this.q[k] ?? '').toString().trim();
          if (v === '') continue;
          const d = (el.dataset[k] ?? '').toString();
          if (this.contains.includes(k)) { if (!d.includes(v)) return false; }
          else if (d !== v) return false;
        }
        return true;
      },
      apply(){
        const matched = this.rows.filter(el => this.rowMatches(el));
        this.total = matched.length;
        this.pages = Math.max(1, Math.ceil(this.total / this.perPage));
        if (this.page > this.pages) this.page = this.pages;
        const start = (this.page - 1) * this.perPage, end = start + this.perPage;
        this.rows.forEach(el => { el.style.display = 'none'; });
        matched.forEach((el, i) => { el.style.display = (i >= start && i < end) ? '' : 'none'; });
      },
      onFilter(){ this.page = 1; this.apply(); },
      reset(){ for (const k of Object.keys(this.q)) this.q[k] = ''; this.page = 1; this.apply(); },
      go(p){ if (p >= 1 && p <= this.pages){ this.page = p; this.apply(); } },
      get from(){ return this.total ? (this.page - 1) * this.perPage + 1 : 0; },
      get to(){ return Math.min(this.total, this.page * this.perPage); },
      get pageList(){
        const a = [], span = 2;
        let s = Math.max(1, this.page - span), e = Math.min(this.pages, this.page + span);
        for (let i = s; i <= e; i++) a.push(i);
        return a;
      },
    };
  }
</script>
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
<?php
  $arMonths = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
  $monthLabel = function($ym) use($arMonths){ [$y,$m]=explode('-',$ym); return $arMonths[(int)$m-1].' '.$y; };
  $activeMonth = session('active_month', now()->format('Y-m'));
  $roleLabels = ['admin'=>'مشرف عام','supervisor'=>'مشرف المحتوى','designer'=>'مصمم جرافيك','editor'=>'مونتير ومصمم'];
  $avatarColor = fn($id)=> ['#191824','#5B4BDB','#059669','#0284c7'][$id % 4];
?>
</head>
<body class="min-h-screen">
<?php if(auth()->guard()->check()): ?>
<div x-data="{ nav:false }" class="flex flex-row-reverse min-h-screen">
  <!-- المحتوى -->
  <main class="flex-1 min-w-0 flex flex-col">
    <?php echo $__env->make('partials.topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="flex-1 px-5 md:px-8 py-6 max-w-[1500px] w-full mx-auto">
      <?php if(session('ok')): ?>
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3500)" x-transition
             class="mb-4 bg-ink text-white text-sm font-semibold px-4 py-3 rounded-xl shadow-lift flex items-center gap-2">
          <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
          <?php echo e(session('ok')); ?>

        </div>
      <?php endif; ?>
      <?php if($errors->any()): ?>
        <div class="mb-4 bg-rose-50 text-rose-700 text-sm px-4 py-3 rounded-xl border border-rose-200">
          <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <div><?php echo e($e); ?></div> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      <?php endif; ?>
      <?php echo $__env->yieldContent('content'); ?>
    </div>
  </main>

  <!-- الشريط الجانبي (يمين الشاشة) -->
  <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <!-- طبقة تعتيم للجوال عند فتح القائمة -->
  <div x-cloak x-show="nav" x-on:click="nav=false" x-transition.opacity class="fixed inset-0 bg-ink/40 z-30 lg:hidden"></div>
</div>
<?php else: ?>
  <?php echo $__env->yieldContent('content'); ?>
<?php endif; ?>
</body>
</html>
<?php /**PATH D:\markting\promanage\resources\views/layouts/app.blade.php ENDPATH**/ ?>