<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page d'accueil -->
    <div v-if="!selectedService">
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
      v-else
      :service="selectedService"
      @back="handleBack"
      @view-all-intervenants="handleViewAllIntervenants"
      @view-profile="handleViewProfile"
      @task-click="handleTaskClick"
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
import LoginModal from './components/LoginModal.vue'
import SignupModal from './components/SignupModal.vue'

// État pour gérer la navigation
const selectedService = ref(null)

// État pour les modals
const showLoginModal = ref(false)
const showSignupModal = ref(false)

const handleSearch = () => {
  console.log('Search clicked')
  // TODO: Implémenter la recherche
}

const handleServiceClick = (serviceId) => {
  console.log('Service clicked:', serviceId)
  selectedService.value = serviceId
  // Scroll vers le haut de la page
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const handleBack = () => {
  selectedService.value = null
  // Scroll vers le haut de la page
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const handleViewAllIntervenants = () => {
  console.log('View all intervenants for:', selectedService.value)
  // TODO: Implémenter la page avec tous les intervenants
}

const handleViewProfile = (intervenantId) => {
  console.log('View profile:', intervenantId, 'for service:', selectedService.value)
  // TODO: Implémenter la page de profil de l'intervenant
}

const handleTaskClick = (taskName) => {
  console.log('Task clicked:', taskName, 'for service:', selectedService.value)
  // TODO: Implémenter la page de réservation
}

// Fonction pour basculer de login à signup
const handleSwitchToSignup = () => {
  showLoginModal.value = false
  showSignupModal.value = true
}
</script>