<template>
  <BaseCard>
    <!-- Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900">Documents</h2>
        <BaseButton
          variant="primary"
          @click="showUploadModal = true"
        >
          Upload Document
        </BaseButton>
      </div>
    </template>

    <!-- Filters -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <BaseInput
        v-model="filters.search"
        placeholder="Search documents..."
        type="search"
      >
        <template #prefix>
          <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
          </svg>
        </template>
      </BaseInput>

      <BaseSelect
        v-model="filters.category"
        :options="categoryOptions"
        placeholder="Filter by category"
      />

      <BaseSelect
        v-model="filters.type"
        :options="typeOptions"
        placeholder="Filter by type"
      />

      <BaseSelect
        v-model="filters.uploadedBy"
        :options="userOptions"
        placeholder="Filter by uploader"
      />
    </div>

    <!-- Table -->
    <BaseTable
      :columns="columns"
      :data="filteredDocuments"
      :loading="loading"
      :sort-by="sortBy"
      :sort-desc="sortDesc"
      @update:sort-by="sortBy = $event"
      @update:sort-desc="sortDesc = $event"
    >
      <template #cell-type="{ item }">
        <span
          :class="[
            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
            typeClasses[item.type] || ''
          ]"
        >
          {{ item.type }}
        </span>
      </template>

      <template #cell-size="{ item }">
        {{ formatFileSize(item.size) }}
      </template>

      <template #cell-uploaded_at="{ item }">
        {{ formatDate(item.uploaded_at) }}
      </template>

      <template #cell-actions="{ item }">
        <div class="flex items-center space-x-2">
          <BaseButton
            variant="secondary"
            size="sm"
            @click="viewDocument(item)"
          >
            View
          </BaseButton>
          <BaseButton
            variant="secondary"
            size="sm"
            @click="downloadDocument(item)"
          >
            Download
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

    <!-- Upload Modal -->
    <DocumentUpload
      v-if="showUploadModal"
      :show="showUploadModal"
      @update:show="showUploadModal = false"
      @uploaded="handleUploaded"
    />

    <!-- View Modal -->
    <DocumentViewer
      v-if="showViewModal"
      :show="showViewModal"
      :document="selectedDocument"
      @update:show="showViewModal = false"
    />

    <!-- Delete Confirmation Modal -->
    <BaseModal
      :model-value="showDeleteModal"
      title="Delete Document"
      @update:model-value="showDeleteModal = false"
    >
      <p class="mb-4 text-sm text-gray-500">
        Are you sure you want to delete this document? This action cannot be undone.
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
            @click="deleteDocument"
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
  name: 'DocumentList',

  setup() {
    const { showToast } = useToast()
    const api = useApi()

    // State
    const documents = ref([])
    const loading = ref(true)
    const deleting = ref(false)
    const showUploadModal = ref(false)
    const showViewModal = ref(false)
    const showDeleteModal = ref(false)
    const selectedDocument = ref(null)
    const sortBy = ref('uploaded_at')
    const sortDesc = ref(true)

    // Filters
    const filters = ref({
      search: '',
      category: '',
      type: '',
      uploadedBy: ''
    })

    // Table columns
    const columns = [
      { key: 'name', label: 'Name', sortable: true },
      { key: 'type', label: 'Type', sortable: true },
      { key: 'category', label: 'Category', sortable: true },
      { key: 'size', label: 'Size', sortable: true },
      { key: 'uploaded_by', label: 'Uploaded By', sortable: true },
      { key: 'uploaded_at', label: 'Upload Date', sortable: true },
      { key: 'actions', label: 'Actions', sortable: false }
    ]

    // Options
    const categoryOptions = [
      { value: 'manual', label: 'Manual' },
      { value: 'specification', label: 'Specification' },
      { value: 'report', label: 'Report' },
      { value: 'contract', label: 'Contract' },
      { value: 'certificate', label: 'Certificate' },
      { value: 'other', label: 'Other' }
    ]

    const typeOptions = [
      { value: 'pdf', label: 'PDF' },
      { value: 'doc', label: 'Word Document' },
      { value: 'xls', label: 'Excel Spreadsheet' },
      { value: 'img', label: 'Image' },
      { value: 'other', label: 'Other' }
    ]

    const userOptions = ref([])

    // Type classes for badges
    const typeClasses = {
      pdf: 'bg-red-100 text-red-800',
      doc: 'bg-blue-100 text-blue-800',
      xls: 'bg-green-100 text-green-800',
      img: 'bg-purple-100 text-purple-800',
      other: 'bg-gray-100 text-gray-800'
    }

    // Computed
    const filteredDocuments = computed(() => {
      let filtered = [...documents.value]

      // Apply search filter
      if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase()
        filtered = filtered.filter(doc =>
          doc.name.toLowerCase().includes(searchTerm) ||
          doc.description?.toLowerCase().includes(searchTerm)
        )
      }

      // Apply category filter
      if (filters.value.category) {
        filtered = filtered.filter(doc => doc.category === filters.value.category)
      }

      // Apply type filter
      if (filters.value.type) {
        filtered = filtered.filter(doc => doc.type === filters.value.type)
      }

      // Apply uploader filter
      if (filters.value.uploadedBy) {
        filtered = filtered.filter(doc => doc.uploaded_by === filters.value.uploadedBy)
      }

      return filtered
    })

    // Methods
    const fetchDocuments = async () => {
      try {
        loading.value = true
        const response = await api.get('/api/documents')
        documents.value = response.data
      } catch (error) {
        showToast('Error fetching documents', 'error')
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

    const viewDocument = (document) => {
      selectedDocument.value = document
      showViewModal.value = true
    }

    const downloadDocument = async (document) => {
      try {
        const response = await api.get(`/api/documents/${document.id}/download`, {
          responseType: 'blob'
        })

        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', document.name)
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
      } catch (error) {
        showToast('Error downloading document', 'error')
      }
    }

    const confirmDelete = (document) => {
      selectedDocument.value = document
      showDeleteModal.value = true
    }

    const deleteDocument = async () => {
      try {
        deleting.value = true
        await api.delete(`/api/documents/${selectedDocument.value.id}`)
        await fetchDocuments()
        showDeleteModal.value = false
        showToast('Document deleted successfully')
      } catch (error) {
        showToast('Error deleting document', 'error')
      } finally {
        deleting.value = false
      }
    }

    const handleUploaded = async () => {
      await fetchDocuments()
      showUploadModal.value = false
      showToast('Document uploaded successfully')
    }

    const formatDate = (date) => {
      if (!date) return ''
      return new Date(date).toLocaleDateString()
    }

    const formatFileSize = (bytes) => {
      if (!bytes) return '0 B'
      const k = 1024
      const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`
    }

    // Lifecycle hooks
    onMounted(async () => {
      await Promise.all([
        fetchDocuments(),
        fetchUsers()
      ])
    })

    return {
      // State
      documents,
      loading,
      deleting,
      showUploadModal,
      showViewModal,
      showDeleteModal,
      selectedDocument,
      sortBy,
      sortDesc,
      filters,

      // Data
      columns,
      categoryOptions,
      typeOptions,
      userOptions,
      typeClasses,

      // Computed
      filteredDocuments,

      // Methods
      viewDocument,
      downloadDocument,
      confirmDelete,
      deleteDocument,
      handleUploaded,
      formatDate,
      formatFileSize
    }
  }
}
</script> 