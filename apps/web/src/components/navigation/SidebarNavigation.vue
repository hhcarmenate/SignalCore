<script setup lang="ts">
import { useRoute } from 'vue-router'

import { useAppStore } from '../../stores/app'

const route = useRoute()
const appStore = useAppStore()
</script>

<template>
  <aside class="sidebar">
    <div class="brand-block">
      <div class="brand-mark">SC</div>
      <div>
        <p class="eyebrow">SignalCore</p>
        <h1 class="brand-title">Operator Console</h1>
      </div>
    </div>

    <nav class="sidebar-nav">
      <RouterLink
        v-for="item in appStore.navigation"
        :key="item.to"
        :to="item.to"
        class="nav-item"
        :class="{ active: route.path === item.to }"
      >
        {{ item.label }}
      </RouterLink>
    </nav>

    <div class="sidebar-footer card subtle-card">
      <p class="eyebrow">Environment</p>
      <div class="meta-row">
        <span>Mode</span>
        <strong>{{ appStore.environment.mode }}</strong>
      </div>
      <div class="meta-row">
        <span>Provider</span>
        <strong>{{ appStore.environment.provider }}</strong>
      </div>
      <div class="meta-row">
        <span>Market</span>
        <strong>{{ appStore.environment.market }}</strong>
      </div>
    </div>
  </aside>
</template>
