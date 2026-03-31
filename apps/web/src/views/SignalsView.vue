<script setup lang="ts">
import { computed } from 'vue'

import AppIcon from '../components/ui/AppIcon.vue'
import SignalsTable from '../components/dashboard/SignalsTable.vue'
import { useAppStore } from '../stores/app'

const appStore = useAppStore()

const signalSummary = computed(() => {
  const signals = appStore.persistedSignals
  const highPriorityCount = signals.filter((signal) => signal.reviewPriority === 'high').length
  const pendingReviewCount = signals.filter((signal) => signal.status === 'pending_review').length
  const avgConfidence = Math.round(
    signals.reduce((total, signal) => total + signal.confidence, 0) / Math.max(signals.length, 1),
  )
  const bullishShare = Math.round(
    (signals.filter((signal) => signal.direction === 'bullish').length / Math.max(signals.length, 1)) * 100,
  )

  return [
    {
      label: 'High priority queue',
      value: String(highPriorityCount),
      footnote: 'Needs immediate review',
      icon: 'ShieldAlert',
      tone: 'danger',
    },
    {
      label: 'Pending review',
      value: String(pendingReviewCount),
      footnote: 'Actively waiting for triage',
      icon: 'Clock3',
      tone: 'warning',
    },
    {
      label: 'Average confidence',
      value: `${avgConfidence}%`,
      footnote: 'Across persisted signals',
      icon: 'Gauge',
      tone: 'default',
    },
    {
      label: 'Bullish share',
      value: `${bullishShare}%`,
      footnote: 'Directional mix in queue',
      icon: 'TrendingUp',
      tone: 'success',
    },
  ]
})

function toneClass(tone: string) {
  if (tone === 'danger') return 'border-sc-danger/20 bg-sc-danger-soft text-sc-danger'
  if (tone === 'warning') return 'border-sc-warning/20 bg-sc-warning-soft text-sc-warning'
  if (tone === 'success') return 'border-sc-success/20 bg-sc-success-soft text-sc-success'
  return 'border-white/10 bg-white/8 text-white/80'
}
</script>

<template>
  <section class="flex flex-col gap-5">
    <section
      class="overflow-hidden rounded-[28px] border border-white/10 bg-[radial-gradient(circle_at_top_right,rgba(96,165,250,0.18),transparent_28%),linear-gradient(180deg,rgba(15,23,42,0.96),rgba(17,24,39,0.88))] p-6 shadow-[0_20px_60px_rgba(0,0,0,0.28)]"
    >
      <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div class="max-w-4xl">
          <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sc-muted">
            Dashboard and UI ? Task #99
          </p>
          <h1 class="mt-2 text-4xl font-semibold tracking-tight text-white">Signals review queue</h1>
          <p class="mt-3 max-w-3xl text-sm leading-6 text-sc-muted">
            Production-ready triage surface for persisted signals, with clearer operator focus, lower filter friction,
            and a denser table that actually earns the horizontal space.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 xl:justify-end">
          <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/6 px-3.5 py-2 text-sm text-white/85">
            <AppIcon name="DatabaseZap" :size="16" />
            <span>Persisted feed</span>
          </div>
          <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/4 px-3.5 py-2 text-sm text-white/70">
            <AppIcon name="PanelTopClose" :size="16" />
            <span>Dropdown filters</span>
          </div>
          <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/4 px-3.5 py-2 text-sm text-white/70">
            <AppIcon name="StretchHorizontal" :size="16" />
            <span>Full-width table</span>
          </div>
        </div>
      </div>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-4">
      <article
        v-for="item in signalSummary"
        :key="item.label"
        class="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-[0_20px_50px_rgba(0,0,0,0.22)]"
      >
        <div class="flex items-start gap-3">
          <div class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border" :class="toneClass(item.tone)">
            <AppIcon :name="item.icon" :size="18" />
          </div>
          <div>
            <p class="text-sm text-sc-muted">{{ item.label }}</p>
            <strong class="mt-1 block text-3xl font-semibold tracking-tight text-white">{{ item.value }}</strong>
            <p class="mt-1 text-sm text-sc-muted">{{ item.footnote }}</p>
          </div>
        </div>
      </article>
    </section>

    <section class="rounded-[28px] border border-white/10 bg-white/4 p-5 shadow-[0_20px_50px_rgba(0,0,0,0.22)]">
      <SignalsTable mode="full" />
    </section>
  </section>
</template>
