<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  label: string
  tone?: 'success' | 'danger' | 'warning' | 'neutral' | 'outline'
}>()

const className = computed(() => `badge-${props.tone ?? 'neutral'}`)
</script>

<template>
  <span class="badge" :class="className">{{ label }}</span>
</template>
