<template>
  <BaseModal
    :model-value="show"
    title="Space Details"
    size="xl"
    @update:model-value="$emit('update:show', $event)"
  >
    <div class="space-y-8">
      <!-- Basic Information -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <label class="text-sm font-medium text-gray-500">Space Code</label>
            <p class="mt-1">{{ space.code }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Name</label>
            <p class="mt-1">{{ space.name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Type</label>
            <p class="mt-1 capitalize">{{ space.type }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Status</label>
            <p class="mt-1">
              <span
                :class="[
                  'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                  statusClasses[space.status] || ''
                ]"
              >
                {{ space.status }}
              </span>
            </p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Area</label>
            <p class="mt-1">{{ space.area }} sqm</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Capacity</label>
            <p class="mt-1">{{ space.capacity }} people</p>
          </div>
          <div class="sm:col-span-2 lg:col-span-3">
            <label class="text-sm font-medium text-gray-500">Description</label>
            <p class="mt-1">{{ space.description || 'No description provided' }}</p>
          </div>
        </div>
      </section>

      <!-- Location Information -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Location</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="text-sm font-medium text-gray-500">Building</label>
            <p class="mt-1">{{ space.building?.name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Floor</label>
            <p class="mt-1">{{ space.floor?.name }}</p>
          </div>
        </div>
      </section>

      <!-- Features -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Features</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div>
              <label class="text-sm font-medium text-gray-500">Wi-Fi</label>
              <p class="mt-1">
                <span
                  :class="[
                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                    space.metadata?.features?.wifi ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ space.metadata?.features?.wifi ? 'Available' : 'Not Available' }}
                </span>
              </p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Projector</label>
              <p class="mt-1">
                <span
                  :class="[
                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                    space.metadata?.features?.projector ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ space.metadata?.features?.projector ? 'Available' : 'Not Available' }}
                </span>
              </p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Whiteboard</label>
              <p class="mt-1">
                <span
                  :class="[
                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                    space.metadata?.features?.whiteboard ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ space.metadata?.features?.whiteboard ? 'Available' : 'Not Available' }}
                </span>
              </p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Video Conferencing</label>
              <p class="mt-1">
                <span
                  :class="[
                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                    space.metadata?.features?.video_conferencing ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ space.metadata?.features?.video_conferencing ? 'Available' : 'Not Available' }}
                </span>
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Equipment -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Equipment</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
              <label class="text-sm font-medium text-gray-500">Chairs</label>
              <p class="mt-1">{{ space.metadata?.equipment?.chairs || 0 }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Tables</label>
              <p class="mt-1">{{ space.metadata?.equipment?.tables || 0 }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Power Outlets</label>
              <p class="mt-1">{{ space.metadata?.equipment?.power_outlets || 0 }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Access Control -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Access Control</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="text-sm font-medium text-gray-500">Access Level</label>
              <p class="mt-1">{{ space.metadata?.access_level }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Operating Hours</label>
              <p class="mt-1">
                {{ space.metadata?.operating_hours?.start || '09:00' }} - 
                {{ space.metadata?.operating_hours?.end || '17:00' }}
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Usage Statistics -->
      <section v-if="space.statistics">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Usage Statistics</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Usage Rate
                    </dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">
                        {{ space.statistics?.usage_rate || 0 }}%
                      </div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Bookings This Month
                    </dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">
                        {{ space.statistics?.bookings_this_month || 0 }}
                      </div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Active Work Orders
                    </dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">
                        {{ space.statistics?.active_work_orders || 0 }}
                      </div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Pending Maintenance
                    </dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">
                        {{ space.statistics?.pending_maintenance || 0 }}
                      </div>
                    </dd>
                  </dl>
                </div>
              </div>
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
          @click="$emit('closed')"
        >
          Close
        </BaseButton>
      </div>
    </template>
  </BaseModal>
</template>

<script>
export default {
  name: 'SpaceDetails',
  props: {
    show: {
      type: Boolean,
      required: true
    },
    space: {
      type: Object,
      required: true
    }
  },
  emits: ['update:show', 'closed'],
  setup() {
    const statusClasses = {
      active: 'bg-green-100 text-green-800',
      inactive: 'bg-gray-100 text-gray-800',
      maintenance: 'bg-yellow-100 text-yellow-800',
      renovation: 'bg-blue-100 text-blue-800',
      occupied: 'bg-blue-100 text-blue-800',
      vacant: 'bg-gray-100 text-gray-800'
    }

    return {
      statusClasses
    }
  }
}
</script> 