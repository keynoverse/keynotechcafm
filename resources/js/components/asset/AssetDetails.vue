<template>
  <BaseModal
    :model-value="show"
    title="Asset Details"
    size="xl"
    @update:model-value="$emit('update:show', $event)"
  >
    <div class="space-y-8">
      <!-- Basic Information -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <label class="text-sm font-medium text-gray-500">Asset Code</label>
            <p class="mt-1">{{ asset.code }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Name</label>
            <p class="mt-1">{{ asset.name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Category</label>
            <p class="mt-1">{{ asset.category?.name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Status</label>
            <p class="mt-1">
              <span
                :class="[
                  'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                  statusClasses[asset.status] || ''
                ]"
              >
                {{ asset.status }}
              </span>
            </p>
          </div>
          <div class="sm:col-span-2 lg:col-span-3">
            <label class="text-sm font-medium text-gray-500">Description</label>
            <p class="mt-1">{{ asset.description || 'No description provided' }}</p>
          </div>
        </div>
      </section>

      <!-- Location -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Location</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div>
            <label class="text-sm font-medium text-gray-500">Building</label>
            <p class="mt-1">{{ asset.building?.name || 'Not assigned' }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Floor</label>
            <p class="mt-1">{{ asset.floor?.name || 'Not assigned' }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Space</label>
            <p class="mt-1">{{ asset.space?.name || 'Not assigned' }}</p>
          </div>
        </div>
      </section>

      <!-- Purchase Information -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Purchase Information</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div>
            <label class="text-sm font-medium text-gray-500">Purchase Cost</label>
            <p class="mt-1">{{ formatCurrency(asset.purchase_cost) }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Purchase Date</label>
            <p class="mt-1">{{ formatDate(asset.purchase_date) }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Warranty Expiry</label>
            <p class="mt-1">{{ formatDate(asset.warranty_expiry) }}</p>
          </div>
        </div>
      </section>

      <!-- Specifications -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Specifications</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="text-sm font-medium text-gray-500">Manufacturer</label>
              <p class="mt-1">{{ asset.metadata?.manufacturer || 'Not specified' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Model</label>
              <p class="mt-1">{{ asset.metadata?.model || 'Not specified' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Serial Number</label>
              <p class="mt-1">{{ asset.metadata?.serial_number || 'Not specified' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Part Number</label>
              <p class="mt-1">{{ asset.metadata?.part_number || 'Not specified' }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Maintenance -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Maintenance</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="text-sm font-medium text-gray-500">Maintenance Frequency</label>
              <p class="mt-1">{{ asset.metadata?.maintenance_frequency || 0 }} days</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Last Maintenance</label>
              <p class="mt-1">{{ formatDate(asset.metadata?.last_maintenance_date) || 'Never' }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Additional Information -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <div>
            <label class="text-sm font-medium text-gray-500">Notes</label>
            <p class="mt-1">{{ asset.metadata?.notes || 'No notes provided' }}</p>
          </div>
        </div>
      </section>

      <!-- Statistics -->
      <section v-if="asset.statistics">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Statistics</h3>
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
                      Uptime
                    </dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">
                        {{ asset.statistics?.uptime || 0 }}%
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
                        {{ asset.statistics?.active_work_orders || 0 }}
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
                      Maintenance Due
                    </dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">
                        {{ formatDate(asset.statistics?.next_maintenance_date) || 'N/A' }}
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total Cost
                    </dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">
                        {{ formatCurrency(asset.statistics?.total_maintenance_cost) }}
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
  name: 'AssetDetails',
  props: {
    show: {
      type: Boolean,
      required: true
    },
    asset: {
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
      repair: 'bg-red-100 text-red-800',
      disposed: 'bg-gray-100 text-gray-800'
    }

    const formatDate = (date) => {
      if (!date) return null
      return new Date(date).toLocaleDateString()
    }

    const formatCurrency = (amount) => {
      if (!amount) return '$0.00'
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount)
    }

    return {
      statusClasses,
      formatDate,
      formatCurrency
    }
  }
}
</script> 