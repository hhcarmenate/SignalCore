<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AppIcon from '../ui/AppIcon.vue'
import BaseSelect, { type SelectOption } from '../ui/forms/BaseSelect.vue'
import {
  useAppStore,
  type PersistedSignalRecord,
  type ReviewPriority,
  type SignalDirection,
  type SignalStatus,
} from '../../stores/app'
import StatusBadge from '../ui/StatusBadge.vue'

const props = withDefaults(
  defineProps<{
    mode?: 'compact' | 'full'
  }>(),
  {
    mode: 'compact',
  },
)

const appStore = useAppStore()
const route = useRoute()
const router = useRouter()

const loadState = ref<'loading' | 'ready' | 'error'>('loading')
const errorMessage = ref('')
const search = ref('')
const selectedStrategy = ref('all')
const selectedStatus = ref('all')
const selectedDirections = ref<SignalDirection[]>([])
const selectedTimeframes = ref<string[]>([])
const selectedPriorities = ref<ReviewPriority[]>([])
const sortKey = ref<
  'symbol' | 'strategyLabel' | 'score' | 'confidence' | 'reviewPriority' | 'signalGeneratedAt' | 'status'
>('reviewPriority')
const sortDirection = ref<'asc' | 'desc'>('asc')
const selectedSignalId = ref<string | null>(null)

const compactSignals = computed(() => appStore.topSignals)
const signals = computed(() => appStore.persistedSignals)
const selectedSignal = computed(() => signals.value.find((signal) => signal.id === selectedSignalId.value) ?? null)

const strategyOptions = computed<SelectOption[]>(() => [
  { value: 'all', label: 'All strategies' },
  ...[...new Set(signals.value.map((signal) => signal.strategyLabel))].map((strategy) => ({
    value: strategy,
    label: strategy,
  })),
])

const statusOptions = computed<SelectOption[]>(() => [
  { value: 'all', label: 'All statuses' },
  ...(['new', 'pending_review', 'accepted', 'rejected', 'expired', 'actioned', 'ignored'] as SignalStatus[]).map(
    (status) => ({ value: status, label: formatLabel(status) }),
  ),
])

const directionOptions = computed<SignalDirection[]>(() => ['bullish', 'bearish'])
const timeframeOptions = computed(() => [...new Set(signals.value.map((signal) => signal.timeframe))])
const priorityOptions = computed<ReviewPriority[]>(() => ['high', 'medium', 'low'])

const priorityRank: Record<ReviewPriority, number> = {
  high: 0,
  medium: 1,
  low: 2,
}

const filteredSignals = computed(() => {
  const term = search.value.trim().toLowerCase()

  return signals.value.filter((signal) => {
    const matchesSearch =
      term.length === 0 ||
      signal.symbol.toLowerCase().includes(term) ||
      signal.strategyLabel.toLowerCase().includes(term) ||
      signal.strategyKey.toLowerCase().includes(term)

    const matchesStrategy = selectedStrategy.value === 'all' || selectedStrategy.value === signal.strategyLabel
    const matchesStatus = selectedStatus.value === 'all' || selectedStatus.value === signal.status
    const matchesDirection =
      selectedDirections.value.length === 0 || selectedDirections.value.includes(signal.direction)
    const matchesTimeframe =
      selectedTimeframes.value.length === 0 || selectedTimeframes.value.includes(signal.timeframe)
    const matchesPriority =
      selectedPriorities.value.length === 0 || selectedPriorities.value.includes(signal.reviewPriority)

    return (
      matchesSearch &&
      matchesStrategy &&
      matchesStatus &&
      matchesDirection &&
      matchesTimeframe &&
      matchesPriority
    )
  })
})

