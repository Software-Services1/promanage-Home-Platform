@extends('layouts.app')
@section('title','إعدادات النظام')
@section('content')
<form method="POST" action="{{ route('settings.update') }}" class="max-w-3xl space-y-5">@csrf @method('PUT')

  <div class="bg-white rounded-2xl border border-line shadow-soft p-5 md:p-6">
    <h3 class="ff-display font-bold text-lg mb-1">احتساب نقاط الإشراف</h3>
    <p class="text-sm text-muted mb-4">نسبة كل مشرف تُحدَّد من «إدارة المستخدمين».</p>

    <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer mb-3 transition {{ $mode==='auto' ? 'border-brand bg-violet-50' : 'border-line hover:border-brand/40' }}">
      <input type="radio" name="supervisor_credit_mode" value="auto" @checked($mode==='auto') class="accent-brand w-5 h-5 mt-0.5">
      <div><div class="font-bold text-ink">توزيع تلقائي</div>
        <div class="text-sm text-muted mt-0.5">نسبة المشرف ٪ × إجمالي نقاط جميع المصممين.</div></div>
    </label>
    <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition {{ $mode==='assigned' ? 'border-brand bg-violet-50' : 'border-line hover:border-brand/40' }}">
      <input type="radio" name="supervisor_credit_mode" value="assigned" @checked($mode==='assigned') class="accent-brand w-5 h-5 mt-0.5">
      <div><div class="font-bold text-ink">حسب الإسناد</div>
        <div class="text-sm text-muted mt-0.5">نسبة المشرف ٪ × نقاط المهام/العناصر المُسنَدة إليه كمتابِع.</div></div>
    </label>
  </div>

  <div class="bg-white rounded-2xl border border-line shadow-soft p-5 md:p-6">
    <h3 class="ff-display font-bold text-lg mb-1">حقول كارت خطة المحتوى</h3>
    <p class="text-sm text-muted mb-4">اختر البيانات التي تظهر على وجه كل كارت في خطة المحتوى.</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
      @foreach($allCardFields as $key => $label)
        <label class="flex items-center gap-2 text-sm bg-canvas rounded-xl px-3 py-2.5 cursor-pointer border border-transparent hover:border-brand/30 transition">
          <input type="checkbox" name="content_card_fields[]" value="{{ $key }}" @checked(in_array($key,$cardFields)) class="accent-brand w-4 h-4">
          {{ $label }}
        </label>
      @endforeach
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-line shadow-soft p-5 md:p-6">
    <h3 class="ff-display font-bold text-lg mb-1">المكافآت والخصومات</h3>
    <p class="text-sm text-muted mb-4">اضبط نِسَب المكافأة والخصم وعتبة النقاط المطلوبة.</p>
    <div class="grid sm:grid-cols-3 gap-4">
      <div>
        <label class="text-xs font-semibold text-muted mb-1.5 block">نسبة المكافأة ٪</label>
        <input name="payroll_bonus_pct" type="number" step="0.1" min="0" max="100" value="{{ $payroll['bonus_pct'] }}" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum">
        <p class="text-[11px] text-muted mt-1">من الراتب عند تجاوز العتبة.</p>
      </div>
      <div>
        <label class="text-xs font-semibold text-muted mb-1.5 block">حد أقصى للخصم ٪</label>
        <input name="payroll_deduction_pct" type="number" step="0.1" min="0" max="100" value="{{ $payroll['deduction_pct'] }}" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum">
        <p class="text-[11px] text-muted mt-1">عند عدم تحقيق العتبة (متناسب).</p>
      </div>
      <div>
        <label class="text-xs font-semibold text-muted mb-1.5 block">عتبة النقاط</label>
        <input name="payroll_threshold" type="number" min="0" value="{{ $payroll['threshold'] }}" placeholder="تارجت كل موظف" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm tnum">
        <p class="text-[11px] text-muted mt-1">اتركها فارغة لاستخدام تارجت كل موظف.</p>
      </div>
    </div>
  </div>

  <div class="flex justify-end">
    <button class="px-5 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd shadow-lift">حفظ الإعدادات</button>
  </div>
</form>
@endsection
