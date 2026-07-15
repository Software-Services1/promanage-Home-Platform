@php
  $vals = [
    'plan_time'    => ['التوقيت', $p->plan_time],
    'day_name'     => ['اليوم', $p->day_name],
    'plan_date'    => ['التاريخ', optional($p->plan_date)->format('Y-m-d')],
    'content_type' => ['نوع المحتوى', $p->content_type],
    'post_type'    => ['نوع المنشور', $p->post_type],
    'work_type'    => ['نوع العمل', $p->work_type],
    'assignee'     => ['المصمم', optional($p->assignee)->name],
    'supervisor'   => ['المتابِع', optional($p->supervisor)->name],
    'caption'      => ['الكابشن', $p->caption],
  ];
  $dz = $p->designers->map(fn ($d) => ['user_id' => (string) $d->id, 'work_type' => $d->pivot->work_type ?: ''])->values();
  $current = $p->currentDesigner();
  $editObj = "{ id:{$p->id}, platform:".json_encode($p->platform,JSON_UNESCAPED_UNICODE).", company_name:".json_encode($p->company_name,JSON_UNESCAPED_UNICODE).", plan_date:".json_encode(optional($p->plan_date)->format('Y-m-d')).", plan_time:".json_encode($p->plan_time).", content_type:".json_encode($p->content_type,JSON_UNESCAPED_UNICODE).", post_type:".json_encode($p->post_type,JSON_UNESCAPED_UNICODE).", status:".json_encode($p->status,JSON_UNESCAPED_UNICODE).", assigned_to:".($p->assigned_to ?? 'null').", supervisor_id:".($p->supervisor_id ?? 'null').", work_type:".json_encode($p->work_type,JSON_UNESCAPED_UNICODE).", design_content:".json_encode($p->design_content,JSON_UNESCAPED_UNICODE).", design_text:".json_encode($p->design_text,JSON_UNESCAPED_UNICODE).", caption:".json_encode($p->caption,JSON_UNESCAPED_UNICODE).", post_text:".json_encode($p->post_text,JSON_UNESCAPED_UNICODE).", reference_link:".json_encode($p->reference_link).", notes:".json_encode($p->notes,JSON_UNESCAPED_UNICODE).", designers:".json_encode($dz)." }";
  $viewObj = "{ id:{$p->id}, owner:".($p->assigned_to===auth()->id() ? 'true':'false').", platform:".json_encode($p->platform,JSON_UNESCAPED_UNICODE).", date:".json_encode(optional($p->plan_date)->format('Y-m-d')).", content_type:".json_encode($p->content_type,JSON_UNESCAPED_UNICODE).", post_type:".json_encode($p->post_type,JSON_UNESCAPED_UNICODE).", status:".json_encode($p->status,JSON_UNESCAPED_UNICODE).", caption:".json_encode($p->caption,JSON_UNESCAPED_UNICODE).", post_text:".json_encode($p->post_text,JSON_UNESCAPED_UNICODE).", design_content:".json_encode($p->design_content,JSON_UNESCAPED_UNICODE).", design_text:".json_encode($p->design_text,JSON_UNESCAPED_UNICODE).", notes:".json_encode($p->notes,JSON_UNESCAPED_UNICODE).", reference_link:".json_encode($p->reference_link).", assignee:".json_encode(optional($p->assignee)->name,JSON_UNESCAPED_UNICODE)." }";
  $designerNames = $p->designers->pluck('name')->join('، ');
  $wtLabel = \App\Models\TaskType::map()[$p->work_type]['label'] ?? '';
