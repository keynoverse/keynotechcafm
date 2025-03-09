<template>
  <BaseModal
    :model-value="show"
    :title="floor ? 'Edit Floor' : 'Create Floor'"
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
          />
        </div>

        <!-- Floor Number -->
        <BaseInput
          v-model.number="form.floor_number"
          type="number"
          label="Floor Number"
          required
          :error="errors.floor_number"
        />

        <!-- Floor Name -->
        <BaseInput
          v-model="form.name"
          label="Floor Name"
          placeholder="e.g., Ground Floor, First Floor"
          required
          :error="errors.name"
        />

        <!-- Description -->
        <div class="sm:col-span-2">
          <BaseInput
            v-model="form.description"
            label="Description"
            type="textarea"
            rows="3"
            placeholder="Enter floor description"
            :error="errors.description"
          />
        </div>

        <!-- Total Area -->
        <BaseInput
          v-model.number="form.total_area"
          type="number"
          label="Total Area (sqm)"
          min="0"
          step="0.01"
          required
          :error="errors.total_area"
        />

        <!-- Status -->
        <BaseSelect
          v-model="form.status"
          label="Status"
          :options="statusOptions"
          required
          :error="errors.status"
        />

        <!-- Additional Information -->
        <div class="sm:col-span-2">
          <h4 class="text-sm font-medium text-gray-900 mb-4">Additional Information</h4>
          
          <!-- Floor Type -->
          <BaseSelect
            v-model="form.metadata.floor_type"
            label="Floor Type"
            :options="floorTypes"
            required
            :error="errors['metadata.floor_type']"
          />

          <!-- Access Control -->
          <div class="mt-4 p-4 bg-gray-50 rounded-md">
            <h5 class="text-sm font-medium text-gray-900 mb-3">Access Control</h5>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <BaseSelect
                v-model="form.metadata.access_level"
                label="Access Level"
                :options="accessLevels"
                :error="errors['metadata.access_level']"
              />
              <BaseInput
                v-model="form.metadata.access_notes"
                label="Access Notes"
                placeholder="Enter access control notes"
                :error="errors['metadata.access_notes']"
              />
            </div>
          </div>

          <!-- Emergency Information -->
          <div class="mt-4 p-4 bg-gray-50 rounded-md">
            <h5 class="text-sm font-medium text-gray-900 mb-3">Emergency Information</h5>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <BaseInput
                v-model="form.metadata.emergency_exit_count"
                type="number"
                label="Emergency Exits"
                min="0"
                :error="errors['metadata.emergency_exit_count']"
              />
              <BaseInput
                v-model="form.metadata.fire_extinguisher_count"
                type="number"
                label="Fire Extinguishers"
                min="0"
                :error="errors['metadata.fire_extinguisher_count']"
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
          {{ floor ? 'Update' : 'Create' }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>

<script>
import { ref } from 'vue'
import axios from 'axios'

export default {
  name: 'FloorForm',
  props: {
    show: {
      type: Boolean,
      required: true
    },
    floor: {
      type: Object,
      default: null
    },
    buildings: {
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
      floor_number: 1,
      name: '',
      description: '',
      total_area: 0,
      status: 'active',
      metadata: {
        floor_type: '',
        access_level: '',
        access_notes: '',
        emergency_exit_count: 0,
        fire_extinguisher_count: 0
      }
    })

    // Initialize form with floor data if editing
    if (props.floor) {
      form.value = {
        ...props.floor,
        metadata: {
          ...form.value.metadata,
          ...props.floor.metadata
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

    const statusOptions = [
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
      { value: 'maintenance', label: 'Under Maintenance' },
      { value: 'renovation', label: 'Under Renovation' }
    ]

    const floorTypes = [
      { value: 'Office', label: 'Office' },
      { value: 'Retail', label: 'Retail' },
      { value: 'Residential', label: 'Residential' },
      { value: 'Parking', label: 'Parking' },
      { value: 'Mixed', label: 'Mixed Use' },
      { value: 'Technical', label: 'Technical' }
    ]

    const accessLevels = [
      { value: 'public', label: 'Public Access' },
      { value: 'restricted', label: 'Restricted Access' },
      { value: 'private', label: 'Private Access' },
      { value: 'secure', label: 'Secure Access' }
    ]

    // Form submission
    const handleSubmit = async () => {
      try {
        loading.value = true
        errors.value = {}

        const url = props.floor
          ? `/api/floors/${props.floor.id}`
          : '/api/floors'
        
        const method = props.floor ? 'put' : 'post'
        
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
      statusOptions,
      floorTypes,
      accessLevels,
      handleSubmit
    }
  }
}
</script> 