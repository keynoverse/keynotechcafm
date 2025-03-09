<template>
  <BaseModal
    :model-value="show"
    title="Upload Document"
    size="lg"
    @update:model-value="$emit('update:show', $event)"
  >
    <form @submit.prevent="handleSubmit">
      <div class="space-y-6">
        <!-- Basic Information -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Document Information</h3>
          <div class="grid grid-cols-1 gap-4">
            <BaseInput
              v-model="form.name"
              label="Document Name"
              required
              :error="errors.name"
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

        <!-- File Upload -->
        <section>
          <h3 class="text-lg font-medium text-gray-900 mb-4">File Upload</h3>
          <div class="space-y-4">
            <div
              class="relative border-2 border-dashed rounded-lg p-6"
              :class="[
                isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300',
                hasFile ? 'bg-gray-50' : 'bg-white'
              ]"
              @dragenter.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @dragover.prevent
              @drop.prevent="handleDrop"
            >
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
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                  />
                </svg>
                <div class="mt-4 flex text-sm text-gray-600">
                  <label
                    for="file-upload"
                    class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"
                  >
                    <span>Upload a file</span>
                    <input
                      id="file-upload"
                      name="file-upload"
                      type="file"
                      class="sr-only"
                      @change="handleFileChange"
                    >
                  </label>
                  <p class="pl-1">or drag and drop</p>
                </div>
                <p class="text-xs text-gray-500">
                  PDF, Word, Excel, or image files up to 10MB
                </p>
              </div>

              <div
                v-if="hasFile"
                class="mt-4 p-4 bg-white rounded-lg border border-gray-200"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <svg
                      class="h-8 w-8 text-gray-400"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                      />
                    </svg>
                    <div class="ml-4">
                      <p class="text-sm font-medium text-gray-900">
                        {{ form.file.name }}
                      </p>
                      <p class="text-xs text-gray-500">
                        {{ formatFileSize(form.file.size) }}
                      </p>
                    </div>
                  </div>
                  <button
                    type="button"
                    class="text-red-600 hover:text-red-900"
                    @click="removeFile"
                  >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path
                        fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </button>
                </div>
              </div>

              <div
                v-if="errors.file"
                class="mt-2 text-sm text-red-600"
              >
                {{ errors.file }}
              </div>
            </div>

            <!-- Tags -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Tags</label>
              <div class="mt-1">
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="tag in form.tags"
                    :key="tag"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                  >
                    {{ tag }}
                    <button
                      type="button"
                      class="ml-1.5 inline-flex text-blue-400 hover:text-blue-600"
                      @click="removeTag(tag)"
                    >
                      <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                        <path
                          fill-rule="evenodd"
                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>
                  </span>
                  <input
                    v-model="newTag"
                    type="text"
                    class="flex-1 min-w-0 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    placeholder="Add tags..."
                    @keydown.enter.prevent="addTag"
                  >
                </div>
              </div>
            </div>
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
            :loading="uploading"
            :disabled="!hasFile"
          >
            Upload
          </BaseButton>
        </div>
      </template>
    </form>
  </BaseModal>
</template>

<script>
import { ref, computed } from 'vue'
import { useToast } from '@/composables/useToast'
import { useApi } from '@/composables/useApi'

export default {
  name: 'DocumentUpload',

  props: {
    show: {
      type: Boolean,
      required: true
    }
  },

  emits: ['update:show', 'uploaded'],

  setup(props, { emit }) {
    const { showToast } = useToast()
    const api = useApi()

    // Form state
    const form = ref({
      name: '',
      category: '',
      description: '',
      file: null,
      tags: []
    })

    const errors = ref({})
    const uploading = ref(false)
    const isDragging = ref(false)
    const newTag = ref('')

    // Options
    const categoryOptions = [
      { value: 'manual', label: 'Manual' },
      { value: 'specification', label: 'Specification' },
      { value: 'report', label: 'Report' },
      { value: 'contract', label: 'Contract' },
      { value: 'certificate', label: 'Certificate' },
      { value: 'other', label: 'Other' }
    ]

    // Computed
    const hasFile = computed(() => !!form.value.file)

    // Methods
    const handleFileChange = (event) => {
      const file = event.target.files[0]
      if (file) {
        validateAndSetFile(file)
      }
    }

    const handleDrop = (event) => {
      isDragging.value = false
      const file = event.dataTransfer.files[0]
      if (file) {
        validateAndSetFile(file)
      }
    }

    const validateAndSetFile = (file) => {
      const maxSize = 10 * 1024 * 1024 // 10MB
      const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png',
        'image/gif'
      ]

      if (file.size > maxSize) {
        errors.value.file = 'File size must be less than 10MB'
        return
      }

      if (!allowedTypes.includes(file.type)) {
        errors.value.file = 'Invalid file type'
        return
      }

      form.value.file = file
      errors.value.file = null

      // Set default name if not already set
      if (!form.value.name) {
        form.value.name = file.name.split('.')[0]
      }
    }

    const removeFile = () => {
      form.value.file = null
      errors.value.file = null
    }

    const addTag = () => {
      const tag = newTag.value.trim()
      if (tag && !form.value.tags.includes(tag)) {
        form.value.tags.push(tag)
      }
      newTag.value = ''
    }

    const removeTag = (tag) => {
      form.value.tags = form.value.tags.filter(t => t !== tag)
    }

    const handleSubmit = async () => {
      try {
        uploading.value = true
        errors.value = {}

        const formData = new FormData()
        formData.append('file', form.value.file)
        formData.append('name', form.value.name)
        formData.append('category', form.value.category)
        formData.append('description', form.value.description)
        formData.append('tags', JSON.stringify(form.value.tags))

        await api.post('/api/documents', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        emit('uploaded')
      } catch (error) {
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
        }
        showToast('Error uploading document', 'error')
      } finally {
        uploading.value = false
      }
    }

    const formatFileSize = (bytes) => {
      if (!bytes) return '0 B'
      const k = 1024
      const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`
    }

    return {
      form,
      errors,
      uploading,
      isDragging,
      newTag,
      categoryOptions,
      hasFile,
      handleFileChange,
      handleDrop,
      removeFile,
      addTag,
      removeTag,
      handleSubmit,
      formatFileSize
    }
  }
}
</script> 