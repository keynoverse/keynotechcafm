<template>
  <div>
    <BaseCard>
      <!-- Card Header -->
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-900">Buildings</h3>
          <BaseButton
            v-if="can.create"
            variant="primary"
            @click="showCreateModal = true"
          >
            Add Building
          </BaseButton>
        </div>
      </template>

      <!-- Search and Filters -->
      <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <BaseInput
          v-model="filters.search"
          placeholder="Search buildings..."
          clearable
          :leading-icon="SearchIcon"
        />
        <BaseSelect
          v-model="filters.status"
          :options="statusOptions"
          placeholder="Filter by status"
        />
      </div>

      <!-- Buildings Table -->
      <BaseTable
        :items="filteredBuildings"
        :columns="columns"
        :selectable="can.delete"
        pagination
        @update:selected="selectedIds = $event"
      >
        <!-- Status Column -->
        <template #status="{ value }">
          <span
            :class="[
              'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
              statusClasses[value] || ''
            ]"
          >
            {{ value }}
          </span>
        </template>

        <!-- Actions Column -->
        <template #actions="{ item }">
          <div class="flex items-center space-x-2">
            <BaseButton
              v-if="can.view"
              variant="secondary"
              size="sm"
              :icon="EyeIcon"
              @click="viewBuilding(item)"
            >
              View
            </BaseButton>
            <BaseButton
              v-if="can.edit"
              variant="secondary"
              size="sm"
              :icon="PencilIcon"
              @click="editBuilding(item)"
            >
              Edit
            </BaseButton>
            <BaseButton
              v-if="can.delete"
              variant="danger"
              size="sm"
              :icon="TrashIcon"
              @click="deleteBuilding(item)"
            >
              Delete
            </BaseButton>
          </div>
        </template>

        <!-- Empty State -->
        <template #empty>
          <div class="text-center">
            <svg
              class="mx-auto h-12 w-12 text-gray-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
              />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No buildings</h3>
            <p class="mt-1 text-sm text-gray-500">
              Get started by creating a new building.
            </p>
            <div class="mt-6">
              <BaseButton
                v-if="can.create"
                variant="primary"
                :icon="PlusIcon"
                @click="showCreateModal = true"
              >
                Add Building
              </BaseButton>
            </div>
          </div>
        </template>
      </BaseTable>
    </BaseCard>

    <!-- Create/Edit Modal -->
    <BuildingForm
      v-if="showCreateModal || editingBuilding"
      v-model:show="showCreateModal"
      :building="editingBuilding"
      @saved="handleSaved"
      @closed="handleClosed"
    />

    <!-- View Modal -->
    <BuildingDetails
      v-if="viewingBuilding"
      v-model:show="showViewModal"
      :building="viewingBuilding"
      @closed="viewingBuilding = null"
    />

    <!-- Delete Confirmation Modal -->
    <BaseModal
      v-model="showDeleteModal"
      title="Delete Building"
      size="sm"
    >
      <p class="text-sm text-gray-500">
        Are you sure you want to delete this building? This action cannot be undone.
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
            @click="confirmDelete"
          >
            Delete
          </BaseButton>
        </div>
      </template>
    </BaseModal>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import {
  EyeIcon,
  PencilIcon,
  PlusIcon,
  SearchIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'
import { usePermissions } from '@/composables/usePermissions'
import BuildingForm from './BuildingForm.vue'
import BuildingDetails from './BuildingDetails.vue'

export default {
  name: 'BuildingList',
  components: {
    BuildingForm,
    BuildingDetails
  },
  props: {
    buildings: {
      type: Array,
      required: true
    }
  },
  emits: ['refresh'],
  setup(props, { emit }) {
    // Permissions
    const { can } = usePermissions('buildings')

    // Table Configuration
    const columns = [
      { key: 'code', label: 'Code', sortable: true },
      { key: 'name', label: 'Name', sortable: true },
      { key: 'total_floors', label: 'Floors', sortable: true },
      { key: 'total_area', label: 'Area (sqm)', sortable: true },
      { key: 'status', label: 'Status', sortable: true },
      { key: 'actions', label: 'Actions' }
    ]

    const statusClasses = {
      active: 'bg-green-100 text-green-800',
      inactive: 'bg-gray-100 text-gray-800',
      maintenance: 'bg-yellow-100 text-yellow-800',
      renovation: 'bg-blue-100 text-blue-800'
    }

    const statusOptions = [
      { value: '', label: 'All Statuses' },
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
      { value: 'maintenance', label: 'Under Maintenance' },
      { value: 'renovation', label: 'Under Renovation' }
    ]

    // State
    const filters = ref({
      search: '',
      status: ''
    })

    const selectedIds = ref([])
    const showCreateModal = ref(false)
    const showViewModal = ref(false)
    const showDeleteModal = ref(false)
    const editingBuilding = ref(null)
    const viewingBuilding = ref(null)
    const deletingBuilding = ref(null)
    const deleting = ref(false)

    // Computed
    const filteredBuildings = computed(() => {
      let result = [...props.buildings]

      // Apply search filter
      if (filters.value.search) {
        const search = filters.value.search.toLowerCase()
        result = result.filter(building =>
          building.name.toLowerCase().includes(search) ||
          building.code.toLowerCase().includes(search)
        )
      }

      // Apply status filter
      if (filters.value.status) {
        result = result.filter(building => building.status === filters.value.status)
      }

      return result
    })

    // Methods
    const viewBuilding = (building) => {
      viewingBuilding.value = building
      showViewModal.value = true
    }

    const editBuilding = (building) => {
      editingBuilding.value = building
      showCreateModal.value = true
    }

    const deleteBuilding = (building) => {
      deletingBuilding.value = building
      showDeleteModal.value = true
    }

    const handleSaved = () => {
      showCreateModal.value = false
      editingBuilding.value = null
      emit('refresh')
    }

    const handleClosed = () => {
      showCreateModal.value = false
      editingBuilding.value = null
    }

    const confirmDelete = async () => {
      if (!deletingBuilding.value) return

      try {
        deleting.value = true
        await axios.delete(`/api/buildings/${deletingBuilding.value.id}`)
        showDeleteModal.value = false
        deletingBuilding.value = null
        emit('refresh')
      } catch (error) {
        console.error('Error deleting building:', error)
      } finally {
        deleting.value = false
      }
    }

    return {
      can,
      columns,
      statusClasses,
      statusOptions,
      filters,
      selectedIds,
      showCreateModal,
      showViewModal,
      showDeleteModal,
      editingBuilding,
      viewingBuilding,
      deleting,
      filteredBuildings,
      viewBuilding,
      editBuilding,
      deleteBuilding,
      handleSaved,
      handleClosed,
      confirmDelete,
      // Icons
      EyeIcon,
      PencilIcon,
      PlusIcon,
      SearchIcon,
      TrashIcon
    }
  }
}
</script> 