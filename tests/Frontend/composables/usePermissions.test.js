import { usePermissions } from '@/composables/usePermissions'
import { useStore } from 'vuex'
import { computed } from 'vue'

// Mock Vuex store
vi.mock('vuex', () => ({
    useStore: vi.fn(() => ({
        state: {
            auth: {
                user: {
                    permissions: [
                        'view_assets',
                        'create_assets',
                        'view_work_orders',
                        'create_work_orders'
                    ]
                }
            }
        }
    }))
}))

describe('usePermissions', () => {
    let permissions

    beforeEach(() => {
        // Reset mocks
        vi.clearAllMocks()
        // Create new instance
        permissions = usePermissions()
    })

    it('checks single permission correctly', () => {
        expect(permissions.hasPermission('view_assets')).toBe(true)
        expect(permissions.hasPermission('delete_assets')).toBe(false)
    })

    it('checks if user has any of the given permissions', () => {
        expect(permissions.hasAnyPermission(['view_assets', 'delete_assets'])).toBe(true)
        expect(permissions.hasAnyPermission(['edit_assets', 'delete_assets'])).toBe(false)
    })

    it('checks if user has all of the given permissions', () => {
        expect(permissions.hasAllPermissions(['view_assets', 'create_assets'])).toBe(true)
        expect(permissions.hasAllPermissions(['view_assets', 'delete_assets'])).toBe(false)
    })

    it('provides computed permission checks', () => {
        expect(permissions.can.view.assets).toBe(true)
        expect(permissions.can.create.assets).toBe(true)
        expect(permissions.can.edit.assets).toBe(false)
        expect(permissions.can.delete.assets).toBe(false)
    })

    it('provides computed permission checks for work orders', () => {
        expect(permissions.can.view.workOrders).toBe(true)
        expect(permissions.can.create.workOrders).toBe(true)
        expect(permissions.can.edit.workOrders).toBe(false)
        expect(permissions.can.delete.workOrders).toBe(false)
    })

    it('handles undefined user permissions', () => {
        // Mock store with undefined permissions
        vi.mocked(useStore).mockImplementation(() => ({
            state: {
                auth: {
                    user: {
                        permissions: undefined
                    }
                }
            }
        }))

        permissions = usePermissions()

        expect(permissions.hasPermission('view_assets')).toBe(false)
        expect(permissions.hasAnyPermission(['view_assets', 'create_assets'])).toBe(false)
        expect(permissions.hasAllPermissions(['view_assets', 'create_assets'])).toBe(false)
    })

    it('handles null user', () => {
        // Mock store with null user
        vi.mocked(useStore).mockImplementation(() => ({
            state: {
                auth: {
                    user: null
                }
            }
        }))

        permissions = usePermissions()

        expect(permissions.hasPermission('view_assets')).toBe(false)
        expect(permissions.hasAnyPermission(['view_assets', 'create_assets'])).toBe(false)
        expect(permissions.hasAllPermissions(['view_assets', 'create_assets'])).toBe(false)
    })

    it('handles permission check with wildcard', () => {
        // Mock store with wildcard permission
        vi.mocked(useStore).mockImplementation(() => ({
            state: {
                auth: {
                    user: {
                        permissions: ['*']
                    }
                }
            }
        }))

        permissions = usePermissions()

        expect(permissions.hasPermission('view_assets')).toBe(true)
        expect(permissions.hasPermission('any_permission')).toBe(true)
        expect(permissions.hasAllPermissions(['view_assets', 'delete_assets'])).toBe(true)
    })

    it('provides module-specific permission checks', () => {
        const assetPermissions = permissions.forModule('assets')
        const workOrderPermissions = permissions.forModule('work_orders')

        expect(assetPermissions.canView).toBe(true)
        expect(assetPermissions.canCreate).toBe(true)
        expect(assetPermissions.canEdit).toBe(false)
        expect(assetPermissions.canDelete).toBe(false)

        expect(workOrderPermissions.canView).toBe(true)
        expect(workOrderPermissions.canCreate).toBe(true)
        expect(workOrderPermissions.canEdit).toBe(false)
        expect(workOrderPermissions.canDelete).toBe(false)
    })

    it('checks role-based permissions', () => {
        // Mock store with roles
        vi.mocked(useStore).mockImplementation(() => ({
            state: {
                auth: {
                    user: {
                        roles: ['admin'],
                        permissions: ['view_assets']
                    }
                }
            }
        }))

        permissions = usePermissions()

        expect(permissions.hasRole('admin')).toBe(true)
        expect(permissions.hasRole('user')).toBe(false)
        expect(permissions.hasAnyRole(['admin', 'manager'])).toBe(true)
        expect(permissions.hasAllRoles(['admin', 'manager'])).toBe(false)
    })

    it('combines role and permission checks', () => {
        // Mock store with roles and permissions
        vi.mocked(useStore).mockImplementation(() => ({
            state: {
                auth: {
                    user: {
                        roles: ['manager'],
                        permissions: ['view_assets', 'create_assets']
                    }
                }
            }
        }))

        permissions = usePermissions()

        expect(permissions.can.manage.assets).toBe(true) // Based on role
        expect(permissions.can.create.assets).toBe(true) // Based on permission
        expect(permissions.can.delete.assets).toBe(false) // No role or permission
    })

    it('caches permission checks', () => {
        const store = useStore()
        const getPermissionsSpy = vi.spyOn(store.state.auth.user, 'permissions', 'get')

        // Call multiple times
        permissions.hasPermission('view_assets')
        permissions.hasPermission('view_assets')
        permissions.hasPermission('view_assets')

        // Should only access the permissions array once
        expect(getPermissionsSpy).toHaveBeenCalledTimes(1)
    })

    it('updates permissions when user changes', () => {
        let user = {
            permissions: ['view_assets']
        }

        // Mock reactive store
        vi.mocked(useStore).mockImplementation(() => ({
            state: {
                auth: {
                    get user() {
                        return user
                    }
                }
            }
        }))

        permissions = usePermissions()

        expect(permissions.hasPermission('view_assets')).toBe(true)
        expect(permissions.hasPermission('create_assets')).toBe(false)

        // Update user permissions
        user = {
            permissions: ['view_assets', 'create_assets']
        }

        expect(permissions.hasPermission('create_assets')).toBe(true)
    })
}) 