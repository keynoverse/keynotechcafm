<template>
  <BaseModal
    :model-value="show"
    title="Maintenance Schedule Details"
    size="xl"
    @update:model-value="$emit('update:show', $event)"
  >
    <div class="space-y-8">
      <!-- Basic Information -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <label class="text-sm font-medium text-gray-500">Title</label>
            <p class="mt-1">{{ schedule.title }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Asset</label>
            <p class="mt-1">{{ schedule.asset?.name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Status</label>
            <p class="mt-1">
              <span
                :class="[
                  'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                  statusClasses[schedule.status] || ''
                ]"
              >
                {{ schedule.status }}
              </span>
            </p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Priority</label>
            <p class="mt-1">
              <span
                :class="[
                  'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                  priorityClasses[schedule.priority] || ''
                ]"
              >
                {{ schedule.priority }}
              </span>
            </p>
          </div>
          <div class="sm:col-span-2 lg:col-span-3">
            <label class="text-sm font-medium text-gray-500">Description</label>
            <p class="mt-1">{{ schedule.description }}</p>
          </div>
        </div>
      </section>

      <!-- Schedule Details -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Schedule Details</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div>
            <label class="text-sm font-medium text-gray-500">Start Date</label>
            <p class="mt-1">{{ formatDate(schedule.scheduled_date) }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Frequency</label>
            <p class="mt-1">Every {{ schedule.frequency }} {{ schedule.frequency_unit }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Next Due</label>
            <p class="mt-1">{{ formatDate(schedule.next_due) }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Last Completed</label>
            <p class="mt-1">{{ formatDate(schedule.last_completed) || 'Never' }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Assigned To</label>
            <p class="mt-1">{{ schedule.assigned_to_name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Estimated Duration</label>
            <p class="mt-1">{{ schedule.estimated_duration }} hours</p>
          </div>
        </div>
      </section>

      <!-- Maintenance Details -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Maintenance Details</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="grid grid-cols-1 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-500">Required Tools</label>
              <p class="mt-1 whitespace-pre-line">{{ schedule.required_tools || 'None specified' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Parts Needed</label>
              <p class="mt-1 whitespace-pre-line">{{ schedule.parts_needed || 'None specified' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Safety Requirements</label>
              <p class="mt-1 whitespace-pre-line">{{ schedule.safety_requirements || 'None specified' }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Procedures -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Procedures</h3>
        <div class="space-y-4">
          <div class="bg-gray-50 rounded-lg p-4">
            <label class="text-sm font-medium text-gray-500">Pre-Check Procedures</label>
            <p class="mt-1 whitespace-pre-line">{{ schedule.pre_check_procedures || 'None specified' }}</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-4">
            <label class="text-sm font-medium text-gray-500">Maintenance Procedures</label>
            <p class="mt-1 whitespace-pre-line">{{ schedule.procedures }}</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-4">
            <label class="text-sm font-medium text-gray-500">Post-Check Procedures</label>
            <p class="mt-1 whitespace-pre-line">{{ schedule.post_check_procedures || 'None specified' }}</p>
          </div>
        </div>
      </section>

      <!-- History -->
      <section v-if="schedule.history?.length">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Maintenance History</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="flow-root">
            <ul class="-mb-8">
              <li v-for="(event, index) in schedule.history" :key="event.id">
                <div class="relative pb-8">
                  <span
                    v-if="index !== schedule.history.length - 1"
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
  name: 'MaintenanceScheduleDetails',

  props: {
    show: {
      type: Boolean,
      required: true
    },
    schedule: {
      type: Object,
      required: true
    }
  },

  emits: ['update:show'],

  setup() {
    const statusClasses = {
      scheduled: 'bg-blue-100 text-blue-800',
      in_progress: 'bg-yellow-100 text-yellow-800',
      completed: 'bg-green-100 text-green-800',
      overdue: 'bg-red-100 text-red-800',
      cancelled: 'bg-gray-100 text-gray-800'
    }

    const priorityClasses = {
      low: 'bg-gray-100 text-gray-800',
      medium: 'bg-blue-100 text-blue-800',
      high: 'bg-yellow-100 text-yellow-800',
      urgent: 'bg-red-100 text-red-800'
    }

    const historyEventClasses = {
      completion: {
        bg: 'bg-green-500',
        icon: 'check-icon',
        path: 'M5 13l4 4L19 7'
      },
      update: {
        bg: 'bg-blue-500',
        icon: 'pencil-icon',
        path: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'
      },
      reschedule: {
        bg: 'bg-yellow-500',
        icon: 'calendar-icon',
        path: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
      },
      note: {
        bg: 'bg-gray-500',
        icon: 'note-icon',
        path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
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