import ReportList from './ReportList.vue'
import ReportGenerator from './ReportGenerator.vue'
import ReportViewer from './ReportViewer.vue'

export {
  ReportList,
  ReportGenerator,
  ReportViewer
}

export default {
  install(app) {
    app.component('ReportList', ReportList)
    app.component('ReportGenerator', ReportGenerator)
    app.component('ReportViewer', ReportViewer)
  }
} 