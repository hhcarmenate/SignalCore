<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    modelValue?: string | number
    label?: string
    type?: string
    placeholder?: string
    disabled?: boolean
    name?: string
    autocomplete?: string
  }>(),
  {
    modelValue: '',
    label: undefined,
    type: 'text',
    placeholder: undefined,
    disabled: false,
    name: undefined,
    autocomplete: undefined,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

function handleInput(event: Event) {
  emit('update:modelValue', (event.target as HTMLInputElement).value)
}
</script>

<template>
  <label class="form-field">
    <span v-if="label">{{ label }}</span>
    <input
      :value="props.modelValue"
      :type="props.type"
      :placeholder="props.placeholder"
      :disabled="props.disabled"
      :name="props.name"
      :autocomplete="props.autocomplete"
      v-bind="$attrs"
      @input="handleInput"
    />
  </label>
</template>
