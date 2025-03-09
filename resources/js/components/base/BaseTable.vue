<template>
  <div class="flex flex-col">
    <!-- Table Header Actions -->
    <div v-if="$slots.actions" class="mb-4">
      <slot name="actions" />
    </div>

    <div class="overflow-x-auto">
      <div class="inline-block min-w-full align-middle">
        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
          <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
              <tr>
                <!-- Selection Column -->
                <th
                  v-if="selectable"
                  scope="col"
                  class="relative w-12 px-6 sm:w-16 sm:px-8"
                >
                  <input
                    type="checkbox"
                    class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 sm:left-6"
                    :checked="isAllSelected"
                    :indeterminate="isIndeterminate"
                    @change="toggleAll"
                  />
                </th>

                <!-- Column Headers -->
                <th
                  v-for="column in columns"
                  :key="column.key"
                  scope="col"
                  :class="[
                    'px-3 py-3.5 text-left text-sm font-semibold text-gray-900',
                    column.sortable ? 'cursor-pointer select-none' : '',
                    column.class
                  ]"
                  @click="column.sortable && sort(column.key)"
                >
                  <div class="group inline-flex">
                    {{ column.label }}
                    <span
                      v-if="column.sortable"
                      :class="[
                        'ml-2 flex-none rounded',
                        sortKey === column.key ? 'bg-gray-200 text-gray-900' : 'invisible text-gray-400 group-hover:visible'
                      ]"
                    >
                      <template v-if="sortKey === column.key">
                        <svg
                          v-if="sortOrder === 'asc'"
                          class="h-5 w-5"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z"
                            clip-rule="evenodd"
                          />
                        </svg>
                        <svg
                          v-else
                          class="h-5 w-5"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </template>
                      <svg
                        v-else
                        class="h-5 w-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </span>
                  </div>
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white">
              <tr
                v-for="(item, index) in sortedItems"
                :key="getItemKey(item)"
                :class="[
                  'hover:bg-gray-50',
                  { 'bg-blue-50': isSelected(item) }
                ]"
              >
                <!-- Selection Column -->
                <td v-if="selectable" class="relative w-12 px-6 sm:w-16 sm:px-8">
                  <input
                    type="checkbox"
                    class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 sm:left-6"
                    :checked="isSelected(item)"
                    @change="toggleSelection(item)"
                  />
                </td>

                <!-- Data Columns -->
                <td
                  v-for="column in columns"
                  :key="column.key"
                  :class="[
                    'whitespace-nowrap px-3 py-4 text-sm text-gray-500',
                    column.class
                  ]"
                >
                  <slot
                    :name="column.key"
                    :item="item"
                    :index="index"
                    :value="item[column.key]"
                  >
                    {{ item[column.key] }}
                  </slot>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="!items.length">
                <td
                  :colspan="columns.length + (selectable ? 1 : 0)"
                  class="px-3 py-8 text-center text-sm text-gray-500"
                >
                  <slot name="empty">
                    No items to display
                  </slot>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div
      v-if="pagination"
      class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6"
    >
      <div class="flex flex-1 justify-between sm:hidden">
        <button
          type="button"
          class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          :disabled="currentPage === 1"
          @click="currentPage--"
        >
          Previous
        </button>
        <button
          type="button"
          class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          :disabled="currentPage === totalPages"
          @click="currentPage++"
        >
          Next
        </button>
      </div>
      <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
          <p class="text-sm text-gray-700">
            Showing
            <span class="font-medium">{{ paginationStart }}</span>
            to
            <span class="font-medium">{{ paginationEnd }}</span>
            of
            <span class="font-medium">{{ items.length }}</span>
            results
          </p>
        </div>
        <div>
          <nav
            class="isolate inline-flex -space-x-px rounded-md shadow-sm"
            aria-label="Pagination"
          >
            <button
              type="button"
              class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
              :disabled="currentPage === 1"
              @click="currentPage--"
            >
              <span class="sr-only">Previous</span>
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
            <button
              v-for="page in visiblePages"
              :key="page"
              type="button"
              :class="[
                'relative inline-flex items-center px-4 py-2 text-sm font-semibold',
                page === currentPage
                  ? 'z-10 bg-blue-600 text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600'
                  : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0'
              ]"
              @click="currentPage = page"
            >
              {{ page }}
            </button>
            <button
              type="button"
              class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
              :disabled="currentPage === totalPages"
              @click="currentPage++"
            >
              <span class="sr-only">Next</span>
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'

