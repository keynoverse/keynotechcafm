import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import SpaceList from '@/components/space/SpaceList.vue'
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

describe('SpaceList.vue', () => {
  let wrapper
  let api
  let toast

  const mockBuilding = {
    id: 1,
    name: 'Test Building'
  }

  const mockFloor = {
    id: 1,
    building_id: 1,
    name: 'First Floor'
  }

  const mockSpaces = [
    {
      id: 1,
      floor_id: 1,
      name: 'Conference Room A',
      type: 'meeting_room',
      area: 500,
      status: 'vacant',
      occupant_id: null,
      assets_count: 5
    },
    {
      id: 2,
      floor_id: 1,
      name: 'Office 101',
      type: 'office',
      area: 300,
      status: 'occupied',
      occupant_id: 1,
      assets_count: 3
    }
  ]

  beforeEach(() => {
    // Reset mocks
    vi.clearAllMocks()

    // Setup API mock
    api = useApi()
    api.get.mockResolvedValue({ data: mockSpaces })

    // Setup Toast mock
    toast = useToast()

    // Mount component
    wrapper = mount(SpaceList, {
      props: {
        building: mockBuilding,
        floor: mockFloor
      },
      global: {
        stubs: {
          BaseCard: true,
          BaseButton: true,
          BaseInput: true,
          BaseSelect: true,
          BaseTable: true,
          SpaceForm: true,
          SpaceDetails: true,
          BaseModal: true
        }
      }
    })
  })

  it('renders the component', () => {
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('h2').text()).toBe('Spaces')
  })

  it('displays the spaces in a table', () => {
    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.exists()).toBe(true)
    expect(table.props('data')).toHaveLength(2)
  })

  it('filters spaces by search term', async () => {
    const searchInput = wrapper.findComponent({ name: 'BaseInput' })
    await searchInput.setValue('Conference Room')
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(1)
    expect(table.props('data')[0].name).toBe('Conference Room A')
  })

  it('filters spaces by type', async () => {
    const typeSelect = wrapper.findComponent({ name: 'BaseSelect' })
    await typeSelect.setValue('meeting_room')
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(1)
    expect(table.props('data')[0].type).toBe('meeting_room')
  })

  it('filters spaces by status', async () => {
    const statusSelect = wrapper.findAllComponents({ name: 'BaseSelect' })[1]
    await statusSelect.setValue('vacant')
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(1)
    expect(table.props('data')[0].status).toBe('vacant')
  })

  it('opens create modal when add button is clicked', async () => {
    const addButton = wrapper.find('button')
    await addButton.trigger('click')

    expect(wrapper.vm.showCreateModal).toBe(true)
    expect(wrapper.findComponent({ name: 'SpaceForm' }).exists()).toBe(true)
  })

  it('opens view modal when view button is clicked', async () => {
    const viewButton = wrapper.find('[data-test="view-button"]')
    await viewButton.trigger('click')

    expect(wrapper.vm.showViewModal).toBe(true)
    expect(wrapper.findComponent({ name: 'SpaceDetails' }).exists()).toBe(true)
    expect(wrapper.vm.selectedSpace).toEqual(mockSpaces[0])
  })

  it('opens edit modal when edit button is clicked', async () => {
    const editButton = wrapper.find('[data-test="edit-button"]')
    await editButton.trigger('click')

    expect(wrapper.vm.showCreateModal).toBe(true)
    expect(wrapper.vm.editingSpace).toEqual(mockSpaces[0])
    expect(wrapper.findComponent({ name: 'SpaceForm' }).exists()).toBe(true)
  })

  it('shows delete confirmation when delete button is clicked', async () => {
    const deleteButton = wrapper.find('[data-test="delete-button"]')
    await deleteButton.trigger('click')

    expect(wrapper.vm.showDeleteModal).toBe(true)
    expect(wrapper.findComponent({ name: 'BaseModal' }).exists()).toBe(true)
  })

  it('deletes space when confirmed', async () => {
    // Setup delete confirmation
    wrapper.vm.deleteSpace(mockSpaces[0])
    await nextTick()

    // Confirm deletion
    const confirmButton = wrapper.find('[data-test="confirm-delete"]')
    await confirmButton.trigger('click')

    expect(api.delete).toHaveBeenCalledWith(`/api/spaces/${mockSpaces[0].id}`)
    expect(wrapper.emitted('refresh')).toBeTruthy()
  })

  it('handles API errors gracefully', async () => {
    api.delete.mockRejectedValue(new Error('API Error'))
    
    wrapper.vm.deleteSpace(mockSpaces[0])
    await nextTick()

    const confirmButton = wrapper.find('[data-test="confirm-delete"]')
    await confirmButton.trigger('click')

    expect(toast.showToast).toHaveBeenCalledWith('Error deleting space', 'error')
  })

  it('emits refresh event when space is saved', async () => {
    wrapper.vm.handleSaved()
    await nextTick()

    expect(wrapper.emitted('refresh')).toBeTruthy()
    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.editingSpace).toBeNull()
  })

  it('closes form without saving', async () => {
    wrapper.vm.editingSpace = mockSpaces[0]
    wrapper.vm.showCreateModal = true
    await nextTick()

    wrapper.vm.handleClosed()
    await nextTick()

    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.editingSpace).toBeNull()
  })

  it('formats area correctly', () => {
    const area = wrapper.vm.formatArea(mockSpaces[0].area)
    expect(area).toBe('500 sq ft')
  })

  it('formats occupancy status correctly', () => {
    const status1 = wrapper.vm.formatOccupancyStatus(mockSpaces[0])
    const status2 = wrapper.vm.formatOccupancyStatus(mockSpaces[1])

    expect(status1).toBe('Vacant')
    expect(status2).toBe('Occupied')
  })

  it('displays correct asset count', () => {
    const assets1 = wrapper.vm.formatAssetCount(mockSpaces[0])
    const assets2 = wrapper.vm.formatAssetCount(mockSpaces[1])

    expect(assets1).toBe('5 assets')
    expect(assets2).toBe('3 assets')
  })
}) 