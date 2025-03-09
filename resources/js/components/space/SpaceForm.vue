<template>
  <BaseModal
    :model-value="show"
    :title="space ? 'Edit Space' : 'Create Space'"
    size="lg"
    @update:model-value="$emit('update:show', $event)"
  >
    <form @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <!-- Building -->
        <div class="sm:col-span-2">
          <BaseSelect
            v-model="form.building_id"
            label="Building"
            :options="buildingOptions"
            required
            :error="errors.building_id"
            @update:model-value="handleBuildingChange"
          />
        </div>

        <!-- Floor -->
        <div class="sm:col-span-2">
          <BaseSelect
            v-model="form.floor_id"
            label="Floor"
            :options="floorOptions"
            required
            :error="errors.floor_id"
            :disabled="!form.building_id"
          />
        </div>

        <!-- Space Code -->
        <BaseInput
          v-model="form.code"
          label="Space Code"
          placeholder="e.g., SP001"
          required
          :error="errors.code"
        />

        <!-- Space Name -->
        <BaseInput
          v-model="form.name"
          label="Space Name"
          placeholder="e.g., Conference Room A"
          required
          :error="errors.name"
        />

        <!-- Space Type -->
        <BaseSelect
          v-model="form.type"
          label="Space Type"
          :options="spaceTypes"
          required
          :error="errors.type"
        />

        <!-- Area -->
        <BaseInput
          v-model.number="form.area"
          type="number"
          label="Area (sqm)"
          min="0"
          step="0.01"
          required
          :error="errors.area"
        />

        <!-- Capacity -->
        <BaseInput
          v-model.number="form.capacity"
          type="number"
          label="Capacity"
          min="0"
          required
          :error="errors.capacity"
        />

        <!-- Status -->
        <BaseSelect
          v-model="form.status"
          label="Status"
          :options="statusOptions"
          required
          :error="errors.status"
        />

        <!-- Description -->
        <div class="sm:col-span-2">
          <BaseInput
            v-model="form.description"
            label="Description"
            type="textarea"
            rows="3"
            placeholder="Enter space description"
            :error="errors.description"
          />
        </div>

        <!-- Additional Information -->
        <div class="sm:col-span-2">
          <h4 class="text-sm font-medium text-gray-900 mb-4">Additional Information</h4>
          
          <!-- Features -->
          <div class="mt-4 p-4 bg-gray-50 rounded-md">
            <h5 class="text-sm font-medium text-gray-900 mb-3">Features</h5>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <BaseInput
                v-model="form.metadata.features.wifi"
                type="checkbox"
                label="Wi-Fi Available"
              />
              <BaseInput
                v-model="form.metadata.features.projector"
                type="checkbox"
                label="Projector Available"
              />
              <BaseInput
                v-model="form.metadata.features.whiteboard"
                type="checkbox"
                label="Whiteboard Available"
              />
              <BaseInput
                v-model="form.metadata.features.video_conferencing"
                type="checkbox"
                label="Video Conferencing"
              />
            </div>
          </div>

          <!-- Equipment -->
          <div class="mt-4 p-4 bg-gray-50 rounded-md">
            <h5 class="text-sm font-medium text-gray-900 mb-3">Equipment</h5>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <BaseInput
                v-model.number="form.metadata.equipment.chairs"
                type="number"
                label="Chairs"
                min="0"
              />
              <BaseInput
                v-model.number="form.metadata.equipment.tables"
                type="number"
                label="Tables"
                min="0"
              />
              <BaseInput
                v-model.number="form.metadata.equipment.power_outlets"
                type="number"
                label="Power Outlets"
                min="0"
              />
            </div>
          </div>

          <!-- Access Control -->
          <div class="mt-4 p-4 bg-gray-50 rounded-md">
            <h5 class="text-sm font-medium text-gray-900 mb-3">Access Control</h5>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <BaseSelect
                v-model="form.metadata.access_level"
                label="Access Level"
                :options="accessLevels"
              />
              <BaseInput
                v-model="form.metadata.access_code"
                label="Access Code"
                type="password"
                placeholder="Enter access code"
              />
            </div>
          </div>

          <!-- Operating Hours -->
          <div class="mt-4 p-4 bg-gray-50 rounded-md">
            <h5 class="text-sm font-medium text-gray-900 mb-3">Operating Hours</h5>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <BaseInput
                v-model="form.metadata.operating_hours.start"
                type="time"
                label="Opening Time"
              />
              <BaseInput
                v-model="form.metadata.operating_hours.end"
                type="time"
                label="Closing Time"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="mt-6 flex justify-end space-x-3">
        <BaseButton
          variant="secondary"
          @click="$emit('closed')"
        >
          Cancel
        </BaseButton>
        <BaseButton
          type="submit"
          variant="primary"
          :loading="loading"
        >
          {{ space ? 'Update' : 'Create' }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>

<script>
import { ref, computed } from 'vue'
import axios from 'axios'

export default {
  name: 'SpaceForm',
  props: {
    show: {
      type: Boolean,
      required: true
    },
    space: {
      type: Object,
      default: null
    },
    buildings: {
      type: Array,
      required: true
    },
    floors: {
      type: Array,
      required: true
    }
  },
  emits: ['update:show', 'saved', 'closed'],
  setup(props, { emit }) {
    const loading = ref(false)
    const errors = ref({})

    // Form state
    const form = ref({
      building_id: '',
      floor_id: '',
      code: '',
      name: '',
      type: '',
      area: 0,
      capacity: 0,
      status: 'active',
      description: '',
      metadata: {
        features: {
          wifi: false,
          projector: false,
          whiteboard: false,
          video_conferencing: false
        },
        equipment: {
          chairs: 0,
          tables: 0,
          power_outlets: 0
        },
        access_level: '',
        access_code: '',
        operating_hours: {
          start: '09:00',
          end: '17:00'
        }
      }
    })

    // Initialize form with space data if editing
    if (props.space) {
      form.value = {
        ...props.space,
        metadata: {
          ...form.value.metadata,
          ...props.space.metadata
        }
      }
    }

    // Options for select inputs
    const buildingOptions = computed(() =>
      props.buildings.map(building => ({
        value: building.id,
        label: building.name
      }))
    )

    const floorOptions = computed(() => {
      if (!form.value.building_id) return []

      return props.floors
        .filter(floor => floor.building_id === form.value.building_id)
        .map(floor => ({
          value: floor.id,
          label: floor.name
        }))
    })

    const spaceTypes = [
      { value: 'office', label: 'Office' },
      { value: 'meeting_room', label: 'Meeting Room' },
      { value: 'conference_room', label: 'Conference Room' },
      { value: 'workspace', label: 'Workspace' },
      { value: 'storage', label: 'Storage' },
      { value: 'reception', label: 'Reception' },
      { value: 'breakout', label: 'Breakout Area' },
      { value: 'kitchen', label: 'Kitchen' },
      { value: 'restroom', label: 'Restroom' },
      { value: 'server_room', label: 'Server Room' },
      { value: 'utility', label: 'Utility Room' }
    ]

    const statusOptions = [
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
      { value: 'maintenance', label: 'Under Maintenance' },
      { value: 'renovation', label: 'Under Renovation' },
      { value: 'occupied', label: 'Occupied' },
      { value: 'vacant', label: 'Vacant' }
    ]

    const accessLevels = [
      { value: 'public', label: 'Public Access' },
      { value: 'restricted', label: 'Restricted Access' },
      { value: 'private', label: 'Private Access' },
      { value: 'secure', label: 'Secure Access' }
    ]

    // Methods
    const handleBuildingChange = () => {
      form.value.floor_id = ''
    }

    const handleSubmit = async () => {
      try {
        loading.value = true
        errors.value = {}

        const url = props.space
          ? `/api/spaces/${props.space.id}`
          : '/api/spaces'
        
        const method = props.space ? 'put' : 'post'
        
        await axios[method](url, form.value)
        emit('saved')
      } catch (error) {
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
        } else {
          console.error('Error submitting form:', error)
        }
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      errors,
      buildingOptions,
      floorOptions,
      spaceTypes,
      statusOptions,
      accessLevels,
      handleBuildingChange,
      handleSubmit
    }
  }
}
</script> 