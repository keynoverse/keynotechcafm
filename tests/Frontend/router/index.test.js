import { createRouter, createWebHistory } from 'vue-router'
import { createStore } from 'vuex'
import routes from '@/router/routes'
import { usePermissions } from '@/composables/usePermissions'

// Mock store
const mockStore = createStore({
    modules: {
        auth: {
            namespaced: true,
            state: {
                user: null
            },
            getters: {
                isAuthenticated: state => !!state.user
            }
        }
    }
})

// Mock usePermissions
vi.mock('@/composables/usePermissions', () => ({
    usePermissions: vi.fn(() => ({
        hasPermission: vi.fn(),
        hasAnyPermission: vi.fn(),
        hasAllPermissions: vi.fn()
    }))
}))

describe('Router', () => {
    let router
    let permissions

    beforeEach(() => {
        // Reset store state
        mockStore.state.auth.user = null

        // Create router instance
        router = createRouter({
            history: createWebHistory(),
            routes
        })

        // Get permissions instance
        permissions = usePermissions()

        // Reset router
        router.push('/')
    })

    describe('Navigation Guards', () => {
        it('redirects to login when accessing protected route while not authenticated', async () => {
            await router.push('/dashboard')

            expect(router.currentRoute.value.path).toBe('/login')
        })

        it('allows access to protected route when authenticated', async () => {
            // Set authenticated user
            mockStore.state.auth.user = {
                id: 1,
                name: 'Test User'
            }

            await router.push('/dashboard')

            expect(router.currentRoute.value.path).toBe('/dashboard')
        })

        it('redirects to dashboard when accessing login while authenticated', async () => {
            // Set authenticated user
            mockStore.state.auth.user = {
                id: 1,
                name: 'Test User'
            }

            await router.push('/login')

            expect(router.currentRoute.value.path).toBe('/dashboard')
        })

        it('checks permissions before accessing route', async () => {
            // Set authenticated user
            mockStore.state.auth.user = {
                id: 1,
                name: 'Test User'
            }

            // Mock permission check
            permissions.hasPermission.mockImplementation(permission => {
                return permission === 'view_assets'
            })

            // Try accessing route that requires permission
            await router.push('/assets')

            expect(router.currentRoute.value.path).toBe('/assets')

            // Try accessing route without permission
            permissions.hasPermission.mockReturnValue(false)
            await router.push('/admin')

            expect(router.currentRoute.value.path).toBe('/403')
        })
    })

    describe('Route Meta Handling', () => {
        it('sets document title based on route meta', async () => {
            await router.push('/dashboard')

            expect(document.title).toBe('Dashboard | CAFM System')
        })

        it('handles breadcrumbs from route meta', async () => {
            await router.push('/assets/1/edit')

            const route = router.currentRoute.value
            expect(route.meta.breadcrumbs).toEqual([
                { text: 'Assets', to: '/assets' },
                { text: 'Edit Asset' }
            ])
        })
    })

    describe('Route Parameters', () => {
        it('handles dynamic route parameters', async () => {
            await router.push('/assets/1')

            const route = router.currentRoute.value
            expect(route.params.id).toBe('1')
        })

        it('handles query parameters', async () => {
            await router.push('/assets?status=active&sort=name')

            const route = router.currentRoute.value
            expect(route.query).toEqual({
                status: 'active',
                sort: 'name'
            })
        })
    })

    describe('Route Guards', () => {
        it('prevents navigation when there are unsaved changes', async () => {
            // Mock unsaved changes
            router.beforeEach((to, from, next) => {
                if (from.path === '/assets/1/edit' && !window.confirm('Discard changes?')) {
                    next(false)
                    return
                }
                next()
            })

            // Navigate to edit page
            await router.push('/assets/1/edit')

            // Mock user cancelling navigation
            window.confirm = vi.fn(() => false)

            // Try to navigate away
            await router.push('/dashboard')

            // Should still be on edit page
            expect(router.currentRoute.value.path).toBe('/assets/1/edit')
        })

        it('handles route loading state', async () => {
            let isLoading = false

            // Add loading guard
            router.beforeEach((to, from, next) => {
                isLoading = true
                next()
            })

            router.afterEach(() => {
                isLoading = false
            })

            // Navigate to route
            const navigationPromise = router.push('/dashboard')
            expect(isLoading).toBe(true)

            await navigationPromise
            expect(isLoading).toBe(false)
        })
    })

    describe('Error Handling', () => {
        it('redirects to 404 for non-existent route', async () => {
            await router.push('/non-existent-route')

            expect(router.currentRoute.value.path).toBe('/404')
        })

        it('handles navigation errors', async () => {
            // Mock route that always fails
            router.addRoute({
                path: '/error',
                component: { render: () => { throw new Error('Route Error') } }
            })

            try {
                await router.push('/error')
            } catch (error) {
                expect(error.message).toBe('Route Error')
            }
        })
    })

    describe('Route Transitions', () => {
        it('applies transition based on route meta', async () => {
            // Add route with transition meta
            router.addRoute({
                path: '/transition-test',
                component: { template: '<div>Test</div>' },
                meta: { transition: 'fade' }
            })

            await router.push('/transition-test')

            const route = router.currentRoute.value
            expect(route.meta.transition).toBe('fade')
        })
    })

    describe('Nested Routes', () => {
        it('handles nested route navigation', async () => {
            await router.push('/assets/1/maintenance-history')

            const route = router.currentRoute.value
            expect(route.matched).toHaveLength(2) // Parent and child route
            expect(route.path).toBe('/assets/1/maintenance-history')
        })

        it('preserves parent route state when navigating between children', async () => {
            // Navigate to parent with some state
            await router.push('/assets/1')
            const parentComponent = { /* mock parent component */ }

            // Navigate between child routes
            await router.push('/assets/1/maintenance-history')
            await router.push('/assets/1/work-orders')

            expect(router.currentRoute.value.matched[0].instances.default).toBe(parentComponent)
        })
    })

    describe('Route Aliases', () => {
        it('handles route aliases', async () => {
            // Add route with alias
            router.addRoute({
                path: '/maintenance-schedules',
                alias: '/schedules',
                component: { template: '<div>Schedules</div>' }
            })

            await router.push('/schedules')

            expect(router.currentRoute.value.path).toBe('/schedules')
            expect(router.currentRoute.value.matched[0].path).toBe('/maintenance-schedules')
        })
    })
}) 