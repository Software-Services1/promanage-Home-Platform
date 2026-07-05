<?php $__env->startSection('title','تسجيل الدخول — ProManage Flow'); ?>
<?php $__env->startSection('content'); ?>
<div class="min-h-screen grid place-items-center px-4">
  <div class="w-full max-w-sm">
    <div class="flex flex-col items-center mb-6">
      <div class="w-14 h-14 rounded-2xl bg-brand grid place-items-center text-white shadow-lift mb-3">
        <svg viewBox="0 0 24 24" class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l8 4.5v9L12 21l-8-4.5v-9z"/><path d="M12 12v9M12 12 4 7.5M12 12l8-4.5"/></svg>
      </div>
      <div class="ff-display font-extrabold text-2xl text-ink">ProManage Flow</div>
      <div class="text-sm text-muted mt-1">نظام إدارة مهام التسويق</div>
    </div>
    <div class="bg-white rounded-2xl border border-line shadow-soft p-6">
      <?php if($errors->any()): ?>
        <div class="mb-4 bg-rose-50 text-rose-700 text-sm px-3 py-2.5 rounded-xl"><?php echo e($errors->first()); ?></div>
      <?php endif; ?>
      <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-4"><?php echo csrf_field(); ?>
        <div>
          <label class="text-xs font-semibold text-muted mb-1.5 block">البريد الإلكتروني</label>
          <input name="email" type="email" value="<?php echo e(old('email')); ?>" required autofocus
                 class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/15 outline-none">
        </div>
        <div>
          <label class="text-xs font-semibold text-muted mb-1.5 block">كلمة المرور</label>
          <input name="password" type="password" required
                 class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/15 outline-none">
        </div>
        <label class="flex items-center gap-2 text-sm text-muted">
          <input type="checkbox" name="remember" class="accent-brand w-4 h-4"> تذكّرني
        </label>
        <button class="w-full bg-brand hover:bg-brandd text-white font-bold py-3 rounded-xl transition shadow-lift">دخول</button>
      </form>
    </div>
    <p class="text-center text-xs text-muted mt-4">كلمة المرور الافتراضية للحسابات التجريبية: <b>password</b></p>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\markting\promanage\resources\views/auth/login.blade.php ENDPATH**/ ?>