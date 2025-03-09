<template>
  <BaseModal
    :model-value="show"
    title="Work Order Details"
    size="xl"
    @update:model-value="$emit('update:show', $event)"
  >
    <div class="space-y-8">
      <!-- Basic Information -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <label class="text-sm font-medium text-gray-500">Work Order Code</label>
            <p class="mt-1">{{ workOrder.code }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Title</label>
            <p class="mt-1">{{ workOrder.title }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Category</label>
            <p class="mt-1">{{ workOrder.category }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Status</label>
            <p class="mt-1">
              <span
                :class="[
                  'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                  statusClasses[workOrder.status] || ''
                ]"
              >
                {{ workOrder.status }}
              </span>
            </p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Priority</label>
            <p class="mt-1">
              <span
                :class="[
                  'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                  priorityClasses[workOrder.priority] || ''
                ]"
              >
                {{ workOrder.priority }}
              </span>
            </p>
          </div>
          <div class="sm:col-span-2 lg:col-span-3">
            <label class="text-sm font-medium text-gray-500">Description</label>
            <p class="mt-1">{{ workOrder.description }}</p>
          </div>
        </div>
      </section>

      <!-- Assignment and Scheduling -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Assignment and Scheduling</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div>
            <label class="text-sm font-medium text-gray-500">Assigned To</label>
            <p class="mt-1">{{ workOrder.assigned_to_name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Due Date</label>
            <p class="mt-1">{{ formatDate(workOrder.due_date) }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Estimated Hours</label>
            <p class="mt-1">{{ workOrder.estimated_hours }}</p>
          </div>
        </div>
      </section>

      <!-- Asset and Location -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Asset and Location</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="text-sm font-medium text-gray-500">Asset</label>
            <p class="mt-1">{{ workOrder.asset?.name || 'Not assigned' }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Building</label>
            <p class="mt-1">{{ workOrder.building?.name || 'Not assigned' }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Floor</label>
            <p class="mt-1">{{ workOrder.floor?.name || 'Not assigned' }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Space</label>
            <p class="mt-1">{{ workOrder.space?.name || 'Not assigned' }}</p>
          </div>
        </div>
      </section>

      <!-- Additional Details -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Details</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="grid grid-cols-1 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-500">Required Parts</label>
              <p class="mt-1 whitespace-pre-line">{{ workOrder.required_parts || 'None specified' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Required Tools</label>
              <p class="mt-1 whitespace-pre-line">{{ workOrder.required_tools || 'None specified' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Safety Requirements</label>
              <p class="mt-1 whitespace-pre-line">{{ workOrder.safety_requirements || 'None specified' }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Progress and History -->
      <section v-if="workOrder.progress || workOrder.history?.length">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Progress and History</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <div v-if="workOrder.progress" class="mb-4">
            <label class="text-sm font-medium text-gray-500">Progress</label>
            <div class="mt-2">
              <div class="relative pt-1">
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                  <div
                    :style="{ width: `${workOrder.progress}%` }"
                    class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500"
                  ></div>
                </div>
                <div class="text-right">
                  <span class="text-sm font-semibold inline-block text-blue-600">
                    {{ workOrder.progress }}% Complete
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div v-if="workOrder.history?.length" class="space-y-4">
            <label class="text-sm font-medium text-gray-500">History</label>
            <div class="flow-root">
              <ul class="-mb-8">
                <li v-for="(event, index) in workOrder.history" :key="event.id">
                  <div class="relative pb-8">
                    <span
                      v-if="index !== workOrder.history.length - 1"
                      class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                      aria-hidden="true"
                    ></span>
                    <div class="relative flex space-x-3">
                      <div>
                        <span
                          :class="[
                            'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white',
                            historyEventClasses[event.type]?.bg || 'bg-gray-400'
                          ]"
                        >
                          <svg
                            class="h-5 w-5 text-white"
                            :class="historyEventClasses[event.type]?.icon || ''"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                          >
                            <path
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              :d="historyEventClasses[event.type]?.path || ''"
                            />
                          </svg>
                        </span>
                      </div>
                      <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                        <div>
                          <p class="text-sm text-gray-500">
                            {{ event.description }}
                            <span class="font-medium text-gray-900">{{ event.user }}</span>
                          </p>
                        </div>
                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                          <time :datetime="event.date">{{ formatDate(event.date) }}</time>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Modal Footer -->
    <template #footer>
      <div class="flex justify-end">
        <BaseButton
          variant="secondary"
          @click="$emit('update:show', false)"
        >
          Close
        </BaseButton>
      </div>
    </template>
  </BaseModal>
</template>

<script>
export default {
  name: 'WorkOrderDetails',

  props: {
    show: {
      type: Boolean,
      required: true
    },
    workOrder: {
      type: Object,
      required: true
    }
  },

  emits: ['update:show'],

  setup() {
    const statusClasses = {
      open: 'bg-blue-100 text-blue-800',
      in_progress: 'bg-yellow-100 text-yellow-800',
      on_hold: 'bg-gray-100 text-gray-800',
      completed: 'bg-green-100 text-green-800',
      cancelled: 'bg-red-100 text-red-800'
    }

    const priorityClasses = {
      low: 'bg-gray-100 text-gray-800',
      medium: 'bg-blue-100 text-blue-800',
      high: 'bg-yellow-100 text-yellow-800',
      urgent: 'bg-red-100 text-red-800'
    }

    const historyEventClasses = {
      status_change: {
        bg: 'bg-blue-500',
        icon: 'history-icon',
        path: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'
      },
      comment: {
        bg: 'bg-gray-500',
        icon: 'comment-icon',
        path: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'
      },
      assignment: {
        bg: 'bg-green-500',
        icon: 'user-icon',
        path: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
      },
      update: {
        bg: 'bg-yellow-500',
        icon: 'pencil-icon',
        path: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'
      }
    }

    const formatDate = (date) => {
      if (!date) return ''
      return new Date(date).toLocaleDateString()
    }

    return {
      statusClasses,
      priorityClasses,
      historyEventClasses,
      formatDate
    }
  }
}
</script> 