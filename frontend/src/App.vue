<template>
  <div class="min-h-screen bg-gray-50">
    <Header @login-click="handleLoginClick" @signup-click="handleSignupClick" />
    <HeroSection @search="handleSearch" />
    <StatsSection />
    <ServicesSection @service-click="handleServiceClick" />
    <TestimonialsSection />
    <Footer />
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

const handleLoginClick = () => {
  console.log('Login clicked')
  // La modal est gérée dans le composant Header
}

const handleSignupClick = () => {
  console.log('Signup clicked')
  // La modal est gérée dans le composant Header
}

const handleSearch = () => {
  console.log('Search clicked')
  // TODO: Implémenter la navigation vers la page de recherche
  // ou ouvrir une modal de recherche
}

const handleServiceClick = (serviceId) => {
  console.log('Service clicked:', serviceId)
  // TODO: Implémenter la navigation vers la page de détail du service
  // ou ouvrir une modal avec les sous-services
}
</script>
