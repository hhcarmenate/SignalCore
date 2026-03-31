<script setup lang="ts">
import { computed } from 'vue'

import AppIcon from '../components/ui/AppIcon.vue'
import BaseCard from '../components/ui/BaseCard.vue'
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
</script>

<template>
  <section class="signals-page-shell">
    <BaseCard class="signals-hero-card">
      <div class="signals-hero-copy">
        <p class="eyebrow">Dashboard and UI ? Task #99</p>
        <h1 class="signals-hero-title">Signals review queue</h1>
        <p class="signals-hero-subtitle">
          Production-style triage surface for persisted signals, with scanability first and operator friction kept low.
        </p>
      </div>

      <div class="signals-hero-actions">
        <div class="signals-hero-pill">
          <AppIcon name="DatabaseZap" :size="16" />
          <span>Persisted feed</span>
        </div>
        <div class="signals-hero-pill muted">
          <AppIcon name="SlidersHorizontal" :size="16" />
          <span>Ops-first filtering</span>
        </div>
      </div>
    </BaseCard>

    <section class="signals-summary-grid">
      <BaseCard
        v-for="item in signalSummary"
        :key="item.label"
        class="signals-summary-card"
        :class="[`tone-${item.tone}`]"
      >
        <div class="signals-summary-icon">
          <AppIcon :name="item.icon" :size="18" />
        </div>
        <div>
          <p class="signals-summary-label">{{ item.label }}</p>
          <strong class="signals-summary-value">{{ item.value }}</strong>
          <p class="signals-summary-footnote">{{ item.footnote }}</p>
        </div>
      </BaseCard>
    </section>

    <BaseCard class="signals-main-card">
      <SignalsTable mode="full" />
    </BaseCard>
  </section>
</template>
