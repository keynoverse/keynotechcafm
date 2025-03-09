import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import WorkOrderList from '@/components/workorder/WorkOrderList.vue'
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

describe('WorkOrderList.vue', () => {
  let wrapper
  let api
  let toast

  const mockAsset = {
    id: 1,
    name: 'Test Asset'
  }

  const mockWorkOrders = [
    {
      id: 1,
      code: 'WO001',
      title: 'Test Work Order 1',
      description: 'Test description 1',
      type: 'corrective',
      priority: 'high',
      status: 'open',
      assigned_to: 1,
      asset_id: 1,
      due_date: '2024-03-20',
      progress: 0
    },
    {
      id: 2,
      code: 'WO002',
      title: 'Test Work Order 2',
      description: 'Test description 2',
      type: 'preventive',
      priority: 'medium',
      status: 'in_progress',
      assigned_to: 1,
      asset_id: 1,
      due_date: '2024-03-25',
      progress: 50
    }
  ]

  beforeEach(() => {
    // Reset mocks
    vi.clearAllMocks()

    // Setup API mock
    api = useApi()
    api.get.mockResolvedValue({ data: mockWorkOrders })

    // Setup Toast mock
    toast = useToast()

    // Mount component
    wrapper = mount(WorkOrderList, {
      props: {
        asset: mockAsset
      },
      global: {
        stubs: {
          BaseCard: true,
          BaseButton: true,
          BaseInput: true,
          BaseSelect: true,
          BaseTable: true,
          WorkOrderForm: true,
          WorkOrderDetails: true,
          BaseModal: true
        }
      }
    })
  })

  it('renders the component', () => {
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('h2').text()).toBe('Work Orders')
  })

  it('displays the work orders in a table', () => {
    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.exists()).toBe(true)
    expect(table.props('data')).toHaveLength(2)
  })

  it('filters work orders by search term', async () => {
    const searchInput = wrapper.findComponent({ name: 'BaseInput' })
    await searchInput.setValue('Test Work Order 1')
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(1)
    expect(table.props('data')[0].title).toBe('Test Work Order 1')
  })

  it('filters work orders by type', async () => {
    const typeSelect = wrapper.findComponent({ name: 'BaseSelect' })
    await typeSelect.setValue('corrective')
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(1)
    expect(table.props('data')[0].type).toBe('corrective')
  })

  it('filters work orders by status', async () => {
    const statusSelect = wrapper.findAllComponents({ name: 'BaseSelect' })[1]
    await statusSelect.setValue('open')
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(1)
    expect(table.props('data')[0].status).toBe('open')
  })

  it('filters work orders by priority', async () => {
    const prioritySelect = wrapper.findAllComponents({ name: 'BaseSelect' })[2]
    await prioritySelect.setValue('high')
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(1)
    expect(table.props('data')[0].priority).toBe('high')
  })

  it('opens create modal when add button is clicked', async () => {
    const addButton = wrapper.find('button')
    await addButton.trigger('click')

    expect(wrapper.vm.showCreateModal).toBe(true)
    expect(wrapper.findComponent({ name: 'WorkOrderForm' }).exists()).toBe(true)
  })

  it('opens view modal when view button is clicked', async () => {
    const viewButton = wrapper.find('[data-test="view-button"]')
    await viewButton.trigger('click')

    expect(wrapper.vm.showViewModal).toBe(true)
    expect(wrapper.findComponent({ name: 'WorkOrderDetails' }).exists()).toBe(true)
    expect(wrapper.vm.selectedWorkOrder).toEqual(mockWorkOrders[0])
  })

  it('opens edit modal when edit button is clicked', async () => {
    const editButton = wrapper.find('[data-test="edit-button"]')
    await editButton.trigger('click')

    expect(wrapper.vm.showCreateModal).toBe(true)
    expect(wrapper.vm.editingWorkOrder).toEqual(mockWorkOrders[0])
    expect(wrapper.findComponent({ name: 'WorkOrderForm' }).exists()).toBe(true)
  })

  it('shows delete confirmation when delete button is clicked', async () => {
    const deleteButton = wrapper.find('[data-test="delete-button"]')
    await deleteButton.trigger('click')

    expect(wrapper.vm.showDeleteModal).toBe(true)
    expect(wrapper.findComponent({ name: 'BaseModal' }).exists()).toBe(true)
  })

  it('deletes work order when confirmed', async () => {
    // Setup delete confirmation
    wrapper.vm.deleteWorkOrder(mockWorkOrders[0])
    await nextTick()

    // Confirm deletion
    const confirmButton = wrapper.find('[data-test="confirm-delete"]')
    await confirmButton.trigger('click')

    expect(api.delete).toHaveBeenCalledWith(`/api/work-orders/${mockWorkOrders[0].id}`)
    expect(wrapper.emitted('refresh')).toBeTruthy()
  })

  it('handles API errors gracefully', async () => {
    api.delete.mockRejectedValue(new Error('API Error'))
    
    wrapper.vm.deleteWorkOrder(mockWorkOrders[0])
    await nextTick()

    const confirmButton = wrapper.find('[data-test="confirm-delete"]')
    await confirmButton.trigger('click')

    expect(toast.showToast).toHaveBeenCalledWith('Error deleting work order', 'error')
  })

  it('emits refresh event when work order is saved', async () => {
    wrapper.vm.handleSaved()
    await nextTick()

    expect(wrapper.emitted('refresh')).toBeTruthy()
    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.editingWorkOrder).toBeNull()
  })

  it('closes form without saving', async () => {
    wrapper.vm.editingWorkOrder = mockWorkOrders[0]
    wrapper.vm.showCreateModal = true
    await nextTick()

    wrapper.vm.handleClosed()
    await nextTick()

    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.editingWorkOrder).toBeNull()
  })

  it('formats priority correctly', () => {
    const priority1 = wrapper.vm.formatPriority('high')
    const priority2 = wrapper.vm.formatPriority('medium')
    const priority3 = wrapper.vm.formatPriority('low')

    expect(priority1).toBe('High')
    expect(priority2).toBe('Medium')
    expect(priority3).toBe('Low')
  })

  it('formats status correctly', () => {
    const status1 = wrapper.vm.formatStatus('open')
    const status2 = wrapper.vm.formatStatus('in_progress')
    const status3 = wrapper.vm.formatStatus('completed')

    expect(status1).toBe('Open')
    expect(status2).toBe('In Progress')
    expect(status3).toBe('Completed')
  })

  it('formats progress correctly', () => {
    const progress1 = wrapper.vm.formatProgress(0)
    const progress2 = wrapper.vm.formatProgress(50)
    const progress3 = wrapper.vm.formatProgress(100)

    expect(progress1).toBe('0%')
    expect(progress2).toBe('50%')
    expect(progress3).toBe('100%')
  })

  it('formats due date correctly', () => {
    const dueDate = wrapper.vm.formatDueDate('2024-03-20')
    expect(dueDate).toMatch(/Mar 20, 2024/)
  })
}) 