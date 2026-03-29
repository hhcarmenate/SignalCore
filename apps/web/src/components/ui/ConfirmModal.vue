<script setup lang="ts">
import BaseModal from './BaseModal.vue'
import AppIcon from './AppIcon.vue'

withDefaults(
  defineProps<{
    open: boolean
    title?: string
    description?: string
    confirmLabel?: string
    cancelLabel?: string
    tone?: 'danger' | 'default'
    isProcessing?: boolean
  }>(),
  {
    title: 'Please confirm',
    description: '',
    confirmLabel: 'Confirm',
    cancelLabel: 'Cancel',
    tone: 'default',
    isProcessing: false,
  },
)

const emit = defineEmits<{
  close: []
  confirm: []
}>()
</script>

<template>
  <BaseModal :open="open" size="sm" @close="emit('close')">
    <div class="confirm-modal-body">
      <div class="confirm-modal-icon" :class="tone === 'danger' ? 'danger' : 'default'">
        <AppIcon :name="tone === 'danger' ? 'TriangleAlert' : 'CircleHelp'" :size="20" />
      </div>

      <div class="confirm-modal-copy">
        <p class="confirm-modal-title">{{ title }}</p>
        <p v-if="description" class="confirm-modal-description">{{ description }}</p>
      </div>

      <div class="confirm-modal-actions">
        <button class="ghost-button" type="button" :disabled="isProcessing" @click="emit('close')">
          {{ cancelLabel }}
        </button>
        <button class="primary-button" :class="tone === 'danger' ? 'danger-solid-button' : ''" type="button" :disabled="isProcessing" @click="emit('confirm')">
          {{ isProcessing ? 'Working…' : confirmLabel }}
        </button>
      </div>
    </div>
  </BaseModal>
</template>
