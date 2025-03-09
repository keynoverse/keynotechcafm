import MaintenanceScheduleList from './MaintenanceScheduleList.vue'
import MaintenanceScheduleForm from './MaintenanceScheduleForm.vue'
import MaintenanceScheduleDetails from './MaintenanceScheduleDetails.vue'

export {
  MaintenanceScheduleList,
  MaintenanceScheduleForm,
  MaintenanceScheduleDetails
}

export default {
  install(app) {
    app.component('MaintenanceScheduleList', MaintenanceScheduleList)
    app.component('MaintenanceScheduleForm', MaintenanceScheduleForm)
    app.component('MaintenanceScheduleDetails', MaintenanceScheduleDetails)
  }
} 