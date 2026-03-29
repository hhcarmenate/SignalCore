<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'

import BaseAutocomplete, { type AutocompleteOption } from '../ui/forms/BaseAutocomplete.vue'
import BaseTextarea from '../ui/forms/BaseTextarea.vue'
import { searchSymbols } from '../../lib/symbols'
import { getMarketTypeLabel } from '../../lib/marketTypes'
import type { SymbolRecord } from '../../types/watchlists'

const props = defineProps<{
  disabled?: boolean
  isSaving?: boolean
  marketType?: string
}>()

const emit = defineEmits<{
  submit: [payload: {
    notes?: string
    symbol_id: number
  }]
}>()

const selectedOption = ref<AutocompleteOption | null>(null)
const selectedSymbol = ref<SymbolRecord | null>(null)
const form = reactive({
  notes: '',
})

const marketLabel = computed(() => getMarketTypeLabel(props.marketType ?? 'us_equities'))

function resetForm() {
  selectedOption.value = null
  selectedSymbol.value = null
  form.notes = ''
}

watch(selectedOption, (option) => {
  selectedSymbol.value = (option?.payload as SymbolRecord | undefined) ?? null
})

async function searchSymbolOptions(query: string): Promise<AutocompleteOption[]> {
  const response = await searchSymbols({
    search: query,
    status: 'active',
    limit: 8,
  })

  return response.data.map((symbol) => ({
    id: symbol.id,
    label: symbol.symbol,
    description: symbol.name ?? undefined,
    meta: [symbol.asset_type.toUpperCase(), symbol.exchange].filter(Boolean).join(' • '),
    payload: symbol,
  }))
}

async function handleSubmit() {
  if (!selectedSymbol.value || props.disabled || props.isSaving) {
    return
  }

  try {
    await emit('submit', {
      notes: form.notes.trim() || undefined,
      symbol_id: selectedSymbol.value.id,
    })

    resetForm()
  } catch {
    // Keep values when the request fails.
  }
}
</script>

<template>
  <form class="stack-form" @submit.prevent="handleSubmit">
    <div class="section-copy compact-copy">
      Search the shared symbol catalog and add an existing instrument to this watchlist.
      <strong v-if="marketType"> Current market: {{ marketLabel }}</strong>
    </div>

    <div class="two-column-form">
      <BaseAutocomplete
        v-model="selectedOption"
        class="full-span"
        label="Symbol"
        placeholder="Search AAPL, SPY, NVIDIA…"
        :disabled="disabled || isSaving"
        :search="searchSymbolOptions"
        :debounce-ms="250"
        :autofocus="true"
        empty-state-text="Start typing a ticker or company name."
        no-results-text="No matching symbols found in the current catalog. Manual adds are disabled."
      />

      <div class="market-lock-card">
        <span class="eyebrow">Inherited market</span>
        <strong>{{ marketLabel }}</strong>
      </div>

      <div class="market-lock-card" :class="{ muted: !selectedSymbol }">
        <span class="eyebrow">Selected symbol</span>
        <strong>{{ selectedSymbol?.symbol ?? 'None selected' }}</strong>
        <small>{{ selectedSymbol?.name ?? 'Choose a symbol from the autocomplete list.' }}</small>
      </div>

      <div class="full-span catalog-policy-note">
        Only symbols already in the shared catalog can be added to this watchlist.
      </div>

      <BaseTextarea
        v-model="form.notes"
        class="full-span"
        label="Notes"
        :rows="3"
        placeholder="Optional notes"
        :disabled="disabled || isSaving"
      />
    </div>

    <div class="modal-form-actions">
      <button class="primary-button full-width-button" type="submit" :disabled="disabled || isSaving || !selectedSymbol">
        {{ isSaving ? 'Adding ticker…' : 'Add ticker' }}
      </button>
    </div>
  </form>
</template>

<style scoped>
.muted {
  opacity: 0.7;
}

.market-lock-card small {
  color: rgba(226, 232, 240, 0.72);
}

.catalog-policy-note {
  padding: 0.85rem 1rem;
  border: 1px dashed rgba(96, 165, 250, 0.35);
  border-radius: 0.9rem;
  color: rgba(226, 232, 240, 0.78);
  background: rgba(15, 23, 42, 0.4);
  font-size: 0.92rem;
}
</style>
