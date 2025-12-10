<!-- App.vue - Fichier principal mis à jour -->
<template>
  <div class="min-h-screen bg-gray-50">
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
import { ref } from 'vue'
import Header from './components/Header.vue'
import HeroSection from './components/HeroSection.vue'
import StatsSection from './components/StatsSection.vue'
import ServicesSection from './components/ServicesSection.vue'
import TestimonialsSection from './components/TestimonialsSection.vue'
import Footer from './components/Footer.vue'
import ServiceDetailPage from './components/ServiceDetailPage.vue'
import AllIntervenantsPage from './components/AllIntervenantsPage.vue'
import LoginModal from './components/LoginModal.vue'
import SignupModal from './components/SignupModal.vue'

// État pour gérer la navigation entre les pages
const currentPage = ref('home') // Valeurs possibles: 'home', 'service-detail', 'all-intervenants'
const selectedService = ref(null)

// État pour les modals
const showLoginModal = ref(false)
const showSignupModal = ref(false)

const handleSearch = () => {
  console.log('Search clicked')
  // TODO: Implémenter la recherche
}

// Navigation depuis l'accueil vers la page de détail du service
const handleServiceClick = (serviceId) => {
  console.log('Service clicked:', serviceId)
  selectedService.value = serviceId
  currentPage.value = 'service-detail'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Retour de la page de détail du service vers l'accueil
const handleBack = () => {
  selectedService.value = null
  currentPage.value = 'home'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Navigation de la page de détail vers la page de tous les intervenants
const handleViewAllIntervenants = () => {
  console.log('View all intervenants for:', selectedService.value)
  currentPage.value = 'all-intervenants'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Retour de la page de tous les intervenants vers la page de détail du service
const handleBackFromAllIntervenants = () => {
  currentPage.value = 'service-detail'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Navigation vers le profil d'un intervenant (depuis n'importe quelle page)
const handleViewProfile = (intervenantId) => {
  console.log('View profile:', intervenantId, 'for service:', selectedService.value)
  // TODO: Implémenter la page de profil de l'intervenant
  // currentPage.value = 'intervenant-profile'
}

// Navigation vers la page de réservation d'une tâche
const handleTaskClick = (taskName) => {
  console.log('Task clicked:', taskName, 'for service:', selectedService.value)
  // TODO: Implémenter la page de réservation
  // currentPage.value = 'booking'
}

// Fonction pour basculer de login à signup
const handleSwitchToSignup = () => {
  showLoginModal.value = false
  showSignupModal.value = true
}
</script>

<style>
/* Styles globaux si nécessaire */
</style>