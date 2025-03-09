import BaseButton from './BaseButton.vue'
import BaseInput from './BaseInput.vue'
import BaseSelect from './BaseSelect.vue'
import BaseCard from './BaseCard.vue'
import BaseModal from './BaseModal.vue'
import BaseTable from './BaseTable.vue'

export {
  BaseButton,
  BaseInput,
  BaseSelect,
  BaseCard,
  BaseModal,
  BaseTable
}

export default {
  install(app) {
    app.component('BaseButton', BaseButton)
    app.component('BaseInput', BaseInput)
    app.component('BaseSelect', BaseSelect)
    app.component('BaseCard', BaseCard)
    app.component('BaseModal', BaseModal)
    app.component('BaseTable', BaseTable)
  }
} 