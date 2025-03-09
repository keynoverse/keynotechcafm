<template>
  <BaseModal
    :model-value="show"
    title="Floor Details"
    size="xl"
    @update:model-value="$emit('update:show', $event)"
  >
    <div class="space-y-8">
      <!-- Basic Information -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <label class="text-sm font-medium text-gray-500">Floor Number</label>
            <p class="mt-1">{{ floor.floor_number }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Name</label>
            <p class="mt-1">{{ floor.name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Status</label>
            <p class="mt-1">
              <span
                :class="[
                  'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                  statusClasses[floor.status] || ''
                ]"
              >
                {{ floor.status }}
              </span>
            </p>
          </div>
          <div class="sm:col-span-2 lg:col-span-3">
            <label class="text-sm font-medium text-gray-500">Description</label>
            <p class="mt-1">{{ floor.description || 'No description provided' }}</p>
          </div>
        </div>
      </section>

      <!-- Building Information -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Building Information</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="text-sm font-medium text-gray-500">Building Name</label>
            <p class="mt-1">{{ floor.building?.name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Building Code</label>
            <p class="mt-1">{{ floor.building?.code }}</p>
          </div>
        </div>
      </section>

      <!-- Floor Specifications -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Floor Specifications</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <label class="text-sm font-medium text-gray-500">Total Area</label>
            <p class="mt-1">{{ floor.total_area }} sqm</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Floor Type</label>
            <p class="mt-1">{{ floor.metadata?.floor_type }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Space Count</label>
            <p class="mt-1">{{ floor.space_count || 0 }}</p>
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
              <p class="mt-1">{{ floor.metadata?.access_level }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Access Notes</label>
              <p class="mt-1">{{ floor.metadata?.access_notes || 'No access notes provided' }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Emergency Information -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Emergency Information</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
              <label class="text-sm font-medium text-gray-500">Emergency Exits</label>
              <p class="mt-1">{{ floor.metadata?.emergency_exit_count || 0 }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Fire Extinguishers</label>
              <p class="mt-1">{{ floor.metadata?.fire_extinguisher_count || 0 }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Usage Statistics -->
      <section v-if="floor.statistics">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Usage Statistics</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total Spaces
                    </dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">
                        {{ floor.statistics?.total_spaces || 0 }}
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Occupancy Rate
                    </dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">
                        {{ floor.statistics?.occupancy_rate || 0 }}%
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
                        {{ floor.statistics?.active_work_orders || 0 }}
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
                        {{ floor.statistics?.pending_maintenance || 0 }}
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
  name: 'FloorDetails',
  props: {
    show: {
      type: Boolean,
      required: true
    },
    floor: {
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
      renovation: 'bg-blue-100 text-blue-800'
    }

    return {
      statusClasses
    }
  }
}
</script> 