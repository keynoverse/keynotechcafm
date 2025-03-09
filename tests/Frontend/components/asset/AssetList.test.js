import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import AssetList from '@/components/asset/AssetList.vue'
import { useToast } from '@/composables/useToast'
import { useApi } from '@/composables/useApi'
import { usePermissions } from '@/composables/usePermissions'

// Mock the composables
vi.mock('@/composables/useToast', () => ({
  useToast: vi.fn(() => ({
    showToast: vi.fn()
  }))
}))

vi.mock('@/composables/useApi', () => ({
  useApi: vi.fn(() => ({
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn()
  }))
}))

vi.mock('@/composables/usePermissions', () => ({
  usePermissions: vi.fn(() => ({
    can: {
      create: true,
      view: true,
      edit: true,
      delete: true
    }
  }))
}))

describe('AssetList.vue', () => {
  let wrapper
  let api
  let toast

  const mockAssets = [
    {
      id: 1,
      code: 'AST001',
      name: 'Test Asset 1',
      category: { id: 1, name: 'Category 1' },
      status: 'active',
      building: { id: 1, name: 'Building 1' },
      floor: { id: 1, name: 'Floor 1' },
      space: { id: 1, name: 'Space 1' }
    },
    {
      id: 2,
      code: 'AST002',
      name: 'Test Asset 2',
      category: { id: 1, name: 'Category 1' },
      status: 'maintenance',
      building: { id: 1, name: 'Building 1' }
    }
  ]

  beforeEach(() => {
    // Reset mocks
    vi.clearAllMocks()

    // Setup API mock
    api = useApi()
    api.get.mockResolvedValue({ data: mockAssets })

    // Setup Toast mock
    toast = useToast()

    // Mount component with required props
    wrapper = mount(AssetList, {
      props: {
        assets: mockAssets,
        categories: [{ id: 1, name: 'Category 1' }],
        buildings: [{ id: 1, name: 'Building 1' }],
        floors: [{ id: 1, name: 'Floor 1' }],
        spaces: [{ id: 1, name: 'Space 1' }]
      },
      global: {
        stubs: {
          BaseCard: true,
          BaseButton: true,
          BaseInput: true,
          BaseSelect: true,
          BaseTable: true,
          AssetForm: true,
          AssetDetails: true,
          BaseModal: true
        }
      }
    })
  })

  it('renders the component', () => {
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('h2').text()).toBe('Assets')
  })

  it('displays the assets in a table', () => {
    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.exists()).toBe(true)
    expect(table.props('data')).toHaveLength(2)
  })

  it('filters assets by search term', async () => {
    const searchInput = wrapper.findComponent({ name: 'BaseInput' })
    await searchInput.setValue('Test Asset 1')
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(1)
    expect(table.props('data')[0].name).toBe('Test Asset 1')
  })

  it('filters assets by category', async () => {
    const categorySelect = wrapper.findAllComponents({ name: 'BaseSelect' })[0]
    await categorySelect.setValue(1)
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(2)
  })

  it('filters assets by status', async () => {
    const statusSelect = wrapper.findAllComponents({ name: 'BaseSelect' })[2]
    await statusSelect.setValue('active')
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(1)
    expect(table.props('data')[0].status).toBe('active')
  })

  it('opens create modal when add button is clicked', async () => {
    const addButton = wrapper.find('button')
    await addButton.trigger('click')

    expect(wrapper.vm.showCreateModal).toBe(true)
    expect(wrapper.findComponent({ name: 'AssetForm' }).exists()).toBe(true)
  })

  it('opens view modal when view button is clicked', async () => {
    const viewButton = wrapper.find('[data-test="view-button"]')
    await viewButton.trigger('click')

    expect(wrapper.vm.showViewModal).toBe(true)
    expect(wrapper.findComponent({ name: 'AssetDetails' }).exists()).toBe(true)
    expect(wrapper.vm.viewingAsset).toEqual(mockAssets[0])
  })

  it('opens edit modal when edit button is clicked', async () => {
    const editButton = wrapper.find('[data-test="edit-button"]')
    await editButton.trigger('click')

    expect(wrapper.vm.showCreateModal).toBe(true)
    expect(wrapper.vm.editingAsset).toEqual(mockAssets[0])
    expect(wrapper.findComponent({ name: 'AssetForm' }).exists()).toBe(true)
  })

  it('shows delete confirmation when delete button is clicked', async () => {
    const deleteButton = wrapper.find('[data-test="delete-button"]')
    await deleteButton.trigger('click')

    expect(wrapper.vm.showDeleteModal).toBe(true)
    expect(wrapper.findComponent({ name: 'BaseModal' }).exists()).toBe(true)
  })

  it('deletes asset when confirmed', async () => {
    // Setup delete confirmation
    wrapper.vm.deleteAsset(mockAssets[0])
    await nextTick()

    // Confirm deletion
    const confirmButton = wrapper.find('[data-test="confirm-delete"]')
    await confirmButton.trigger('click')

    expect(api.delete).toHaveBeenCalledWith(`/api/assets/${mockAssets[0].id}`)
    expect(wrapper.emitted('refresh')).toBeTruthy()
  })

  it('formats location correctly', () => {
    const location1 = wrapper.vm.formatLocation(mockAssets[0])
    const location2 = wrapper.vm.formatLocation(mockAssets[1])

    expect(location1).toBe('Space 1 (Space)')
    expect(location2).toBe('Building 1 (Building)')
  })

  it('handles API errors gracefully', async () => {
    api.delete.mockRejectedValue(new Error('API Error'))
    
    wrapper.vm.deleteAsset(mockAssets[0])
    await nextTick()

    const confirmButton = wrapper.find('[data-test="confirm-delete"]')
    await confirmButton.trigger('click')

    expect(toast.showToast).toHaveBeenCalledWith('Error deleting asset', 'error')
  })

  it('emits refresh event when asset is saved', async () => {
    wrapper.vm.handleSaved()
    await nextTick()

    expect(wrapper.emitted('refresh')).toBeTruthy()
    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.editingAsset).toBeNull()
  })

  it('closes form without saving', async () => {
    wrapper.vm.editingAsset = mockAssets[0]
    wrapper.vm.showCreateModal = true
    await nextTick()

    wrapper.vm.handleClosed()
    await nextTick()

    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.editingAsset).toBeNull()
  })
}) 