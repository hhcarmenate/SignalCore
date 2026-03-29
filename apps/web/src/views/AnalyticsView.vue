<script setup lang="ts">
import BaseCard from '../components/ui/BaseCard.vue'
import KpiGrid from '../components/dashboard/KpiGrid.vue'
</script>

<template>
  <KpiGrid />

  <section class="content-grid single-column-grid">
    <BaseCard>
      <div>
        <p class="eyebrow">Analytics</p>
        <h3>Performance workspace</h3>
        <p class="placeholder-copy">
          Reserved for outcome tracking, signal quality scoring, and bot performance analysis.
        </p>
      </div>
    </BaseCard>
  </section>
</template>