const sortedSignals = computed(() => {
  const items = [...filteredSignals.value]

  items.sort((left, right) => {
    let comparison = 0

    switch (sortKey.value) {
      case 'reviewPriority':
        comparison = priorityRank[left.reviewPriority] - priorityRank[right.reviewPriority]
        break
      case 'score':
        comparison = left.score - right.score
        break
      case 'confidence':
        comparison = left.confidence - right.confidence
        break
      case 'signalGeneratedAt':
        comparison = new Date(left.signalGeneratedAt).getTime() - new Date(right.signalGeneratedAt).getTime()
        break
      case 'symbol':
        comparison = left.symbol.localeCompare(right.symbol)
        break
      case 'strategyLabel':
        comparison = left.strategyLabel.localeCompare(right.strategyLabel)
        break
      case 'status':
        comparison = left.status.localeCompare(right.status)
        break
    }

    if (comparison === 0) {
      comparison = right.reviewScore - left.reviewScore
      if (comparison === 0) {
        comparison = new Date(right.signalGeneratedAt).getTime() - new Date(left.signalGeneratedAt).getTime()
      }
    }

    return sortDirection.value === 'asc' ? comparison : comparison * -1
  })

  return items
})

const highPriorityCount = computed(
  () => sortedSignals.value.filter((signal) => signal.reviewPriority === 'high').length,
)
const pendingReviewCount = computed(
  () => sortedSignals.value.filter((signal) => signal.status === 'pending_review').length,
)
const resultLabel = computed(() => `${sortedSignals.value.length} signal${sortedSignals.value.length === 1 ? '' : 's'}`)
const tableLayoutClass = computed(() =>
  selectedSignal.value
    ? 'grid grid-cols-1 gap-5 2xl:grid-cols-[minmax(0,1.9fr)_380px]'
    : 'grid grid-cols-1 gap-5',
)
const activeFilterCount = computed(
  () =>
    Number(search.value.length > 0) +
    Number(selectedStrategy.value !== 'all') +
    Number(selectedStatus.value !== 'all') +
    Number(selectedDirections.value.length > 0) +
    Number(selectedTimeframes.value.length > 0) +
    Number(selectedPriorities.value.length > 0),
)
const hasActiveFilters = computed(() => activeFilterCount.value > 0)

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

function formatPrice(value?: number): string {
  return typeof value === 'number' ? `$${value.toFixed(2)}` : '--'
}

function badgeToneForDirection(direction: SignalDirection): 'success' | 'danger' {
  return direction === 'bullish' ? 'success' : 'danger'
}

function badgeToneForStatus(status: SignalStatus): 'success' | 'danger' | 'warning' | 'neutral' | 'outline' {
  if (status === 'pending_review') return 'warning'
  if (status === 'accepted' || status === 'actioned') return 'success'
  if (status === 'rejected') return 'danger'
  if (status === 'expired' || status === 'ignored') return 'neutral'
  return 'outline'
}

function badgeToneForPriority(priority: ReviewPriority): 'danger' | 'warning' | 'neutral' {
  if (priority === 'high') return 'danger'
  if (priority === 'medium') return 'warning'
  return 'neutral'
}

function toggleMultiValue<T extends string>(target: T[], value: T): T[] {
  return target.includes(value) ? target.filter((item) => item !== value) : [...target, value]
}

function setSort(nextKey: typeof sortKey.value) {
  if (sortKey.value === nextKey) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    return
  }

  sortKey.value = nextKey
  sortDirection.value = nextKey === 'reviewPriority' ? 'asc' : 'desc'
}

function clearFilters() {
  search.value = ''
  selectedStrategy.value = 'all'
  selectedStatus.value = 'all'
  selectedDirections.value = []
  selectedTimeframes.value = []
  selectedPriorities.value = []
}

function openSignal(signal: PersistedSignalRecord) {
  selectedSignalId.value = signal.id
}

async function hydrateSignals() {
  loadState.value = 'loading'
  errorMessage.value = ''

  try {
    await new Promise((resolve) => setTimeout(resolve, 180))

    if (route.query.simulateSignalsError === '1') {
      throw new Error('Signal feed failed to load. Retry once the data source is reachable.')
    }

    loadState.value = 'ready'
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Signal feed failed to load.'
    loadState.value = 'error'
  }
}

