<template>
  <BaseModal
    :model-value="show"
    :title="workOrder ? 'Edit Work Order' : 'Create Work Order'"
    size="xl"
    @update:model-value="$emit('update:show', $event)"
  >
    <form @submit.prevent="handleSubmit">
      <div class="space-y-6">
        <!-- Basic Information -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <BaseInput
              v-model="form.code"
              label="Work Order Code"
              required
              :error="errors.code"
            />
            <BaseInput
              v-model="form.title"
              label="Title"
              required
              :error="errors.title"
            />
            <div class="sm:col-span-2">
              <BaseInput
                v-model="form.description"
                label="Description"
                type="textarea"
                rows="3"
                required
                :error="errors.description"
              />
            </div>
          </div>
        </section>

        <!-- Status and Priority -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Status and Priority</h3>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <BaseSelect
              v-model="form.status"
              label="Status"
              :options="statusOptions"
              required
              :error="errors.status"
            />
            <BaseSelect
              v-model="form.priority"
              label="Priority"
              :options="priorityOptions"
              required
              :error="errors.priority"
            />
          </div>
        </section>

        <!-- Assignment and Scheduling -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Assignment and Scheduling</h3>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <BaseSelect
              v-model="form.assigned_to"
              label="Assigned To"
              :options="userOptions"
              required
              :error="errors.assigned_to"
            />
            <BaseInput
              v-model="form.due_date"
              label="Due Date"
              type="date"
              required
              :error="errors.due_date"
            />
            <BaseInput
              v-model="form.estimated_hours"
              label="Estimated Hours"
              type="number"
              min="0"
              step="0.5"
              :error="errors.estimated_hours"
            />
            <BaseSelect
              v-model="form.category"
              label="Category"
              :options="categoryOptions"
              :error="errors.category"
            />
          </div>
        </section>

        <!-- Asset and Location -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Asset and Location</h3>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <BaseSelect
              v-model="form.asset_id"
              label="Asset"
              :options="assetOptions"
              :error="errors.asset_id"
            />
            <BaseSelect
              v-model="form.building_id"
              label="Building"
              :options="buildingOptions"
              @update:model-value="handleBuildingChange"
              :error="errors.building_id"
            />
            <BaseSelect
              v-model="form.floor_id"
              label="Floor"
              :options="floorOptions"
              :disabled="!form.building_id"
              @update:model-value="handleFloorChange"
              :error="errors.floor_id"
            />
            <BaseSelect
              v-model="form.space_id"
              label="Space"
              :options="spaceOptions"
              :disabled="!form.floor_id"
              :error="errors.space_id"
            />
          </div>
        </section>

        <!-- Additional Details -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Details</h3>
          <div class="grid grid-cols-1 gap-4">
            <BaseInput
              v-model="form.required_parts"
              label="Required Parts"
              type="textarea"
              rows="2"
              :error="errors.required_parts"
            />
            <BaseInput
              v-model="form.required_tools"
              label="Required Tools"
              type="textarea"
              rows="2"
              :error="errors.required_tools"
            />
            <BaseInput
              v-model="form.safety_requirements"
              label="Safety Requirements"
              type="textarea"
              rows="2"
              :error="errors.safety_requirements"
            />
          </div>
        </section>
      </div>

      <!-- Modal Footer -->
      <template #footer>
        <div class="flex justify-end space-x-3">
          <BaseButton
            variant="secondary"
            @click="$emit('update:show', false)"
          >
            Cancel
          </BaseButton>
          <BaseButton
            type="submit"
            variant="primary"
            :loading="saving"
          >
            {{ workOrder ? 'Update' : 'Create' }}
          </BaseButton>
        </div>
      </template>
    </form>
  </BaseModal>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import { useApi } from '@/composables/useApi'

