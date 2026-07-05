@extends('layouts.app')
@section('title','الأدوار والصلاحيات')
@section('content')
@php
  $permLabels = [
    'view all content'=>'رؤية كل المحتوى','create content'=>'إنشاء محتوى','update content'=>'تعديل المحتوى','approve content'=>'اعتماد المحتوى','delete content'=>'حذف المحتوى','upload design'=>'رفع التصاميم','update content status'=>'تغيير حالة المحتوى','add content note'=>'إضافة ملاحظة',
    'view all tasks'=>'رؤية كل المهام','create tasks'=>'إنشاء المهام','update tasks'=>'تعديل المهام','delete tasks'=>'حذف المهام',
    'view all leaves'=>'رؤية كل الإجازات','approve leaves'=>'اعتماد الإجازات','view all salaries'=>'رؤية كل الرواتب',
    'manage users'=>'إدارة المستخدمين','manage maintenance'=>'إدارة الصيانة','view reports'=>'عرض التقارير',
    'manage roles'=>'إدارة الأدوار','manage task types'=>'إدارة أنواع المهام',
  ];
  $roleLabelsMap = ['admin'=>'مشرف عام','manager'=>'مدير','supervisor'=>'مشرف','designer'=>'مصمم','editor'=>'مونتير','writer'=>'كاتب محتوى','publisher'=>'ناشر محتوى','employee'=>'موظف'];
@endphp
<div x-data="{ create:false, edit:null }">
  <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
    <div class="text-sm text-muted">{{ $roles->count() }} أدوار • {{ $permissions->count() }} صلاحية</div>
    <button x-on:click="create=true" class="inline-flex items-center gap-2 bg-brand hover:bg-brandd text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-lift">
      <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> دور جديد</button>
  </div>

  <div class="grid md:grid-cols-2 gap-4">
    @foreach($roles as $role)
      <div class="bg-white rounded-2xl border border-line shadow-soft p-5">
        <div class="flex items-center justify-between mb-3">
          <div>
            <div class="ff-display font-bold text-lg">{{ $roleLabelsMap[$role->name] ?? $role->name }}</div>
            <div class="text-[11px] text-muted">{{ $role->name }} • {{ $role->permissions->count() }} صلاحية</div>
          </div>
          <div class="flex gap-1">
            <button x-on:click="edit={{ $role->id }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-violet-50 text-brand hover:bg-violet-100">تعديل الصلاحيات</button>
            @if($role->name!=='admin')
              <form method="POST" action="{{ route('roles.destroy',$role) }}" onsubmit="return confirm('حذف هذا الدور؟')">@csrf @method('DELETE')
                <button class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-600 hover:bg-rose-100">حذف</button></form>
            @endif
          </div>
        </div>
        <div class="flex flex-wrap gap-1.5">
          @forelse($role->permissions as $p)
            <span class="text-[11px] bg-canvas border border-line rounded-lg px-2 py-1">{{ $permLabels[$p->name] ?? $p->name }}</span>
          @empty
            <span class="text-xs text-muted">لا توجد صلاحيات (يرى ما يخصّه فقط).</span>
          @endforelse
        </div>
      </div>

      <!-- نافذة تعديل صلاحيات الدور -->
      <div x-cloak x-show="edit==={{ $role->id }}" class="fixed inset-0 z-50 grid place-items-center p-4">
        <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="edit=null"></div>
        <form method="POST" action="{{ route('roles.update',$role) }}" class="relative bg-white rounded-2xl shadow-lift w-full max-w-lg p-5 max-h-[90vh] overflow-y-auto">@csrf @method('PUT')
          <h3 class="ff-display font-bold text-lg mb-1">صلاحيات «{{ $roleLabelsMap[$role->name] ?? $role->name }}»</h3>
          @if($role->name==='admin')
            <p class="text-sm text-muted">دور المشرف العام يملك كل الصلاحيات ولا يُعدّل.</p>
          @else
            <p class="text-xs text-muted mb-3">فعّل ما يملكه هذا الدور.</p>
            <div class="grid sm:grid-cols-2 gap-2">
              @foreach($permissions as $perm)
                <label class="flex items-center gap-2 text-sm bg-canvas rounded-xl px-3 py-2 cursor-pointer">
                  <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="accent-brand w-4 h-4"
                    @checked($role->permissions->contains('name',$perm->name))>
                  {{ $permLabels[$perm->name] ?? $perm->name }}
                </label>
              @endforeach
            </div>
          @endif
          <div class="flex gap-2 justify-end pt-4">
            <button type="button" x-on:click="edit=null" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إغلاق</button>
            @if($role->name!=='admin')<button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">حفظ</button>@endif
          </div>
        </form>
      </div>
    @endforeach
  </div>

  <!-- نافذة إنشاء دور -->
  <div x-cloak x-show="create" class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-on:click="create=false"></div>
    <form method="POST" action="{{ route('roles.store') }}" class="relative bg-white rounded-2xl shadow-lift w-full max-w-lg p-5 max-h-[90vh] overflow-y-auto">@csrf
      <h3 class="ff-display font-bold text-lg mb-3">دور جديد</h3>
      <div class="mb-3"><label class="text-xs font-semibold text-muted mb-1 block">اسم الدور (بالإنجليزية، بدون مسافات)</label>
        <input name="name" required pattern="[a-z_]+" placeholder="content_lead" class="w-full bg-canvas border border-line rounded-xl px-3 py-2.5 text-sm"></div>
      <div class="grid sm:grid-cols-2 gap-2">
        @foreach($permissions as $perm)
          <label class="flex items-center gap-2 text-sm bg-canvas rounded-xl px-3 py-2 cursor-pointer">
            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="accent-brand w-4 h-4">
            {{ $permLabels[$perm->name] ?? $perm->name }}
          </label>
        @endforeach
      </div>
      <div class="flex gap-2 justify-end pt-4">
        <button type="button" x-on:click="create=false" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-muted hover:bg-canvas">إلغاء</button>
        <button class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand text-white hover:bg-brandd">إنشاء</button>
      </div>
    </form>
  </div>
</div>
@endsection
