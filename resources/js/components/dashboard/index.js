import Overview from './Overview.vue'
import Statistics from './Statistics.vue'
import Charts from './Charts.vue'

export {
  Overview,
  Statistics,
  Charts
}

export default {
  install(app) {
    app.component('DashboardOverview', Overview)
    app.component('DashboardStatistics', Statistics)
    app.component('DashboardCharts', Charts)
  }
} 