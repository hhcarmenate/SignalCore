<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    modelValue?: string
    label?: string
    placeholder?: string
    rows?: number | string
    disabled?: boolean
    name?: string
  }>(),
  {
    modelValue: '',
    label: undefined,
    placeholder: undefined,
    rows: 3,
    disabled: false,
    name: undefined,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

function handleInput(event: Event) {
  emit('update:modelValue', (event.target as HTMLTextAreaElement).value)
}
</script>

<template>
  <label class="form-field">
    <span v-if="label">{{ label }}</span>
    <textarea
      :value="props.modelValue"
      :rows="props.rows"
      :placeholder="props.placeholder"
      :disabled="props.disabled"
      :name="props.name"
      v-bind="$attrs"
      @input="handleInput"
    />
  </label>
</template>
