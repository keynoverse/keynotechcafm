import { useApi } from '@/composables/useApi'
import axios from 'axios'
import { ref } from 'vue'

// Mock axios
vi.mock('axios')

describe('useApi', () => {
    let api

    beforeEach(() => {
        // Reset mocks
        vi.clearAllMocks()
        // Create new instance
        api = useApi()
    })

    it('initializes with default state', () => {
        expect(api.loading.value).toBe(false)
        expect(api.error.value).toBeNull()
        expect(api.data.value).toBeNull()
    })

    it('makes successful GET request', async () => {
        const mockData = { id: 1, name: 'Test' }
        axios.get.mockResolvedValue({ data: mockData })

        const response = await api.get('/test')

        expect(axios.get).toHaveBeenCalledWith('/test')
        expect(response).toEqual(mockData)
        expect(api.loading.value).toBe(false)
        expect(api.error.value).toBeNull()
        expect(api.data.value).toEqual(mockData)
    })

    it('makes successful POST request', async () => {
        const payload = { name: 'Test' }
        const mockData = { id: 1, name: 'Test' }
        axios.post.mockResolvedValue({ data: mockData })

        const response = await api.post('/test', payload)

        expect(axios.post).toHaveBeenCalledWith('/test', payload)
        expect(response).toEqual(mockData)
        expect(api.loading.value).toBe(false)
        expect(api.error.value).toBeNull()
        expect(api.data.value).toEqual(mockData)
    })

    it('makes successful PUT request', async () => {
        const payload = { name: 'Updated Test' }
        const mockData = { id: 1, name: 'Updated Test' }
        axios.put.mockResolvedValue({ data: mockData })

        const response = await api.put('/test/1', payload)

        expect(axios.put).toHaveBeenCalledWith('/test/1', payload)
        expect(response).toEqual(mockData)
        expect(api.loading.value).toBe(false)
        expect(api.error.value).toBeNull()
        expect(api.data.value).toEqual(mockData)
    })

    it('makes successful PATCH request', async () => {
        const payload = { name: 'Patched Test' }
        const mockData = { id: 1, name: 'Patched Test' }
        axios.patch.mockResolvedValue({ data: mockData })

        const response = await api.patch('/test/1', payload)

        expect(axios.patch).toHaveBeenCalledWith('/test/1', payload)
        expect(response).toEqual(mockData)
        expect(api.loading.value).toBe(false)
        expect(api.error.value).toBeNull()
        expect(api.data.value).toEqual(mockData)
    })

    it('makes successful DELETE request', async () => {
        axios.delete.mockResolvedValue({ data: null })

        const response = await api.delete('/test/1')

        expect(axios.delete).toHaveBeenCalledWith('/test/1')
        expect(response).toBeNull()
        expect(api.loading.value).toBe(false)
        expect(api.error.value).toBeNull()
        expect(api.data.value).toBeNull()
    })

    it('handles API error', async () => {
        const error = new Error('API Error')
        error.response = { data: { message: 'Something went wrong' } }
        axios.get.mockRejectedValue(error)

        await expect(api.get('/test')).rejects.toThrow('API Error')
        expect(api.loading.value).toBe(false)
        expect(api.error.value).toBe('Something went wrong')
        expect(api.data.value).toBeNull()
    })

    it('handles network error', async () => {
        const error = new Error('Network Error')
        axios.get.mockRejectedValue(error)

        await expect(api.get('/test')).rejects.toThrow('Network Error')
        expect(api.loading.value).toBe(false)
        expect(api.error.value).toBe('Network Error')
        expect(api.data.value).toBeNull()
    })

    it('shows loading state during request', async () => {
        const loadingStates = []
        const mockData = { id: 1, name: 'Test' }
        
        // Delay the API response
        axios.get.mockImplementation(() => {
            return new Promise(resolve => {
                setTimeout(() => {
                    resolve({ data: mockData })
                }, 100)
            })
        })

        // Watch loading state changes
        const stopWatch = watch(() => api.loading.value, (loading) => {
            loadingStates.push(loading)
        })

        const promise = api.get('/test')
        expect(api.loading.value).toBe(true)
        
        await promise
        expect(api.loading.value).toBe(false)
        
        stopWatch()
        expect(loadingStates).toEqual([true, false])
    })

    it('resets state', () => {
        api.data.value = { id: 1 }
        api.error.value = 'Error'
        api.loading.value = true

        api.resetState()

        expect(api.data.value).toBeNull()
        expect(api.error.value).toBeNull()
        expect(api.loading.value).toBe(false)
    })

    it('creates resource API', () => {
        const resourceApi = api.useResource('tests')

        expect(typeof resourceApi.list).toBe('function')
        expect(typeof resourceApi.get).toBe('function')
        expect(typeof resourceApi.create).toBe('function')
        expect(typeof resourceApi.update).toBe('function')
        expect(typeof resourceApi.remove).toBe('function')
    })

    it('makes resource API calls', async () => {
        const resourceApi = api.useResource('tests')
        const mockData = [{ id: 1, name: 'Test' }]
        axios.get.mockResolvedValue({ data: mockData })

        // List
        await resourceApi.list()
        expect(axios.get).toHaveBeenCalledWith('/tests')

        // Get
        await resourceApi.get(1)
        expect(axios.get).toHaveBeenCalledWith('/tests/1')

        // Create
        const createData = { name: 'New Test' }
        await resourceApi.create(createData)
        expect(axios.post).toHaveBeenCalledWith('/tests', createData)

        // Update
        const updateData = { name: 'Updated Test' }
        await resourceApi.update(1, updateData)
        expect(axios.put).toHaveBeenCalledWith('/tests/1', updateData)

        // Delete
        await resourceApi.remove(1)
        expect(axios.delete).toHaveBeenCalledWith('/tests/1')
    })

    it('handles file upload', async () => {
        const file = new File(['test'], 'test.txt', { type: 'text/plain' })
        const formData = new FormData()
        formData.append('file', file)

        const mockData = { id: 1, url: 'http://example.com/test.txt' }
        axios.post.mockResolvedValue({ data: mockData })

        const response = await api.upload('/upload', formData)

        expect(axios.post).toHaveBeenCalledWith('/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        expect(response).toEqual(mockData)
    })

    it('handles request with custom config', async () => {
        const mockData = { id: 1, name: 'Test' }
        axios.get.mockResolvedValue({ data: mockData })

        const config = {
            headers: {
                'Custom-Header': 'test'
            },
            params: {
                filter: 'active'
            }
        }

        await api.get('/test', config)

        expect(axios.get).toHaveBeenCalledWith('/test', config)
    })

    it('handles request cancellation', async () => {
        const mockData = { id: 1, name: 'Test' }
        const cancelToken = axios.CancelToken.source()
        
        axios.get.mockImplementation(() => {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({ data: mockData })
                }, 100)
            })
        })

        const promise = api.get('/test', { cancelToken: cancelToken.token })
        cancelToken.cancel('Request cancelled')

        await expect(promise).rejects.toThrow('Request cancelled')
        expect(api.loading.value).toBe(false)
        expect(api.error.value).toBe('Request cancelled')
    })
}) 