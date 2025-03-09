import AssetList from './AssetList.vue'
import AssetForm from './AssetForm.vue'
import AssetDetails from './AssetDetails.vue'

export {
  AssetList,
  AssetForm,
  AssetDetails
}

export default {
  install(app) {
    app.component('AssetList', AssetList)
    app.component('AssetForm', AssetForm)
    app.component('AssetDetails', AssetDetails)
  }
} 