<script setup lang="ts">
import BaseCard from '../components/ui/BaseCard.vue'
import SignalsTable from '../components/dashboard/SignalsTable.vue'
</script>

<template>
  <section class="content-grid single-column-grid">
    <BaseCard class="section-card">
      <SignalsTable mode="full" />
    </BaseCard>
  </section>
</template>
