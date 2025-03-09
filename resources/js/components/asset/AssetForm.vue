<template>
  <BaseModal
    :model-value="show"
    :title="asset ? 'Edit Asset' : 'Create Asset'"
    size="lg"
    @update:model-value="$emit('update:show', $event)"
  >
    <form @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <!-- Asset Code -->
        <BaseInput
          v-model="form.code"
          label="Asset Code"
          placeholder="e.g., AST001"
          required
          :error="errors.code"
        />

        <!-- Asset Name -->
        <BaseInput
          v-model="form.name"
          label="Asset Name"
          placeholder="e.g., Office Laptop"
          required
          :error="errors.name"
        />

        <!-- Category -->
        <BaseSelect
          v-model="form.category_id"
          label="Category"
          :options="categoryOptions"
          required
          :error="errors.category_id"
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
            placeholder="Enter asset description"
            :error="errors.description"
          />
        </div>

        <!-- Location -->
        <div class="sm:col-span-2">
          <h4 class="text-sm font-medium text-gray-900 mb-4">Location</h4>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <BaseSelect
              v-model="form.building_id"
              label="Building"
              :options="buildingOptions"
              :error="errors.building_id"
              @update:model-value="handleBuildingChange"
            />
            <BaseSelect
              v-model="form.floor_id"
              label="Floor"
              :options="floorOptions"
              :disabled="!form.building_id"
              :error="errors.floor_id"
              @update:model-value="handleFloorChange"
            />
            <BaseSelect
              v-model="form.space_id"
              label="Space"
              :options="spaceOptions"
              :disabled="!form.floor_id"
              :error="errors.space_id"
            />
          </div>
        </div>

        <!-- Purchase Information -->
        <div class="sm:col-span-2">
          <h4 class="text-sm font-medium text-gray-900 mb-4">Purchase Information</h4>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <BaseInput
              v-model.number="form.purchase_cost"
              type="number"
              label="Purchase Cost"
              min="0"
              step="0.01"
              :error="errors.purchase_cost"
            />
            <BaseInput
              v-model="form.purchase_date"
              type="date"
              label="Purchase Date"
              :error="errors.purchase_date"
            />
            <BaseInput
              v-model="form.warranty_expiry"
              type="date"
              label="Warranty Expiry"
              :error="errors.warranty_expiry"
            />
          </div>
        </div>

        <!-- Specifications -->
        <div class="sm:col-span-2">
          <h4 class="text-sm font-medium text-gray-900 mb-4">Specifications</h4>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <BaseInput
              v-model="form.metadata.manufacturer"
              label="Manufacturer"
              placeholder="e.g., Dell"
              :error="errors['metadata.manufacturer']"
            />
            <BaseInput
              v-model="form.metadata.model"
              label="Model"
              placeholder="e.g., Latitude 5420"
              :error="errors['metadata.model']"
            />
            <BaseInput
              v-model="form.metadata.serial_number"
              label="Serial Number"
              :error="errors['metadata.serial_number']"
            />
            <BaseInput
              v-model="form.metadata.part_number"
              label="Part Number"
              :error="errors['metadata.part_number']"
            />
          </div>
        </div>

        <!-- Maintenance -->
        <div class="sm:col-span-2">
          <h4 class="text-sm font-medium text-gray-900 mb-4">Maintenance</h4>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <BaseInput
              v-model="form.metadata.maintenance_frequency"
              type="number"
              label="Maintenance Frequency (days)"
              min="0"
              :error="errors['metadata.maintenance_frequency']"
            />
            <BaseInput
              v-model="form.metadata.last_maintenance_date"
              type="date"
              label="Last Maintenance Date"
              :error="errors['metadata.last_maintenance_date']"
            />
          </div>
        </div>

        <!-- Additional Information -->
        <div class="sm:col-span-2">
          <h4 class="text-sm font-medium text-gray-900 mb-4">Additional Information</h4>
          <div class="grid grid-cols-1 gap-4">
            <BaseInput
              v-model="form.metadata.notes"
              label="Notes"
              type="textarea"
              rows="3"
              placeholder="Enter additional notes"
              :error="errors['metadata.notes']"
            />
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
          {{ asset ? 'Update' : 'Create' }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>

<script>
import { ref, computed } from 'vue'
import axios from 'axios'

export default {
  name: 'AssetForm',
  props: {
    show: {
      type: Boolean,
      required: true
    },
    asset: {
      type: Object,
      default: null
    },
    categories: {
      type: Array,
      required: true
    },
    buildings: {
      type: Array,
      required: true
    },
    floors: {
      type: Array,
      required: true
    },
    spaces: {
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
      code: '',
      name: '',
      category_id: '',
      description: '',
      building_id: '',
      floor_id: '',
      space_id: '',
      status: 'active',
      purchase_cost: 0,
      purchase_date: '',
      warranty_expiry: '',
      metadata: {
        manufacturer: '',
        model: '',
        serial_number: '',
        part_number: '',
        maintenance_frequency: 0,
        last_maintenance_date: '',
        notes: ''
      }
    })

    // Initialize form with asset data if editing
    if (props.asset) {
      form.value = {
        ...props.asset,
        metadata: {
          ...form.value.metadata,
          ...props.asset.metadata
        }
      }
    }

    // Options for select inputs
    const categoryOptions = computed(() =>
      props.categories.map(category => ({
        value: category.id,
        label: category.name
      }))
    )

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

    const spaceOptions = computed(() => {
      if (!form.value.floor_id) return []

      return props.spaces
        .filter(space => space.floor_id === form.value.floor_id)
        .map(space => ({
          value: space.id,
          label: space.name
        }))
    })

    const statusOptions = [
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
      { value: 'maintenance', label: 'Under Maintenance' },
      { value: 'repair', label: 'Under Repair' },
      { value: 'disposed', label: 'Disposed' }
    ]

    // Methods
    const handleBuildingChange = () => {
      form.value.floor_id = ''
      form.value.space_id = ''
    }

    const handleFloorChange = () => {
      form.value.space_id = ''
    }

    const handleSubmit = async () => {
      try {
        loading.value = true
        errors.value = {}

        const url = props.asset
          ? `/api/assets/${props.asset.id}`
          : '/api/assets'
        
        const method = props.asset ? 'put' : 'post'
        
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
      categoryOptions,
      buildingOptions,
      floorOptions,
      spaceOptions,
      statusOptions,
      handleBuildingChange,
      handleFloorChange,
      handleSubmit
    }
  }
}
</script> 