watch(
  () => route.query,
  (query) => {
    search.value = typeof query.search === 'string' ? query.search : ''
    selectedStrategy.value = typeof query.strategy === 'string' && query.strategy.length > 0 ? query.strategy : 'all'
    selectedStatus.value = typeof query.status === 'string' && query.status.length > 0 ? query.status : 'all'
    selectedDirections.value =
      typeof query.direction === 'string' && query.direction.length > 0
        ? (query.direction.split(',') as SignalDirection[])
        : []
    selectedTimeframes.value =
      typeof query.timeframe === 'string' && query.timeframe.length > 0 ? query.timeframe.split(',') : []
    selectedPriorities.value =
      typeof query.priority === 'string' && query.priority.length > 0
        ? (query.priority.split(',') as ReviewPriority[])
        : []
    selectedSignalId.value = typeof query.selected === 'string' ? query.selected : null
  },
  { immediate: true },
)

watch(
  [search, selectedStrategy, selectedStatus, selectedDirections, selectedTimeframes, selectedPriorities, selectedSignalId],
  () => {
    if (props.mode !== 'full') {
      return
    }

    void router.replace({
      query: {
        ...route.query,
        search: search.value || undefined,
        strategy: selectedStrategy.value !== 'all' ? selectedStrategy.value : undefined,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
        direction: selectedDirections.value.length > 0 ? selectedDirections.value.join(',') : undefined,
        timeframe: selectedTimeframes.value.length > 0 ? selectedTimeframes.value.join(',') : undefined,
        priority: selectedPriorities.value.length > 0 ? selectedPriorities.value.join(',') : undefined,
        selected: selectedSignalId.value ?? undefined,
      },
    })
  },
  { deep: true },
)

onMounted(() => {
  if (props.mode === 'full') {
    void hydrateSignals()
  }
})
</script>

