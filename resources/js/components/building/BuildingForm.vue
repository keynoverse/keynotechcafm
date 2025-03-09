<template>
  <BaseModal
    :model-value="show"
    :title="building ? 'Edit Building' : 'Create Building'"
    size="lg"
    @update:model-value="$emit('update:show', $event)"
  >
    <form @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <!-- Building Code -->
        <BaseInput
          v-model="form.code"
          label="Building Code"
          placeholder="e.g., BLD001"
          required
          :error="errors.code"
        />

        <!-- Building Name -->
        <BaseInput
          v-model="form.name"
          label="Building Name"
          placeholder="e.g., Main Office Building"
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
            placeholder="Enter building description"
            :error="errors.description"
          />
        </div>

        <!-- Address -->
        <div class="sm:col-span-2">
          <BaseInput
            v-model="form.address"
            label="Address"
            placeholder="Enter building address"
            required
            :error="errors.address"
          />
        </div>

        <!-- City -->
        <BaseInput
          v-model="form.city"
          label="City"
          placeholder="Enter city"
          required
          :error="errors.city"
        />

        <!-- State/Province -->
        <BaseInput
          v-model="form.state"
          label="State/Province"
          placeholder="Enter state/province"
          required
          :error="errors.state"
        />

        <!-- Country -->
        <BaseInput
          v-model="form.country"
          label="Country"
          placeholder="Enter country"
          required
          :error="errors.country"
        />

        <!-- Postal Code -->
        <BaseInput
          v-model="form.postal_code"
          label="Postal Code"
          placeholder="Enter postal code"
          required
          :error="errors.postal_code"
        />

        <!-- Total Floors -->
        <BaseInput
          v-model.number="form.total_floors"
          type="number"
          label="Total Floors"
          min="1"
          required
          :error="errors.total_floors"
        />

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

        <!-- Year Built -->
        <BaseInput
          v-model.number="form.year_built"
          type="number"
          label="Year Built"
          :min="1800"
          :max="new Date().getFullYear()"
          required
          :error="errors.year_built"
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
          
          <!-- Construction Type -->
          <BaseSelect
            v-model="form.metadata.construction_type"
            label="Construction Type"
            :options="constructionTypes"
            required
            :error="errors['metadata.construction_type']"
          />

          <!-- Occupancy Type -->
          <BaseSelect
            v-model="form.metadata.occupancy_type"
            label="Occupancy Type"
            :options="occupancyTypes"
            required
            :error="errors['metadata.occupancy_type']"
          />

          <!-- Facilities Manager -->
          <div class="mt-4 p-4 bg-gray-50 rounded-md">
            <h5 class="text-sm font-medium text-gray-900 mb-3">Facilities Manager</h5>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <BaseInput
                v-model="form.metadata.facilities_manager.name"
                label="Name"
                placeholder="Enter manager name"
                :error="errors['metadata.facilities_manager.name']"
              />
              <BaseInput
                v-model="form.metadata.facilities_manager.phone"
                label="Phone"
                placeholder="Enter phone number"
                :error="errors['metadata.facilities_manager.phone']"
              />
              <BaseInput
                v-model="form.metadata.facilities_manager.email"
                type="email"
                label="Email"
                placeholder="Enter email address"
                :error="errors['metadata.facilities_manager.email']"
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
          {{ building ? 'Update' : 'Create' }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>

<script>
import { ref, computed } from 'vue'
import axios from 'axios'

export default {
  name: 'BuildingForm',
  props: {
    show: {
      type: Boolean,
      required: true
    },
    building: {
      type: Object,
      default: null
    }
  },
  emits: ['update:show', 'saved', 'closed'],
  setup(props, { emit }) {
    const loading = ref(false)
    const errors = ref({})

    // Form state
    const form = ref({
      code: '',
      name: '',
      description: '',
      address: '',
      city: '',
      state: '',
      country: '',
      postal_code: '',
      total_floors: 1,
      total_area: 0,
      year_built: new Date().getFullYear(),
      status: 'active',
      metadata: {
        construction_type: '',
        occupancy_type: '',
        facilities_manager: {
          name: '',
          phone: '',
          email: ''
        }
      }
    })

    // Initialize form with building data if editing
    if (props.building) {
      form.value = {
        ...props.building,
        metadata: {
          ...form.value.metadata,
          ...props.building.metadata
        }
      }
    }

    // Options for select inputs
    const statusOptions = [
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
      { value: 'maintenance', label: 'Under Maintenance' },
      { value: 'renovation', label: 'Under Renovation' }
    ]

    const constructionTypes = [
      { value: 'Steel', label: 'Steel' },
      { value: 'Concrete', label: 'Concrete' },
      { value: 'Wood Frame', label: 'Wood Frame' },
      { value: 'Masonry', label: 'Masonry' },
      { value: 'Mixed', label: 'Mixed' }
    ]

    const occupancyTypes = [
      { value: 'Commercial', label: 'Commercial' },
      { value: 'Residential', label: 'Residential' },
      { value: 'Mixed Use', label: 'Mixed Use' },
      { value: 'Industrial', label: 'Industrial' },
      { value: 'Institutional', label: 'Institutional' }
    ]

    // Form submission
    const handleSubmit = async () => {
      try {
        loading.value = true
        errors.value = {}

        const url = props.building
          ? `/api/buildings/${props.building.id}`
          : '/api/buildings'
        
        const method = props.building ? 'put' : 'post'
        
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
      statusOptions,
      constructionTypes,
      occupancyTypes,
      handleSubmit
    }
  }
}
</script> 