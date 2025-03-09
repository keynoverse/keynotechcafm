<template>
  <div class="space-y-8">
    <!-- Filters -->
    <section>
      <BaseCard>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
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
          <BaseSelect
            v-model="filters.chartType"
            label="Chart Type"
            :options="chartTypeOptions"
            @update:model-value="fetchData"
          />
        </div>
      </BaseCard>
    </section>

    <!-- Charts Grid -->
    <section class="grid grid-cols-1 gap-8">
      <!-- Work Order Trends -->
      <BaseCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Work Order Trends</h3>
            <div class="flex items-center space-x-4">
              <BaseSelect
                v-model="workOrderChartType"
                :options="workOrderChartOptions"
                size="sm"
              />
              <BaseButton
                variant="secondary"
                size="sm"
                @click="downloadChart('workOrders')"
              >
                Download
              </BaseButton>
            </div>
          </div>
        </template>
        <div class="h-96">
          <canvas ref="workOrdersChart"></canvas>
        </div>
      </BaseCard>

      <!-- Asset Metrics -->
      <BaseCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Asset Metrics</h3>
            <div class="flex items-center space-x-4">
              <BaseSelect
                v-model="assetMetricsChartType"
                :options="assetMetricsChartOptions"
                size="sm"
              />
              <BaseButton
                variant="secondary"
                size="sm"
                @click="downloadChart('assetMetrics')"
              >
                Download
              </BaseButton>
            </div>
          </div>
        </template>
        <div class="h-96">
          <canvas ref="assetMetricsChart"></canvas>
        </div>
      </BaseCard>

      <!-- Maintenance Analytics -->
      <BaseCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Maintenance Analytics</h3>
            <div class="flex items-center space-x-4">
              <BaseSelect
                v-model="maintenanceChartType"
                :options="maintenanceChartOptions"
                size="sm"
              />
              <BaseButton
                variant="secondary"
                size="sm"
                @click="downloadChart('maintenance')"
              >
                Download
              </BaseButton>
            </div>
          </div>
        </template>
        <div class="h-96">
          <canvas ref="maintenanceChart"></canvas>
        </div>
      </BaseCard>

      <!-- Cost Distribution -->
      <BaseCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Cost Distribution</h3>
            <div class="flex items-center space-x-4">
              <BaseSelect
                v-model="costChartType"
                :options="costChartOptions"
                size="sm"
              />
              <BaseButton
                variant="secondary"
                size="sm"
                @click="downloadChart('cost')"
              >
                Download
              </BaseButton>
            </div>
          </div>
        </template>
        <div class="h-96">
          <canvas ref="costChart"></canvas>
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
  name: 'Charts',

  setup() {
    const { showToast } = useToast()
    const api = useApi()

    // Chart references
    const workOrdersChart = ref(null)
    const assetMetricsChart = ref(null)
    const maintenanceChart = ref(null)
    const costChart = ref(null)

    // Chart instances
    let charts = {
      workOrders: null,
      assetMetrics: null,
      maintenance: null,
      cost: null
    }

    // State
    const filters = ref({
      timeRange: 'last_30_days',
      category: 'all',
      groupBy: 'day',
      chartType: 'line'
    })

    const workOrderChartType = ref('line')
    const assetMetricsChartType = ref('bar')
    const maintenanceChartType = ref('doughnut')
    const costChartType = ref('bar')

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

    const chartTypeOptions = [
      { value: 'line', label: 'Line Chart' },
      { value: 'bar', label: 'Bar Chart' },
      { value: 'pie', label: 'Pie Chart' },
      { value: 'doughnut', label: 'Doughnut Chart' }
    ]

    const workOrderChartOptions = [
      { value: 'line', label: 'Line' },
      { value: 'bar', label: 'Bar' },
      { value: 'stacked', label: 'Stacked Bar' }
    ]

    const assetMetricsChartOptions = [
      { value: 'bar', label: 'Bar' },
      { value: 'radar', label: 'Radar' },
      { value: 'polarArea', label: 'Polar Area' }
    ]

    const maintenanceChartOptions = [
      { value: 'doughnut', label: 'Doughnut' },
      { value: 'pie', label: 'Pie' },
      { value: 'bar', label: 'Bar' }
    ]

    const costChartOptions = [
      { value: 'bar', label: 'Bar' },
      { value: 'line', label: 'Line' },
      { value: 'stacked', label: 'Stacked Bar' }
    ]

    // Methods
    const fetchData = async () => {
      try {
        const response = await api.get('/api/charts', { params: filters.value })
        updateCharts(response.data)
      } catch (error) {
        showToast('Error fetching chart data', 'error')
      }
    }

    const initializeCharts = () => {
      // Work Orders Chart
      charts.workOrders = new Chart(workOrdersChart.value, {
        type: workOrderChartType.value,
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

      // Asset Metrics Chart
      charts.assetMetrics = new Chart(assetMetricsChart.value, {
        type: assetMetricsChartType.value,
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

      // Maintenance Chart
      charts.maintenance = new Chart(maintenanceChart.value, {
        type: maintenanceChartType.value,
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

      // Cost Chart
      charts.cost = new Chart(costChart.value, {
        type: costChartType.value,
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
            label: 'Created',
            backgroundColor: '#60A5FA',
            borderColor: '#60A5FA',
            data: data.workOrders.created
          },
          {
            label: 'Completed',
            backgroundColor: '#34D399',
            borderColor: '#34D399',
            data: data.workOrders.completed
          },
          {
            label: 'Overdue',
            backgroundColor: '#F87171',
            borderColor: '#F87171',
            data: data.workOrders.overdue
          }
        ]
      }
      charts.workOrders.update()

      // Update Asset Metrics Chart
      charts.assetMetrics.data = {
        labels: data.assetMetrics.labels,
        datasets: [
          {
            label: 'Utilization',
            backgroundColor: '#60A5FA',
            data: data.assetMetrics.utilization
          },
          {
            label: 'Availability',
            backgroundColor: '#34D399',
            data: data.assetMetrics.availability
          },
          {
            label: 'Performance',
            backgroundColor: '#FCD34D',
            data: data.assetMetrics.performance
          }
        ]
      }
      charts.assetMetrics.update()

      // Update Maintenance Chart
      charts.maintenance.data = {
        labels: data.maintenance.labels,
        datasets: [{
          data: data.maintenance.data,
          backgroundColor: [
            '#34D399',
            '#FCD34D',
            '#F87171',
            '#60A5FA',
            '#A78BFA'
          ]
        }]
      }
      charts.maintenance.update()

      // Update Cost Chart
      charts.cost.data = {
        labels: data.cost.labels,
        datasets: [
          {
            label: 'Labor',
            backgroundColor: '#60A5FA',
            data: data.cost.labor
          },
          {
            label: 'Parts',
            backgroundColor: '#34D399',
            data: data.cost.parts
          },
          {
            label: 'Services',
            backgroundColor: '#FCD34D',
            data: data.cost.services
          }
        ]
      }
      charts.cost.update()
    }

    const downloadChart = (chartId) => {
      const canvas = charts[chartId].canvas
      const link = document.createElement('a')
      link.download = `${chartId}-chart.png`
      link.href = canvas.toDataURL('image/png')
      link.click()
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
      assetMetricsChart,
      maintenanceChart,
      costChart,

      // State
      filters,
      workOrderChartType,
      assetMetricsChartType,
      maintenanceChartType,
      costChartType,

      // Options
      timeRangeOptions,
      categoryOptions,
      groupByOptions,
      chartTypeOptions,
      workOrderChartOptions,
      assetMetricsChartOptions,
      maintenanceChartOptions,
      costChartOptions,

      // Methods
      fetchData,
      downloadChart
    }
  }
}
</script> 