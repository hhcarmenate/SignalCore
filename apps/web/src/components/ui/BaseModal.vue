<script setup lang="ts">
import { computed, onBeforeUnmount, watch } from 'vue'

const props = withDefaults(
  defineProps<{
    open: boolean
    title?: string
    description?: string
    size?: 'sm' | 'md' | 'lg'
    closeOnBackdrop?: boolean
  }>(),
  {
    title: '',
    description: '',
    size: 'md',
    closeOnBackdrop: true,
  },
)

const emit = defineEmits<{
  close: []
}>()

const modalClass = computed(() => `modal-panel modal-${props.size}`)

watch(
  () => props.open,
  (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : ''
  },
  { immediate: true },
)

onBeforeUnmount(() => {
  document.body.style.overflow = ''
})

function handleBackdropClick() {
  if (props.closeOnBackdrop) {
    emit('close')
  }
}

function handleKeydown(event: KeyboardEvent) {
  if (event.key === 'Escape' && props.open) {
    emit('close')
  }
}

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      window.addEventListener('keydown', handleKeydown)
    } else {
      window.removeEventListener('keydown', handleKeydown)
    }
  },
  { immediate: true },
)

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <Teleport to="body">
    <div v-if="open" class="modal-backdrop" @click="handleBackdropClick">
      <div :class="modalClass" @click.stop>
        <div v-if="title || description || $slots.header" class="modal-header">
          <slot name="header">
            <div>
              <p v-if="title" class="modal-title">{{ title }}</p>
              <p v-if="description" class="modal-description">{{ description }}</p>
            </div>
          </slot>

          <button class="modal-close-button" type="button" aria-label="Close modal" @click="emit('close')">
            ×
          </button>
        </div>

        <div class="modal-body">
          <slot />
        </div>
      </div>
    </div>
  </Teleport>
</template>
