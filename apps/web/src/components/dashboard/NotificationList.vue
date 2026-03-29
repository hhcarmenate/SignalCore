<script setup lang="ts">
import { useAppStore } from '../../stores/app'
import SectionHeader from '../ui/SectionHeader.vue'

const appStore = useAppStore()
</script>

<template>
  <div>
    <SectionHeader eyebrow="Inbox" title="Notifications" />

    <ul class="notification-list">
      <li v-for="note in appStore.notifications" :key="note" class="notification-item">
        {{ note }}
      </li>
    </ul>
  </div>
</template>
