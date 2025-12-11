import { createRouter, createWebHistory } from 'vue-router'
import IntervenantDashboard from '@/views/IntervenantDashboard.vue'

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/',
            redirect: '/dashboard/services'
        },
        {
            path: '/dashboard',
            name: 'dashboard',
            component: IntervenantDashboard,
            redirect: '/dashboard/services',
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

export default router
