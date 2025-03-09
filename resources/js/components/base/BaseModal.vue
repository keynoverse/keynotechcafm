<template>
  <Teleport to="body">
    <Transition
      enter-active-class="ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        @click="closeOnBackdrop && $emit('update:modelValue', false)"
      />
    </Transition>

    <Transition
      enter-active-class="ease-out duration-300"
      enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      enter-to-class="opacity-100 translate-y-0 sm:scale-100"
      leave-active-class="ease-in duration-200"
      leave-from-class="opacity-100 translate-y-0 sm:scale-100"
      leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
      <div
        v-if="modelValue"
        class="fixed inset-0 z-10 overflow-y-auto"
        @keydown.esc="closeOnEsc && $emit('update:modelValue', false)"
      >
        <div
          class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
        >
          <div
            ref="modalPanel"
            :class="[
              'relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8',
              sizeClass
            ]"
            @click.stop
          >
            <!-- Modal Header -->
            <div v-if="$slots.header || title" class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
              <slot name="header">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-medium leading-6 text-gray-900">
                    {{ title }}
                  </h3>
                  <button
                    v-if="showClose"
                    type="button"
                    class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    @click="$emit('update:modelValue', false)"
                  >
                    <span class="sr-only">Close</span>
                    <svg
                      class="h-6 w-6"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="1.5"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12"
                      />
                    </svg>
                  </button>
                </div>
              </slot>
            </div>

            <!-- Modal Body -->
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
              <slot />
            </div>

            <!-- Modal Footer -->
            <div
              v-if="$slots.footer"
              class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
            >
              <slot name="footer" />
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import { ref, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

export default {
  name: 'BaseModal',
  props: {
    modelValue: {
      type: Boolean,
      required: true
    },
    title: {
      type: String,
      default: ''
    },
    size: {
      type: String,
      default: 'md',
      validator: value => ['sm', 'md', 'lg', 'xl', 'full'].includes(value)
    },
    showClose: {
      type: Boolean,
      default: true
    },
    closeOnEsc: {
      type: Boolean,
      default: true
    },
    closeOnBackdrop: {
      type: Boolean,
      default: true
    }
  },
  emits: ['update:modelValue'],
  setup(props) {
    const modalPanel = ref(null)
    const previousActiveElement = ref(null)

    const sizeClass = computed(() => {
      const sizes = {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        full: 'sm:max-w-full sm:m-4'
      }
      return sizes[props.size]
    })

    const focusFirstElement = () => {
      const focusableElements = modalPanel.value?.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
      )
      if (focusableElements?.length) {
        focusableElements[0].focus()
      }
    }

    watch(() => props.modelValue, async (newValue) => {
      if (newValue) {
        previousActiveElement.value = document.activeElement
        await nextTick()
        focusFirstElement()
      } else if (previousActiveElement.value) {
        previousActiveElement.value.focus()
      }
    })

    onMounted(() => {
      if (props.modelValue) {
        previousActiveElement.value = document.activeElement
        nextTick(focusFirstElement)
      }
    })

    onBeforeUnmount(() => {
      if (previousActiveElement.value) {
        previousActiveElement.value.focus()
      }
    })

    return {
      modalPanel,
      sizeClass
    }
  }
}
</script> 