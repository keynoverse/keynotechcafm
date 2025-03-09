<template>
  <div>
    <BaseCard>
      <!-- Card Header -->
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-900">Spaces</h3>
          <BaseButton
            v-if="can.create"
            variant="primary"
            @click="showCreateModal = true"
          >
            Add Space
          </BaseButton>
        </div>
      </template>

      <!-- Search and Filters -->
      <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <BaseInput
          v-model="filters.search"
          placeholder="Search spaces..."
          clearable
          :leading-icon="SearchIcon"
        />
        <BaseSelect
          v-model="filters.building_id"
          :options="buildingOptions"
          placeholder="Filter by building"
        />
        <BaseSelect
          v-model="filters.floor_id"
          :options="floorOptions"
          placeholder="Filter by floor"
        />
        <BaseSelect
          v-model="filters.status"
          :options="statusOptions"
          placeholder="Filter by status"
        />
      </div>

      <!-- Spaces Table -->
      <BaseTable
        :items="filteredSpaces"
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

        <!-- Type Column -->
        <template #type="{ value }">
          <span class="capitalize">{{ value }}</span>
        </template>

        <!-- Actions Column -->
        <template #actions="{ item }">
          <div class="flex items-center space-x-2">
            <BaseButton
              v-if="can.view"
              variant="secondary"
              size="sm"
              :icon="EyeIcon"
              @click="viewSpace(item)"
            >
              View
            </BaseButton>
            <BaseButton
              v-if="can.edit"
              variant="secondary"
              size="sm"
              :icon="PencilIcon"
              @click="editSpace(item)"
            >
              Edit
            </BaseButton>
            <BaseButton
              v-if="can.delete"
              variant="danger"
              size="sm"
              :icon="TrashIcon"
              @click="deleteSpace(item)"
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
            <h3 class="mt-2 text-sm font-medium text-gray-900">No spaces</h3>
            <p class="mt-1 text-sm text-gray-500">
              Get started by creating a new space.
            </p>
            <div class="mt-6">
              <BaseButton
                v-if="can.create"
                variant="primary"
                :icon="PlusIcon"
                @click="showCreateModal = true"
              >
                Add Space
              </BaseButton>
            </div>
          </div>
        </template>
      </BaseTable>
    </BaseCard>

    <!-- Create/Edit Modal -->
    <SpaceForm
      v-if="showCreateModal || editingSpace"
      v-model:show="showCreateModal"
      :space="editingSpace"
      :buildings="buildings"
      :floors="floors"
      @saved="handleSaved"
      @closed="handleClosed"
    />

    <!-- View Modal -->
    <SpaceDetails
      v-if="viewingSpace"
      v-model:show="showViewModal"
      :space="viewingSpace"
      @closed="viewingSpace = null"
    />

    <!-- Delete Confirmation Modal -->
    <BaseModal
      v-model="showDeleteModal"
      title="Delete Space"
      size="sm"
    >
      <p class="text-sm text-gray-500">
        Are you sure you want to delete this space? This action cannot be undone.
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
import SpaceForm from './SpaceForm.vue'
import SpaceDetails from './SpaceDetails.vue'

