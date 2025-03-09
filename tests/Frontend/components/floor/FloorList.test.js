import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import FloorList from '@/components/floor/FloorList.vue'
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

describe('FloorList.vue', () => {
  let wrapper
  let api
  let toast

  const mockBuilding = {
    id: 1,
    name: 'Test Building'
  }

  const mockFloors = [
    {
      id: 1,
      building_id: 1,
      name: 'First Floor',
      number: 1,
      total_area: 10000,
      status: 'active',
      spaces_count: 5,
      assets_count: 10
    },
    {
      id: 2,
      building_id: 1,
      name: 'Second Floor',
      number: 2,
      total_area: 12000,
      status: 'maintenance',
      spaces_count: 6,
      assets_count: 8
    }
  ]

  beforeEach(() => {
    // Reset mocks
    vi.clearAllMocks()

    // Setup API mock
    api = useApi()
    api.get.mockResolvedValue({ data: mockFloors })

    // Setup Toast mock
    toast = useToast()

    // Mount component
    wrapper = mount(FloorList, {
      props: {
        building: mockBuilding
      },
      global: {
        stubs: {
          BaseCard: true,
          BaseButton: true,
          BaseInput: true,
          BaseSelect: true,
          BaseTable: true,
          FloorForm: true,
          FloorDetails: true,
          BaseModal: true
        }
      }
    })
  })

  it('renders the component', () => {
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('h2').text()).toBe('Floors')
  })

  it('displays the floors in a table', () => {
    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.exists()).toBe(true)
    expect(table.props('data')).toHaveLength(2)
  })

  it('filters floors by search term', async () => {
    const searchInput = wrapper.findComponent({ name: 'BaseInput' })
    await searchInput.setValue('First Floor')
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(1)
    expect(table.props('data')[0].name).toBe('First Floor')
  })

  it('filters floors by status', async () => {
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
    expect(wrapper.findComponent({ name: 'FloorForm' }).exists()).toBe(true)
  })

  it('opens view modal when view button is clicked', async () => {
    const viewButton = wrapper.find('[data-test="view-button"]')
    await viewButton.trigger('click')

    expect(wrapper.vm.showViewModal).toBe(true)
    expect(wrapper.findComponent({ name: 'FloorDetails' }).exists()).toBe(true)
    expect(wrapper.vm.selectedFloor).toEqual(mockFloors[0])
  })

  it('opens edit modal when edit button is clicked', async () => {
    const editButton = wrapper.find('[data-test="edit-button"]')
    await editButton.trigger('click')

    expect(wrapper.vm.showCreateModal).toBe(true)
    expect(wrapper.vm.editingFloor).toEqual(mockFloors[0])
    expect(wrapper.findComponent({ name: 'FloorForm' }).exists()).toBe(true)
  })

  it('shows delete confirmation when delete button is clicked', async () => {
    const deleteButton = wrapper.find('[data-test="delete-button"]')
    await deleteButton.trigger('click')

    expect(wrapper.vm.showDeleteModal).toBe(true)
    expect(wrapper.findComponent({ name: 'BaseModal' }).exists()).toBe(true)
  })

  it('deletes floor when confirmed', async () => {
    // Setup delete confirmation
    wrapper.vm.deleteFloor(mockFloors[0])
    await nextTick()

    // Confirm deletion
    const confirmButton = wrapper.find('[data-test="confirm-delete"]')
    await confirmButton.trigger('click')

    expect(api.delete).toHaveBeenCalledWith(`/api/floors/${mockFloors[0].id}`)
    expect(wrapper.emitted('refresh')).toBeTruthy()
  })

  it('handles API errors gracefully', async () => {
    api.delete.mockRejectedValue(new Error('API Error'))
    
    wrapper.vm.deleteFloor(mockFloors[0])
    await nextTick()

    const confirmButton = wrapper.find('[data-test="confirm-delete"]')
    await confirmButton.trigger('click')

    expect(toast.showToast).toHaveBeenCalledWith('Error deleting floor', 'error')
  })

  it('emits refresh event when floor is saved', async () => {
    wrapper.vm.handleSaved()
    await nextTick()

    expect(wrapper.emitted('refresh')).toBeTruthy()
    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.editingFloor).toBeNull()
  })

  it('closes form without saving', async () => {
    wrapper.vm.editingFloor = mockFloors[0]
    wrapper.vm.showCreateModal = true
    await nextTick()

    wrapper.vm.handleClosed()
    await nextTick()

    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.editingFloor).toBeNull()
  })

  it('formats area correctly', () => {
    const area = wrapper.vm.formatArea(mockFloors[0].total_area)
    expect(area).toBe('10,000 sq ft')
  })

  it('displays correct occupancy information', () => {
    const occupancy = wrapper.vm.formatOccupancy(mockFloors[0])
    expect(occupancy).toBe('5 spaces, 10 assets')
  })
}) 