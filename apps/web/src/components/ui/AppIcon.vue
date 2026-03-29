<script setup lang="ts">
import { computed } from 'vue'
import * as lucideIcons from 'lucide-vue-next'

const icons = lucideIcons as Record<string, unknown>

const props = withDefaults(
  defineProps<{
    name: string
    size?: number
    strokeWidth?: number
    class?: string
  }>(),
  {
    size: 18,
    strokeWidth: 2,
    class: '',
  },
)

const iconComponent = computed(() => (icons[props.name] as object) ?? (icons.CircleHelp as object))
</script>

<template>
  <component :is="iconComponent" :size="size" :stroke-width="strokeWidth" :class="props.class" />
</template>
