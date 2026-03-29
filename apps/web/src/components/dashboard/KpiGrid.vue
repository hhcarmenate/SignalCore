<script setup lang="ts">
import { useAppStore } from '../../stores/app'

const appStore = useAppStore()
</script>

<template>
  <section class="kpi-grid">
    <article
      v-for="item in appStore.dashboardKpis"
      :key="item.label"
      class="card metric-card"
      :class="{ 'warning-card': item.tone === 'warning' }"
    >
      <p class="metric-label">{{ item.label }}</p>
      <strong class="metric-value">{{ item.value }}</strong>
      <span class="metric-footnote">{{ item.footnote }}</span>
    </article>
  </section>
</template>
