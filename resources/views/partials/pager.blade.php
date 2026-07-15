{{-- شريط ترقيم فوري — يعمل ضمن مكوّن boardFilter --}}
<div x-show="pages > 1" class="flex items-center justify-between gap-3 mt-4 flex-wrap">
  <div class="text-xs text-muted">
    عرض <span class="tnum font-semibold" x-text="from"></span>–<span class="tnum font-semibold" x-text="to"></span>
    من <span class="tnum font-semibold" x-text="total"></span>
  </div>
  <div class="flex items-center gap-1">
    <button type="button" x-on:click="go(page-1)" :disabled="page===1"
            class="w-9 h-9 rounded-lg grid place-items-center border border-line bg-white text-muted hover:border-brand/40 disabled:opacity-40 disabled:pointer-events-none">
      <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 6l6 6-6 6"/></svg>
    </button>
    <template x-if="pageList[0] > 1">
      <div class="flex items-center gap-1">
        <button type="button" x-on:click="go(1)" class="w-9 h-9 rounded-lg text-sm font-bold border border-line bg-white text-ink hover:border-brand/40 tnum">1</button>
        <span class="text-muted px-1">…</span>
      </div>
    </template>
    <template x-for="p in pageList" :key="p">
      <button type="button" x-on:click="go(p)" x-text="p"
              :class="p===page ? 'bg-brand text-white border-brand' : 'bg-white text-ink border-line hover:border-brand/40'"
              class="w-9 h-9 rounded-lg text-sm font-bold border tnum"></button>
    </template>
    <template x-if="pageList[pageList.length-1] < pages">
      <div class="flex items-center gap-1">
        <span class="text-muted px-1">…</span>
        <button type="button" x-on:click="go(pages)" x-text="pages" class="w-9 h-9 rounded-lg text-sm font-bold border border-line bg-white text-ink hover:border-brand/40 tnum"></button>
      </div>
    </template>
    <button type="button" x-on:click="go(page+1)" :disabled="page===pages"
            class="w-9 h-9 rounded-lg grid place-items-center border border-line bg-white text-muted hover:border-brand/40 disabled:opacity-40 disabled:pointer-events-none">
      <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 6l-6 6 6 6"/></svg>
    </button>
  </div>
</div>
