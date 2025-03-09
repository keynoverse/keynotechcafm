import SpaceList from './SpaceList.vue'
import SpaceForm from './SpaceForm.vue'
import SpaceDetails from './SpaceDetails.vue'

export {
  SpaceList,
  SpaceForm,
  SpaceDetails
}

export default {
  install(app) {
    app.component('SpaceList', SpaceList)
    app.component('SpaceForm', SpaceForm)
    app.component('SpaceDetails', SpaceDetails)
  }
} 