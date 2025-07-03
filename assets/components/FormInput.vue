<template>
  <div class="flex flex-col">
    <label :for="id" class="text-sm font-medium text-stone-700 dark:text-stone-200 mb-1">
      {{ label }}
    </label>
    <input
      :id="id"
      :type="type"
      :placeholder="placeholder"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      class="w-full rounded border border-stone-300 p-2 bg-stone-50 dark:bg-stone-800 dark:border-stone-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500"
    />
  </div>
</template>

<script setup>
defineProps({
  modelValue: String,
  label: String,
  type: {
    type: String,
    default: 'text'
  },
  placeholder: {
    type: String,
    default: ''
  },
  id: {
    type: String,
    default: () => `input-${Math.random().toString(36).substring(7)}`
  }
})

defineEmits(['update:modelValue'])
</script>
