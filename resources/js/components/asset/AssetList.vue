<template>
  <div>
    <BaseCard>
      <!-- Card Header -->
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-900">Assets</h3>
          <BaseButton
            v-if="can.create"
            variant="primary"
            @click="showCreateModal = true"
          >
            Add Asset
          </BaseButton>
        </div>
      </template>

      <!-- Search and Filters -->
      <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <BaseInput
          v-model="filters.search"
          placeholder="Search assets..."
          clearable
          :leading-icon="SearchIcon"
        />
        <BaseSelect
          v-model="filters.category_id"
          :options="categoryOptions"
          placeholder="Filter by category"
        />
        <BaseSelect
          v-model="filters.location_type"
          :options="locationTypeOptions"
          placeholder="Filter by location type"
        />
        <BaseSelect
          v-model="filters.status"
          :options="statusOptions"
          placeholder="Filter by status"
        />
      </div>

      <!-- Assets Table -->
      <BaseTable
        :items="filteredAssets"
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

        <!-- Location Column -->
        <template #location="{ item }">
          <span>
            {{ formatLocation(item) }}
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
              @click="viewAsset(item)"
            >
              View
            </BaseButton>
            <BaseButton
              v-if="can.edit"
              variant="secondary"
              size="sm"
              :icon="PencilIcon"
              @click="editAsset(item)"
            >
              Edit
            </BaseButton>
            <BaseButton
              v-if="can.delete"
              variant="danger"
              size="sm"
              :icon="TrashIcon"
              @click="deleteAsset(item)"
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
                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
              />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No assets</h3>
            <p class="mt-1 text-sm text-gray-500">
              Get started by creating a new asset.
            </p>
            <div class="mt-6">
              <BaseButton
                v-if="can.create"
                variant="primary"
                :icon="PlusIcon"
                @click="showCreateModal = true"
              >
                Add Asset
              </BaseButton>
            </div>
          </div>
        </template>
      </BaseTable>
    </BaseCard>

    <!-- Create/Edit Modal -->
    <AssetForm
      v-if="showCreateModal || editingAsset"
      v-model:show="showCreateModal"
      :asset="editingAsset"
      :categories="categories"
      :buildings="buildings"
      :floors="floors"
      :spaces="spaces"
      @saved="handleSaved"
      @closed="handleClosed"
    />

    <!-- View Modal -->
    <AssetDetails
      v-if="viewingAsset"
      v-model:show="showViewModal"
      :asset="viewingAsset"
      @closed="viewingAsset = null"
    />

    <!-- Delete Confirmation Modal -->
    <BaseModal
      v-model="showDeleteModal"
      title="Delete Asset"
      size="sm"
    >
      <p class="text-sm text-gray-500">
        Are you sure you want to delete this asset? This action cannot be undone.
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
import AssetForm from './AssetForm.vue'
import AssetDetails from './AssetDetails.vue'

