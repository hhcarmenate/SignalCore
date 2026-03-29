<script setup lang="ts">
import BotRunsList from '../components/dashboard/BotRunsList.vue'
import KpiGrid from '../components/dashboard/KpiGrid.vue'
import NotificationList from '../components/dashboard/NotificationList.vue'
import SignalsTable from '../components/dashboard/SignalsTable.vue'
import WatchlistSnapshot from '../components/dashboard/WatchlistSnapshot.vue'
import BaseCard from '../components/ui/BaseCard.vue'
</script>

<template>
  <KpiGrid />

  <section class="content-grid">
    <BaseCard class="section-span-2">
      <SignalsTable />
    </BaseCard>

    <BaseCard>
      <NotificationList />
    </BaseCard>

    <BaseCard class="section-span-2">
      <BotRunsList />
    </BaseCard>

    <BaseCard>
      <WatchlistSnapshot />
    </BaseCard>
  </section>
</template>
