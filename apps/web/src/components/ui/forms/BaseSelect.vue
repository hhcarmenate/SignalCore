<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'

export type SelectOption = {
  value: string
  label: string
}

const props = withDefaults(
  defineProps<{
    modelValue?: string
    label?: string
    disabled?: boolean
    options: readonly SelectOption[]
    name?: string
  }>(),
  {
    modelValue: '',
    label: undefined,
    disabled: false,
    name: undefined,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
  focus: [event: FocusEvent]
}>()

const isOpen = ref(false)
const rootRef = ref<HTMLElement | null>(null)
const buttonRef = ref<HTMLButtonElement | null>(null)
const highlightedIndex = ref(-1)

const selectedOption = computed(
  () => props.options.find((option) => option.value === props.modelValue) ?? props.options[0],
)

function openMenu() {
  if (props.disabled || props.options.length === 0) {
    return
  }

  isOpen.value = true
  highlightedIndex.value = Math.max(
    props.options.findIndex((option) => option.value === props.modelValue),
    0,
  )
}

function closeMenu() {
  isOpen.value = false
  highlightedIndex.value = -1
}

function toggleMenu() {
  if (isOpen.value) {
    closeMenu()
    return
  }

  openMenu()
}

function selectOption(value: string) {
  emit('update:modelValue', value)
  closeMenu()
}

function handleFocus(event: FocusEvent) {
  emit('focus', event)
}

function handleDocumentClick(event: MouseEvent) {
  if (!rootRef.value) {
    return
  }

  if (!rootRef.value.contains(event.target as Node)) {
    closeMenu()
  }
}

function handleKeydown(event: KeyboardEvent) {
  if (props.disabled || props.options.length === 0) {
    return
  }

  if (event.key === 'Tab') {
    closeMenu()
    return
  }

  if (!isOpen.value && ['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
    event.preventDefault()
    openMenu()
    return
  }

  if (!isOpen.value) {
    return
  }

  if (event.key === 'Escape') {
    event.preventDefault()
    closeMenu()
    buttonRef.value?.focus()
    return
  }

  if (event.key === 'ArrowDown') {
    event.preventDefault()
    highlightedIndex.value = Math.min(highlightedIndex.value + 1, props.options.length - 1)
    return
  }

  if (event.key === 'ArrowUp') {
    event.preventDefault()
    highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0)
    return
  }

  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault()

    const option = props.options[highlightedIndex.value]

    if (option) {
      selectOption(option.value)
    }
  }
}

watch(
  () => props.disabled,
  (disabled) => {
    if (disabled) {
      closeMenu()
    }
  },
)

watch(isOpen, (open) => {
  if (open) {
    document.addEventListener('click', handleDocumentClick)
    return
  }

  document.removeEventListener('click', handleDocumentClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
})
</script>

<template>
  <label ref="rootRef" class="form-field custom-select-field">
    <span v-if="label">{{ label }}</span>

    <input v-if="name" type="hidden" :name="name" :value="modelValue" />

    <button
      ref="buttonRef"
      class="custom-select-trigger"
      type="button"
      :disabled="disabled"
      :aria-expanded="isOpen"
      aria-haspopup="listbox"
      v-bind="$attrs"
      @click="toggleMenu"
      @focus="handleFocus"
      @keydown="handleKeydown"
    >
      <span class="custom-select-value">{{ selectedOption?.label ?? 'Select an option' }}</span>
      <span class="custom-select-chevron" aria-hidden="true">?</span>
    </button>

    <div v-if="isOpen" class="custom-select-menu card" role="listbox">
      <button
        v-for="(option, index) in options"
        :key="option.value"
        class="custom-select-option"
        :class="{ selected: option.value === modelValue, highlighted: index === highlightedIndex }"
        type="button"
        role="option"
        :aria-selected="option.value === modelValue"
        @click="selectOption(option.value)"
        @mouseenter="highlightedIndex = index"
      >
        {{ option.label }}
      </button>
    </div>
  </label>
</template>
