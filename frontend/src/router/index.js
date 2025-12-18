import { createRouter, createWebHistory } from 'vue-router'
import IntervenantDashboard from '@/views/IntervenantDashboard.vue'
import authService from '@/services/authService'

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/',
            name: 'home',
            // App.vue gère déjà la navigation manuellement, donc on ne rend rien ici
            component: { template: '<div></div>' }
        },
        {
            path: '/dashboard',
            name: 'dashboard',
            component: IntervenantDashboard,
            redirect: '/dashboard/services',
            meta: { requiresAuth: true },
            children: [
                {
                    path: 'profile',
                    name: 'profile',
                    component: () => import('@/components/intervenant/ProfileTab.vue')
                },
                {
                    path: 'services',
                    name: 'services',
                    component: () => import('@/components/intervenant/ServiceSelectionTab.vue')
                },
                {
                    path: 'myservices',
                    name: 'myservices',
                    component: () => import('@/components/intervenant/MyServicesTab.vue')
                },
                {
                    path: 'reservations',
                    name: 'reservations',
                    component: () => import('@/components/intervenant/ReservationsTab.vue')
                },
                {
                    path: 'availability',
                    name: 'availability',
                    component: () => import('@/components/intervenant/AvailabilityTab.vue')
                },
                {
                    path: 'reviewclients',
                    name: 'reviewclients',
                    component: () => import('@/components/intervenant/ClientReviewsTab.vue')
                },
                {
                    path: 'reviewsstats',
                    name: 'reviewsstats',
                    component: () => import('@/components/intervenant/ReviewsStatsTab.vue')
                },
                {
                    path: 'history',
                    name: 'history',
                    component: () => import('@/components/intervenant/HistoryTab.vue')
                }
            ]
        }
    ]
})

// Navigation guard for authentication
router.beforeEach((to, from, next) => {
    if (to.meta.requiresAuth && !authService.isAuthenticated()) {
        next('/login')
    } else {
        next()
    }
})

export default router