export default {
  name: 'BaseTable',
  props: {
    items: {
      type: Array,
      required: true
    },
    columns: {
      type: Array,
      required: true,
      validator: columns => columns.every(column => column.key && column.label)
    },
    selectable: {
      type: Boolean,
      default: false
    },
    itemKey: {
      type: String,
      default: 'id'
    },
    pagination: {
      type: Boolean,
      default: false
    },
    perPage: {
      type: Number,
      default: 10
    }
  },
  emits: ['update:selected', 'sort'],
  setup(props, { emit }) {
    const sortKey = ref('')
    const sortOrder = ref('asc')
    const selectedItems = ref(new Set())
    const currentPage = ref(1)

    const getItemKey = (item) => {
      return item[props.itemKey]
    }

    const sort = (key) => {
      if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
      } else {
        sortKey.value = key
        sortOrder.value = 'asc'
      }
      emit('sort', { key: sortKey.value, order: sortOrder.value })
    }

    const sortedItems = computed(() => {
      let result = [...props.items]
      if (sortKey.value) {
        result.sort((a, b) => {
          const aVal = a[sortKey.value]
          const bVal = b[sortKey.value]
          if (aVal === bVal) return 0
          const comparison = aVal > bVal ? 1 : -1
          return sortOrder.value === 'asc' ? comparison : -comparison
        })
      }
      if (props.pagination) {
        const start = (currentPage.value - 1) * props.perPage
        return result.slice(start, start + props.perPage)
      }
      return result
    })

    const isSelected = (item) => {
      return selectedItems.value.has(getItemKey(item))
    }

    const toggleSelection = (item) => {
      const key = getItemKey(item)
      if (selectedItems.value.has(key)) {
        selectedItems.value.delete(key)
      } else {
        selectedItems.value.add(key)
      }
      emit('update:selected', Array.from(selectedItems.value))
    }

    const isAllSelected = computed(() => {
      return props.items.length > 0 && props.items.every(item => isSelected(item))
    })

    const isIndeterminate = computed(() => {
      return !isAllSelected.value && props.items.some(item => isSelected(item))
    })

    const toggleAll = () => {
      if (isAllSelected.value) {
        selectedItems.value.clear()
      } else {
        props.items.forEach(item => {
          selectedItems.value.add(getItemKey(item))
        })
      }
      emit('update:selected', Array.from(selectedItems.value))
    }

    // Pagination
    const totalPages = computed(() => {
      return Math.ceil(props.items.length / props.perPage)
    })

    const paginationStart = computed(() => {
      return (currentPage.value - 1) * props.perPage + 1
    })

    const paginationEnd = computed(() => {
      return Math.min(currentPage.value * props.perPage, props.items.length)
    })

    const visiblePages = computed(() => {
      const delta = 2
      const range = []
      const rangeWithDots = []
      let l

      for (let i = 1; i <= totalPages.value; i++) {
        if (
          i === 1 ||
          i === totalPages.value ||
          (i >= currentPage.value - delta && i <= currentPage.value + delta)
        ) {
          range.push(i)
        }
      }

      range.forEach(i => {
        if (l) {
          if (i - l === 2) {
            rangeWithDots.push(l + 1)
          } else if (i - l !== 1) {
            rangeWithDots.push('...')
          }
        }
        rangeWithDots.push(i)
        l = i
      })

      return rangeWithDots
    })

    return {
      sortKey,
      sortOrder,
      selectedItems,
      currentPage,
      sortedItems,
      isSelected,
      toggleSelection,
      isAllSelected,
      isIndeterminate,
      toggleAll,
      sort,
      getItemKey,
      totalPages,
      paginationStart,
      paginationEnd,
      visiblePages
    }
  }
}
</script> 