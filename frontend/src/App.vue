<!-- App.vue - Navigation hybride: Manuel pour home, Router pour dashboard -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Pages Manuelles (Home, Services, etc.) -->
    <div v-if="!isDashboardRoute">
      <!-- Page d'accueil -->
      <div v-if="currentPage === 'home'">
        <Header 
          @login-click="showLoginModal = true" 
          @signup-click="showSignupModal = true" 
        />
        <HeroSection @search="handleSearch" />
        <StatsSection />
        <ServicesSection @service-click="handleServiceClick" />
        <TestimonialsSection />
        <Footer />
      </div>

      <!-- Page de détail du service -->
      <ServiceDetailPage
        v-else-if="currentPage === 'service-detail'"
        :service="selectedService"
        @back="handleBack"
        @view-all-intervenants="handleViewAllIntervenants"
        @view-profile="handleViewProfile"
        @task-click="handleTaskClick"
      />

      <!-- Page de tous les intervenants -->
      <AllIntervenantsPage
        v-else-if="currentPage === 'all-intervenants'"
        :service="selectedService"
        @back="handleBackFromAllIntervenants"
        @view-profile="handleViewProfile"
      />
    </div>

    <!-- Dashboard Intervenant (géré par le Router) -->
    <router-view v-else />

    <!-- Page de tous les intervenants
    <AllIntervenantsPage
      v-else-if="currentPage === 'all-intervenants'"
      :service="selectedService"
      @back="handleBackFromAllIntervenants"
      @view-profile="handleViewProfile"
    /> -->

    <!-- Page de profil d'intervenant -->
    <IntervenantProfile
      v-if="currentPage === 'intervenant-profile'"
      :intervenant-id="selectedIntervenantId"
      :service="getServiceName(selectedService)"
      @back="handleBackFromProfile"
    />

    <!-- Modals -->
    <LoginModal 
      :is-open="showLoginModal" 
      @close="showLoginModal = false"
      @signup-click="handleSwitchToSignup"
    />
    
    <SignupModal 
      :is-open="showSignupModal" 
      @close="showSignupModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import Header from './components/Header.vue'
import HeroSection from './components/HeroSection.vue'
import StatsSection from './components/StatsSection.vue'
import ServicesSection from './components/ServicesSection.vue'
import TestimonialsSection from './components/TestimonialsSection.vue'
import Footer from './components/Footer.vue'
import ServiceDetailPage from './components/ServiceDetailPage.vue'
import AllIntervenantsPage from './components/AllIntervenantsPage.vue'
import IntervenantProfile from './components/IntervenantProfile.vue'
import LoginModal from './components/LoginModal.vue'
import SignupModal from './components/SignupModal.vue'

const route = useRoute()

// Détecte si on est sur une route dashboard
const isDashboardRoute = computed(() => {
  return route.path.startsWith('/dashboard')
})

// État pour gérer la navigation manuelle
const currentPage = ref('home')
const selectedService = ref(null)
const selectedIntervenantId = ref(null)
const previousPage = ref('home')

// État pour les modals
const showLoginModal = ref(false)
const showSignupModal = ref(false)

const handleSearch = () => {
  console.log('Search clicked')
}

const handleServiceClick = (serviceId) => {
  console.log('Service clicked:', serviceId)
  selectedService.value = serviceId
  currentPage.value = 'service-detail'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const handleBack = () => {
  selectedService.value = null
  currentPage.value = 'home'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const handleViewAllIntervenants = () => {
  console.log('View all intervenants for:', selectedService.value)
  currentPage.value = 'all-intervenants'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const handleBackFromAllIntervenants = () => {
  currentPage.value = 'service-detail'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const handleViewProfile = (intervenantId) => {
  console.log('View profile:', intervenantId)
  selectedIntervenantId.value = intervenantId
  previousPage.value = currentPage.value
  currentPage.value = 'intervenant-profile'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Retour du profil vers la page précédente
const handleBackFromProfile = () => {
  currentPage.value = previousPage.value
  selectedIntervenantId.value = null
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Helper pour déterminer le nom du service pour le composant profile
const getServiceName = (serviceId) => {
  if (serviceId === 1) return 'jardinage'
  if (serviceId === 2) return 'menage'
  return 'menage' // default fallback
}

const handleTaskClick = (taskName) => {
  console.log('Task clicked:', taskName, 'for service:', selectedService.value)
}

const handleSwitchToSignup = () => {
  showLoginModal.value = false
  showSignupModal.value = true
}
</script>

<style>
/* Styles globaux si nécessaire */
</style>
