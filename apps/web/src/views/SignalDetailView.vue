<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import StatusBadge from '../components/ui/StatusBadge.vue'
import AppIcon from '../components/ui/AppIcon.vue'
import {
  useAppStore,
  type PersistedSignalRecord,
  type ReviewPriority,
  type SignalActionItem,
  type SignalStatus,
} from '../stores/app'

const route = useRoute()
const router = useRouter()
const appStore = useAppStore()

const loadState = ref<'loading' | 'ready' | 'error' | 'not-found'>('loading')
const errorMessage = ref('')
const signal = ref<PersistedSignalRecord | null>(null)

const signalId = computed(() => String(route.params.id ?? ''))

function formatLabel(value: string): string {
  return value.replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
}

function formatDate(value?: string): string {
  if (!value) return '--'

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

function badgeToneForDirection(direction: PersistedSignalRecord['direction']): 'success' | 'danger' {
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

function actionClass(action: SignalActionItem): string {
  if (!action.enabled) return 'border-white/10 bg-white/4 text-white/35 cursor-not-allowed'
  if (action.tone === 'success') return 'border-sc-success/25 bg-sc-success-soft text-white hover:border-sc-success/45'
  if (action.tone === 'danger') return 'border-sc-danger/25 bg-sc-danger-soft text-white hover:border-sc-danger/45'
  if (action.tone === 'primary') return 'border-sc-primary/25 bg-sc-primary-soft text-white hover:border-sc-primary/45'
  return 'border-white/10 bg-white/6 text-white/85 hover:border-white/20'
}

async function hydrateSignal() {
  loadState.value = 'loading'
  errorMessage.value = ''
  signal.value = null

  try {
    await new Promise((resolve) => setTimeout(resolve, 160))

    if (route.query.simulateSignalError === '1') {
      throw new Error('Signal detail failed to load. Retry once the signal store is reachable.')
    }

    const record = appStore.findSignalById(signalId.value)

    if (!record) {
      loadState.value = 'not-found'
      return
    }

    signal.value = record
    loadState.value = 'ready'
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Signal detail failed to load.'
    loadState.value = 'error'
  }
}

watch(() => signalId.value, () => {
  void hydrateSignal()
})

onMounted(() => {
  void hydrateSignal()
})
</script>

<template>
  <section class="flex flex-col gap-5">
    <section class="rounded-[28px] border border-white/10 bg-[radial-gradient(circle_at_top_right,rgba(96,165,250,0.18),transparent_28%),linear-gradient(180deg,rgba(15,23,42,0.96),rgba(17,24,39,0.88))] p-6 shadow-[0_20px_60px_rgba(0,0,0,0.28)]">
      <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
        <div class="max-w-4xl">
          <div class="flex flex-wrap items-center gap-3 text-sm text-sc-muted">
            <button class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/6 px-3 py-2 text-white/80" @click="router.push({ name: 'signals' })">
              <AppIcon name="ArrowLeft" :size="15" />
              <span>Back to signals</span>
            </button>
            <span class="text-[11px] font-semibold uppercase tracking-[0.16em]">Dashboard and UI · Task #100</span>
          </div>
          <template v-if="signal">
            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-white">{{ signal.symbol }} signal detail</h1>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-sc-muted">
              Investigation surface for one persisted signal, with explainability, lifecycle clarity, and source context.
            </p>
          </template>
          <template v-else>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-white">Signal detail</h1>
          </template>
        </div>

        <div v-if="signal" class="flex flex-wrap items-center gap-2 xl:justify-end">
          <StatusBadge :label="formatLabel(signal.direction)" :tone="badgeToneForDirection(signal.direction)" />
          <StatusBadge :label="formatLabel(signal.executionHint)" tone="neutral" />
          <StatusBadge :label="formatLabel(signal.status)" :tone="badgeToneForStatus(signal.status)" />
          <StatusBadge :label="formatLabel(signal.reviewPriority)" :tone="badgeToneForPriority(signal.reviewPriority)" />
        </div>
      </div>
    </section>

    <div v-if="loadState === 'loading'" class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1.75fr)_380px]">
      <section class="flex flex-col gap-5">
        <div class="h-44 rounded-[28px] border border-white/10 bg-white/5" />
        <div class="h-32 rounded-[28px] border border-white/10 bg-white/5" />
        <div class="h-56 rounded-[28px] border border-white/10 bg-white/5" />
        <div class="h-72 rounded-[28px] border border-white/10 bg-white/5" />
      </section>
      <aside class="flex flex-col gap-5">
        <div class="h-56 rounded-[28px] border border-white/10 bg-white/5" />
        <div class="h-64 rounded-[28px] border border-white/10 bg-white/5" />
      </aside>
    </div>

    <div v-else-if="loadState === 'error'" class="rounded-[28px] border border-sc-danger/25 bg-sc-danger-soft p-6">
      <p class="text-base font-semibold text-white">Unable to load the signal detail</p>
      <p class="mt-2 text-sm text-white/75">{{ errorMessage }}</p>
      <div class="mt-4 flex gap-3">
        <button class="rounded-full border border-white/10 bg-white/8 px-4 py-2 text-sm text-white/85" @click="hydrateSignal">Retry</button>
        <button class="rounded-full border border-white/10 bg-white/6 px-4 py-2 text-sm text-white/75" @click="router.push({ name: 'signals' })">Back to signals</button>
      </div>
    </div>

    <div v-else-if="loadState === 'not-found'" class="rounded-[28px] border border-dashed border-white/12 bg-white/4 p-6">
      <p class="text-base font-semibold text-white">Signal not found</p>
      <p class="mt-2 text-sm text-sc-muted">The requested persisted signal does not exist in the current detail feed.</p>
      <button class="mt-4 rounded-full border border-white/10 bg-white/8 px-4 py-2 text-sm text-white/85" @click="router.push({ name: 'signals' })">Return to signals list</button>
    </div>

    <div v-else-if="signal" class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1.75fr)_380px]">
      <section class="flex flex-col gap-5">
        <article class="rounded-[28px] border border-white/10 bg-white/4 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
          <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sc-muted">Signal summary</p>
              <h2 class="mt-2 text-3xl font-semibold tracking-tight text-white">{{ signal.symbol }} · {{ signal.strategyLabel }}</h2>
              <p class="mt-2 text-sm text-sc-muted">{{ signal.strategyKey }} · {{ signal.timeframe }} · generated {{ formatDate(signal.signalGeneratedAt) }}</p>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
              <div class="rounded-2xl border border-sc-primary/25 bg-sc-primary-soft p-4">
                <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Score</span>
                <strong class="mt-2 block text-2xl font-semibold text-white">{{ signal.score }}</strong>
              </div>
              <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
                <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Confidence</span>
                <strong class="mt-2 block text-2xl font-semibold text-white">{{ signal.confidence }}%</strong>
              </div>
              <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
                <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Rank score</span>
                <strong class="mt-2 block text-2xl font-semibold text-white">{{ signal.rankingScore ?? '--' }}</strong>
              </div>
              <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
                <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Rank position</span>
                <strong class="mt-2 block text-2xl font-semibold text-white">{{ signal.rankingPosition ?? '--' }}</strong>
              </div>
            </div>
          </div>

          <div class="mt-5 flex flex-wrap gap-2">
            <StatusBadge :label="formatLabel(signal.direction)" :tone="badgeToneForDirection(signal.direction)" />
            <StatusBadge :label="formatLabel(signal.executionHint)" tone="neutral" />
            <StatusBadge :label="formatLabel(signal.status)" :tone="badgeToneForStatus(signal.status)" />
            <StatusBadge :label="formatLabel(signal.reviewPriority)" :tone="badgeToneForPriority(signal.reviewPriority)" />
          </div>
        </article>

        <article class="rounded-[28px] border border-white/10 bg-white/4 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
          <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sc-muted">Thesis</p>
          <p class="mt-3 text-base leading-7 text-white/82">{{ signal.thesis }}</p>
        </article>

        <article class="rounded-[28px] border border-white/10 bg-white/4 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sc-muted">Levels</p>
              <h3 class="mt-1 text-xl font-semibold text-white">Actionable trade structure</h3>
            </div>
          </div>
          <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-white/6 p-5">
              <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Entry</span>
              <strong class="mt-2 block text-2xl font-semibold text-white">{{ formatPrice(signal.entryPrice) }}</strong>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/6 p-5">
              <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Stop loss</span>
              <strong class="mt-2 block text-2xl font-semibold text-white">{{ formatPrice(signal.stopLoss) }}</strong>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/6 p-5">
              <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Target</span>
              <strong class="mt-2 block text-2xl font-semibold text-white">{{ formatPrice(signal.targetPrice) }}</strong>
            </div>
          </div>
        </article>

        <article class="rounded-[28px] border border-white/10 bg-white/4 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
          <div class="flex flex-col gap-2">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sc-muted">Signal quality</p>
            <h3 class="text-xl font-semibold text-white">Score breakdown</h3>
          </div>
          <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div v-for="item in signal.scoreBreakdown" :key="item.label" class="rounded-2xl border border-white/10 bg-white/6 p-4">
              <div class="flex items-center justify-between gap-3">
                <span class="text-sm text-white/80">{{ item.label }}</span>
                <strong class="text-lg font-semibold text-white">{{ item.value }}</strong>
              </div>
            </div>
          </div>
        </article>

        <article class="rounded-[28px] border border-white/10 bg-white/4 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
          <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <section>
              <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sc-muted">Technical context</p>
              <div class="mt-4 space-y-3">
                <div v-for="item in signal.indicatorSnapshot" :key="item.label" class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3">
                  <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-sc-muted">{{ item.label }}</span>
                    <strong class="text-sm font-semibold text-white">{{ item.value }}</strong>
                  </div>
                </div>
              </div>
            </section>

            <section>
              <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sc-muted">Market context</p>
              <div class="mt-4 space-y-3">
                <div v-for="item in signal.marketContext" :key="item.label" class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3">
                  <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-sc-muted">{{ item.label }}</span>
                    <strong class="text-sm font-semibold text-white">{{ item.value }}</strong>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </article>

        <article class="rounded-[28px] border border-white/10 bg-white/4 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
          <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <section>
              <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sc-muted">Review</p>
              <h3 class="mt-1 text-xl font-semibold text-white">Review summary and notes</h3>
              <p class="mt-4 text-sm leading-6 text-white/80">{{ signal.reviewSummary ?? 'No review summary yet.' }}</p>
              <div class="mt-4 rounded-2xl border border-white/10 bg-white/6 p-4">
                <p class="text-xs uppercase tracking-[0.14em] text-sc-muted">Decision context</p>
                <p class="mt-2 text-sm leading-6 text-white/78">{{ signal.reviewDecisionContext ?? 'No review decision context recorded yet.' }}</p>
              </div>
              <div class="mt-4 space-y-3">
                <article v-for="note in signal.reviewNotes" :key="note.id" class="rounded-2xl border border-white/10 bg-white/6 p-4">
                  <div class="flex items-center justify-between gap-3">
                    <strong class="text-sm text-white">{{ note.author }}</strong>
                    <span class="text-xs text-sc-muted">{{ formatDate(note.createdAt) }}</span>
                  </div>
                  <p class="mt-2 text-sm leading-6 text-white/78">{{ note.body }}</p>
                </article>
              </div>
            </section>

            <section>
              <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sc-muted">Lifecycle</p>
              <h3 class="mt-1 text-xl font-semibold text-white">Status and timing</h3>
              <div class="mt-4 space-y-3">
                <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3"><div class="flex items-center justify-between gap-3"><span class="text-sm text-sc-muted">Current status</span><strong class="text-sm font-semibold text-white">{{ formatLabel(signal.status) }}</strong></div></div>
                <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3"><div class="flex items-center justify-between gap-3"><span class="text-sm text-sc-muted">Status reason</span><strong class="text-sm font-semibold text-white">{{ signal.statusReason ?? '--' }}</strong></div></div>
                <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3"><div class="flex items-center justify-between gap-3"><span class="text-sm text-sc-muted">Queued for review</span><strong class="text-sm font-semibold text-white">{{ formatDate(signal.queuedForReviewAt) }}</strong></div></div>
                <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3"><div class="flex items-center justify-between gap-3"><span class="text-sm text-sc-muted">Reviewed at</span><strong class="text-sm font-semibold text-white">{{ formatDate(signal.reviewedAt) }}</strong></div></div>
                <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3"><div class="flex items-center justify-between gap-3"><span class="text-sm text-sc-muted">Actioned at</span><strong class="text-sm font-semibold text-white">{{ formatDate(signal.actionedAt) }}</strong></div></div>
                <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3"><div class="flex items-center justify-between gap-3"><span class="text-sm text-sc-muted">Invalidated at</span><strong class="text-sm font-semibold text-white">{{ formatDate(signal.invalidatedAt) }}</strong></div></div>
                <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3"><div class="flex items-center justify-between gap-3"><span class="text-sm text-sc-muted">Expires at</span><strong class="text-sm font-semibold text-white">{{ formatDate(signal.expiresAt) }}</strong></div></div>
              </div>
            </section>
          </div>
        </article>

        <article class="rounded-[28px] border border-white/10 bg-white/4 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
          <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sc-muted">Audit trail</p>
          <h3 class="mt-1 text-xl font-semibold text-white">Activity history</h3>
          <div class="mt-5 space-y-3">
            <article v-for="event in signal.auditTrail" :key="event.id" class="rounded-2xl border border-white/10 bg-white/6 p-4">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                  <p class="text-sm font-semibold text-white">{{ event.actionLabel }}</p>
                  <p class="mt-1 text-xs uppercase tracking-[0.14em] text-sc-muted">{{ formatLabel(event.eventType) }}</p>
                </div>
                <span class="text-xs text-sc-muted">{{ formatDate(event.occurredAt) }}</span>
              </div>
              <div class="mt-3 flex flex-wrap items-center gap-2">
                <StatusBadge v-if="event.fromStatus" :label="formatLabel(event.fromStatus)" tone="outline" />
                <AppIcon v-if="event.fromStatus" name="ArrowRight" :size="14" class="text-white/45" />
                <StatusBadge :label="formatLabel(event.toStatus)" :tone="badgeToneForStatus(event.toStatus)" />
              </div>
              <p class="mt-3 text-sm leading-6 text-white/78">{{ event.reason }}</p>
            </article>
          </div>
        </article>
      </section>

      <aside class="flex flex-col gap-5">
        <article class="rounded-[28px] border border-white/10 bg-white/4 p-5 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
          <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sc-muted">Actions</p>
          <div class="mt-4 space-y-3">
            <button v-for="action in signal.actions" :key="action.key" class="w-full rounded-2xl border px-4 py-3 text-left text-sm transition" :class="actionClass(action)" :disabled="!action.enabled">
              <span class="block font-semibold">{{ action.label }}</span>
              <span class="mt-1 block text-xs opacity-80">{{ action.hint }}</span>
            </button>
          </div>
        </article>

        <article class="rounded-[28px] border border-white/10 bg-white/4 p-5 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
          <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sc-muted">Source context</p>
          <div class="mt-4 space-y-3">
            <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3"><div class="flex items-center justify-between gap-3"><span class="text-sm text-sc-muted">Source run</span><strong class="text-sm font-semibold text-white">{{ signal.sourceRunReference ?? '--' }}</strong></div></div>
            <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3"><div class="flex items-center justify-between gap-3"><span class="text-sm text-sc-muted">Source signal</span><strong class="text-sm font-semibold text-white">{{ signal.sourceSignalReference ?? '--' }}</strong></div></div>
            <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3"><div class="flex items-center justify-between gap-3"><span class="text-sm text-sc-muted">Strategy key</span><strong class="text-sm font-semibold text-white">{{ signal.strategyKey }}</strong></div></div>
            <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3"><div class="flex items-center justify-between gap-3"><span class="text-sm text-sc-muted">Watchlist</span><strong class="text-sm font-semibold text-white">{{ signal.watchlistLabel ?? '--' }}</strong></div></div>
          </div>
        </article>
      </aside>
    </div>
  </section>
</template>
