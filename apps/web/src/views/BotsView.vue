<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AppIcon from '../components/ui/AppIcon.vue'
import StatusBadge from '../components/ui/StatusBadge.vue'
import {
  useAppStore,
  type ScannerRunStatus,
} from '../stores/app'

const appStore = useAppStore()
const route = useRoute()
const router = useRouter()

const loadState = ref<'loading' | 'ready' | 'error'>('loading')
const errorMessage = ref('')
const search = ref('')
const selectedStatuses = ref<ScannerRunStatus[]>([])
const selectedTimeframes = ref<string[]>([])
const selectedWatchlists = ref<string[]>([])
const selectedRecency = ref<'24h' | '7d' | '30d'>('7d')
const selectedRunReference = ref<string | null>(null)

const statusOptions = computed<ScannerRunStatus[]>(() => ['completed', 'completed_with_errors', 'failed'])
const timeframeOptions = computed(() => [...new Set(appStore.scannerRuns.map((run) => run.timeframe))])
const watchlistOptions = computed(() => [...new Set(appStore.scannerRuns.map((run) => run.watchlist))])

const filteredRuns = computed(() => {
  const term = search.value.trim().toLowerCase()
  const now = new Date('2026-03-31T12:00:00Z').getTime()
  const maxAgeMs = selectedRecency.value === '24h' ? 24 * 60 * 60 * 1000 : selectedRecency.value === '7d' ? 7 * 24 * 60 * 60 * 1000 : 30 * 24 * 60 * 60 * 1000

  return appStore.scannerRuns.filter((run) => {
    const startedAt = new Date(run.startedAt).getTime()
    const matchesSearch =
      term.length === 0 ||
      run.runReference.toLowerCase().includes(term) ||
      run.watchlist.toLowerCase().includes(term) ||
      run.triggerType.toLowerCase().includes(term)

    const matchesStatus = selectedStatuses.value.length === 0 || selectedStatuses.value.includes(run.status)
    const matchesTimeframe = selectedTimeframes.value.length === 0 || selectedTimeframes.value.includes(run.timeframe)
    const matchesWatchlist = selectedWatchlists.value.length === 0 || selectedWatchlists.value.includes(run.watchlist)
    const matchesRecency = now - startedAt <= maxAgeMs

    return matchesSearch && matchesStatus && matchesTimeframe && matchesWatchlist && matchesRecency
  })
})

const sortedRuns = computed(() =>
  [...filteredRuns.value].sort((left, right) => new Date(right.startedAt).getTime() - new Date(left.startedAt).getTime()),
)

const selectedRun = computed(() => {
  const fromSelected = selectedRunReference.value
    ? sortedRuns.value.find((run) => run.runReference === selectedRunReference.value) ?? null
    : null

  return fromSelected ?? sortedRuns.value[0] ?? null
})

const summary = computed(() => ({
  totalRuns: sortedRuns.value.length,
  failedRuns: sortedRuns.value.filter((run) => run.status === 'failed').length,
  warningRuns: sortedRuns.value.filter((run) => run.status === 'completed_with_errors').length,
  signalsFound: sortedRuns.value.reduce((sum, run) => sum + run.signalsFoundCount, 0),
}))

function formatLabel(value: string): string {
  return value.replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
}

function formatDate(value: string): string {
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  }).format(new Date(value))
}

function statusTone(status: ScannerRunStatus): 'success' | 'warning' | 'danger' {
  if (status === 'completed') return 'success'
  if (status === 'completed_with_errors') return 'warning'
  return 'danger'
}

function toggleMultiValue<T extends string>(target: T[], value: T): T[] {
  return target.includes(value) ? target.filter((item) => item !== value) : [...target, value]
}

function clearFilters() {
  search.value = ''
  selectedStatuses.value = []
  selectedTimeframes.value = []
  selectedWatchlists.value = []
  selectedRecency.value = '7d'
}

async function hydrateRuns() {
  loadState.value = 'loading'
  errorMessage.value = ''

  try {
    await new Promise((resolve) => setTimeout(resolve, 180))

    if (route.query.simulateRunsError === '1') {
      throw new Error('Scanner run monitor failed to load. Retry when tracking data is available.')
    }

    loadState.value = 'ready'
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Scanner run monitor failed to load.'
    loadState.value = 'error'
  }
}

