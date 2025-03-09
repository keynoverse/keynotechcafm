<template>
  <div class="space-y-8">
    <!-- Stats Overview -->
    <section>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Assets -->
        <BaseCard>
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Total Assets</h3>
              <p class="mt-1 text-3xl font-semibold text-blue-600">{{ stats.total_assets }}</p>
            </div>
          </div>
        </BaseCard>

        <!-- Active Work Orders -->
        <BaseCard>
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Active Work Orders</h3>
              <p class="mt-1 text-3xl font-semibold text-yellow-600">{{ stats.active_work_orders }}</p>
            </div>
          </div>
        </BaseCard>

        <!-- Scheduled Maintenance -->
        <BaseCard>
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Scheduled Maintenance</h3>
              <p class="mt-1 text-3xl font-semibold text-green-600">{{ stats.scheduled_maintenance }}</p>
            </div>
          </div>
        </BaseCard>

        <!-- Overdue Tasks -->
        <BaseCard>
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Overdue Tasks</h3>
              <p class="mt-1 text-3xl font-semibold text-red-600">{{ stats.overdue_tasks }}</p>
            </div>
          </div>
        </BaseCard>
      </div>
    </section>

    <!-- Recent Activity -->
    <section>
      <BaseCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900">Recent Activity</h2>
            <BaseButton
              variant="secondary"
              size="sm"
              @click="refreshActivity"
            >
              Refresh
            </BaseButton>
          </div>
        </template>

        <div class="flow-root">
          <ul class="-mb-8">
            <li v-for="(activity, index) in recentActivity" :key="activity.id">
              <div class="relative pb-8">
                <span
                  v-if="index !== recentActivity.length - 1"
                  class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                  aria-hidden="true"
                ></span>
                <div class="relative flex space-x-3">
                  <div>
                    <span
                      :class="[
                        'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white',
                        activityTypeClasses[activity.type]?.bg || 'bg-gray-400'
                      ]"
                    >
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
                          :d="activityTypeClasses[activity.type]?.icon || ''"
                        />
                      </svg>
                    </span>
                  </div>
                  <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                    <div>
                      <p class="text-sm text-gray-500">
                        {{ activity.description }}
                        <span class="font-medium text-gray-900">{{ activity.user }}</span>
                      </p>
                    </div>
                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                      <time :datetime="activity.created_at">{{ formatDate(activity.created_at) }}</time>
                    </div>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </BaseCard>
    </section>

    <!-- Quick Actions -->
    <section>
      <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <BaseCard
          v-for="action in quickActions"
          :key="action.name"
          class="hover:bg-gray-50 cursor-pointer transition-colors duration-200"
          @click="action.action"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg
                class="h-6 w-6"
                :class="action.iconColor"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  :d="action.icon"
                />
              </svg>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">{{ action.name }}</h3>
              <p class="mt-1 text-sm text-gray-500">{{ action.description }}</p>
            </div>
          </div>
        </BaseCard>
      </div>
    </section>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import { useApi } from '@/composables/useApi'
import { useRouter } from 'vue-router'

export default {
  name: 'Overview',

  setup() {
    const { showToast } = useToast()
    const api = useApi()
    const router = useRouter()

    // State
    const stats = ref({
      total_assets: 0,
      active_work_orders: 0,
      scheduled_maintenance: 0,
      overdue_tasks: 0
    })

    const recentActivity = ref([])

    // Activity type classes
    const activityTypeClasses = {
      work_order: {
        bg: 'bg-yellow-500',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'
      },
      maintenance: {
        bg: 'bg-blue-500',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
      },
      asset: {
        bg: 'bg-green-500',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'
      },
      document: {
        bg: 'bg-purple-500',
        icon: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'
      }
    }

    // Quick actions
    const quickActions = [
      {
        name: 'Create Work Order',
        description: 'Create a new work order for maintenance or repair',
        icon: 'M12 6v6m0 0v6m0-6h6m-6 0H6',
        iconColor: 'text-yellow-600',
        action: () => router.push('/work-orders/create')
      },
      {
        name: 'Schedule Maintenance',
        description: 'Schedule preventive maintenance for assets',
        icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        iconColor: 'text-blue-600',
        action: () => router.push('/maintenance/schedule')
      },
      {
        name: 'Add Asset',
        description: 'Register a new asset in the system',
        icon: 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z',
        iconColor: 'text-green-600',
        action: () => router.push('/assets/create')
      },
      {
        name: 'Upload Document',
        description: 'Upload documentation or reports',
        icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',
        iconColor: 'text-purple-600',
        action: () => router.push('/documents')
      }
    ]

    // Methods
    const fetchStats = async () => {
      try {
        const response = await api.get('/api/dashboard/stats')
        stats.value = response.data
      } catch (error) {
        showToast('Error fetching dashboard statistics', 'error')
      }
    }

    const fetchActivity = async () => {
      try {
        const response = await api.get('/api/dashboard/activity')
        recentActivity.value = response.data
      } catch (error) {
        showToast('Error fetching recent activity', 'error')
      }
    }

    const refreshActivity = async () => {
      await fetchActivity()
      showToast('Activity feed refreshed')
    }

    const formatDate = (date) => {
      if (!date) return ''
      return new Date(date).toLocaleDateString()
    }

    // Lifecycle hooks
    onMounted(async () => {
      await Promise.all([
        fetchStats(),
        fetchActivity()
      ])
    })

    return {
      stats,
      recentActivity,
      activityTypeClasses,
      quickActions,
      refreshActivity,
      formatDate
    }
  }
}
</script> 