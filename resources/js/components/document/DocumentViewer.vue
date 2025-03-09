<template>
  <BaseModal
    :model-value="show"
    title="Document Details"
    size="xl"
    @update:model-value="$emit('update:show', $event)"
  >
    <div class="space-y-8">
      <!-- Document Information -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Document Information</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <label class="text-sm font-medium text-gray-500">Name</label>
            <p class="mt-1">{{ document.name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Category</label>
            <p class="mt-1">{{ document.category }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Type</label>
            <p class="mt-1">
              <span
                :class="[
                  'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                  typeClasses[document.type] || ''
                ]"
              >
                {{ document.type }}
              </span>
            </p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Size</label>
            <p class="mt-1">{{ formatFileSize(document.size) }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Uploaded By</label>
            <p class="mt-1">{{ document.uploaded_by_name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Upload Date</label>
            <p class="mt-1">{{ formatDate(document.uploaded_at) }}</p>
          </div>
          <div class="sm:col-span-2 lg:col-span-3">
            <label class="text-sm font-medium text-gray-500">Description</label>
            <p class="mt-1">{{ document.description || 'No description provided' }}</p>
          </div>
        </div>
      </section>

      <!-- Tags -->
      <section v-if="document.tags?.length">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Tags</h3>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="tag in document.tags"
            :key="tag"
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
          >
            {{ tag }}
          </span>
        </div>
      </section>

      <!-- Preview -->
      <section>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Preview</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <!-- PDF Preview -->
          <template v-if="document.type === 'pdf' && document.preview_url">
            <iframe
              :src="document.preview_url"
              class="w-full h-[600px] border-0 rounded"
              title="PDF Preview"
            ></iframe>
          </template>

          <!-- Image Preview -->
          <template v-else-if="document.type === 'img' && document.preview_url">
            <img
              :src="document.preview_url"
              :alt="document.name"
              class="max-w-full h-auto rounded"
            >
          </template>

          <!-- No Preview -->
          <template v-else>
            <div class="text-center py-12">
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
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No preview available</h3>
              <p class="mt-1 text-sm text-gray-500">
                This file type cannot be previewed. Please download the file to view its contents.
              </p>
              <div class="mt-6">
                <BaseButton
                  variant="primary"
                  @click="downloadDocument"
                >
                  Download File
                </BaseButton>
              </div>
            </div>
          </template>
        </div>
      </section>

      <!-- Version History -->
      <section v-if="document.versions?.length">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Version History</h3>
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="flow-root">
            <ul class="-mb-8">
              <li v-for="(version, index) in document.versions" :key="version.id">
                <div class="relative pb-8">
                  <span
                    v-if="index !== document.versions.length - 1"
                    class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                    aria-hidden="true"
                  ></span>
                  <div class="relative flex space-x-3">
                    <div>
                      <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                        <svg
                          class="h-5 w-5 text-white"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                          />
                        </svg>
                      </span>
                    </div>
                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                      <div>
                        <p class="text-sm text-gray-500">
                          Version {{ version.version }} uploaded by
                          <span class="font-medium text-gray-900">{{ version.uploaded_by_name }}</span>
                        </p>
                      </div>
                      <div class="text-right text-sm whitespace-nowrap text-gray-500">
                        <time :datetime="version.created_at">{{ formatDate(version.created_at) }}</time>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </section>
    </div>

    <!-- Modal Footer -->
    <template #footer>
      <div class="flex justify-end space-x-3">
        <BaseButton
          variant="secondary"
          @click="downloadDocument"
        >
          Download
        </BaseButton>
        <BaseButton
          variant="secondary"
          @click="$emit('update:show', false)"
        >
          Close
        </BaseButton>
      </div>
    </template>
  </BaseModal>
</template>

<script>
import { useToast } from '@/composables/useToast'
import { useApi } from '@/composables/useApi'

export default {
  name: 'DocumentViewer',

  props: {
    show: {
      type: Boolean,
      required: true
    },
    document: {
      type: Object,
      required: true
    }
  },

  emits: ['update:show'],

  setup(props) {
    const { showToast } = useToast()
    const api = useApi()

    const typeClasses = {
      pdf: 'bg-red-100 text-red-800',
      doc: 'bg-blue-100 text-blue-800',
      xls: 'bg-green-100 text-green-800',
      img: 'bg-purple-100 text-purple-800',
      other: 'bg-gray-100 text-gray-800'
    }

    const downloadDocument = async () => {
      try {
        const response = await api.get(`/api/documents/${props.document.id}/download`, {
          responseType: 'blob'
        })

        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', props.document.name)
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
      } catch (error) {
        showToast('Error downloading document', 'error')
      }
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

    return {
      typeClasses,
      downloadDocument,
      formatDate,
      formatFileSize
    }
  }
}
</script> 