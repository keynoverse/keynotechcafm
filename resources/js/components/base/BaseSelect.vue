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
      <select
        :id="id"
        :value="modelValue"
        :multiple="multiple"
        :disabled="disabled"
        :required="required"
        :class="[
          'block w-full rounded-md shadow-sm',
          'focus:ring-blue-500 focus:border-blue-500 sm:text-sm',
          { 'border-red-300': error },
          { 'border-gray-300': !error },
          { 'pr-10': !multiple },
          { 'h-auto': multiple }
        ]"
        @change="handleChange"
      >
        <!-- Placeholder option -->
        <option v-if="placeholder && !multiple" value="" disabled selected>
          {{ placeholder }}
        </option>

        <!-- Options with groups -->
        <template v-if="hasGroups">
          <optgroup
            v-for="(group, groupLabel) in groupedOptions"
            :key="groupLabel"
            :label="groupLabel"
          >
            <option
              v-for="option in group"
              :key="getOptionValue(option)"
              :value="getOptionValue(option)"
              :disabled="option.disabled"
            >
              {{ getOptionLabel(option) }}
            </option>
          </optgroup>
        </template>

        <!-- Flat options list -->
        <template v-else>
          <option
            v-for="option in options"
            :key="getOptionValue(option)"
            :value="getOptionValue(option)"
            :disabled="option.disabled"
          >
            {{ getOptionLabel(option) }}
          </option>
        </template>
      </select>

      <!-- Dropdown icon -->
      <div
        v-if="!multiple"
        class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none"
      >
        <svg
          class="h-5 w-5 text-gray-400"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 20 20"
          fill="currentColor"
          aria-hidden="true"
        >
          <path
            fill-rule="evenodd"
            d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
            clip-rule="evenodd"
          />
        </svg>
      </div>
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
import { computed } from 'vue'

export default {
  name: 'BaseSelect',
  props: {
    modelValue: {
      type: [String, Number, Array],
      default: ''
    },
    options: {
      type: Array,
      required: true
    },
    label: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: 'Select an option'
    },
    required: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    multiple: {
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
    valueKey: {
      type: String,
      default: 'value'
    },
    labelKey: {
      type: String,
      default: 'label'
    },
    groupKey: {
      type: String,
      default: 'group'
    }
  },
  emits: ['update:modelValue', 'change'],
  setup(props, { emit }) {
    const id = computed(() => `select-${Math.random().toString(36).substr(2, 9)}`)

    const hasGroups = computed(() => {
      return props.options.some(option => option[props.groupKey])
    })

    const groupedOptions = computed(() => {
      if (!hasGroups.value) return {}

      return props.options.reduce((groups, option) => {
        const group = option[props.groupKey] || 'Other'
        if (!groups[group]) {
          groups[group] = []
        }
        groups[group].push(option)
        return groups
      }, {})
    })

    const getOptionValue = (option) => {
      return typeof option === 'object' ? option[props.valueKey] : option
    }

    const getOptionLabel = (option) => {
      return typeof option === 'object' ? option[props.labelKey] : option
    }

    const handleChange = (event) => {
      const value = props.multiple
        ? Array.from(event.target.selectedOptions).map(option => option.value)
        : event.target.value

      emit('update:modelValue', value)
      emit('change', value)
    }

    return {
      id,
      hasGroups,
      groupedOptions,
      getOptionValue,
      getOptionLabel,
      handleChange
    }
  }
}
</script>

<style scoped>
select[multiple] {
  padding-right: 0.75rem;
  min-height: 8rem;
}
</style> 