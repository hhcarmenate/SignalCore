<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'

import AppIcon from '../components/ui/AppIcon.vue'
import StatusBadge from '../components/ui/StatusBadge.vue'
import { useAppStore, type ScannerRunStatus, type SignalStatus } from '../stores/app'

const router = useRouter()
const appStore = useAppStore()

const signals = computed(() => appStore.persistedSignals)
const scannerRuns = computed(() => appStore.scannerRuns)

const counts = computed(() => {
  const items = signals.value
  return {
    newSignals: items.filter((signal) => signal.status === 'new').length,
    pendingReview: items.filter((signal) => signal.status === 'pending_review').length,
    highPriority: items.filter((signal) => signal.reviewPriority === 'high').length,
    bullish: items.filter((signal) => signal.direction === 'bullish').length,
    bearish: items.filter((signal) => signal.direction === 'bearish').length,
  }
})

const signalsByStatus = computed(() => {
  const statuses: SignalStatus[] = ['new', 'pending_review', 'accepted', 'rejected', 'actioned', 'expired', 'ignored']
  return statuses.map((status) => ({
    status,
    count: signals.value.filter((signal) => signal.status === status).length,
  }))
})

const latestRun = computed(() =>
  [...scannerRuns.value].sort((left, right) => new Date(right.startedAt).getTime() - new Date(left.startedAt).getTime())[0] ?? null,
)

const recentRunHealth = computed(() => {
  const recent = [...scannerRuns.value]
    .sort((left, right) => new Date(right.startedAt).getTime() - new Date(left.startedAt).getTime())
    .slice(0, 3)

  return {
    warningOrFailedCount: recent.filter((run) => run.status !== 'completed').length,
    totalErrors: recent.reduce((sum, run) => sum + run.errorCount, 0),
  }
})

const topRankedSignals = computed(() =>
  [...signals.value]
    .sort((left, right) => {
      const leftRank = left.rankingScore ?? left.reviewScore
      const rightRank = right.rankingScore ?? right.reviewScore
      if (rightRank === leftRank) return right.reviewScore - left.reviewScore
      return rightRank - leftRank
    })
    .slice(0, 4),
)

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

function statusTone(status: ScannerRunStatus): 'success' | 'warning' | 'danger' {
  if (status === 'completed') return 'success'
  if (status === 'completed_with_errors') return 'warning'
  return 'danger'
}

function signalStatusTone(status: SignalStatus): 'success' | 'warning' | 'danger' | 'neutral' | 'outline' {
  if (status === 'pending_review') return 'warning'
  if (status === 'accepted' || status === 'actioned') return 'success'
  if (status === 'rejected') return 'danger'
  if (status === 'expired' || status === 'ignored') return 'neutral'
  return 'outline'
}

function priorityTone(priority: string): 'danger' | 'warning' | 'neutral' {
  if (priority === 'high') return 'danger'
  if (priority === 'medium') return 'warning'
  return 'neutral'
}

function openSignalsWith(query: Record<string, string>) {
  void router.push({ name: 'signals', query })
}

function openSignalDetail(id: string) {
  void router.push({ name: 'signal-detail', params: { id } })
}

function openBotsWith(query: Record<string, string>) {
  void router.push({ name: 'bots', query })
}
</script>

