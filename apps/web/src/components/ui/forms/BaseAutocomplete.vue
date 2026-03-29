<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'

export interface AutocompleteOption {
  id: number | string
  label: string
  description?: string
  meta?: string
  payload?: unknown
}

const props = withDefaults(
  defineProps<{
    modelValue?: AutocompleteOption | null
    label?: string
    placeholder?: string
    disabled?: boolean
    minSearchLength?: number
    debounceMs?: number
    emptyStateText?: string
    noResultsText?: string
    loadingText?: string
    autofocus?: boolean
    search: (query: string) => Promise<AutocompleteOption[]>
  }>(),
  {
    modelValue: null,
    label: undefined,
    placeholder: 'Search…',
    disabled: false,
    minSearchLength: 1,
    debounceMs: 250,
    emptyStateText: 'Type to search.',
    noResultsText: 'No matches found.',
    loadingText: 'Searching…',
    autofocus: false,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: AutocompleteOption | null]
}>()

const inputRef = ref<HTMLInputElement | null>(null)
const query = ref(props.modelValue?.label ?? '')
const results = ref<AutocompleteOption[]>([])
const isLoading = ref(false)
const isOpen = ref(false)
const hasFocus = ref(false)
const highlightedIndex = ref(-1)
const suppressNextQueryReset = ref(false)
let debounceTimer: ReturnType<typeof setTimeout> | null = null
let requestToken = 0

const canSearch = computed(() => query.value.trim().length >= props.minSearchLength)
const selectedId = computed(() => props.modelValue?.id ?? null)
const hasSelection = computed(() => props.modelValue !== null)
const showMenu = computed(() => hasFocus.value && isOpen.value)
const hasResults = computed(() => results.value.length > 0)

watch(
  () => props.modelValue,
  (value) => {
    query.value = value?.label ?? ''
  },
)

watch(query, (value) => {
  if (suppressNextQueryReset.value) {
    suppressNextQueryReset.value = false
    return
  }

  emit('update:modelValue', null)
  highlightedIndex.value = -1

  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }

  if (!hasFocus.value) {
    return
  }

  if (!value.trim()) {
    results.value = []
    isLoading.value = false
    isOpen.value = true
    return
  }

  debounceTimer = setTimeout(async () => {
    if (!canSearch.value) {
      results.value = []
      isOpen.value = true
      return
    }

    const token = ++requestToken
    isLoading.value = true
    isOpen.value = true

    try {
      const options = await props.search(value.trim())

      if (token !== requestToken) {
        return
      }

      results.value = options
      highlightedIndex.value = options.length > 0 ? 0 : -1
    } catch {
      if (token !== requestToken) {
        return
      }

      results.value = []
      highlightedIndex.value = -1
    } finally {
      if (token === requestToken) {
        isLoading.value = false
      }
    }
  }, props.debounceMs)
})

function handleFocus() {
  hasFocus.value = true
  isOpen.value = true
}

function handleBlur() {
  window.setTimeout(() => {
    hasFocus.value = false
    isOpen.value = false
    highlightedIndex.value = -1

    if (props.modelValue) {
      suppressNextQueryReset.value = true
      query.value = props.modelValue.label
    }
  }, 120)
}

function selectOption(option: AutocompleteOption) {
  emit('update:modelValue', option)
  suppressNextQueryReset.value = true
  query.value = option.label
  isOpen.value = false
  results.value = []
  highlightedIndex.value = -1
}

function moveHighlight(direction: 1 | -1) {
  if (!hasResults.value) {
    return
  }

  if (highlightedIndex.value === -1) {
    highlightedIndex.value = 0
    return
  }

  highlightedIndex.value = (highlightedIndex.value + direction + results.value.length) % results.value.length
}

function handleKeydown(event: KeyboardEvent) {
  if (event.key === 'ArrowDown') {
    event.preventDefault()
    isOpen.value = true
    moveHighlight(1)
    return
  }

  if (event.key === 'ArrowUp') {
    event.preventDefault()
    isOpen.value = true
    moveHighlight(-1)
    return
  }

  if (event.key === 'Enter') {
    if (showMenu.value && highlightedIndex.value >= 0 && results.value[highlightedIndex.value]) {
      event.preventDefault()
      selectOption(results.value[highlightedIndex.value])
    }

    return
  }

  if (event.key === 'Escape') {
    isOpen.value = false
    highlightedIndex.value = -1
  }
}

