<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import {
  useAppStore,
  type PersistedSignalRecord,
  type ReviewPriority,
  type SignalDirection,
  type SignalStatus,
} from '../../stores/app'
import SectionHeader from '../ui/SectionHeader.vue'
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
const selectedStrategies = ref<string[]>([])
const selectedStatuses = ref<SignalStatus[]>([])
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

const strategyOptions = computed(() => [...new Set(signals.value.map((signal) => signal.strategyLabel))])
const statusOptions = computed<SignalStatus[]>(() => [
  'new',
  'pending_review',
  'accepted',
  'rejected',
  'expired',
  'actioned',
  'ignored',
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

    const matchesStrategy =
      selectedStrategies.value.length === 0 || selectedStrategies.value.includes(signal.strategyLabel)
    const matchesStatus = selectedStatuses.value.length === 0 || selectedStatuses.value.includes(signal.status)
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

const resultLabel = computed(() => `${sortedSignals.value.length} signal${sortedSignals.value.length === 1 ? '' : 's'}`)
const hasActiveFilters = computed(
  () =>
    search.value.length > 0 ||
    selectedStrategies.value.length > 0 ||
    selectedStatuses.value.length > 0 ||
    selectedDirections.value.length > 0 ||
    selectedTimeframes.value.length > 0 ||
    selectedPriorities.value.length > 0,
)

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
  selectedStrategies.value = []
  selectedStatuses.value = []
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
    selectedStrategies.value =
      typeof query.strategy === 'string' && query.strategy.length > 0 ? query.strategy.split(',') : []
    selectedStatuses.value =
      typeof query.status === 'string' && query.status.length > 0 ? (query.status.split(',') as SignalStatus[]) : []
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
  [search, selectedStrategies, selectedStatuses, selectedDirections, selectedTimeframes, selectedPriorities, selectedSignalId],
  () => {
    if (props.mode !== 'full') {
      return
    }

    void router.replace({
      query: {
        ...route.query,
        search: search.value || undefined,
        strategy: selectedStrategies.value.length > 0 ? selectedStrategies.value.join(',') : undefined,
        status: selectedStatuses.value.length > 0 ? selectedStatuses.value.join(',') : undefined,
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
    <SectionHeader eyebrow="Priority Feed" title="Top signals" action-label="View all" />

    <div class="table-shell">
      <table>
        <thead>
          <tr>
            <th>Symbol</th>
            <th>Direction</th>
            <th>Hint</th>
            <th>Score</th>
            <th>Strategy</th>
            <th>Timeframe</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="signal in compactSignals" :key="signal.id">
            <td class="symbol-cell">{{ signal.symbol }}</td>
            <td>
              <StatusBadge
                :label="signal.directionLabel"
                :tone="signal.directionLabel === 'Bullish' ? 'success' : 'danger'"
              />
            </td>
            <td>
              <StatusBadge :label="signal.hintLabel" tone="neutral" />
            </td>
            <td>{{ signal.score }}</td>
            <td>{{ signal.bot }}</td>
            <td>{{ signal.timeframe }}</td>
            <td>
              <StatusBadge :label="signal.statusLabel" tone="outline" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div v-else class="signals-list-view">
    <div class="signals-page-header">
      <SectionHeader eyebrow="Signal Ops" title="Persisted signals" />

      <div class="signals-header-meta">
        <div class="meta-stat compact-stat">
          <span>Results</span>
          <strong>{{ resultLabel }}</strong>
        </div>

        <button class="ghost-button" type="button" @click="hydrateSignals">Refresh</button>
      </div>
    </div>

    <div class="signals-toolbar">
      <label class="signals-search-field">
        <span class="eyebrow">Search</span>
        <input v-model="search" type="search" placeholder="Search by symbol or strategy" />
      </label>

      <button v-if="hasActiveFilters" class="ghost-button" type="button" @click="clearFilters">
        Clear all filters
      </button>
    </div>

    <div class="signals-filter-groups">
      <div class="filter-group">
        <span class="filter-group-label">Status</span>
        <button
          v-for="status in statusOptions"
          :key="status"
          class="filter-chip"
          :class="{ active: selectedStatuses.includes(status) }"
          type="button"
          @click="selectedStatuses = toggleMultiValue(selectedStatuses, status)"
        >
          {{ formatLabel(status) }}
        </button>
      </div>

      <div class="filter-group">
        <span class="filter-group-label">Direction</span>
        <button
          v-for="direction in directionOptions"
          :key="direction"
          class="filter-chip"
          :class="{ active: selectedDirections.includes(direction) }"
          type="button"
          @click="selectedDirections = toggleMultiValue(selectedDirections, direction)"
        >
          {{ formatLabel(direction) }}
        </button>
      </div>

      <div class="filter-group">
        <span class="filter-group-label">Timeframe</span>
        <button
          v-for="timeframe in timeframeOptions"
          :key="timeframe"
          class="filter-chip"
          :class="{ active: selectedTimeframes.includes(timeframe) }"
          type="button"
          @click="selectedTimeframes = toggleMultiValue(selectedTimeframes, timeframe)"
        >
          {{ timeframe }}
        </button>
      </div>

      <div class="filter-group">
        <span class="filter-group-label">Strategy</span>
        <button
          v-for="strategy in strategyOptions"
          :key="strategy"
          class="filter-chip"
          :class="{ active: selectedStrategies.includes(strategy) }"
          type="button"
          @click="selectedStrategies = toggleMultiValue(selectedStrategies, strategy)"
        >
          {{ strategy }}
        </button>
      </div>

      <div class="filter-group">
        <span class="filter-group-label">Review priority</span>
        <button
          v-for="priority in priorityOptions"
          :key="priority"
          class="filter-chip"
          :class="{ active: selectedPriorities.includes(priority) }"
          type="button"
          @click="selectedPriorities = toggleMultiValue(selectedPriorities, priority)"
        >
          {{ formatLabel(priority) }}
        </button>
      </div>
    </div>

    <div v-if="loadState === 'loading'" class="empty-state-panel signals-state-panel">
      <p class="empty-state-title">Loading persisted signals...</p>
      <p class="empty-state-copy">Preparing the review queue and preserving the final table layout.</p>
      <div class="signals-skeleton-list top-gap">
        <div v-for="item in 5" :key="item" class="signals-skeleton-row" />
      </div>
    </div>

    <div v-else-if="loadState === 'error'" class="empty-state-panel signals-state-panel">
      <p class="empty-state-title">Unable to load the signals feed</p>
      <p class="empty-state-copy">{{ errorMessage }}</p>
      <button class="ghost-button top-gap" type="button" @click="hydrateSignals">Retry</button>
    </div>

    <div v-else-if="signals.length === 0" class="empty-state-panel signals-state-panel">
      <p class="empty-state-title">No persisted signals yet</p>
      <p class="empty-state-copy">The scanner has not produced any saved signals for review.</p>
    </div>

    <div v-else-if="sortedSignals.length === 0" class="empty-state-panel signals-state-panel">
      <p class="empty-state-title">No signals match the current filters</p>
      <p class="empty-state-copy">Adjust the filters or clear them to restore the review queue.</p>
      <button class="ghost-button top-gap" type="button" @click="clearFilters">Clear filters</button>
    </div>

    <div v-else class="signals-table-layout">
      <div class="table-shell signals-table-shell">
        <table>
          <thead>
            <tr>
              <th>
                <button class="table-sort-button" type="button" @click="setSort('symbol')">Symbol</button>
              </th>
              <th>
                <button class="table-sort-button" type="button" @click="setSort('strategyLabel')">
                  Strategy
                </button>
              </th>
              <th>Direction</th>
              <th>Hint</th>
              <th>Timeframe</th>
              <th>
                <button class="table-sort-button" type="button" @click="setSort('status')">Status</button>
              </th>
              <th>
                <button class="table-sort-button" type="button" @click="setSort('score')">Score</button>
              </th>
              <th>
                <button class="table-sort-button" type="button" @click="setSort('confidence')">
                  Confidence
                </button>
              </th>
              <th>
                <button class="table-sort-button" type="button" @click="setSort('reviewPriority')">
                  Review Priority
                </button>
              </th>
              <th>
                <button class="table-sort-button" type="button" @click="setSort('signalGeneratedAt')">
                  Generated
                </button>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="signal in sortedSignals"
              :key="signal.id"
              class="signals-data-row"
              :class="{ selected: selectedSignalId === signal.id }"
              @click="openSignal(signal)"
            >
              <td class="symbol-cell">{{ signal.symbol }}</td>
              <td>
                <div class="table-stack-cell">
                  <strong>{{ signal.strategyLabel }}</strong>
                  <small>{{ signal.strategyKey }}</small>
                </div>
              </td>
              <td>
                <StatusBadge :label="formatLabel(signal.direction)" :tone="badgeToneForDirection(signal.direction)" />
              </td>
              <td>
                <StatusBadge :label="formatLabel(signal.executionHint)" tone="neutral" />
              </td>
              <td>{{ signal.timeframe }}</td>
              <td>
                <StatusBadge :label="formatLabel(signal.status)" :tone="badgeToneForStatus(signal.status)" />
              </td>
              <td>{{ signal.score }}</td>
              <td>{{ signal.confidence }}</td>
              <td>
                <StatusBadge
                  :label="formatLabel(signal.reviewPriority)"
                  :tone="badgeToneForPriority(signal.reviewPriority)"
                />
              </td>
              <td>{{ formatDate(signal.signalGeneratedAt) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <aside v-if="selectedSignal" class="signal-detail-preview">
        <div class="signal-detail-preview-header">
          <div>
            <p class="eyebrow">Signal detail preview</p>
            <h3>{{ selectedSignal.symbol }} - {{ selectedSignal.strategyLabel }}</h3>
          </div>
          <button class="ghost-button" type="button" @click="selectedSignalId = null">Close</button>
        </div>

        <div class="signal-detail-badges">
          <StatusBadge
            :label="formatLabel(selectedSignal.direction)"
            :tone="badgeToneForDirection(selectedSignal.direction)"
          />
          <StatusBadge :label="formatLabel(selectedSignal.executionHint)" tone="neutral" />
          <StatusBadge
            :label="formatLabel(selectedSignal.status)"
            :tone="badgeToneForStatus(selectedSignal.status)"
          />
          <StatusBadge
            :label="formatLabel(selectedSignal.reviewPriority)"
            :tone="badgeToneForPriority(selectedSignal.reviewPriority)"
          />
        </div>

        <div class="signal-preview-metrics">
          <div class="summary-stat">
            <span>Score</span>
            <strong>{{ selectedSignal.score }}</strong>
          </div>
          <div class="summary-stat">
            <span>Confidence</span>
            <strong>{{ selectedSignal.confidence }}</strong>
          </div>
          <div class="summary-stat">
            <span>Generated</span>
            <strong>{{ formatDate(selectedSignal.signalGeneratedAt) }}</strong>
          </div>
        </div>

        <div class="signal-preview-section">
          <p class="eyebrow">Thesis</p>
          <p class="section-copy compact-copy">{{ selectedSignal.thesis }}</p>
        </div>

        <div class="signal-preview-section">
          <p class="eyebrow">Levels</p>
          <div class="signal-preview-levels">
            <div class="summary-stat">
              <span>Entry</span>
              <strong>{{ formatPrice(selectedSignal.entryPrice) }}</strong>
            </div>
            <div class="summary-stat">
              <span>Stop</span>
              <strong>{{ formatPrice(selectedSignal.stopLoss) }}</strong>
            </div>
            <div class="summary-stat">
              <span>Target</span>
              <strong>{{ formatPrice(selectedSignal.targetPrice) }}</strong>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>
