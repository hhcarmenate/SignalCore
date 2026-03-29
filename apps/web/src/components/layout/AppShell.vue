<script setup lang="ts">
import SidebarNavigation from '../navigation/SidebarNavigation.vue'
import TopBar from '../navigation/TopBar.vue'
</script>

<template>
  <div class="app-shell">
    <SidebarNavigation />

    <main class="main-panel">
      <TopBar />
      <RouterView />
    </main>
  </div>
</template>
