<template>
  <BaseCard>
    <!-- Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900">Work Orders</h2>
        <BaseButton
          variant="primary"
          @click="showCreateModal = true"
        >
          Create Work Order
        </BaseButton>
      </div>
    </template>

    <!-- Filters -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <BaseInput
        v-model="filters.search"
        placeholder="Search work orders..."
        type="search"
      >
        <template #prefix>
          <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
          </svg>
        </template>
      </BaseInput>

      <BaseSelect
        v-model="filters.status"
        :options="statusOptions"
        placeholder="Filter by status"
      />

      <BaseSelect
        v-model="filters.priority"
        :options="priorityOptions"
        placeholder="Filter by priority"
      />

      <BaseSelect
        v-model="filters.assignedTo"
        :options="userOptions"
        placeholder="Filter by assignee"
      />
    </div>

    <!-- Table -->
    <BaseTable
      :columns="columns"
      :data="filteredWorkOrders"
      :loading="loading"
      :sort-by="sortBy"
      :sort-desc="sortDesc"
      @update:sort-by="sortBy = $event"
      @update:sort-desc="sortDesc = $event"
    >
      <template #cell-status="{ item }">
        <span
          :class="[
            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
            statusClasses[item.status] || ''
          ]"
        >
          {{ item.status }}
        </span>
      </template>

      <template #cell-priority="{ item }">
        <span
          :class="[
            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
            priorityClasses[item.priority] || ''
          ]"
        >
          {{ item.priority }}
        </span>
      </template>

      <template #cell-due_date="{ item }">
        {{ formatDate(item.due_date) }}
      </template>

      <template #cell-created_at="{ item }">
        {{ formatDate(item.created_at) }}
      </template>

      <template #cell-actions="{ item }">
        <div class="flex items-center space-x-2">
          <BaseButton
            variant="secondary"
            size="sm"
            @click="viewWorkOrder(item)"
          >
            View
          </BaseButton>
          <BaseButton
            variant="secondary"
            size="sm"
            @click="editWorkOrder(item)"
          >
            Edit
          </BaseButton>
          <BaseButton
            variant="danger"
            size="sm"
            @click="confirmDelete(item)"
          >
            Delete
          </BaseButton>
        </div>
      </template>
    </BaseTable>

    <!-- Create/Edit Modal -->
    <WorkOrderForm
      v-if="showCreateModal || showEditModal"
      :show="showCreateModal || showEditModal"
      :work-order="selectedWorkOrder"
      @update:show="closeForm"
      @saved="handleSaved"
    />

    <!-- View Modal -->
    <WorkOrderDetails
      v-if="showViewModal"
      :show="showViewModal"
      :work-order="selectedWorkOrder"
      @update:show="showViewModal = false"
    />

    <!-- Delete Confirmation Modal -->
    <BaseModal
      :model-value="showDeleteModal"
      title="Delete Work Order"
      @update:model-value="showDeleteModal = false"
    >
      <p class="mb-4 text-sm text-gray-500">
        Are you sure you want to delete this work order? This action cannot be undone.
      </p>
      <template #footer>
        <div class="flex justify-end space-x-3">
          <BaseButton
            variant="secondary"
            @click="showDeleteModal = false"
          >
            Cancel
          </BaseButton>
          <BaseButton
            variant="danger"
            :loading="deleting"
            @click="deleteWorkOrder"
          >
            Delete
          </BaseButton>
        </div>
      </template>
    </BaseModal>
  </BaseCard>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import { useApi } from '@/composables/useApi'