watch(
  () => route.query,
  (query) => {
    search.value = typeof query.search === 'string' ? query.search : ''
    selectedStatuses.value = typeof query.status === 'string' && query.status.length > 0 ? (query.status.split(',') as ScannerRunStatus[]) : []
    selectedTimeframes.value = typeof query.timeframe === 'string' && query.timeframe.length > 0 ? query.timeframe.split(',') : []
    selectedWatchlists.value = typeof query.watchlist === 'string' && query.watchlist.length > 0 ? query.watchlist.split(',') : []
    selectedRecency.value = query.recency === '24h' || query.recency === '30d' ? query.recency : '7d'
    selectedRunReference.value = typeof query.run === 'string' ? query.run : null
  },
  { immediate: true },
)

watch(
  [search, selectedStatuses, selectedTimeframes, selectedWatchlists, selectedRecency, selectedRunReference],
  () => {
    void router.replace({
      query: {
        ...route.query,
        search: search.value || undefined,
        status: selectedStatuses.value.length > 0 ? selectedStatuses.value.join(',') : undefined,
        timeframe: selectedTimeframes.value.length > 0 ? selectedTimeframes.value.join(',') : undefined,
        watchlist: selectedWatchlists.value.length > 0 ? selectedWatchlists.value.join(',') : undefined,
        recency: selectedRecency.value !== '7d' ? selectedRecency.value : undefined,
        run: selectedRunReference.value ?? undefined,
      },
    })
  },
  { deep: true },
)

onMounted(() => {
  void hydrateRuns()
})
</script>

