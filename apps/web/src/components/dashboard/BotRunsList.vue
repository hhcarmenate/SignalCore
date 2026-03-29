<script setup lang="ts">
import { useAppStore } from '../../stores/app'
import SectionHeader from '../ui/SectionHeader.vue'
import StatusBadge from '../ui/StatusBadge.vue'

const appStore = useAppStore()
</script>

<template>
  <div>
    <SectionHeader eyebrow="Runtime Health" title="Recent bot runs" action-label="Open bots" />

    <div class="run-list">
      <div v-for="run in appStore.botRuns" :key="`${run.name}-${run.timeframe}`" class="run-item">
        <div>
          <h4>{{ run.name }}</h4>
          <p>{{ run.timeframe }} · {{ run.scanned }} symbols scanned</p>
        </div>

        <div class="run-meta">
          <StatusBadge :label="run.status" :tone="run.status === 'Success' ? 'success' : 'warning'" />
          <span>{{ run.created }} signals</span>
          <span>{{ run.duration }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
