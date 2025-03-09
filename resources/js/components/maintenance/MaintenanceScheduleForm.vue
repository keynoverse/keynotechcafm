<template>
  <BaseModal
    :model-value="show"
    :title="schedule ? 'Edit Maintenance Schedule' : 'Create Maintenance Schedule'"
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
              v-model="form.title"
              label="Title"
              required
              :error="errors.title"
            />
            <BaseSelect
              v-model="form.asset_id"
              label="Asset"
              :options="assetOptions"
              required
              :error="errors.asset_id"
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

        <!-- Schedule Details -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Schedule Details</h3>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <BaseInput
              v-model="form.scheduled_date"
              label="Start Date"
              type="date"
              required
              :error="errors.scheduled_date"
            />
            <BaseSelect
              v-model="form.frequency_unit"
              label="Frequency Unit"
              :options="frequencyUnitOptions"
              required
              :error="errors.frequency_unit"
            />
            <BaseInput
              v-model="form.frequency"
              label="Frequency"
              type="number"
              min="1"
              required
              :error="errors.frequency"
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

        <!-- Assignment -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Assignment</h3>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <BaseSelect
              v-model="form.assigned_to"
              label="Assigned To"
              :options="userOptions"
              required
              :error="errors.assigned_to"
            />
            <BaseSelect
              v-model="form.status"
              label="Status"
              :options="statusOptions"
              required
              :error="errors.status"
            />
          </div>
        </section>

        <!-- Maintenance Details -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Maintenance Details</h3>
          <div class="grid grid-cols-1 gap-4">
            <BaseInput
              v-model="form.estimated_duration"
              label="Estimated Duration (hours)"
              type="number"
              min="0.5"
              step="0.5"
              :error="errors.estimated_duration"
            />
            <BaseInput
              v-model="form.required_tools"
              label="Required Tools"
              type="textarea"
              rows="2"
              :error="errors.required_tools"
            />
            <BaseInput
              v-model="form.parts_needed"
              label="Parts Needed"
              type="textarea"
              rows="2"
              :error="errors.parts_needed"
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

        <!-- Procedures -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Procedures</h3>
          <div class="grid grid-cols-1 gap-4">
            <BaseInput
              v-model="form.pre_check_procedures"
              label="Pre-Check Procedures"
              type="textarea"
              rows="3"
              :error="errors.pre_check_procedures"
            />
            <BaseInput
              v-model="form.procedures"
              label="Maintenance Procedures"
              type="textarea"
              rows="3"
              required
              :error="errors.procedures"
            />
            <BaseInput
              v-model="form.post_check_procedures"
              label="Post-Check Procedures"
              type="textarea"
              rows="3"
              :error="errors.post_check_procedures"
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
            {{ schedule ? 'Update' : 'Create' }}
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
  name: 'MaintenanceScheduleForm',

  props: {
    show: {
      type: Boolean,
      required: true
    },
    schedule: {
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
      title: '',
      asset_id: '',
      description: '',
      scheduled_date: '',
      frequency: '',
      frequency_unit: '',
      priority: '',
      status: '',
      assigned_to: '',
      estimated_duration: '',
      required_tools: '',
      parts_needed: '',
      safety_requirements: '',
      pre_check_procedures: '',
      procedures: '',
      post_check_procedures: ''
    })

    const errors = ref({})
    const saving = ref(false)

    // Options
    const statusOptions = [
      { value: 'scheduled', label: 'Scheduled' },
      { value: 'in_progress', label: 'In Progress' },
      { value: 'completed', label: 'Completed' },
      { value: 'overdue', label: 'Overdue' },
      { value: 'cancelled', label: 'Cancelled' }
    ]

    const priorityOptions = [
      { value: 'low', label: 'Low' },
      { value: 'medium', label: 'Medium' },
      { value: 'high', label: 'High' },
      { value: 'urgent', label: 'Urgent' }
    ]

    const frequencyUnitOptions = [
      { value: 'days', label: 'Days' },
      { value: 'weeks', label: 'Weeks' },
      { value: 'months', label: 'Months' },
      { value: 'years', label: 'Years' }
    ]

    const assetOptions = ref([])
    const userOptions = ref([])

    // Methods
    const fetchOptions = async () => {
      try {
        const [assets, users] = await Promise.all([
          api.get('/api/assets'),
          api.get('/api/users')
        ])

        assetOptions.value = assets.data.map(asset => ({
          value: asset.id,
          label: `${asset.code} - ${asset.name}`
        }))

        userOptions.value = users.data.map(user => ({
          value: user.id,
          label: user.name
        }))
      } catch (error) {
        showToast('Error fetching options', 'error')
      }
    }

    const handleSubmit = async () => {
      try {
        saving.value = true
        errors.value = {}

        const endpoint = props.schedule
          ? `/api/maintenance-schedules/${props.schedule.id}`
          : '/api/maintenance-schedules'

        const method = props.schedule ? 'put' : 'post'
        await api[method](endpoint, form.value)

        showToast(
          props.schedule
            ? 'Maintenance schedule updated successfully'
            : 'Maintenance schedule created successfully'
        )
        emit('saved')
      } catch (error) {
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
        }
        showToast(
          props.schedule
            ? 'Error updating maintenance schedule'
            : 'Error creating maintenance schedule',
          'error'
        )
      } finally {
        saving.value = false
      }
    }

    // Initialize form if editing
    if (props.schedule) {
      Object.assign(form.value, props.schedule)
    }

    // Fetch options on mount
    onMounted(fetchOptions)

    return {
      form,
      errors,
      saving,
      statusOptions,
      priorityOptions,
      frequencyUnitOptions,
      assetOptions,
      userOptions,
      handleSubmit
    }
  }
}
</script> 