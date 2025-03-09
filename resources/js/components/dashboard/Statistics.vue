<template>
  <div class="space-y-8">
    <!-- Filters -->
    <section>
      <BaseCard>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <BaseSelect
            v-model="filters.timeRange"
            label="Time Range"
            :options="timeRangeOptions"
            @update:model-value="fetchData"
          />
          <BaseSelect
            v-model="filters.category"
            label="Category"
            :options="categoryOptions"
            @update:model-value="fetchData"
          />
          <BaseSelect
            v-model="filters.groupBy"
            label="Group By"
            :options="groupByOptions"
            @update:model-value="fetchData"
          />
        </div>
      </BaseCard>
    </section>

    <!-- Key Performance Indicators -->
    <section>
      <h2 class="text-xl font-semibold text-gray-900 mb-4">Key Performance Indicators</h2>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <BaseCard>
          <div class="flex flex-col">
            <dt class="text-sm font-medium text-gray-500">Average Response Time</dt>
            <dd class="mt-1 text-3xl font-semibold text-blue-600">{{ formatDuration(kpis.avg_response_time) }}</dd>
            <dd class="mt-2 text-sm text-gray-500">
              <span
                :class="[
                  kpis.response_time_trend > 0 ? 'text-red-600' : 'text-green-600',
                  'font-medium'
                ]"
              >
                {{ Math.abs(kpis.response_time_trend) }}%
              </span>
              <span class="ml-1">vs previous period</span>
            </dd>
          </div>
        </BaseCard>

        <BaseCard>
          <div class="flex flex-col">
            <dt class="text-sm font-medium text-gray-500">Resolution Rate</dt>
            <dd class="mt-1 text-3xl font-semibold text-green-600">{{ kpis.resolution_rate }}%</dd>
            <dd class="mt-2 text-sm text-gray-500">
              <span
                :class="[
                  kpis.resolution_rate_trend > 0 ? 'text-green-600' : 'text-red-600',
                  'font-medium'
                ]"
              >
                {{ Math.abs(kpis.resolution_rate_trend) }}%
              </span>
              <span class="ml-1">vs previous period</span>
            </dd>
          </div>
        </BaseCard>

        <BaseCard>
          <div class="flex flex-col">
            <dt class="text-sm font-medium text-gray-500">Asset Utilization</dt>
            <dd class="mt-1 text-3xl font-semibold text-yellow-600">{{ kpis.asset_utilization }}%</dd>
            <dd class="mt-2 text-sm text-gray-500">
              <span
                :class="[
                  kpis.asset_utilization_trend > 0 ? 'text-green-600' : 'text-red-600',
                  'font-medium'
                ]"
              >
                {{ Math.abs(kpis.asset_utilization_trend) }}%
              </span>
              <span class="ml-1">vs previous period</span>
            </dd>
          </div>
        </BaseCard>

        <BaseCard>
          <div class="flex flex-col">
            <dt class="text-sm font-medium text-gray-500">Maintenance Compliance</dt>
            <dd class="mt-1 text-3xl font-semibold text-purple-600">{{ kpis.maintenance_compliance }}%</dd>
            <dd class="mt-2 text-sm text-gray-500">
              <span
                :class="[
                  kpis.maintenance_compliance_trend > 0 ? 'text-green-600' : 'text-red-600',
                  'font-medium'
                ]"
              >
                {{ Math.abs(kpis.maintenance_compliance_trend) }}%
              </span>
              <span class="ml-1">vs previous period</span>
            </dd>
          </div>
        </BaseCard>
      </div>
    </section>

    <!-- Charts -->
    <section class="grid grid-cols-1 gap-8 lg:grid-cols-2">
      <!-- Work Orders by Status -->
      <BaseCard>
        <template #header>
          <h3 class="text-lg font-medium text-gray-900">Work Orders by Status</h3>
        </template>
        <div class="h-80">
          <canvas ref="workOrdersChart"></canvas>
        </div>
      </BaseCard>

      <!-- Asset Performance -->
      <BaseCard>
        <template #header>
          <h3 class="text-lg font-medium text-gray-900">Asset Performance</h3>
        </template>
        <div class="h-80">
          <canvas ref="assetPerformanceChart"></canvas>
        </div>
      </BaseCard>

      <!-- Maintenance Schedule Compliance -->
      <BaseCard>
        <template #header>
          <h3 class="text-lg font-medium text-gray-900">Maintenance Schedule Compliance</h3>
        </template>
        <div class="h-80">
          <canvas ref="maintenanceComplianceChart"></canvas>
        </div>
      </BaseCard>

      <!-- Cost Analysis -->
      <BaseCard>
        <template #header>
          <h3 class="text-lg font-medium text-gray-900">Cost Analysis</h3>
        </template>
        <div class="h-80">
          <canvas ref="costAnalysisChart"></canvas>
        </div>
      </BaseCard>
    </section>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'
import { useToast } from '@/composables/useToast'
import { useApi } from '@/composables/useApi'
import Chart from 'chart.js/auto'