@endphp
<div data-row
     data-company="{{ $p->company_name }}"
     data-platform="{{ $p->platform }}"
     data-user="{{ $designerNames ?: optional($p->assignee)->name }}"
     data-worktype="{{ $wtLabel }}"
     data-date="{{ optional($p->plan_date)->format('Y-m-d') }}"
     data-status="{{ $p->status }}"
     class="bg-white rounded-2xl border border-line shadow-soft p-3.5 hover:shadow-lift hover:border-brand/30 transition">
  <div class="flex items-start justify-between gap-2">
    <div class="min-w-0">
      <div class="font-bold ff-display text-sm truncate">{{ $p->company_name ?: $p->platform }}</div>
      <div class="text-[11px] text-muted truncate">{{ $p->platform }}@if($p->plan_date) · {{ $p->plan_date->format('m-d') }}@endif</div>
    </div>
    @if($canApprove)<input type="checkbox" value="{{ $p->id }}" class="rowchk accent-brand w-4 h-4 mt-1 shrink-0">@endif
  </div>

  <div class="mt-2.5 space-y-1">
    @foreach($cardFields as $cf)
      @if(isset($vals[$cf]) && $vals[$cf][1])
        <div class="flex items-center justify-between gap-2 text-[11.5px]">
          <span class="text-muted shrink-0">{{ $vals[$cf][0] }}</span>
          <span class="font-semibold text-ink truncate">{{ $vals[$cf][1] }}</span>
        </div>
      @endif
    @endforeach
  </div>

  @if($p->designers->isNotEmpty())
    <div class="mt-3 flex flex-wrap items-center gap-1">
      @foreach($p->designers as $d)
        <span class="text-[10px] px-2 py-0.5 rounded-full inline-flex items-center gap-1 {{ $d->pivot->step_status==='مكتمل' ? 'bg-emerald-50 text-emerald-700' : ($d->pivot->step_status==='قيد العمل' ? 'bg-brand/10 text-brand font-bold' : 'bg-gray-100 text-gray-500') }}">
          <span class="tnum">{{ $d->pivot->position }}.</span>{{ $d->name }}
          @if($d->pivot->step_status==='مكتمل')<svg viewBox="0 0 24 24" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>@endif
        </span>
        @if(! $loop->last)<svg viewBox="0 0 24 24" class="w-3 h-3 text-line" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 6l-6 6 6 6"/></svg>@endif
      @endforeach
    </div>
    @if($current && $current->id === auth()->id())
      <form method="POST" action="{{ route('content.advance',$p) }}" class="mt-2">@csrf
        <button class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg py-2">
          <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg> إكمال دوري وتسليم للتالي</button>
      </form>
    @endif
  @endif

  <div class="mt-3 flex items-center gap-1.5 flex-wrap">
    @if(in_array('approval',$cardFields))<span class="text-[10px] font-semibold px-2 py-0.5 rounded-md {{ $approvalColor[$p->approval_state] ?? 'bg-gray-100' }}">{{ $approvalLabel[$p->approval_state] ?? $p->approval_state }}</span>@endif
    @if(in_array('status',$cardFields))<span class="text-[10px] font-semibold px-2 py-0.5 rounded-md {{ $statusColor[$p->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $p->status }}</span>@endif
    @if($p->design_file)<a href="{{ asset('storage/'.$p->design_file) }}" target="_blank" class="text-[10px] text-emerald-700 font-semibold hover:underline">تصميم</a>@endif

    <div class="flex-1"></div>

    @if($canContribute && ! $canUpdate)
      <button type="button" class="w-8 h-8 rounded-lg grid place-items-center text-muted hover:text-brand hover:bg-violet-50" title="استعراض / ملاحظة"
        x-on:click="view={{ $p->id }}; vrow={{ $viewObj }}">
        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
      </button>
    @endif
    @if($canUpdate)
      <button type="button" class="w-8 h-8 rounded-lg grid place-items-center text-muted hover:text-brand hover:bg-violet-50" title="تعديل"
        x-on:click="edit={{ $p->id }}; form={{ $editObj }}">
        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
      </button>
    @endif
    @if($canDelete)
      <button type="button" class="w-8 h-8 rounded-lg grid place-items-center text-muted hover:text-rose-600 hover:bg-rose-50" title="حذف"
        x-on:click="if(confirm('حذف هذا الصف؟')){ delRow({{ $p->id }}); }">
        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
      </button>
    @endif
  </div>
</div>
