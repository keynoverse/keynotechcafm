<template>
  <BaseModal
    :model-value="show"
    title="Generate Report"
    size="lg"
    @update:model-value="$emit('update:show', $event)"
  >
    <form @submit.prevent="handleSubmit">
      <div class="space-y-6">
        <!-- Basic Information -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Report Information</h3>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <BaseInput
              v-model="form.name"
              label="Report Name"
              required
              :error="errors.name"
            />
            <BaseSelect
              v-model="form.type"
              label="Report Type"
              :options="typeOptions"
              required
              :error="errors.type"
            />
            <BaseSelect
              v-model="form.category"
              label="Category"
              :options="categoryOptions"
              required
              :error="errors.category"
            />
            <BaseInput
              v-model="form.description"
              label="Description"
              type="textarea"
              rows="3"
              :error="errors.description"
            />
          </div>
        </section>

        <!-- Date Range -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Date Range</h3>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <BaseInput
              v-model="form.start_date"
              label="Start Date"
              type="date"
              required
              :error="errors.start_date"
            />
            <BaseInput
              v-model="form.end_date"
              label="End Date"
              type="date"
              required
              :error="errors.end_date"
            />
          </div>
        </section>

        <!-- Filters -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>
          <div class="grid grid-cols-1 gap-4">
            <!-- Asset Report Filters -->
            <template v-if="form.type === 'asset'">
              <BaseSelect
                v-model="form.filters.asset_category"
                label="Asset Category"
                :options="assetCategoryOptions"
                :error="errors['filters.asset_category']"
              />
              <BaseSelect
                v-model="form.filters.status"
                label="Asset Status"
                :options="assetStatusOptions"
                :error="errors['filters.status']"
              />
              <BaseSelect
                v-model="form.filters.location"
                label="Location"
                :options="locationOptions"
                :error="errors['filters.location']"
              />
            </template>

            <!-- Maintenance Report Filters -->
            <template v-if="form.type === 'maintenance'">
              <BaseSelect
                v-model="form.filters.maintenance_type"
                label="Maintenance Type"
                :options="maintenanceTypeOptions"
                :error="errors['filters.maintenance_type']"
              />
              <BaseSelect
                v-model="form.filters.priority"
                label="Priority"
                :options="priorityOptions"
                :error="errors['filters.priority']"
              />
              <BaseSelect
                v-model="form.filters.status"
                label="Status"
                :options="maintenanceStatusOptions"
                :error="errors['filters.status']"
              />
            </template>

            <!-- Work Order Report Filters -->
            <template v-if="form.type === 'work_order'">
              <BaseSelect
                v-model="form.filters.work_order_type"
                label="Work Order Type"
                :options="workOrderTypeOptions"
                :error="errors['filters.work_order_type']"
              />
              <BaseSelect
                v-model="form.filters.priority"
                label="Priority"
                :options="priorityOptions"
                :error="errors['filters.priority']"
              />
              <BaseSelect
                v-model="form.filters.status"
                label="Status"
                :options="workOrderStatusOptions"
                :error="errors['filters.status']"
              />
              <BaseSelect
                v-model="form.filters.assigned_to"
                label="Assigned To"
                :options="userOptions"
                :error="errors['filters.assigned_to']"
              />
            </template>

            <!-- Cost Analysis Filters -->
            <template v-if="form.type === 'cost'">
              <BaseSelect
                v-model="form.filters.cost_type"
                label="Cost Type"
                :options="costTypeOptions"
                :error="errors['filters.cost_type']"
              />
              <BaseSelect
                v-model="form.filters.category"
                label="Cost Category"
                :options="costCategoryOptions"
                :error="errors['filters.category']"
              />
              <BaseSelect
                v-model="form.filters.group_by"
                label="Group By"
                :options="costGroupByOptions"
                :error="errors['filters.group_by']"
              />
            </template>

            <!-- Performance Report Filters -->
            <template v-if="form.type === 'performance'">
              <BaseSelect
                v-model="form.filters.metric"
                label="Performance Metric"
                :options="performanceMetricOptions"
                :error="errors['filters.metric']"
              />
              <BaseSelect
                v-model="form.filters.group_by"
                label="Group By"
                :options="performanceGroupByOptions"
                :error="errors['filters.group_by']"
              />
            </template>
          </div>
        </section>

        <!-- Format Options -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Format Options</h3>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <BaseSelect
              v-model="form.format"
              label="File Format"
              :options="formatOptions"
              required
              :error="errors.format"
            />
            <BaseSelect
              v-model="form.template"
              label="Template"
              :options="templateOptions"
              required
              :error="errors.template"
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
            :loading="generating"
          >
            Generate
          </BaseButton>
        </div>
      </template>
    </form>
  </BaseModal>
</template>

<script>
import { ref } from 'vue'
import { useToast } from '@/composables/useToast'
import { useApi } from '@/composables/useApi'

