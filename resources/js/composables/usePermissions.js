import { computed } from 'vue'
import { useStore } from 'vuex'

export function usePermissions(module) {
  const store = useStore()

  const can = computed(() => ({
    view: store.getters['auth/hasPermission'](`view ${module}`),
    create: store.getters['auth/hasPermission'](`create ${module}`),
    edit: store.getters['auth/hasPermission'](`edit ${module}`),
    delete: store.getters['auth/hasPermission'](`delete ${module}`)
  }))

  const hasPermission = (permission) => {
    return store.getters['auth/hasPermission'](permission)
  }

  const hasAnyPermission = (permissions) => {
    return permissions.some(permission => hasPermission(permission))
  }

  const hasAllPermissions = (permissions) => {
    return permissions.every(permission => hasPermission(permission))
  }

  return {
    can,
    hasPermission,
    hasAnyPermission,
    hasAllPermissions
  }
} 