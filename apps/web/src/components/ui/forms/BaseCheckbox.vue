<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    modelValue?: boolean
    label: string
    disabled?: boolean
    name?: string
  }>(),
  {
    modelValue: false,
    disabled: false,
    name: undefined,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

function handleChange(event: Event) {
  emit('update:modelValue', (event.target as HTMLInputElement).checked)
}
</script>

<template>
  <label class="checkbox-field">
    <input
      :checked="props.modelValue"
      type="checkbox"
      :disabled="props.disabled"
      :name="props.name"
      v-bind="$attrs"
      @change="handleChange"
    />
    <span>{{ label }}</span>
  </label>
</template>