<template>
  <section class="flex flex-col gap-5">
    <section class="rounded-[28px] border border-white/10 bg-[radial-gradient(circle_at_top_right,rgba(96,165,250,0.18),transparent_28%),linear-gradient(180deg,rgba(15,23,42,0.96),rgba(17,24,39,0.88))] p-6 shadow-[0_20px_60px_rgba(0,0,0,0.28)]">
      <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div class="max-w-4xl">
          <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sc-muted">Scanner engine · Task #102</p>
          <h1 class="mt-2 text-4xl font-semibold tracking-tight text-white">Scanner run monitor</h1>
          <p class="mt-3 max-w-3xl text-sm leading-6 text-sc-muted">
            Operational surface for recent scanner executions, with health visibility, throughput metrics, and drill-down context.
          </p>
        </div>

        <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
          <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3">
            <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Runs</span>
            <strong class="mt-2 block text-lg font-semibold text-white">{{ summary.totalRuns }}</strong>
          </div>
          <div class="rounded-2xl border border-sc-danger/20 bg-sc-danger-soft px-4 py-3">
            <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Failed</span>
            <strong class="mt-2 block text-lg font-semibold text-white">{{ summary.failedRuns }}</strong>
          </div>
          <div class="rounded-2xl border border-sc-warning/20 bg-sc-warning-soft px-4 py-3">
            <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Warnings</span>
            <strong class="mt-2 block text-lg font-semibold text-white">{{ summary.warningRuns }}</strong>
          </div>
          <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3">
            <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Signals found</span>
            <strong class="mt-2 block text-lg font-semibold text-white">{{ summary.signalsFound }}</strong>
          </div>
        </div>
      </div>
    </section>

    <section class="rounded-[24px] border border-white/10 bg-black/15 p-4">
      <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.3fr)_220px]">
        <label class="flex flex-col gap-2">
          <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Search</span>
          <div class="relative">
            <AppIcon name="Search" :size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/45" />
            <input
              v-model="search"
              type="search"
              placeholder="Search by run reference, watchlist, or trigger"
              class="min-h-11 w-full rounded-xl border border-white/10 bg-black/20 py-2.5 pl-10 pr-3 text-sm text-white outline-none transition placeholder:text-white/35 focus:border-sc-primary/45 focus:ring-2 focus:ring-sc-primary/30"
            />
          </div>
        </label>

        <label class="flex flex-col gap-2">
          <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Recency</span>
          <select v-model="selectedRecency" class="min-h-11 rounded-xl border border-white/10 bg-black/20 px-3 text-sm text-white outline-none focus:border-sc-primary/45 focus:ring-2 focus:ring-sc-primary/30">
            <option value="24h">Last 24 hours</option>
            <option value="7d">Last 7 days</option>
            <option value="30d">Last 30 days</option>
          </select>
        </label>
      </div>

      <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="flex flex-col gap-3">
          <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Status</span>
          <div class="flex flex-wrap gap-2">
            <button v-for="status in statusOptions" :key="status" type="button" class="rounded-full border px-3 py-2 text-sm transition" :class="selectedStatuses.includes(status) ? 'border-sc-primary/30 bg-sc-primary-soft text-white' : 'border-white/10 bg-white/5 text-white/75 hover:border-white/20'" @click="selectedStatuses = toggleMultiValue(selectedStatuses, status)">{{ formatLabel(status) }}</button>
          </div>
        </div>

        <div class="flex flex-col gap-3">
          <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Timeframe</span>
          <div class="flex flex-wrap gap-2">
            <button v-for="timeframe in timeframeOptions" :key="timeframe" type="button" class="rounded-full border px-3 py-2 text-sm transition" :class="selectedTimeframes.includes(timeframe) ? 'border-sc-primary/30 bg-sc-primary-soft text-white' : 'border-white/10 bg-white/5 text-white/75 hover:border-white/20'" @click="selectedTimeframes = toggleMultiValue(selectedTimeframes, timeframe)">{{ timeframe }}</button>
          </div>
        </div>

        <div class="flex flex-col gap-3">
          <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Watchlist</span>
          <div class="flex flex-wrap gap-2">
            <button v-for="watchlist in watchlistOptions" :key="watchlist" type="button" class="rounded-full border px-3 py-2 text-sm transition" :class="selectedWatchlists.includes(watchlist) ? 'border-sc-primary/30 bg-sc-primary-soft text-white' : 'border-white/10 bg-white/5 text-white/75 hover:border-white/20'" @click="selectedWatchlists = toggleMultiValue(selectedWatchlists, watchlist)">{{ watchlist }}</button>
          </div>
        </div>
      </div>

      <div class="mt-4 flex flex-wrap items-center gap-3">
        <button class="rounded-full border border-white/10 bg-white/6 px-3 py-2 text-sm text-white/80" @click="clearFilters">Clear filters</button>
        <button class="rounded-full border border-white/10 bg-white/6 px-3 py-2 text-sm text-white/80" @click="hydrateRuns">Refresh</button>
      </div>
    </section>

    <div v-if="loadState === 'loading'" class="rounded-3xl border border-dashed border-white/12 bg-white/4 p-5">
      <p class="text-base font-semibold text-white">Loading scanner run monitor...</p>
      <div class="mt-4 flex flex-col gap-3">
        <div v-for="item in 6" :key="item" class="h-12 rounded-2xl bg-white/6" />
      </div>
    </div>

    <div v-else-if="loadState === 'error'" class="rounded-3xl border border-sc-danger/25 bg-sc-danger-soft p-5">
      <p class="text-base font-semibold text-white">Unable to load scanner runs</p>
      <p class="mt-2 text-sm text-white/75">{{ errorMessage }}</p>
      <button class="mt-4 rounded-full border border-white/10 bg-white/8 px-3 py-2 text-sm text-white/85" @click="hydrateRuns">Retry</button>
    </div>

    <div v-else-if="appStore.scannerRuns.length === 0" class="rounded-3xl border border-dashed border-white/12 bg-white/4 p-5">
      <p class="text-base font-semibold text-white">No recorded scanner runs yet</p>
      <p class="mt-2 text-sm text-sc-muted">Scanner run history is not available yet for this environment.</p>
    </div>

    <div v-else-if="sortedRuns.length === 0" class="rounded-3xl border border-dashed border-white/12 bg-white/4 p-5">
      <p class="text-base font-semibold text-white">No scanner runs match the current filters</p>
      <p class="mt-2 text-sm text-sc-muted">Adjust the monitor filters or clear them to restore the latest executions.</p>
      <button class="mt-4 rounded-full border border-white/10 bg-white/8 px-3 py-2 text-sm text-white/85" @click="clearFilters">Clear filters</button>
    </div>

    <div v-else class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1.75fr)_380px]">
      <section class="overflow-hidden rounded-[24px] border border-white/10 bg-black/15">
        <div class="flex flex-col gap-3 border-b border-white/8 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Run monitor</p>
            <h3 class="mt-1 text-lg font-semibold text-white">Recent scanner executions</h3>
          </div>
          <div class="inline-flex items-center gap-2 text-sm text-sc-muted">
            <AppIcon name="Activity" :size="15" />
            <span>Newest runs first · click a row to inspect</span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full table-auto border-collapse">
            <thead>
              <tr class="border-b border-white/8 text-left text-[11px] uppercase tracking-[0.14em] text-sc-muted">
                <th class="px-4 py-3">Run reference</th>
                <th class="px-4 py-3">Watchlist</th>
                <th class="px-4 py-3">Timeframe</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Symbols</th>
                <th class="px-4 py-3">Strategies</th>
                <th class="px-4 py-3">Signals</th>
                <th class="px-4 py-3">Errors</th>
                <th class="px-4 py-3">Started</th>
                <th class="px-4 py-3">Completed</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="run in sortedRuns" :key="run.id" class="cursor-pointer border-b border-white/6 transition hover:bg-white/4" :class="selectedRun?.runReference === run.runReference ? 'bg-sc-primary-soft/70' : ''" @click="selectedRunReference = run.runReference">
                <td class="px-4 py-3 align-top">
                  <div class="flex flex-col gap-1">
                    <strong class="text-sm font-semibold text-white">{{ run.runReference }}</strong>
                    <small class="text-xs text-sc-muted">{{ formatLabel(run.triggerType) }}</small>
                  </div>
                </td>
                <td class="px-4 py-3 align-top text-sm text-white/80">{{ run.watchlist }}</td>
                <td class="px-4 py-3 align-top text-sm text-white/80">{{ run.timeframe }}</td>
                <td class="px-4 py-3 align-top"><StatusBadge :label="formatLabel(run.status)" :tone="statusTone(run.status)" /></td>
                <td class="px-4 py-3 align-top text-sm text-white">{{ run.symbolsScannedCount }}</td>
                <td class="px-4 py-3 align-top text-sm text-white">{{ run.strategiesExecutedCount }}</td>
                <td class="px-4 py-3 align-top"><span class="inline-flex min-w-14 items-center justify-center rounded-full border border-white/10 bg-white/6 px-3 py-1.5 text-sm text-white">{{ run.signalsFoundCount }}</span></td>
                <td class="px-4 py-3 align-top"><span class="inline-flex min-w-14 items-center justify-center rounded-full border px-3 py-1.5 text-sm" :class="run.errorCount > 0 ? 'border-sc-danger/25 bg-sc-danger-soft text-white' : 'border-white/10 bg-white/6 text-white/80'">{{ run.errorCount }}</span></td>
                <td class="px-4 py-3 align-top text-sm text-white/80">{{ formatDate(run.startedAt) }}</td>
                <td class="px-4 py-3 align-top text-sm text-white/80">{{ formatDate(run.completedAt) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <aside v-if="selectedRun" class="sticky top-6 flex h-fit flex-col gap-4 rounded-[24px] border border-white/10 bg-black/20 p-5">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Run detail</p>
          <h3 class="mt-1 text-lg font-semibold text-white">{{ selectedRun.runReference }}</h3>
        </div>

        <div class="flex flex-wrap gap-2">
          <StatusBadge :label="formatLabel(selectedRun.status)" :tone="statusTone(selectedRun.status)" />
          <StatusBadge :label="selectedRun.timeframe" tone="neutral" />
          <StatusBadge :label="selectedRun.watchlist" tone="outline" />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="rounded-2xl border border-white/10 bg-white/6 p-4"><span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Duration</span><strong class="mt-2 block text-xl font-semibold text-white">{{ selectedRun.duration }}</strong></div>
          <div class="rounded-2xl border border-white/10 bg-white/6 p-4"><span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Trigger</span><strong class="mt-2 block text-base font-semibold text-white">{{ formatLabel(selectedRun.triggerType) }}</strong></div>
          <div class="rounded-2xl border border-white/10 bg-white/6 p-4"><span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Signals found</span><strong class="mt-2 block text-xl font-semibold text-white">{{ selectedRun.signalsFoundCount }}</strong></div>
          <div class="rounded-2xl border border-white/10 bg-white/6 p-4"><span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Errors</span><strong class="mt-2 block text-xl font-semibold text-white">{{ selectedRun.errorCount }}</strong></div>
        </div>

        <section class="flex flex-col gap-2">
          <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Error summary</p>
          <p class="text-sm leading-6 text-white/78">{{ selectedRun.errorSummary ?? 'No error summary recorded.' }}</p>
        </section>

        <section class="flex flex-col gap-3">
          <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Lifecycle events</p>
          <article v-for="event in selectedRun.lifecycleEvents" :key="event.id" class="rounded-2xl border border-white/10 bg-white/6 p-4">
            <div class="flex items-center justify-between gap-3">
              <strong class="text-sm text-white">{{ event.label }}</strong>
              <span class="text-xs text-sc-muted">{{ formatDate(event.occurredAt) }}</span>
            </div>
            <p class="mt-2 text-sm leading-6 text-white/78">{{ event.detail }}</p>
          </article>
        </section>
      </aside>
    </div>
  </section>
</template>