export default {
  name: 'SpaceList',
  components: {
    SpaceForm,
    SpaceDetails
  },
  props: {
    spaces: {
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
    }
  },
  emits: ['refresh'],
  setup(props, { emit }) {
    // Permissions
    const { can } = usePermissions('spaces')

    // Table Configuration
    const columns = [
      { key: 'code', label: 'Code', sortable: true },
      { key: 'name', label: 'Name', sortable: true },
      { key: 'type', label: 'Type', sortable: true },
      { key: 'building.name', label: 'Building', sortable: true },
      { key: 'floor.name', label: 'Floor', sortable: true },
      { key: 'area', label: 'Area (sqm)', sortable: true },
      { key: 'capacity', label: 'Capacity', sortable: true },
      { key: 'status', label: 'Status', sortable: true },
      { key: 'actions', label: 'Actions' }
    ]

    const statusClasses = {
      active: 'bg-green-100 text-green-800',
      inactive: 'bg-gray-100 text-gray-800',
      maintenance: 'bg-yellow-100 text-yellow-800',
      renovation: 'bg-blue-100 text-blue-800',
      occupied: 'bg-blue-100 text-blue-800',
      vacant: 'bg-gray-100 text-gray-800'
    }

    const statusOptions = [
      { value: '', label: 'All Statuses' },
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
      { value: 'maintenance', label: 'Under Maintenance' },
      { value: 'renovation', label: 'Under Renovation' },
      { value: 'occupied', label: 'Occupied' },
      { value: 'vacant', label: 'Vacant' }
    ]

    const buildingOptions = computed(() => [
      { value: '', label: 'All Buildings' },
      ...props.buildings.map(building => ({
        value: building.id,
        label: building.name
      }))
    ])

    const floorOptions = computed(() => {
      const options = [{ value: '', label: 'All Floors' }]
      
      if (filters.value.building_id) {
        const buildingFloors = props.floors.filter(
          floor => floor.building_id === filters.value.building_id
        )
        options.push(
          ...buildingFloors.map(floor => ({
            value: floor.id,
            label: floor.name
          }))
        )
      } else {
        options.push(
          ...props.floors.map(floor => ({
            value: floor.id,
            label: `${floor.name} (${floor.building.name})`
          }))
        )
      }

      return options
    })

    // State
    const filters = ref({
      search: '',
      building_id: '',
      floor_id: '',
      status: ''
    })

    const selectedIds = ref([])
    const showCreateModal = ref(false)
    const showViewModal = ref(false)
    const showDeleteModal = ref(false)
    const editingSpace = ref(null)
    const viewingSpace = ref(null)
    const deletingSpace = ref(null)
    const deleting = ref(false)

    // Computed
    const filteredSpaces = computed(() => {
      let result = [...props.spaces]

      // Apply search filter
      if (filters.value.search) {
        const search = filters.value.search.toLowerCase()
        result = result.filter(space =>
          space.name.toLowerCase().includes(search) ||
          space.code.toLowerCase().includes(search) ||
          space.building.name.toLowerCase().includes(search) ||
          space.floor.name.toLowerCase().includes(search)
        )
      }

      // Apply building filter
      if (filters.value.building_id) {
        result = result.filter(space => space.building_id === filters.value.building_id)
      }

      // Apply floor filter
      if (filters.value.floor_id) {
        result = result.filter(space => space.floor_id === filters.value.floor_id)
      }

      // Apply status filter
      if (filters.value.status) {
        result = result.filter(space => space.status === filters.value.status)
      }

      return result
    })

    // Methods
    const viewSpace = (space) => {
      viewingSpace.value = space
      showViewModal.value = true
    }

    const editSpace = (space) => {
      editingSpace.value = space
      showCreateModal.value = true
    }

    const deleteSpace = (space) => {
      deletingSpace.value = space
      showDeleteModal.value = true
    }

    const handleSaved = () => {
      showCreateModal.value = false
      editingSpace.value = null
      emit('refresh')
    }

    const handleClosed = () => {
      showCreateModal.value = false
      editingSpace.value = null
    }

    const confirmDelete = async () => {
      if (!deletingSpace.value) return

      try {
        deleting.value = true
        await axios.delete(`/api/spaces/${deletingSpace.value.id}`)
        showDeleteModal.value = false
        deletingSpace.value = null
        emit('refresh')
      } catch (error) {
        console.error('Error deleting space:', error)
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
      floorOptions,
      filters,
      selectedIds,
      showCreateModal,
      showViewModal,
      showDeleteModal,
      editingSpace,
      viewingSpace,
      deleting,
      filteredSpaces,
      viewSpace,
      editSpace,
      deleteSpace,
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