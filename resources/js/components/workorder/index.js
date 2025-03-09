import WorkOrderList from './WorkOrderList.vue'
import WorkOrderForm from './WorkOrderForm.vue'
import WorkOrderDetails from './WorkOrderDetails.vue'

export {
  WorkOrderList,
  WorkOrderForm,
  WorkOrderDetails
}

export default {
  install(app) {
    app.component('WorkOrderList', WorkOrderList)
    app.component('WorkOrderForm', WorkOrderForm)
    app.component('WorkOrderDetails', WorkOrderDetails)
  }
} 