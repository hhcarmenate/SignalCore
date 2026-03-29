<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

import { useAppStore } from '../../stores/app'

const route = useRoute()
const appStore = useAppStore()

const pageTitle = computed(() => {
  const currentPage = appStore.navigation.find((item) => item.to === route.path)

  return currentPage?.label ?? 'Dashboard'
})
</script>

<template>
  <header class="topbar card">
    <div>
      <p class="eyebrow">{{ pageTitle }}</p>
      <h2 class="page-title">SignalCore overview</h2>
    </div>

    <div class="topbar-actions">
      <div class="search-shell">Search signals, bots, symbols…</div>
      <div class="status-pill success">{{ appStore.environment.marketStatus }}</div>
      <div class="status-pill neutral">{{ appStore.environment.unreadCount }} unread</div>
    </div>
  </header>
</template>