export default {
  name: 'Statistics',

  setup() {
    const { showToast } = useToast()
    const api = useApi()

    // Chart references
    const workOrdersChart = ref(null)
    const assetPerformanceChart = ref(null)
    const maintenanceComplianceChart = ref(null)
    const costAnalysisChart = ref(null)

    // Chart instances
    let charts = {
      workOrders: null,
      assetPerformance: null,
      maintenanceCompliance: null,
      costAnalysis: null
    }

    // State
    const filters = ref({
      timeRange: 'last_30_days',
      category: 'all',
      groupBy: 'day'
    })

    const kpis = ref({
      avg_response_time: 0,
      response_time_trend: 0,
      resolution_rate: 0,
      resolution_rate_trend: 0,
      asset_utilization: 0,
      asset_utilization_trend: 0,
      maintenance_compliance: 0,
      maintenance_compliance_trend: 0
    })

    // Options
    const timeRangeOptions = [
      { value: 'last_7_days', label: 'Last 7 Days' },
      { value: 'last_30_days', label: 'Last 30 Days' },
      { value: 'last_90_days', label: 'Last 90 Days' },
      { value: 'last_year', label: 'Last Year' }
    ]

    const categoryOptions = [
      { value: 'all', label: 'All Categories' },
      { value: 'mechanical', label: 'Mechanical' },
      { value: 'electrical', label: 'Electrical' },
      { value: 'plumbing', label: 'Plumbing' },
      { value: 'hvac', label: 'HVAC' }
    ]

    const groupByOptions = [
      { value: 'day', label: 'Day' },
      { value: 'week', label: 'Week' },
      { value: 'month', label: 'Month' }
    ]

    // Methods
    const fetchData = async () => {
      try {
        const [kpiResponse, chartData] = await Promise.all([
          api.get('/api/statistics/kpis', { params: filters.value }),
          api.get('/api/statistics/charts', { params: filters.value })
        ])

        kpis.value = kpiResponse.data
        updateCharts(chartData.data)
      } catch (error) {
        showToast('Error fetching statistics', 'error')
      }
    }

    const initializeCharts = () => {
      // Work Orders Chart
      charts.workOrders = new Chart(workOrdersChart.value, {
        type: 'bar',
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      })

      // Asset Performance Chart
      charts.assetPerformance = new Chart(assetPerformanceChart.value, {
        type: 'line',
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      })

      // Maintenance Compliance Chart
      charts.maintenanceCompliance = new Chart(maintenanceComplianceChart.value, {
        type: 'doughnut',
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      })

      // Cost Analysis Chart
      charts.costAnalysis = new Chart(costAnalysisChart.value, {
        type: 'bar',
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      })
    }

    const updateCharts = (data) => {
      // Update Work Orders Chart
      charts.workOrders.data = {
        labels: data.workOrders.labels,
        datasets: [
          {
            label: 'Open',
            backgroundColor: '#FCD34D',
            data: data.workOrders.open
          },
          {
            label: 'In Progress',
            backgroundColor: '#60A5FA',
            data: data.workOrders.inProgress
          },
          {
            label: 'Completed',
            backgroundColor: '#34D399',
            data: data.workOrders.completed
          }
        ]
      }
      charts.workOrders.update()

      // Update Asset Performance Chart
      charts.assetPerformance.data = {
        labels: data.assetPerformance.labels,
        datasets: [
          {
            label: 'Uptime',
            borderColor: '#34D399',
            data: data.assetPerformance.uptime
          },
          {
            label: 'Downtime',
            borderColor: '#EF4444',
            data: data.assetPerformance.downtime
          }
        ]
      }
      charts.assetPerformance.update()

      // Update Maintenance Compliance Chart
      charts.maintenanceCompliance.data = {
        labels: ['Compliant', 'Non-Compliant', 'Overdue'],
        datasets: [{
          data: [
            data.maintenanceCompliance.compliant,
            data.maintenanceCompliance.nonCompliant,
            data.maintenanceCompliance.overdue
          ],
          backgroundColor: ['#34D399', '#FCD34D', '#EF4444']
        }]
      }
      charts.maintenanceCompliance.update()

      // Update Cost Analysis Chart
      charts.costAnalysis.data = {
        labels: data.costAnalysis.labels,
        datasets: [
          {
            label: 'Maintenance Cost',
            backgroundColor: '#60A5FA',
            data: data.costAnalysis.maintenance
          },
          {
            label: 'Repair Cost',
            backgroundColor: '#F87171',
            data: data.costAnalysis.repair
          }
        ]
      }
      charts.costAnalysis.update()
    }

    const formatDuration = (minutes) => {
      const hours = Math.floor(minutes / 60)
      const remainingMinutes = minutes % 60
      return `${hours}h ${remainingMinutes}m`
    }

    // Lifecycle hooks
    onMounted(() => {
      initializeCharts()
      fetchData()
    })

    onUnmounted(() => {
      Object.values(charts).forEach(chart => {
        if (chart) {
          chart.destroy()
        }
      })
    })

    return {
      // Refs
      workOrdersChart,
      assetPerformanceChart,
      maintenanceComplianceChart,
      costAnalysisChart,

      // State
      filters,
      kpis,

      // Options
      timeRangeOptions,
      categoryOptions,
      groupByOptions,

      // Methods
      fetchData,
      formatDuration
    }
  }
}
</script> 