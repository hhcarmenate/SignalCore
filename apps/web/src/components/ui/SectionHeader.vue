<script setup lang="ts">
defineProps<{
  eyebrow: string
  title: string
  actionLabel?: string
}>()
</script>

<template>
  <div class="section-header" :class="{ compact: !actionLabel }">
    <div>
      <p class="eyebrow">{{ eyebrow }}</p>
      <h3>{{ title }}</h3>
    </div>

    <button v-if="actionLabel" class="ghost-button" type="button">{{ actionLabel }}</button>
  </div>
</template>
