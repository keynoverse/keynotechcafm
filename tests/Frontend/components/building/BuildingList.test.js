import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import BuildingList from '@/components/building/BuildingList.vue'
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

describe('BuildingList.vue', () => {
  let wrapper
  let api
  let toast

  const mockBuildings = [
    {
      id: 1,
      code: 'BLD001',
      name: 'Test Building 1',
      address: '123 Test St',
      city: 'Test City',
      state: 'Test State',
      country: 'Test Country',
      status: 'active',
      total_floors: 10,
      total_area: 50000
    },
    {
      id: 2,
      code: 'BLD002',
      name: 'Test Building 2',
      address: '456 Test Ave',
      city: 'Test City',
      state: 'Test State',
      country: 'Test Country',
      status: 'maintenance',
      total_floors: 15,
      total_area: 75000
    }
  ]

  beforeEach(() => {
    // Reset mocks
    vi.clearAllMocks()

    // Setup API mock
    api = useApi()
    api.get.mockResolvedValue({ data: mockBuildings })

    // Setup Toast mock
    toast = useToast()

    // Mount component
    wrapper = mount(BuildingList, {
      global: {
        stubs: {
          BaseCard: true,
          BaseButton: true,
          BaseInput: true,
          BaseSelect: true,
          BaseTable: true,
          BuildingForm: true,
          BuildingDetails: true,
          BaseModal: true
        }
      }
    })
  })

  it('renders the component', () => {
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('h2').text()).toBe('Buildings')
  })

  it('displays the buildings in a table', () => {
    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.exists()).toBe(true)
    expect(table.props('data')).toHaveLength(2)
  })

  it('filters buildings by search term', async () => {
    const searchInput = wrapper.findComponent({ name: 'BaseInput' })
    await searchInput.setValue('Test Building 1')
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(1)
    expect(table.props('data')[0].name).toBe('Test Building 1')
  })

  it('filters buildings by status', async () => {
    const statusSelect = wrapper.findComponent({ name: 'BaseSelect' })
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
    expect(wrapper.findComponent({ name: 'BuildingForm' }).exists()).toBe(true)
  })

  it('opens view modal when view button is clicked', async () => {
    const viewButton = wrapper.find('[data-test="view-button"]')
    await viewButton.trigger('click')

    expect(wrapper.vm.showViewModal).toBe(true)
    expect(wrapper.findComponent({ name: 'BuildingDetails' }).exists()).toBe(true)
    expect(wrapper.vm.selectedBuilding).toEqual(mockBuildings[0])
  })

  it('opens edit modal when edit button is clicked', async () => {
    const editButton = wrapper.find('[data-test="edit-button"]')
    await editButton.trigger('click')

    expect(wrapper.vm.showCreateModal).toBe(true)
    expect(wrapper.vm.editingBuilding).toEqual(mockBuildings[0])
    expect(wrapper.findComponent({ name: 'BuildingForm' }).exists()).toBe(true)
  })

  it('shows delete confirmation when delete button is clicked', async () => {
    const deleteButton = wrapper.find('[data-test="delete-button"]')
    await deleteButton.trigger('click')

    expect(wrapper.vm.showDeleteModal).toBe(true)
    expect(wrapper.findComponent({ name: 'BaseModal' }).exists()).toBe(true)
  })

  it('deletes building when confirmed', async () => {
    // Setup delete confirmation
    wrapper.vm.deleteBuilding(mockBuildings[0])
    await nextTick()

    // Confirm deletion
    const confirmButton = wrapper.find('[data-test="confirm-delete"]')
    await confirmButton.trigger('click')

    expect(api.delete).toHaveBeenCalledWith(`/api/buildings/${mockBuildings[0].id}`)
    expect(wrapper.emitted('refresh')).toBeTruthy()
  })

  it('handles API errors gracefully', async () => {
    api.delete.mockRejectedValue(new Error('API Error'))
    
    wrapper.vm.deleteBuilding(mockBuildings[0])
    await nextTick()

    const confirmButton = wrapper.find('[data-test="confirm-delete"]')
    await confirmButton.trigger('click')

    expect(toast.showToast).toHaveBeenCalledWith('Error deleting building', 'error')
  })

  it('emits refresh event when building is saved', async () => {
    wrapper.vm.handleSaved()
    await nextTick()

    expect(wrapper.emitted('refresh')).toBeTruthy()
    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.editingBuilding).toBeNull()
  })

  it('closes form without saving', async () => {
    wrapper.vm.editingBuilding = mockBuildings[0]
    wrapper.vm.showCreateModal = true
    await nextTick()

    wrapper.vm.handleClosed()
    await nextTick()

    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.editingBuilding).toBeNull()
  })

  it('formats address correctly', () => {
    const address = wrapper.vm.formatAddress(mockBuildings[0])
    expect(address).toBe('123 Test St, Test City, Test State')
  })

  it('formats area correctly', () => {
    const area = wrapper.vm.formatArea(mockBuildings[0].total_area)
    expect(area).toBe('50,000 sq ft')
  })
}) 