export default {
  name: 'WorkOrderList',

  setup() {
    const { showToast } = useToast()
    const api = useApi()

    // State
    const workOrders = ref([])
    const loading = ref(true)
    const deleting = ref(false)
    const showCreateModal = ref(false)
    const showEditModal = ref(false)
    const showViewModal = ref(false)
    const showDeleteModal = ref(false)
    const selectedWorkOrder = ref(null)
    const sortBy = ref('created_at')
    const sortDesc = ref(true)

    // Filters
    const filters = ref({
      search: '',
      status: '',
      priority: '',
      assignedTo: ''
    })

    // Table columns
    const columns = [
      { key: 'code', label: 'Code', sortable: true },
      { key: 'title', label: 'Title', sortable: true },
      { key: 'status', label: 'Status', sortable: true },
      { key: 'priority', label: 'Priority', sortable: true },
      { key: 'assigned_to', label: 'Assigned To', sortable: true },
      { key: 'due_date', label: 'Due Date', sortable: true },
      { key: 'created_at', label: 'Created At', sortable: true },
      { key: 'actions', label: 'Actions', sortable: false }
    ]

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

    const userOptions = ref([])

    // Status and priority classes for badges
    const statusClasses = {
      open: 'bg-blue-100 text-blue-800',
      in_progress: 'bg-yellow-100 text-yellow-800',
      on_hold: 'bg-gray-100 text-gray-800',
      completed: 'bg-green-100 text-green-800',
      cancelled: 'bg-red-100 text-red-800'
    }

    const priorityClasses = {
      low: 'bg-gray-100 text-gray-800',
      medium: 'bg-blue-100 text-blue-800',
      high: 'bg-yellow-100 text-yellow-800',
      urgent: 'bg-red-100 text-red-800'
    }

    // Computed
    const filteredWorkOrders = computed(() => {
      let filtered = [...workOrders.value]

      // Apply search filter
      if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase()
        filtered = filtered.filter(wo =>
          wo.code.toLowerCase().includes(searchTerm) ||
          wo.title.toLowerCase().includes(searchTerm) ||
          wo.description.toLowerCase().includes(searchTerm)
        )
      }

      // Apply status filter
      if (filters.value.status) {
        filtered = filtered.filter(wo => wo.status === filters.value.status)
      }

      // Apply priority filter
      if (filters.value.priority) {
        filtered = filtered.filter(wo => wo.priority === filters.value.priority)
      }

      // Apply assignee filter
      if (filters.value.assignedTo) {
        filtered = filtered.filter(wo => wo.assigned_to === filters.value.assignedTo)
      }

      return filtered
    })

    // Methods
    const fetchWorkOrders = async () => {
      try {
        loading.value = true
        const response = await api.get('/api/work-orders')
        workOrders.value = response.data
      } catch (error) {
        showToast('Error fetching work orders', 'error')
      } finally {
        loading.value = false
      }
    }

    const fetchUsers = async () => {
      try {
        const response = await api.get('/api/users')
        userOptions.value = response.data.map(user => ({
          value: user.id,
          label: user.name
        }))
      } catch (error) {
        showToast('Error fetching users', 'error')
      }
    }

    const viewWorkOrder = (workOrder) => {
      selectedWorkOrder.value = workOrder
      showViewModal.value = true
    }

    const editWorkOrder = (workOrder) => {
      selectedWorkOrder.value = workOrder
      showEditModal.value = true
    }

    const confirmDelete = (workOrder) => {
      selectedWorkOrder.value = workOrder
      showDeleteModal.value = true
    }

    const deleteWorkOrder = async () => {
      try {
        deleting.value = true
        await api.delete(`/api/work-orders/${selectedWorkOrder.value.id}`)
        await fetchWorkOrders()
        showDeleteModal.value = false
        showToast('Work order deleted successfully')
      } catch (error) {
        showToast('Error deleting work order', 'error')
      } finally {
        deleting.value = false
      }
    }

    const closeForm = () => {
      showCreateModal.value = false
      showEditModal.value = false
      selectedWorkOrder.value = null
    }

    const handleSaved = async () => {
      await fetchWorkOrders()
      closeForm()
      showToast('Work order saved successfully')
    }

    const formatDate = (date) => {
      if (!date) return ''
      return new Date(date).toLocaleDateString()
    }

    // Lifecycle hooks
    onMounted(async () => {
      await Promise.all([
        fetchWorkOrders(),
        fetchUsers()
      ])
    })

    return {
      // State
      workOrders,
      loading,
      deleting,
      showCreateModal,
      showEditModal,
      showViewModal,
      showDeleteModal,
      selectedWorkOrder,
      sortBy,
      sortDesc,
      filters,

      // Data
      columns,
      statusOptions,
      priorityOptions,
      userOptions,
      statusClasses,
      priorityClasses,

      // Computed
      filteredWorkOrders,

      // Methods
      viewWorkOrder,
      editWorkOrder,
      confirmDelete,
      deleteWorkOrder,
      closeForm,
      handleSaved,
      formatDate
    }
  }
}
</script> 