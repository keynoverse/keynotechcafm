<template>
  <div>
    <label
      v-if="label"
      :for="id"
      class="block text-sm font-medium text-gray-700 mb-1"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative">
      <!-- Leading Icon -->
      <div
        v-if="leadingIcon"
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
      >
        <component
          :is="leadingIcon"
          class="h-5 w-5 text-gray-400"
          aria-hidden="true"
        />
      </div>

      <!-- Input Element -->
      <input
        :id="id"
        ref="input"
        v-bind="$attrs"
        :value="modelValue"
        :type="type"
        :class="[
          'block w-full rounded-md shadow-sm',
          'focus:ring-blue-500 focus:border-blue-500 sm:text-sm',
          { 'border-red-300': error },
          { 'border-gray-300': !error },
          { 'pl-10': leadingIcon },
          { 'pr-10': trailingIcon || clearable },
        ]"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
      />

      <!-- Trailing Icon -->
      <div
        v-if="trailingIcon"
        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
      >
        <component
          :is="trailingIcon"
          class="h-5 w-5 text-gray-400"
          aria-hidden="true"
        />
      </div>

      <!-- Clear Button -->
      <button
        v-if="clearable && modelValue"
        type="button"
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
        @click="clearInput"
      >
        <svg
          class="h-5 w-5 text-gray-400 hover:text-gray-500"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
            clip-rule="evenodd"
          />
        </svg>
      </button>
    </div>

    <!-- Error Message -->
    <p v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </p>

    <!-- Help Text -->
    <p v-if="helpText" class="mt-1 text-sm text-gray-500">
      {{ helpText }}
    </p>
  </div>
</template>

<script>
import { ref, computed } from 'vue'

export default {
  name: 'BaseInput',
  inheritAttrs: false,
  props: {
    modelValue: {
      type: [String, Number],
      default: ''
    },
    label: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: 'text'
    },
    placeholder: {
      type: String,
      default: ''
    },
    required: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    error: {
      type: String,
      default: ''
    },
    helpText: {
      type: String,
      default: ''
    },
    clearable: {
      type: Boolean,
      default: false
    },
    leadingIcon: {
      type: [Object, Function],
      default: null
    },
    trailingIcon: {
      type: [Object, Function],
      default: null
    }
  },
  emits: ['update:modelValue', 'blur', 'clear'],
  setup(props) {
    const input = ref(null)
    const id = computed(() => `input-${Math.random().toString(36).substr(2, 9)}`)

    const clearInput = () => {
      input.value.value = ''
      input.value.dispatchEvent(new Event('input'))
    }

    return {
      input,
      id,
      clearInput
    }
  }
}
</script> 