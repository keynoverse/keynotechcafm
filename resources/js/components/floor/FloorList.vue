<template>
  <div>
    <BaseCard>
      <!-- Card Header -->
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-900">Floors</h3>
          <BaseButton
            v-if="can.create"
            variant="primary"
            @click="showCreateModal = true"
          >
            Add Floor
          </BaseButton>
        </div>
      </template>

      <!-- Search and Filters -->
      <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <BaseInput
          v-model="filters.search"
          placeholder="Search floors..."
          clearable
          :leading-icon="SearchIcon"
        />
        <BaseSelect
          v-model="filters.building_id"
          :options="buildingOptions"
          placeholder="Filter by building"
        />
        <BaseSelect
          v-model="filters.status"
          :options="statusOptions"
          placeholder="Filter by status"
        />
      </div>

      <!-- Floors Table -->
      <BaseTable
        :items="filteredFloors"
        :columns="columns"
        :selectable="can.delete"
        pagination
        @update:selected="selectedIds = $event"
      >
        <!-- Floor Number Column -->
        <template #floor_number="{ value }">
          {{ value }}
        </template>

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
              @click="viewFloor(item)"
            >
              View
            </BaseButton>
            <BaseButton
              v-if="can.edit"
              variant="secondary"
              size="sm"
              :icon="PencilIcon"
              @click="editFloor(item)"
            >
              Edit
            </BaseButton>
            <BaseButton
              v-if="can.delete"
              variant="danger"
              size="sm"
              :icon="TrashIcon"
              @click="deleteFloor(item)"
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
            <h3 class="mt-2 text-sm font-medium text-gray-900">No floors</h3>
            <p class="mt-1 text-sm text-gray-500">
              Get started by creating a new floor.
            </p>
            <div class="mt-6">
              <BaseButton
                v-if="can.create"
                variant="primary"
                :icon="PlusIcon"
                @click="showCreateModal = true"
              >
                Add Floor
              </BaseButton>
            </div>
          </div>
        </template>
      </BaseTable>
    </BaseCard>

    <!-- Create/Edit Modal -->
    <FloorForm
      v-if="showCreateModal || editingFloor"
      v-model:show="showCreateModal"
      :floor="editingFloor"
      :buildings="buildings"
      @saved="handleSaved"
      @closed="handleClosed"
    />

    <!-- View Modal -->
    <FloorDetails
      v-if="viewingFloor"
      v-model:show="showViewModal"
      :floor="viewingFloor"
      @closed="viewingFloor = null"
    />

    <!-- Delete Confirmation Modal -->
    <BaseModal
      v-model="showDeleteModal"
      title="Delete Floor"
      size="sm"
    >
      <p class="text-sm text-gray-500">
        Are you sure you want to delete this floor? This action cannot be undone.
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
import FloorForm from './FloorForm.vue'
import FloorDetails from './FloorDetails.vue'

export default {
  name: 'FloorList',
  components: {
    FloorForm,
    FloorDetails
  },
  props: {
    floors: {
      type: Array,
      required: true
    },
    buildings: {
      type: Array,
      required: true
    }
  },
  emits: ['refresh'],
  setup(props, { emit }) {
    // Permissions
    const { can } = usePermissions('floors')

    // Table Configuration
    const columns = [
      { key: 'floor_number', label: 'Floor Number', sortable: true },
      { key: 'name', label: 'Name', sortable: true },
      { key: 'building.name', label: 'Building', sortable: true },
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

    const buildingOptions = computed(() => [
      { value: '', label: 'All Buildings' },
      ...props.buildings.map(building => ({
        value: building.id,
        label: building.name
      }))
    ])

    // State
    const filters = ref({
      search: '',
      building_id: '',
      status: ''
    })

    const selectedIds = ref([])
    const showCreateModal = ref(false)
    const showViewModal = ref(false)
    const showDeleteModal = ref(false)
    const editingFloor = ref(null)
    const viewingFloor = ref(null)
    const deletingFloor = ref(null)
    const deleting = ref(false)

    // Computed
    const filteredFloors = computed(() => {
      let result = [...props.floors]

      // Apply search filter
      if (filters.value.search) {
        const search = filters.value.search.toLowerCase()
        result = result.filter(floor =>
          floor.name.toLowerCase().includes(search) ||
          floor.floor_number.toString().includes(search) ||
          floor.building.name.toLowerCase().includes(search)
        )
      }

      // Apply building filter
      if (filters.value.building_id) {
        result = result.filter(floor => floor.building_id === filters.value.building_id)
      }

      // Apply status filter
      if (filters.value.status) {
        result = result.filter(floor => floor.status === filters.value.status)
      }

      return result
    })

    // Methods
    const viewFloor = (floor) => {
      viewingFloor.value = floor
      showViewModal.value = true
    }

    const editFloor = (floor) => {
      editingFloor.value = floor
      showCreateModal.value = true
    }

    const deleteFloor = (floor) => {
      deletingFloor.value = floor
      showDeleteModal.value = true
    }

    const handleSaved = () => {
      showCreateModal.value = false
      editingFloor.value = null
      emit('refresh')
    }

    const handleClosed = () => {
      showCreateModal.value = false
      editingFloor.value = null
    }

    const confirmDelete = async () => {
      if (!deletingFloor.value) return

      try {
        deleting.value = true
        await axios.delete(`/api/floors/${deletingFloor.value.id}`)
        showDeleteModal.value = false
        deletingFloor.value = null
        emit('refresh')
      } catch (error) {
        console.error('Error deleting floor:', error)
      } finally {
        deleting.value = false
      }
    }

    return {
      can,
      columns,
      statusClasses,
      statusOptions,
      buildingOptions,
      filters,
      selectedIds,
      showCreateModal,
      showViewModal,
      showDeleteModal,
      editingFloor,
      viewingFloor,
      deleting,
      filteredFloors,
      viewFloor,
      editFloor,
      deleteFloor,
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