onMounted(async () => {
  if (props.autofocus && !props.disabled) {
    await nextTick()
    inputRef.value?.focus()
  }
})

onBeforeUnmount(() => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
})
</script>

<template>
  <label class="form-field autocomplete-field" :class="{ 'has-selection': hasSelection }">
    <div class="autocomplete-label-row">
      <span v-if="label">{{ label }}</span>
      <span v-if="hasSelection" class="selection-chip">Selected</span>
    </div>

    <div class="autocomplete-shell">
      <input
        ref="inputRef"
        :value="query"
        :placeholder="placeholder"
        :disabled="disabled"
        type="text"
        autocomplete="off"
        :class="{ 'input-selected': hasSelection }"
        @input="query = ($event.target as HTMLInputElement).value"
        @focus="handleFocus"
        @blur="handleBlur"
        @keydown="handleKeydown"
      />

      <div v-if="showMenu" class="autocomplete-dropdown" role="listbox">
        <div v-if="!query.trim()" class="autocomplete-state">
          {{ emptyStateText }}
        </div>

        <div v-else-if="isLoading" class="autocomplete-state">
          {{ loadingText }}
        </div>

        <div v-else-if="results.length === 0" class="autocomplete-state">
          {{ noResultsText }}
        </div>

        <button
          v-for="(option, index) in results"
          :key="option.id"
          class="autocomplete-option"
          :class="{
            selected: option.id === selectedId,
            highlighted: index === highlightedIndex,
          }"
          type="button"
          @mousedown.prevent="selectOption(option)"
        >
          <span class="autocomplete-option-copy">
            <strong>{{ option.label }}</strong>
            <small v-if="option.description">{{ option.description }}</small>
          </span>
          <span v-if="option.meta" class="autocomplete-option-meta">{{ option.meta }}</span>
        </button>
      </div>
    </div>
  </label>
</template>

<style scoped>
.autocomplete-field {
  gap: 0.55rem;
}

.autocomplete-label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.selection-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.2rem 0.55rem;
  border: 1px solid rgba(74, 222, 128, 0.45);
  border-radius: 999px;
  background: rgba(34, 197, 94, 0.14);
  color: rgb(187, 247, 208);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.autocomplete-shell {
  position: relative;
}

.input-selected {
  border-color: rgba(74, 222, 128, 0.55) !important;
  box-shadow: 0 0 0 1px rgba(74, 222, 128, 0.18);
}

.autocomplete-dropdown {
  position: absolute;
  z-index: 30;
  top: calc(100% + 0.35rem);
  left: 0;
  right: 0;
  display: grid;
  gap: 0.35rem;
  max-height: 18rem;
  overflow-y: auto;
  padding: 0.5rem;
  border: 1px solid rgba(148, 163, 184, 0.24);
  border-radius: 0.9rem;
  background: rgba(15, 23, 42, 0.98);
  box-shadow: 0 24px 48px rgba(15, 23, 42, 0.35);
}

.autocomplete-state {
  padding: 0.75rem;
  border-radius: 0.75rem;
  color: rgba(226, 232, 240, 0.72);
  font-size: 0.9rem;
  line-height: 1.45;
}

.autocomplete-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  width: 100%;
  padding: 0.8rem 0.9rem;
  border: 1px solid transparent;
  border-radius: 0.8rem;
  background: rgba(30, 41, 59, 0.72);
  color: inherit;
  text-align: left;
  cursor: pointer;
}

.autocomplete-option:hover,
.autocomplete-option.selected,
.autocomplete-option.highlighted {
  border-color: rgba(96, 165, 250, 0.5);
  background: rgba(30, 64, 175, 0.22);
}

.autocomplete-option-copy {
  display: grid;
  gap: 0.15rem;
}

.autocomplete-option-copy small,
.autocomplete-option-meta {
  color: rgba(226, 232, 240, 0.72);
}
</style>