export default {
  name: 'WorkOrderForm',

  props: {
    show: {
      type: Boolean,
      required: true
    },
    workOrder: {
      type: Object,
      default: null
    }
  },

  emits: ['update:show', 'saved'],

  setup(props) {
    const { showToast } = useToast()
    const api = useApi()

    // Form state
    const form = ref({
      code: '',
      title: '',
      description: '',
      status: '',
      priority: '',
      assigned_to: '',
      due_date: '',
      estimated_hours: '',
      category: '',
      asset_id: '',
      building_id: '',
      floor_id: '',
      space_id: '',
      required_parts: '',
      required_tools: '',
      safety_requirements: ''
    })

    const errors = ref({})
    const saving = ref(false)

    // Options
    const statusOptions = [
      { value: 'open', label: 'Open' },
      { value: 'in_progress', label: 'In Progress' },
      { value: 'on_hold', label: 'On Hold' },
      { value: 'completed', label: 'Completed' },
      { value: 'cancelled', label: 'Cancelled' }
    ]

    const priorityOptions = [
      { value: 'low', label: 'Low' },
      { value: 'medium', label: 'Medium' },
      { value: 'high', label: 'High' },
      { value: 'urgent', label: 'Urgent' }
    ]

    const categoryOptions = [
      { value: 'preventive', label: 'Preventive Maintenance' },
      { value: 'corrective', label: 'Corrective Maintenance' },
      { value: 'emergency', label: 'Emergency Repair' },
      { value: 'inspection', label: 'Inspection' },
      { value: 'installation', label: 'Installation' },
      { value: 'upgrade', label: 'Upgrade' }
    ]

    const userOptions = ref([])
    const assetOptions = ref([])
    const buildingOptions = ref([])
    const floorOptions = ref([])
    const spaceOptions = ref([])

    // Methods
    const fetchOptions = async () => {
      try {
        const [users, assets, buildings] = await Promise.all([
          api.get('/api/users'),
          api.get('/api/assets'),
          api.get('/api/buildings')
        ])

        userOptions.value = users.data.map(user => ({
          value: user.id,
          label: user.name
        }))

        assetOptions.value = assets.data.map(asset => ({
          value: asset.id,
          label: `${asset.code} - ${asset.name}`
        }))

        buildingOptions.value = buildings.data.map(building => ({
          value: building.id,
          label: building.name
        }))
      } catch (error) {
        showToast('Error fetching options', 'error')
      }
    }

    const handleBuildingChange = async () => {
      form.value.floor_id = ''
      form.value.space_id = ''
      floorOptions.value = []
      spaceOptions.value = []

      if (form.value.building_id) {
        try {
          const response = await api.get(`/api/buildings/${form.value.building_id}/floors`)
          floorOptions.value = response.data.map(floor => ({
            value: floor.id,
            label: floor.name
          }))
        } catch (error) {
          showToast('Error fetching floors', 'error')
        }
      }
    }

    const handleFloorChange = async () => {
      form.value.space_id = ''
      spaceOptions.value = []

      if (form.value.floor_id) {
        try {
          const response = await api.get(`/api/floors/${form.value.floor_id}/spaces`)
          spaceOptions.value = response.data.map(space => ({
            value: space.id,
            label: space.name
          }))
        } catch (error) {
          showToast('Error fetching spaces', 'error')
        }
      }
    }

    const handleSubmit = async () => {
      try {
        saving.value = true
        errors.value = {}

        const endpoint = props.workOrder
          ? `/api/work-orders/${props.workOrder.id}`
          : '/api/work-orders'

        const method = props.workOrder ? 'put' : 'post'
        await api[method](endpoint, form.value)

        showToast(
          props.workOrder
            ? 'Work order updated successfully'
            : 'Work order created successfully'
        )
        emit('saved')
      } catch (error) {
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
        }
        showToast(
          props.workOrder
            ? 'Error updating work order'
            : 'Error creating work order',
          'error'
        )
      } finally {
        saving.value = false
      }
    }

    // Initialize form if editing
    if (props.workOrder) {
      Object.assign(form.value, props.workOrder)
    }

    // Fetch options on mount
    onMounted(async () => {
      await fetchOptions()
      if (form.value.building_id) {
        await handleBuildingChange()
      }
      if (form.value.floor_id) {
        await handleFloorChange()
      }
    })

    return {
      form,
      errors,
      saving,
      statusOptions,
      priorityOptions,
      categoryOptions,
      userOptions,
      assetOptions,
      buildingOptions,
      floorOptions,
      spaceOptions,
      handleBuildingChange,
      handleFloorChange,
      handleSubmit
    }
  }
}
</script> 