import BuildingList from './BuildingList.vue'
import BuildingForm from './BuildingForm.vue'
import BuildingDetails from './BuildingDetails.vue'

export {
  BuildingList,
  BuildingForm,
  BuildingDetails
}

export default {
  install(app) {
    app.component('BuildingList', BuildingList)
    app.component('BuildingForm', BuildingForm)
    app.component('BuildingDetails', BuildingDetails)
  }
} 