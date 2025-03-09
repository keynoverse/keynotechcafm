import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import MaintenanceScheduleList from '@/components/maintenance/MaintenanceScheduleList.vue'
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

describe('MaintenanceScheduleList.vue', () => {
  let wrapper
  let api
  let toast

  const mockAsset = {
    id: 1,
    name: 'Test Asset'
  }

  const mockSchedules = [
    {
      id: 1,
      asset_id: 1,
      title: 'Monthly Maintenance',
      description: 'Regular monthly maintenance check',
      frequency: 30,
      frequency_unit: 'days',
      next_due_date: '2024-03-20',
      last_completed_at: '2024-02-20',
      assigned_to: 1,
      status: 'active',
      completion_rate: 100
    },
    {
      id: 2,
      asset_id: 1,
      title: 'Quarterly Inspection',
      description: 'Detailed quarterly inspection',
      frequency: 90,
      frequency_unit: 'days',
      next_due_date: '2024-05-15',
      last_completed_at: '2024-02-15',
      assigned_to: 1,
      status: 'paused',
      completion_rate: 75
    }
  ]

  beforeEach(() => {
    // Reset mocks
    vi.clearAllMocks()

    // Setup API mock
    api = useApi()
    api.get.mockResolvedValue({ data: mockSchedules })

    // Setup Toast mock
    toast = useToast()

    // Mount component
    wrapper = mount(MaintenanceScheduleList, {
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
          MaintenanceScheduleForm: true,
          MaintenanceScheduleDetails: true,
          BaseModal: true
        }
      }
    })
  })

  it('renders the component', () => {
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('h2').text()).toBe('Maintenance Schedules')
  })

  it('displays the maintenance schedules in a table', () => {
    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.exists()).toBe(true)
    expect(table.props('data')).toHaveLength(2)
  })

  it('filters schedules by search term', async () => {
    const searchInput = wrapper.findComponent({ name: 'BaseInput' })
    await searchInput.setValue('Monthly Maintenance')
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(1)
    expect(table.props('data')[0].title).toBe('Monthly Maintenance')
  })

  it('filters schedules by status', async () => {
    const statusSelect = wrapper.findComponent({ name: 'BaseSelect' })
    await statusSelect.setValue('active')
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(1)
    expect(table.props('data')[0].status).toBe('active')
  })

  it('filters schedules by frequency', async () => {
    const frequencySelect = wrapper.findAllComponents({ name: 'BaseSelect' })[1]
    await frequencySelect.setValue('monthly')
    await nextTick()

    const table = wrapper.findComponent({ name: 'BaseTable' })
    expect(table.props('data')).toHaveLength(1)
    expect(table.props('data')[0].frequency).toBe(30)
  })

  it('opens create modal when add button is clicked', async () => {
    const addButton = wrapper.find('button')
    await addButton.trigger('click')

    expect(wrapper.vm.showCreateModal).toBe(true)
    expect(wrapper.findComponent({ name: 'MaintenanceScheduleForm' }).exists()).toBe(true)
  })

  it('opens view modal when view button is clicked', async () => {
    const viewButton = wrapper.find('[data-test="view-button"]')
    await viewButton.trigger('click')

    expect(wrapper.vm.showViewModal).toBe(true)
    expect(wrapper.findComponent({ name: 'MaintenanceScheduleDetails' }).exists()).toBe(true)
    expect(wrapper.vm.selectedSchedule).toEqual(mockSchedules[0])
  })

  it('opens edit modal when edit button is clicked', async () => {
    const editButton = wrapper.find('[data-test="edit-button"]')
    await editButton.trigger('click')

    expect(wrapper.vm.showCreateModal).toBe(true)
    expect(wrapper.vm.editingSchedule).toEqual(mockSchedules[0])
    expect(wrapper.findComponent({ name: 'MaintenanceScheduleForm' }).exists()).toBe(true)
  })

  it('shows delete confirmation when delete button is clicked', async () => {
    const deleteButton = wrapper.find('[data-test="delete-button"]')
    await deleteButton.trigger('click')

    expect(wrapper.vm.showDeleteModal).toBe(true)
    expect(wrapper.findComponent({ name: 'BaseModal' }).exists()).toBe(true)
  })

  it('deletes schedule when confirmed', async () => {
    // Setup delete confirmation
    wrapper.vm.deleteSchedule(mockSchedules[0])
    await nextTick()

    // Confirm deletion
    const confirmButton = wrapper.find('[data-test="confirm-delete"]')
    await confirmButton.trigger('click')

    expect(api.delete).toHaveBeenCalledWith(`/api/maintenance-schedules/${mockSchedules[0].id}`)
    expect(wrapper.emitted('refresh')).toBeTruthy()
  })

  it('handles API errors gracefully', async () => {
    api.delete.mockRejectedValue(new Error('API Error'))
    
    wrapper.vm.deleteSchedule(mockSchedules[0])
    await nextTick()

    const confirmButton = wrapper.find('[data-test="confirm-delete"]')
    await confirmButton.trigger('click')

    expect(toast.showToast).toHaveBeenCalledWith('Error deleting maintenance schedule', 'error')
  })

  it('emits refresh event when schedule is saved', async () => {
    wrapper.vm.handleSaved()
    await nextTick()

    expect(wrapper.emitted('refresh')).toBeTruthy()
    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.editingSchedule).toBeNull()
  })

  it('closes form without saving', async () => {
    wrapper.vm.editingSchedule = mockSchedules[0]
    wrapper.vm.showCreateModal = true
    await nextTick()

    wrapper.vm.handleClosed()
    await nextTick()

    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.editingSchedule).toBeNull()
  })

  it('formats frequency correctly', () => {
    const frequency1 = wrapper.vm.formatFrequency(30, 'days')
    const frequency2 = wrapper.vm.formatFrequency(12, 'months')
    const frequency3 = wrapper.vm.formatFrequency(1, 'year')

    expect(frequency1).toBe('30 days')
    expect(frequency2).toBe('12 months')
    expect(frequency3).toBe('1 year')
  })

  it('formats status correctly', () => {
    const status1 = wrapper.vm.formatStatus('active')
    const status2 = wrapper.vm.formatStatus('paused')
    const status3 = wrapper.vm.formatStatus('completed')

    expect(status1).toBe('Active')
    expect(status2).toBe('Paused')
    expect(status3).toBe('Completed')
  })

  it('formats completion rate correctly', () => {
    const rate1 = wrapper.vm.formatCompletionRate(100)
    const rate2 = wrapper.vm.formatCompletionRate(75)
    const rate3 = wrapper.vm.formatCompletionRate(0)

    expect(rate1).toBe('100%')
    expect(rate2).toBe('75%')
    expect(rate3).toBe('0%')
  })

  it('formats dates correctly', () => {
    const date1 = wrapper.vm.formatDate('2024-03-20')
    const date2 = wrapper.vm.formatDate('2024-02-20')

    expect(date1).toMatch(/Mar 20, 2024/)
    expect(date2).toMatch(/Feb 20, 2024/)
  })

  it('calculates next due status correctly', () => {
    const status1 = wrapper.vm.getNextDueStatus('2024-03-20') // Future date
    const status2 = wrapper.vm.getNextDueStatus('2024-02-20') // Past date

    expect(status1).toBe('upcoming')
    expect(status2).toBe('overdue')
  })
}) 