<template>
  <div v-if="mode === 'compact'">
    <div class="mb-4 flex items-center justify-between gap-4">
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Priority feed</p>
        <h3 class="mt-1 text-lg font-semibold text-white">Top signals</h3>
      </div>
      <button class="rounded-full border border-white/10 bg-white/6 px-3 py-2 text-sm text-white/80">View all</button>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full table-auto border-collapse">
        <thead>
          <tr class="border-b border-white/8 text-left text-[11px] uppercase tracking-[0.14em] text-sc-muted">
            <th class="px-3 py-3">Symbol</th>
            <th class="px-3 py-3">Direction</th>
            <th class="px-3 py-3">Hint</th>
            <th class="px-3 py-3">Score</th>
            <th class="px-3 py-3">Strategy</th>
            <th class="px-3 py-3">Timeframe</th>
            <th class="px-3 py-3">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="signal in compactSignals" :key="signal.id" class="border-b border-white/6 last:border-b-0">
            <td class="px-3 py-3 font-semibold text-white">{{ signal.symbol }}</td>
            <td class="px-3 py-3">
              <StatusBadge :label="signal.directionLabel" :tone="signal.directionLabel === 'Bullish' ? 'success' : 'danger'" />
            </td>
            <td class="px-3 py-3"><StatusBadge :label="signal.hintLabel" tone="neutral" /></td>
            <td class="px-3 py-3 text-white/85">{{ signal.score }}</td>
            <td class="px-3 py-3 text-white/85">{{ signal.bot }}</td>
            <td class="px-3 py-3 text-white/70">{{ signal.timeframe }}</td>
            <td class="px-3 py-3"><StatusBadge :label="signal.statusLabel" tone="outline" /></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div v-else class="flex flex-col gap-5">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Operator surface</p>
        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-white">Persisted signals</h2>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-sc-muted">
          Status and strategy now use dropdown filters, while the review table expands to consume the available width.
        </p>
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3">
          <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Results</span>
          <strong class="mt-2 block text-lg font-semibold text-white">{{ resultLabel }}</strong>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3">
          <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">High priority</span>
          <strong class="mt-2 block text-lg font-semibold text-white">{{ highPriorityCount }}</strong>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3">
          <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Pending review</span>
          <strong class="mt-2 block text-lg font-semibold text-white">{{ pendingReviewCount }}</strong>
        </div>
      </div>
    </div>

    <section class="rounded-[24px] border border-white/10 bg-black/15 p-4">
      <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div class="grid flex-1 grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1.3fr)_220px_220px]">
          <label class="flex flex-col gap-2">
            <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Search</span>
            <div class="relative">
              <AppIcon name="Search" :size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/45" />
              <input
                v-model="search"
                type="search"
                placeholder="Search by symbol or strategy"
                class="min-h-11 w-full rounded-xl border border-white/10 bg-black/20 py-2.5 pl-10 pr-3 text-sm text-white outline-none transition placeholder:text-white/35 focus:border-sc-primary/45 focus:ring-2 focus:ring-sc-primary/30"
              />
            </div>
          </label>

          <BaseSelect v-model="selectedStatus" label="Status" :options="statusOptions" />
          <BaseSelect v-model="selectedStrategy" label="Strategy" :options="strategyOptions" />
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <div
            class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm"
            :class="hasActiveFilters ? 'border-sc-primary/30 bg-sc-primary-soft text-white' : 'border-white/10 bg-white/5 text-white/70'"
          >
            <AppIcon name="SlidersHorizontal" :size="15" />
            <span>{{ activeFilterCount }} active filters</span>
          </div>
          <button v-if="hasActiveFilters" class="rounded-full border border-white/10 bg-white/6 px-3 py-2 text-sm text-white/80" @click="clearFilters">Clear all</button>
          <button class="rounded-full border border-white/10 bg-white/6 px-3 py-2 text-sm text-white/80" @click="hydrateSignals">Refresh</button>
        </div>
      </div>

      <div class="mt-4 flex flex-col gap-3">
        <div class="flex flex-wrap items-center gap-2">
          <span class="min-w-28 text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Direction</span>
          <button
            v-for="direction in directionOptions"
            :key="direction"
            type="button"
            class="rounded-full border px-3 py-2 text-sm transition"
            :class="selectedDirections.includes(direction) ? 'border-sc-primary/30 bg-sc-primary-soft text-white' : 'border-white/10 bg-white/5 text-white/75 hover:border-white/20'"
            @click="selectedDirections = toggleMultiValue(selectedDirections, direction)"
          >
            {{ formatLabel(direction) }}
          </button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <span class="min-w-28 text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Timeframe</span>
          <button
            v-for="timeframe in timeframeOptions"
            :key="timeframe"
            type="button"
            class="rounded-full border px-3 py-2 text-sm transition"
            :class="selectedTimeframes.includes(timeframe) ? 'border-sc-primary/30 bg-sc-primary-soft text-white' : 'border-white/10 bg-white/5 text-white/75 hover:border-white/20'"
            @click="selectedTimeframes = toggleMultiValue(selectedTimeframes, timeframe)"
          >
            {{ timeframe }}
          </button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <span class="min-w-28 text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Priority</span>
          <button
            v-for="priority in priorityOptions"
            :key="priority"
            type="button"
            class="rounded-full border px-3 py-2 text-sm transition"
            :class="selectedPriorities.includes(priority) ? 'border-sc-primary/30 bg-sc-primary-soft text-white' : 'border-white/10 bg-white/5 text-white/75 hover:border-white/20'"
            @click="selectedPriorities = toggleMultiValue(selectedPriorities, priority)"
          >
            {{ formatLabel(priority) }}
          </button>
        </div>
      </div>
    </section>

    <div v-if="loadState === 'loading'" class="rounded-3xl border border-dashed border-white/12 bg-white/4 p-5">
      <p class="text-base font-semibold text-white">Loading persisted signals...</p>
      <p class="mt-2 text-sm text-sc-muted">Preparing the review queue and preserving the final table layout.</p>
      <div class="mt-4 flex flex-col gap-3">
        <div v-for="item in 6" :key="item" class="h-12 rounded-2xl bg-white/6" />
      </div>
    </div>

    <div v-else-if="loadState === 'error'" class="rounded-3xl border border-sc-danger/25 bg-sc-danger-soft p-5">
      <p class="text-base font-semibold text-white">Unable to load the signals feed</p>
      <p class="mt-2 text-sm text-white/75">{{ errorMessage }}</p>
      <button class="mt-4 rounded-full border border-white/10 bg-white/8 px-3 py-2 text-sm text-white/85" @click="hydrateSignals">Retry</button>
    </div>

    <div v-else-if="signals.length === 0" class="rounded-3xl border border-dashed border-white/12 bg-white/4 p-5">
      <p class="text-base font-semibold text-white">No persisted signals yet</p>
      <p class="mt-2 text-sm text-sc-muted">The scanner has not produced any saved signals for review.</p>
    </div>

    <div v-else-if="sortedSignals.length === 0" class="rounded-3xl border border-dashed border-white/12 bg-white/4 p-5">
      <p class="text-base font-semibold text-white">No signals match the current filters</p>
      <p class="mt-2 text-sm text-sc-muted">Adjust the filters or clear them to restore the review queue.</p>
      <button class="mt-4 rounded-full border border-white/10 bg-white/8 px-3 py-2 text-sm text-white/85" @click="clearFilters">Clear filters</button>
    </div>

    <div v-else :class="tableLayoutClass">
      <section class="overflow-hidden rounded-[24px] border border-white/10 bg-black/15">
        <div class="flex flex-col gap-3 border-b border-white/8 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Queue</p>
            <h3 class="mt-1 text-lg font-semibold text-white">Prioritized review list</h3>
          </div>
          <div class="inline-flex items-center gap-2 text-sm text-sc-muted">
            <AppIcon name="ArrowUpDown" :size="15" />
            <span>Click column labels to sort</span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full table-auto border-collapse">
            <thead>
              <tr class="border-b border-white/8 text-left text-[11px] uppercase tracking-[0.14em] text-sc-muted">
                <th class="px-4 py-3">
                  <button class="text-inherit" type="button" @click="setSort('symbol')">Symbol</button>
                </th>
                <th class="px-4 py-3">
                  <button class="text-inherit" type="button" @click="setSort('strategyLabel')">Strategy</button>
                </th>
                <th class="px-4 py-3">Direction</th>
                <th class="px-4 py-3">Execution</th>
                <th class="px-4 py-3">Timeframe</th>
                <th class="px-4 py-3">
                  <button class="text-inherit" type="button" @click="setSort('status')">Status</button>
                </th>
                <th class="px-4 py-3">
                  <button class="text-inherit" type="button" @click="setSort('score')">Score</button>
                </th>
                <th class="px-4 py-3">
                  <button class="text-inherit" type="button" @click="setSort('confidence')">Confidence</button>
                </th>
                <th class="px-4 py-3">
                  <button class="text-inherit" type="button" @click="setSort('reviewPriority')">Priority</button>
                </th>
                <th class="px-4 py-3">
                  <button class="text-inherit" type="button" @click="setSort('signalGeneratedAt')">Generated</button>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="signal in sortedSignals"
                :key="signal.id"
                class="cursor-pointer border-b border-white/6 transition hover:bg-white/4"
                :class="selectedSignalId === signal.id ? 'bg-sc-primary-soft/80' : ''"
                @click="openSignal(signal)"
              >
                <td class="px-4 py-3 align-top">
                  <div class="flex flex-col gap-1">
                    <strong class="text-sm font-semibold text-white">{{ signal.symbol }}</strong>
                    <small class="text-xs text-sc-muted">#{{ signal.id.slice(-3) }}</small>
                  </div>
                </td>
                <td class="px-4 py-3 align-top">
                  <div class="flex flex-col gap-1">
                    <strong class="text-sm font-medium text-white">{{ signal.strategyLabel }}</strong>
                    <small class="text-xs text-sc-muted">{{ signal.strategyKey }}</small>
                  </div>
                </td>
                <td class="px-4 py-3 align-top"><StatusBadge :label="formatLabel(signal.direction)" :tone="badgeToneForDirection(signal.direction)" /></td>
                <td class="px-4 py-3 align-top"><StatusBadge :label="formatLabel(signal.executionHint)" tone="neutral" /></td>
                <td class="px-4 py-3 align-top text-sm text-white/80">{{ signal.timeframe }}</td>
                <td class="px-4 py-3 align-top"><StatusBadge :label="formatLabel(signal.status)" :tone="badgeToneForStatus(signal.status)" /></td>
                <td class="px-4 py-3 align-top">
                  <span class="inline-flex min-w-16 items-center justify-center rounded-full border border-white/10 bg-white/6 px-3 py-1.5 text-sm text-white">{{ signal.score }}</span>
                </td>
                <td class="px-4 py-3 align-top">
                  <span class="inline-flex min-w-18 items-center justify-center rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-sm text-white/85">{{ signal.confidence }}%</span>
                </td>
                <td class="px-4 py-3 align-top"><StatusBadge :label="formatLabel(signal.reviewPriority)" :tone="badgeToneForPriority(signal.reviewPriority)" /></td>
                <td class="px-4 py-3 align-top">
                  <div class="flex flex-col gap-1">
                    <strong class="text-sm font-medium text-white">{{ formatDate(signal.signalGeneratedAt) }}</strong>
                    <small class="text-xs text-sc-muted">review {{ signal.reviewScore }}</small>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <aside v-if="selectedSignal" class="sticky top-6 flex h-fit flex-col gap-4 rounded-[24px] border border-white/10 bg-black/20 p-5">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Detail rail</p>
            <h3 class="mt-1 text-lg font-semibold text-white">{{ selectedSignal.symbol }} - {{ selectedSignal.strategyLabel }}</h3>
          </div>
          <button class="rounded-full border border-white/10 bg-white/6 px-3 py-2 text-sm text-white/80" @click="selectedSignalId = null">Close</button>
        </div>

        <div class="flex flex-wrap gap-2">
          <StatusBadge :label="formatLabel(selectedSignal.direction)" :tone="badgeToneForDirection(selectedSignal.direction)" />
          <StatusBadge :label="formatLabel(selectedSignal.executionHint)" tone="neutral" />
          <StatusBadge :label="formatLabel(selectedSignal.status)" :tone="badgeToneForStatus(selectedSignal.status)" />
          <StatusBadge :label="formatLabel(selectedSignal.reviewPriority)" :tone="badgeToneForPriority(selectedSignal.reviewPriority)" />
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 xl:grid-cols-1 2xl:grid-cols-3">
          <div class="rounded-2xl border border-sc-primary/25 bg-sc-primary-soft p-4">
            <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Score</span>
            <strong class="mt-2 block text-xl font-semibold text-white">{{ selectedSignal.score }}</strong>
          </div>
          <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
            <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Confidence</span>
            <strong class="mt-2 block text-xl font-semibold text-white">{{ selectedSignal.confidence }}%</strong>
          </div>
          <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
            <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Generated</span>
            <strong class="mt-2 block text-base font-semibold text-white">{{ formatDate(selectedSignal.signalGeneratedAt) }}</strong>
          </div>
        </div>

        <section class="flex flex-col gap-2">
          <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Thesis</p>
          <p class="text-sm leading-6 text-white/78">{{ selectedSignal.thesis }}</p>
        </section>

        <section class="flex flex-col gap-3">
          <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Levels</p>
          <div class="grid grid-cols-3 gap-3">
            <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
              <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Entry</span>
              <strong class="mt-2 block text-base font-semibold text-white">{{ formatPrice(selectedSignal.entryPrice) }}</strong>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
              <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Stop</span>
              <strong class="mt-2 block text-base font-semibold text-white">{{ formatPrice(selectedSignal.stopLoss) }}</strong>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
              <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Target</span>
              <strong class="mt-2 block text-base font-semibold text-white">{{ formatPrice(selectedSignal.targetPrice) }}</strong>
            </div>
          </div>
        </section>
      </aside>
    </div>
  </div>
</template>
