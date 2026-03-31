<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  label: string
  tone?: 'success' | 'danger' | 'warning' | 'neutral' | 'outline'
}>()

const className = computed(() => {
  const tones = {
    success: 'border border-sc-success/25 bg-sc-success-soft text-sc-success',
    danger: 'border border-sc-danger/25 bg-sc-danger-soft text-sc-danger',
    warning: 'border border-sc-warning/25 bg-sc-warning-soft text-sc-warning',
    neutral: 'border border-white/8 bg-white/6 text-white/80',
    outline: 'border border-white/12 bg-transparent text-white/85',
  } as const

  return tones[props.tone ?? 'neutral']
})
</script>

<template>
  <span
    class="inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold tracking-[0.02em]"
    :class="className"
  >
    {{ label }}
  </span>
</template>
