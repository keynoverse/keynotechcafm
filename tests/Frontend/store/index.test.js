import { createStore } from 'vuex'
import auth from '@/store/modules/auth'
import assets from '@/store/modules/assets'
import workOrders from '@/store/modules/workOrders'
import maintenanceSchedules from '@/store/modules/maintenanceSchedules'

describe('Vuex Store', () => {
    let store

    beforeEach(() => {
        store = createStore({
            modules: {
                auth,
                assets,
                workOrders,
                maintenanceSchedules
            }
        })
    })

    describe('Auth Module', () => {
        it('sets user when login is successful', () => {
            const user = {
                id: 1,
                name: 'Test User',
                email: 'test@example.com',
                roles: ['user'],
                permissions: ['view_assets']
            }

            store.commit('auth/SET_USER', user)

            expect(store.state.auth.user).toEqual(user)
            expect(store.getters['auth/isAuthenticated']).toBe(true)
        })

        it('clears user when logout is called', () => {
            // Set initial user
            store.commit('auth/SET_USER', {
                id: 1,
                name: 'Test User'
            })

            store.commit('auth/LOGOUT')

            expect(store.state.auth.user).toBeNull()
            expect(store.getters['auth/isAuthenticated']).toBe(false)
        })

        it('updates user profile', () => {
            const user = {
                id: 1,
                name: 'Test User',
                email: 'test@example.com'
            }

            store.commit('auth/SET_USER', user)

            const updates = {
                name: 'Updated Name',
                email: 'updated@example.com'
            }

            store.commit('auth/UPDATE_PROFILE', updates)

            expect(store.state.auth.user.name).toBe('Updated Name')
            expect(store.state.auth.user.email).toBe('updated@example.com')
        })
    })

    describe('Assets Module', () => {
        it('sets assets list', () => {
            const assets = [
                { id: 1, name: 'Asset 1' },
                { id: 2, name: 'Asset 2' }
            ]

            store.commit('assets/SET_ASSETS', assets)

            expect(store.state.assets.list).toEqual(assets)
        })

        it('adds new asset', () => {
            const newAsset = { id: 1, name: 'New Asset' }

            store.commit('assets/ADD_ASSET', newAsset)

            expect(store.state.assets.list).toContainEqual(newAsset)
        })

        it('updates existing asset', () => {
            const asset = { id: 1, name: 'Asset 1' }
            store.commit('assets/SET_ASSETS', [asset])

            const updatedAsset = { id: 1, name: 'Updated Asset' }
            store.commit('assets/UPDATE_ASSET', updatedAsset)

            expect(store.state.assets.list[0].name).toBe('Updated Asset')
        })

        it('removes asset', () => {
            const assets = [
                { id: 1, name: 'Asset 1' },
                { id: 2, name: 'Asset 2' }
            ]
            store.commit('assets/SET_ASSETS', assets)

            store.commit('assets/REMOVE_ASSET', 1)

            expect(store.state.assets.list).toHaveLength(1)
            expect(store.state.assets.list[0].id).toBe(2)
        })
    })

    describe('Work Orders Module', () => {
        it('sets work orders list', () => {
            const workOrders = [
                { id: 1, title: 'Work Order 1' },
                { id: 2, title: 'Work Order 2' }
            ]

            store.commit('workOrders/SET_WORK_ORDERS', workOrders)

            expect(store.state.workOrders.list).toEqual(workOrders)
        })

        it('adds new work order', () => {
            const newWorkOrder = { id: 1, title: 'New Work Order' }

            store.commit('workOrders/ADD_WORK_ORDER', newWorkOrder)

            expect(store.state.workOrders.list).toContainEqual(newWorkOrder)
        })

        it('updates existing work order', () => {
            const workOrder = { id: 1, title: 'Work Order 1', status: 'open' }
            store.commit('workOrders/SET_WORK_ORDERS', [workOrder])

            const updatedWorkOrder = { id: 1, title: 'Work Order 1', status: 'in_progress' }
            store.commit('workOrders/UPDATE_WORK_ORDER', updatedWorkOrder)

            expect(store.state.workOrders.list[0].status).toBe('in_progress')
        })

        it('removes work order', () => {
            const workOrders = [
                { id: 1, title: 'Work Order 1' },
                { id: 2, title: 'Work Order 2' }
            ]
            store.commit('workOrders/SET_WORK_ORDERS', workOrders)

            store.commit('workOrders/REMOVE_WORK_ORDER', 1)

            expect(store.state.workOrders.list).toHaveLength(1)
            expect(store.state.workOrders.list[0].id).toBe(2)
        })
    })

    describe('Maintenance Schedules Module', () => {
        it('sets maintenance schedules list', () => {
            const schedules = [
                { id: 1, title: 'Schedule 1' },
                { id: 2, title: 'Schedule 2' }
            ]

            store.commit('maintenanceSchedules/SET_SCHEDULES', schedules)

            expect(store.state.maintenanceSchedules.list).toEqual(schedules)
        })

        it('adds new maintenance schedule', () => {
            const newSchedule = { id: 1, title: 'New Schedule' }

            store.commit('maintenanceSchedules/ADD_SCHEDULE', newSchedule)

            expect(store.state.maintenanceSchedules.list).toContainEqual(newSchedule)
        })

        it('updates existing maintenance schedule', () => {
            const schedule = { id: 1, title: 'Schedule 1', frequency: 30 }
            store.commit('maintenanceSchedules/SET_SCHEDULES', [schedule])

            const updatedSchedule = { id: 1, title: 'Schedule 1', frequency: 45 }
            store.commit('maintenanceSchedules/UPDATE_SCHEDULE', updatedSchedule)

            expect(store.state.maintenanceSchedules.list[0].frequency).toBe(45)
        })

        it('removes maintenance schedule', () => {
            const schedules = [
                { id: 1, title: 'Schedule 1' },
                { id: 2, title: 'Schedule 2' }
            ]
            store.commit('maintenanceSchedules/SET_SCHEDULES', schedules)

            store.commit('maintenanceSchedules/REMOVE_SCHEDULE', 1)

            expect(store.state.maintenanceSchedules.list).toHaveLength(1)
            expect(store.state.maintenanceSchedules.list[0].id).toBe(2)
        })
    })

    describe('Cross-module Interactions', () => {
        it('clears all module data on logout', () => {
            // Set initial data
            store.commit('assets/SET_ASSETS', [{ id: 1, name: 'Asset 1' }])
            store.commit('workOrders/SET_WORK_ORDERS', [{ id: 1, title: 'Work Order 1' }])
            store.commit('maintenanceSchedules/SET_SCHEDULES', [{ id: 1, title: 'Schedule 1' }])

            // Logout
            store.commit('auth/LOGOUT')

            // Check all modules are cleared
            expect(store.state.assets.list).toHaveLength(0)
            expect(store.state.workOrders.list).toHaveLength(0)
            expect(store.state.maintenanceSchedules.list).toHaveLength(0)
        })

        it('updates related data when asset is deleted', () => {
            const assetId = 1
            
            // Set initial data
            store.commit('assets/SET_ASSETS', [{ id: assetId, name: 'Asset 1' }])
            store.commit('workOrders/SET_WORK_ORDERS', [
                { id: 1, asset_id: assetId, title: 'Work Order 1' },
                { id: 2, asset_id: 2, title: 'Work Order 2' }
            ])
            store.commit('maintenanceSchedules/SET_SCHEDULES', [
                { id: 1, asset_id: assetId, title: 'Schedule 1' },
                { id: 2, asset_id: 2, title: 'Schedule 2' }
            ])

            // Delete asset
            store.dispatch('assets/deleteAsset', assetId)

            // Check related data is removed
            expect(store.state.workOrders.list.find(wo => wo.asset_id === assetId)).toBeUndefined()
            expect(store.state.maintenanceSchedules.list.find(s => s.asset_id === assetId)).toBeUndefined()
        })
    })

    describe('Store Getters', () => {
        it('filters assets by status', () => {
            const assets = [
                { id: 1, status: 'active' },
                { id: 2, status: 'active' },
                { id: 3, status: 'maintenance' }
            ]
            store.commit('assets/SET_ASSETS', assets)

            const activeAssets = store.getters['assets/byStatus']('active')
            expect(activeAssets).toHaveLength(2)
        })

        it('filters work orders by priority', () => {
            const workOrders = [
                { id: 1, priority: 'high' },
                { id: 2, priority: 'high' },
                { id: 3, priority: 'low' }
            ]
            store.commit('workOrders/SET_WORK_ORDERS', workOrders)

            const highPriorityWorkOrders = store.getters['workOrders/byPriority']('high')
            expect(highPriorityWorkOrders).toHaveLength(2)
        })

        it('gets overdue maintenance schedules', () => {
            const schedules = [
                { id: 1, next_due_date: '2024-01-01' },
                { id: 2, next_due_date: '2024-12-31' }
            ]
            store.commit('maintenanceSchedules/SET_SCHEDULES', schedules)

            const overdueSchedules = store.getters['maintenanceSchedules/overdue']
            expect(overdueSchedules).toHaveLength(1)
        })
    })
}) 