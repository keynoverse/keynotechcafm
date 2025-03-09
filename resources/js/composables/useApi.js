import { ref } from 'vue'
import axios from 'axios'

export function useApi() {
  const loading = ref(false)
  const error = ref(null)
  const data = ref(null)

  const request = async (config) => {
    loading.value = true
    error.value = null
    data.value = null

    try {
      const response = await axios(config)
      data.value = response.data
      return response
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  const get = (url, config = {}) => {
    return request({ ...config, method: 'get', url })
  }

  const post = (url, data = {}, config = {}) => {
    return request({ ...config, method: 'post', url, data })
  }

  const put = (url, data = {}, config = {}) => {
    return request({ ...config, method: 'put', url, data })
  }

  const patch = (url, data = {}, config = {}) => {
    return request({ ...config, method: 'patch', url, data })
  }

  const del = (url, config = {}) => {
    return request({ ...config, method: 'delete', url })
  }

  const resetState = () => {
    loading.value = false
    error.value = null
    data.value = null
  }

  return {
    loading,
    error,
    data,
    request,
    get,
    post,
    put,
    patch,
    del,
    resetState
  }
}

// Optional: Create resource-specific API hooks
export function useResource(resource) {
  const api = useApi()
  const baseUrl = `/api/${resource}`

  const list = (params) => {
    return api.get(baseUrl, { params })
  }

  const get = (id) => {
    return api.get(`${baseUrl}/${id}`)
  }

  const create = (data) => {
    return api.post(baseUrl, data)
  }

  const update = (id, data) => {
    return api.put(`${baseUrl}/${id}`, data)
  }

  const updateStatus = (id, status) => {
    return api.patch(`${baseUrl}/${id}/status`, { status })
  }

  const remove = (id) => {
    return api.del(`${baseUrl}/${id}`)
  }

  return {
    ...api,
    list,
    get,
    create,
    update,
    updateStatus,
    remove
  }
} 