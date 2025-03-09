<template>
  <BaseModal
    :model-value="show"
    title="Building Details"
    size="xl"
    @update:model-value="$emit('update:show', $event)"
  >
    <div class="space-y-8">
      <!-- Basic Information -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <label class="text-sm font-medium text-gray-500">Building Code</label>
            <p class="mt-1">{{ building.code }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Name</label>
            <p class="mt-1">{{ building.name }}</p>
          </div>
          <div class="sm:col-span-2 lg:col-span-1">
            <label class="text-sm font-medium text-gray-500">Status</label>
            <p class="mt-1">
              <span
                :class="[
                  'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                  statusClasses[building.status] || ''
                ]"
              >
                {{ building.status }}
              </span>
            </p>
          </div>
          <div class="sm:col-span-2 lg:col-span-3">
            <label class="text-sm font-medium text-gray-500">Description</label>
            <p class="mt-1">{{ building.description || 'No description provided' }}</p>
          </div>
        </div>
      </section>

      <!-- Location Information -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Location</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div class="sm:col-span-2 lg:col-span-3">
            <label class="text-sm font-medium text-gray-500">Address</label>
            <p class="mt-1">{{ building.address }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">City</label>
            <p class="mt-1">{{ building.city }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">State/Province</label>
            <p class="mt-1">{{ building.state }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Country</label>
            <p class="mt-1">{{ building.country }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Postal Code</label>
            <p class="mt-1">{{ building.postal_code }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Coordinates</label>
            <p class="mt-1">
              {{ building.latitude }}, {{ building.longitude }}
            </p>
          </div>
        </div>
      </section>

      <!-- Building Specifications -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Building Specifications</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div>
            <label class="text-sm font-medium text-gray-500">Total Floors</label>
            <p class="mt-1">{{ building.total_floors }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Total Area</label>
            <p class="mt-1">{{ building.total_area }} sqm</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Year Built</label>
            <p class="mt-1">{{ building.year_built }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Construction Type</label>
            <p class="mt-1">{{ building.metadata?.construction_type }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Occupancy Type</label>
            <p class="mt-1">{{ building.metadata?.occupancy_type }}</p>
          </div>
        </div>
      </section>

      <!-- Facilities Management -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Facilities Management</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Facilities Manager</h4>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
              <label class="text-sm font-medium text-gray-500">Name</label>
              <p class="mt-1">{{ building.metadata?.facilities_manager?.name }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Phone</label>
              <p class="mt-1">{{ building.metadata?.facilities_manager?.phone }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Email</label>
              <p class="mt-1">{{ building.metadata?.facilities_manager?.email }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Emergency Contacts -->
      <section v-if="building.metadata?.emergency_contacts?.length">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Emergency Contacts</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div
            v-for="(contact, index) in building.metadata.emergency_contacts"
            :key="index"
            class="bg-gray-50 rounded-lg p-4"
          >
            <div class="space-y-2">
              <div>
                <label class="text-sm font-medium text-gray-500">Name</label>
                <p class="mt-1">{{ contact.name }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Phone</label>
                <p class="mt-1">{{ contact.phone }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Role</label>
                <p class="mt-1">{{ contact.role }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Certifications -->
      <section v-if="building.metadata?.certifications?.length">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Certifications</h3>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="cert in building.metadata.certifications"
            :key="cert"
            class="inline-flex items-center rounded-full bg-blue-100 px-3 py-0.5 text-sm font-medium text-blue-800"
          >
            {{ cert }}
          </span>
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
  name: 'BuildingDetails',
  props: {
    show: {
      type: Boolean,
      required: true
    },
    building: {
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