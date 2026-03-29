<script setup lang="ts">
import { useAppStore } from '../../stores/app'
import SectionHeader from '../ui/SectionHeader.vue'

const appStore = useAppStore()
</script>

<template>
  <div>
    <SectionHeader eyebrow="Watchlists" title="Scan snapshot" />

    <div class="snapshot-list">
      <div v-for="item in appStore.watchlistSnapshot" :key="item.label" class="meta-row">
        <span>{{ item.label }}</span>
        <strong>{{ item.value }}</strong>
      </div>
    </div>
  </div>
</template>
