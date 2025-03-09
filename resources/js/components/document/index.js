import DocumentList from './DocumentList.vue'
import DocumentUpload from './DocumentUpload.vue'
import DocumentViewer from './DocumentViewer.vue'

export {
  DocumentList,
  DocumentUpload,
  DocumentViewer
}

export default {
  install(app) {
    app.component('DocumentList', DocumentList)
    app.component('DocumentUpload', DocumentUpload)
    app.component('DocumentViewer', DocumentViewer)
  }
} 