export default {
  name: 'ReportGenerator',

  props: {
    show: {
      type: Boolean,
      required: true
    }
  },

  emits: ['update:show', 'generated'],

  setup(props, { emit }) {
    const { showToast } = useToast()
    const api = useApi()

    // Form state
    const form = ref({
      name: '',
      type: '',
      category: '',
      description: '',
      start_date: '',
      end_date: '',
      format: 'pdf',
      template: 'standard',
      filters: {}
    })

    const errors = ref({})
    const generating = ref(false)

    // Options
    const typeOptions = [
      { value: 'asset', label: 'Asset Report' },
      { value: 'maintenance', label: 'Maintenance Report' },
      { value: 'work_order', label: 'Work Order Report' },
      { value: 'cost', label: 'Cost Analysis' },
      { value: 'performance', label: 'Performance Report' }
    ]

    const categoryOptions = [
      { value: 'daily', label: 'Daily Report' },
      { value: 'weekly', label: 'Weekly Report' },
      { value: 'monthly', label: 'Monthly Report' },
      { value: 'quarterly', label: 'Quarterly Report' },
      { value: 'annual', label: 'Annual Report' },
      { value: 'custom', label: 'Custom Report' }
    ]

    const formatOptions = [
      { value: 'pdf', label: 'PDF Document' },
      { value: 'xlsx', label: 'Excel Spreadsheet' },
      { value: 'csv', label: 'CSV File' }
    ]

    const templateOptions = [
      { value: 'standard', label: 'Standard Template' },
      { value: 'detailed', label: 'Detailed Template' },
      { value: 'summary', label: 'Summary Template' },
      { value: 'compact', label: 'Compact Template' }
    ]

    const assetCategoryOptions = [
      { value: 'all', label: 'All Categories' },
      { value: 'mechanical', label: 'Mechanical' },
      { value: 'electrical', label: 'Electrical' },
      { value: 'plumbing', label: 'Plumbing' },
      { value: 'hvac', label: 'HVAC' }
    ]

    const assetStatusOptions = [
      { value: 'all', label: 'All Statuses' },
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
      { value: 'maintenance', label: 'Under Maintenance' },
      { value: 'repair', label: 'Under Repair' }
    ]

    const locationOptions = [
      { value: 'all', label: 'All Locations' },
      { value: 'building', label: 'By Building' },
      { value: 'floor', label: 'By Floor' },
      { value: 'space', label: 'By Space' }
    ]

    const maintenanceTypeOptions = [
      { value: 'all', label: 'All Types' },
      { value: 'preventive', label: 'Preventive' },
      { value: 'corrective', label: 'Corrective' },
      { value: 'emergency', label: 'Emergency' }
    ]

    const maintenanceStatusOptions = [
      { value: 'all', label: 'All Statuses' },
      { value: 'scheduled', label: 'Scheduled' },
      { value: 'in_progress', label: 'In Progress' },
      { value: 'completed', label: 'Completed' },
      { value: 'overdue', label: 'Overdue' }
    ]

    const workOrderTypeOptions = [
      { value: 'all', label: 'All Types' },
      { value: 'repair', label: 'Repair' },
      { value: 'maintenance', label: 'Maintenance' },
      { value: 'inspection', label: 'Inspection' },
      { value: 'installation', label: 'Installation' }
    ]

    const workOrderStatusOptions = [
      { value: 'all', label: 'All Statuses' },
      { value: 'open', label: 'Open' },
      { value: 'in_progress', label: 'In Progress' },
      { value: 'completed', label: 'Completed' },
      { value: 'cancelled', label: 'Cancelled' }
    ]

    const priorityOptions = [
      { value: 'all', label: 'All Priorities' },
      { value: 'low', label: 'Low' },
      { value: 'medium', label: 'Medium' },
      { value: 'high', label: 'High' },
      { value: 'urgent', label: 'Urgent' }
    ]

    const costTypeOptions = [
      { value: 'all', label: 'All Types' },
      { value: 'maintenance', label: 'Maintenance' },
      { value: 'repair', label: 'Repair' },
      { value: 'replacement', label: 'Replacement' },
      { value: 'operation', label: 'Operation' }
    ]

    const costCategoryOptions = [
      { value: 'all', label: 'All Categories' },
      { value: 'labor', label: 'Labor' },
      { value: 'parts', label: 'Parts' },
      { value: 'services', label: 'Services' },
      { value: 'utilities', label: 'Utilities' }
    ]

    const costGroupByOptions = [
      { value: 'type', label: 'By Type' },
      { value: 'category', label: 'By Category' },
      { value: 'asset', label: 'By Asset' },
      { value: 'location', label: 'By Location' }
    ]

    const performanceMetricOptions = [
      { value: 'uptime', label: 'Uptime' },
      { value: 'mtbf', label: 'Mean Time Between Failures' },
      { value: 'mttr', label: 'Mean Time To Repair' },
      { value: 'availability', label: 'Availability' },
      { value: 'efficiency', label: 'Efficiency' }
    ]

    const performanceGroupByOptions = [
      { value: 'asset', label: 'By Asset' },
      { value: 'category', label: 'By Category' },
      { value: 'location', label: 'By Location' },
      { value: 'time', label: 'By Time Period' }
    ]

    const userOptions = ref([])

    // Methods
    const handleSubmit = async () => {
      try {
        generating.value = true
        errors.value = {}

        const response = await api.post('/api/reports/generate', form.value)
        emit('generated', response.data)
      } catch (error) {
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
        }
        showToast('Error generating report', 'error')
      } finally {
        generating.value = false
      }
    }

    return {
      form,
      errors,
      generating,
      typeOptions,
      categoryOptions,
      formatOptions,
      templateOptions,
      assetCategoryOptions,
      assetStatusOptions,
      locationOptions,
      maintenanceTypeOptions,
      maintenanceStatusOptions,
      workOrderTypeOptions,
      workOrderStatusOptions,
      priorityOptions,
      costTypeOptions,
      costCategoryOptions,
      costGroupByOptions,
      performanceMetricOptions,
      performanceGroupByOptions,
      userOptions,
      handleSubmit
    }
  }
}
</script> 