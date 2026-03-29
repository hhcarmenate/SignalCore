<script setup lang="ts">
import { reactive } from 'vue'

import BaseCheckbox from '../ui/forms/BaseCheckbox.vue'
import BaseInput from '../ui/forms/BaseInput.vue'
import BaseSelect from '../ui/forms/BaseSelect.vue'
import BaseTextarea from '../ui/forms/BaseTextarea.vue'
import { MARKET_TYPE_OPTIONS } from '../../lib/marketTypes'

const props = defineProps<{
  isSaving?: boolean
}>()

const emit = defineEmits<{
  submit: [payload: { name: string; description?: string; market_type: string; is_active: boolean }]
}>()

const form = reactive({
  name: '',
  description: '',
  market_type: 'us_equities',
  is_active: true,
})

async function handleSubmit() {
  if (!form.name.trim() || props.isSaving) {
    return
  }

  try {
    await emit('submit', {
      name: form.name.trim(),
      description: form.description.trim() || undefined,
      market_type: form.market_type,
      is_active: form.is_active,
    })

    form.name = ''
    form.description = ''
    form.market_type = 'us_equities'
    form.is_active = true
  } catch {
    // Keep form values when the request fails.
  }
}
</script>

<template>
  <form class="stack-form" @submit.prevent="handleSubmit">
    <BaseInput v-model="form.name" label="Name" type="text" placeholder="Core momentum" />

    <BaseSelect v-model="form.market_type" label="Market type" :options="MARKET_TYPE_OPTIONS" />

    <BaseTextarea
      v-model="form.description"
      label="Description"
      :rows="3"
      placeholder="Optional description"
    />

    <BaseCheckbox v-model="form.is_active" label="Active" />

    <div class="modal-form-actions">
      <button class="primary-button full-width-button" type="submit" :disabled="isSaving">
        {{ isSaving ? 'Saving…' : 'Create watchlist' }}
      </button>
    </div>
  </form>
</template>
