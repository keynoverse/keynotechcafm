<template>
  <div
    :class="[
      'bg-white shadow rounded-lg overflow-hidden',
      { 'border border-gray-200': bordered },
      { 'hover:shadow-lg transition-shadow duration-200': hoverable }
    ]"
  >
    <!-- Card Header -->
    <div
      v-if="$slots.header || title"
      class="px-4 py-5 sm:px-6 border-b border-gray-200"
      :class="[headerClass]"
    >
      <slot name="header">
        <div class="flex items-center justify-between">
          <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ title }}
          </h3>
          <div v-if="$slots.headerActions" class="flex items-center space-x-3">
            <slot name="headerActions" />
          </div>
        </div>
      </slot>
    </div>

    <!-- Card Body -->
    <div
      :class="[
        'px-4 py-5 sm:p-6',
        { 'bg-gray-50': variant === 'secondary' },
        bodyClass
      ]"
    >
      <slot />
    </div>

    <!-- Card Footer -->
    <div
      v-if="$slots.footer"
      class="px-4 py-4 sm:px-6 border-t border-gray-200"
      :class="[footerClass]"
    >
      <slot name="footer" />
    </div>

    <!-- Loading Overlay -->
    <div
      v-if="loading"
      class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center"
    >
      <svg
        class="animate-spin h-8 w-8 text-blue-600"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        />
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        />
      </svg>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BaseCard',
  props: {
    title: {
      type: String,
      default: ''
    },
    variant: {
      type: String,
      default: 'primary',
      validator: value => ['primary', 'secondary'].includes(value)
    },
    bordered: {
      type: Boolean,
      default: true
    },
    hoverable: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    headerClass: {
      type: String,
      default: ''
    },
    bodyClass: {
      type: String,
      default: ''
    },
    footerClass: {
      type: String,
      default: ''
    }
  }
}
</script> 