<template>
  <section class="flex flex-col gap-5">
    <section class="rounded-[28px] border border-white/10 bg-[radial-gradient(circle_at_top_right,rgba(96,165,250,0.18),transparent_28%),linear-gradient(180deg,rgba(15,23,42,0.96),rgba(17,24,39,0.88))] p-6 shadow-[0_20px_60px_rgba(0,0,0,0.28)]">
      <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div class="max-w-4xl">
          <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sc-muted">Dashboard and UI · Task #103</p>
          <h1 class="mt-2 text-4xl font-semibold tracking-tight text-white">Operational overview</h1>
          <p class="mt-3 max-w-3xl text-sm leading-6 text-sc-muted">
            Fast situational awareness for what needs review now, which opportunities rank highest, and whether scanner health is degrading.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 xl:justify-end">
          <button class="rounded-full border border-white/10 bg-white/6 px-3.5 py-2 text-sm text-white/85" @click="openSignalsWith({ status: 'pending_review' })">Open review queue</button>
          <button class="rounded-full border border-white/10 bg-white/6 px-3.5 py-2 text-sm text-white/85" @click="openBotsWith({ recency: '24h' })">Open run monitor</button>
        </div>
      </div>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-4">
      <button class="rounded-3xl border border-white/10 bg-white/5 p-5 text-left shadow-[0_20px_50px_rgba(0,0,0,0.22)]" @click="openSignalsWith({ status: 'new' })">
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">New signals</p>
        <strong class="mt-3 block text-4xl font-semibold tracking-tight text-white">{{ counts.newSignals }}</strong>
        <p class="mt-2 text-sm text-sc-muted">Fresh signals waiting to be triaged.</p>
      </button>

      <button class="rounded-3xl border border-sc-warning/20 bg-sc-warning-soft p-5 text-left shadow-[0_20px_50px_rgba(0,0,0,0.22)]" @click="openSignalsWith({ status: 'pending_review' })">
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Pending review</p>
        <strong class="mt-3 block text-4xl font-semibold tracking-tight text-white">{{ counts.pendingReview }}</strong>
        <p class="mt-2 text-sm text-white/75">Workflow pressure in the active queue.</p>
      </button>

      <button class="rounded-3xl border border-sc-danger/20 bg-sc-danger-soft p-5 text-left shadow-[0_20px_50px_rgba(0,0,0,0.22)]" @click="openSignalsWith({ priority: 'high' })">
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">High priority</p>
        <strong class="mt-3 block text-4xl font-semibold tracking-tight text-white">{{ counts.highPriority }}</strong>
        <p class="mt-2 text-sm text-white/75">Most actionable opportunities right now.</p>
      </button>

      <button class="rounded-3xl border border-white/10 bg-white/5 p-5 text-left shadow-[0_20px_50px_rgba(0,0,0,0.22)]" @click="openBotsWith({ recency: '24h' })">
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Latest run health</p>
        <strong class="mt-3 block text-2xl font-semibold tracking-tight text-white">{{ latestRun ? formatLabel(latestRun.status) : 'No runs' }}</strong>
        <p class="mt-2 text-sm text-sc-muted">
          {{ latestRun ? `${latestRun.signalsFoundCount} signals · ${latestRun.errorCount} errors` : 'Run tracking not available.' }}
        </p>
      </button>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.9fr)]">
      <article class="rounded-[28px] border border-white/10 bg-white/4 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Top-ranked opportunities</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-white">Best signals to inspect next</h2>
          </div>
          <button class="rounded-full border border-white/10 bg-white/6 px-3 py-2 text-sm text-white/80" @click="openSignalsWith({ sort: 'reviewScore', order: 'desc' })">Open signals</button>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-3">
          <button v-for="signal in topRankedSignals" :key="signal.id" class="rounded-3xl border border-white/10 bg-white/6 p-4 text-left transition hover:border-white/20 hover:bg-white/8" @click="openSignalDetail(signal.id)">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
              <div>
                <div class="flex flex-wrap items-center gap-2">
                  <h3 class="text-lg font-semibold text-white">{{ signal.symbol }}</h3>
                  <StatusBadge :label="formatLabel(signal.direction)" :tone="signal.direction === 'bullish' ? 'success' : 'danger'" />
                  <StatusBadge :label="signal.timeframe" tone="neutral" />
                  <StatusBadge :label="formatLabel(signal.reviewPriority)" :tone="priorityTone(signal.reviewPriority)" />
                </div>
                <p class="mt-2 text-sm text-sc-muted">{{ signal.strategyLabel }} · {{ signal.strategyKey }}</p>
                <p class="mt-3 line-clamp-2 text-sm leading-6 text-white/78">{{ signal.thesis }}</p>
              </div>

              <div class="grid grid-cols-2 gap-3 lg:min-w-[180px]">
                <div class="rounded-2xl border border-white/10 bg-black/20 px-3 py-3">
                  <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Review</span>
                  <strong class="mt-1 block text-xl font-semibold text-white">{{ signal.reviewScore }}</strong>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 px-3 py-3">
                  <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Rank</span>
                  <strong class="mt-1 block text-xl font-semibold text-white">{{ signal.rankingScore ?? '--' }}</strong>
                </div>
              </div>
            </div>
          </button>
        </div>
      </article>

      <div class="flex flex-col gap-5">
        <article class="rounded-[28px] border border-white/10 bg-white/4 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Signals by direction</p>
              <h2 class="mt-1 text-xl font-semibold text-white">Market orientation</h2>
            </div>
          </div>

          <div class="mt-5 grid grid-cols-2 gap-4">
            <button class="rounded-3xl border border-sc-success/20 bg-sc-success-soft p-5 text-left" @click="openSignalsWith({ direction: 'bullish' })">
              <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Bullish</span>
              <strong class="mt-2 block text-3xl font-semibold text-white">{{ counts.bullish }}</strong>
            </button>
            <button class="rounded-3xl border border-sc-danger/20 bg-sc-danger-soft p-5 text-left" @click="openSignalsWith({ direction: 'bearish' })">
              <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Bearish</span>
              <strong class="mt-2 block text-3xl font-semibold text-white">{{ counts.bearish }}</strong>
            </button>
          </div>
        </article>

        <article class="rounded-[28px] border border-white/10 bg-white/4 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Recent scanner runs</p>
              <h2 class="mt-1 text-xl font-semibold text-white">Health snapshot</h2>
            </div>
            <button class="rounded-full border border-white/10 bg-white/6 px-3 py-2 text-sm text-white/80" @click="openBotsWith({ recency: '24h' })">Open runs</button>
          </div>

          <div class="mt-5 space-y-3">
            <div v-if="latestRun" class="rounded-3xl border border-white/10 bg-white/6 p-4">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-sm font-semibold text-white">Latest run</p>
                  <p class="mt-1 text-xs text-sc-muted">{{ latestRun.runReference }} · {{ latestRun.watchlist }}</p>
                </div>
                <StatusBadge :label="formatLabel(latestRun.status)" :tone="statusTone(latestRun.status)" />
              </div>
              <div class="mt-4 grid grid-cols-3 gap-3">
                <div class="rounded-2xl border border-white/10 bg-black/20 px-3 py-3">
                  <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Signals</span>
                  <strong class="mt-1 block text-lg font-semibold text-white">{{ latestRun.signalsFoundCount }}</strong>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 px-3 py-3">
                  <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Errors</span>
                  <strong class="mt-1 block text-lg font-semibold text-white">{{ latestRun.errorCount }}</strong>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/20 px-3 py-3">
                  <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Started</span>
                  <strong class="mt-1 block text-sm font-semibold text-white">{{ formatDate(latestRun.startedAt) }}</strong>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <button class="rounded-2xl border border-sc-warning/20 bg-sc-warning-soft p-4 text-left" @click="openBotsWith({ status: 'completed_with_errors,failed', recency: '24h' })">
                <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Warnings / failed</span>
                <strong class="mt-2 block text-2xl font-semibold text-white">{{ recentRunHealth.warningOrFailedCount }}</strong>
              </button>
              <button class="rounded-2xl border border-white/10 bg-white/6 p-4 text-left" @click="openBotsWith({ recency: '24h' })">
                <span class="text-xs uppercase tracking-[0.14em] text-sc-muted">Recent errors</span>
                <strong class="mt-2 block text-2xl font-semibold text-white">{{ recentRunHealth.totalErrors }}</strong>
              </button>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">
      <article class="rounded-[28px] border border-white/10 bg-white/4 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Signals by status</p>
            <h2 class="mt-1 text-xl font-semibold text-white">Workflow distribution</h2>
          </div>
          <button class="rounded-full border border-white/10 bg-white/6 px-3 py-2 text-sm text-white/80" @click="openSignalsWith({})">Open all</button>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
          <button v-for="item in signalsByStatus" :key="item.status" class="rounded-3xl border border-white/10 bg-white/6 p-4 text-left transition hover:border-white/20" @click="openSignalsWith({ status: item.status })">
            <div class="flex items-center justify-between gap-3">
              <StatusBadge :label="formatLabel(item.status)" :tone="signalStatusTone(item.status)" />
              <strong class="text-2xl font-semibold text-white">{{ item.count }}</strong>
            </div>
          </button>
        </div>
      </article>

      <article class="rounded-[28px] border border-white/10 bg-white/4 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sc-muted">Drill-down shortcuts</p>
            <h2 class="mt-1 text-xl font-semibold text-white">Common next actions</h2>
          </div>
        </div>

        <div class="mt-5 space-y-3">
          <button class="flex w-full items-center justify-between gap-3 rounded-3xl border border-white/10 bg-white/6 px-4 py-4 text-left transition hover:border-white/20" @click="openSignalsWith({ status: 'new' })">
            <div>
              <p class="text-sm font-semibold text-white">Review fresh signals</p>
              <p class="mt-1 text-xs text-sc-muted">Go straight to newly created opportunities.</p>
            </div>
            <AppIcon name="ArrowRight" :size="16" class="text-white/60" />
          </button>
          <button class="flex w-full items-center justify-between gap-3 rounded-3xl border border-white/10 bg-white/6 px-4 py-4 text-left transition hover:border-white/20" @click="openSignalsWith({ priority: 'high', sort: 'reviewScore', order: 'desc' })">
            <div>
              <p class="text-sm font-semibold text-white">Inspect highest-priority queue</p>
              <p class="mt-1 text-xs text-sc-muted">High-priority signals ranked for fast review.</p>
            </div>
            <AppIcon name="ArrowRight" :size="16" class="text-white/60" />
          </button>
          <button class="flex w-full items-center justify-between gap-3 rounded-3xl border border-white/10 bg-white/6 px-4 py-4 text-left transition hover:border-white/20" @click="openBotsWith({ status: 'completed_with_errors,failed', recency: '24h' })">
            <div>
              <p class="text-sm font-semibold text-white">Investigate scanner issues</p>
              <p class="mt-1 text-xs text-sc-muted">Jump to degraded runs from the last 24 hours.</p>
            </div>
            <AppIcon name="ArrowRight" :size="16" class="text-white/60" />
          </button>
        </div>
      </article>
    </section>
  </section>
</template>