export default {
  name: 'AssetList',
  components: {
    AssetForm,
    AssetDetails
  },
  props: {
    assets: {
      type: Array,
      required: true
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
  emits: ['refresh'],
  setup(props, { emit }) {
    // Permissions
    const { can } = usePermissions('assets')

    // Table Configuration
    const columns = [
      { key: 'code', label: 'Code', sortable: true },
      { key: 'name', label: 'Name', sortable: true },
      { key: 'category.name', label: 'Category', sortable: true },
      { key: 'location', label: 'Location', sortable: true },
      { key: 'purchase_date', label: 'Purchase Date', sortable: true },
      { key: 'warranty_expiry', label: 'Warranty Expiry', sortable: true },
      { key: 'status', label: 'Status', sortable: true },
      { key: 'actions', label: 'Actions' }
    ]

    const statusClasses = {
      active: 'bg-green-100 text-green-800',
      inactive: 'bg-gray-100 text-gray-800',
      maintenance: 'bg-yellow-100 text-yellow-800',
      repair: 'bg-red-100 text-red-800',
      disposed: 'bg-gray-100 text-gray-800'
    }

    const statusOptions = [
      { value: '', label: 'All Statuses' },
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
      { value: 'maintenance', label: 'Under Maintenance' },
      { value: 'repair', label: 'Under Repair' },
      { value: 'disposed', label: 'Disposed' }
    ]

    const categoryOptions = computed(() => [
      { value: '', label: 'All Categories' },
      ...props.categories.map(category => ({
        value: category.id,
        label: category.name
      }))
    ])

    const locationTypeOptions = [
      { value: '', label: 'All Locations' },
      { value: 'building', label: 'Building' },
      { value: 'floor', label: 'Floor' },
      { value: 'space', label: 'Space' }
    ]

    // State
    const filters = ref({
      search: '',
      category_id: '',
      location_type: '',
      status: ''
    })

    const selectedIds = ref([])
    const showCreateModal = ref(false)
    const showViewModal = ref(false)
    const showDeleteModal = ref(false)
    const editingAsset = ref(null)
    const viewingAsset = ref(null)
    const deletingAsset = ref(null)
    const deleting = ref(false)

    // Methods
    const formatLocation = (asset) => {
      if (asset.space_id) {
        const space = props.spaces.find(s => s.id === asset.space_id)
        return `${space?.name} (Space)`
      }
      if (asset.floor_id) {
        const floor = props.floors.find(f => f.id === asset.floor_id)
        return `${floor?.name} (Floor)`
      }
      if (asset.building_id) {
        const building = props.buildings.find(b => b.id === asset.building_id)
        return `${building?.name} (Building)`
      }
      return 'No location'
    }

    // Computed
    const filteredAssets = computed(() => {
      let result = [...props.assets]

      // Apply search filter
      if (filters.value.search) {
        const search = filters.value.search.toLowerCase()
        result = result.filter(asset =>
          asset.name.toLowerCase().includes(search) ||
          asset.code.toLowerCase().includes(search) ||
          asset.category?.name.toLowerCase().includes(search)
        )
      }

      // Apply category filter
      if (filters.value.category_id) {
        result = result.filter(asset => asset.category_id === filters.value.category_id)
      }

      // Apply location type filter
      if (filters.value.location_type) {
        switch (filters.value.location_type) {
          case 'building':
            result = result.filter(asset => asset.building_id && !asset.floor_id && !asset.space_id)
            break
          case 'floor':
            result = result.filter(asset => asset.floor_id && !asset.space_id)
            break
          case 'space':
            result = result.filter(asset => asset.space_id)
            break
        }
      }

      // Apply status filter
      if (filters.value.status) {
        result = result.filter(asset => asset.status === filters.value.status)
      }

      return result
    })

    const viewAsset = (asset) => {
      viewingAsset.value = asset
      showViewModal.value = true
    }

    const editAsset = (asset) => {
      editingAsset.value = asset
      showCreateModal.value = true
    }

    const deleteAsset = (asset) => {
      deletingAsset.value = asset
      showDeleteModal.value = true
    }

    const handleSaved = () => {
      showCreateModal.value = false
      editingAsset.value = null
      emit('refresh')
    }

    const handleClosed = () => {
      showCreateModal.value = false
      editingAsset.value = null
    }

    const confirmDelete = async () => {
      if (!deletingAsset.value) return

      try {
        deleting.value = true
        await axios.delete(`/api/assets/${deletingAsset.value.id}`)
        showDeleteModal.value = false
        deletingAsset.value = null
        emit('refresh')
      } catch (error) {
        console.error('Error deleting asset:', error)
      } finally {
        deleting.value = false
      }
    }

    return {
      can,
      columns,
      statusClasses,
      statusOptions,
      categoryOptions,
      locationTypeOptions,
      filters,
      selectedIds,
      showCreateModal,
      showViewModal,
      showDeleteModal,
      editingAsset,
      viewingAsset,
      deleting,
      filteredAssets,
      formatLocation,
      viewAsset,
      editAsset,
      deleteAsset,
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