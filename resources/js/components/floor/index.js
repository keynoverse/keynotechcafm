import FloorList from './FloorList.vue'
import FloorForm from './FloorForm.vue'
import FloorDetails from './FloorDetails.vue'

export {
  FloorList,
  FloorForm,
  FloorDetails
}

export default {
  install(app) {
    app.component('FloorList', FloorList)
    app.component('FloorForm', FloorForm)
    app.component('FloorDetails', FloorDetails)
  }
} 