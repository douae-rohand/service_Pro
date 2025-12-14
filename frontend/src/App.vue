<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page d'accueil -->
    <div v-if="currentPage === 'home'">
      <Header
        @login-click="showLoginModal = true"
        @signup-click="showSignupModal = true"
        @navigate-home="handleNavigateHome"
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
      @view-profile="handleViewProfileFromDetail"
      @task-click="handleTaskClick"
    />

    <!-- Page de tous les intervenants -->
    <AllIntervenantsPage
      v-else-if="currentPage === 'all-intervenants'"
      :service="selectedService"
      @back="handleBackFromAllIntervenants"
      @view-profile="handleViewProfileFromList"
    />

    <!-- Page des intervenants pour un sous-service spécifique -->
    <TaskIntervenantsPage
      v-else-if="currentPage === 'task-intervenants'"
      :task-id="selectedTaskId"
      :service-id="selectedService"
      @back="handleBackFromTaskIntervenants"
      @view-profile="handleViewProfileFromTask"
    />

    <!-- Profil de l'intervenant -->
    <IntervenantProfile
      v-else-if="currentPage === 'intervenant-profile'"
      :intervenant-data="selectedIntervenantData"
      :intervenant-id="selectedIntervenantId"
      :service="serviceType"
      @back="handleBackFromProfile"
    />

    <!-- Modals -->
    <LoginModal
      :is-open="showLoginModal"
      @close="showLoginModal = false"
      @signup-click="handleSwitchToSignup"
    />

    <SignupModal :is-open="showSignupModal" @close="showSignupModal = false" />
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import Header from "./components/Header.vue";
import HeroSection from "./components/HeroSection.vue";
import StatsSection from "./components/StatsSection.vue";
import ServicesSection from "./components/ServicesSection.vue";
import TestimonialsSection from "./components/TestimonialsSection.vue";
import Footer from "./components/Footer.vue";
import ServiceDetailPage from "./components/ServiceDetailPage.vue";
import AllIntervenantsPage from "./components/AllIntervenantsPage.vue";
import TaskIntervenantsPage from "./components/TaskIntervenantsPage.vue";
import IntervenantProfile from "./components/IntervenantProfile.vue"; // ✅ CORRIGÉ
import LoginModal from "./components/LoginModal.vue";
import SignupModal from "./components/SignupModal.vue";

// ============================================
// ÉTAT DE NAVIGATION
// ============================================
const currentPage = ref("home"); // 'home', 'service-detail', 'all-intervenants', 'task-intervenants', 'intervenant-profile'
const previousPage = ref("home"); // Pour savoir d'où on vient lors du retour

// ============================================
// ÉTAT DES DONNÉES
// ============================================
const selectedService = ref(null); // ID du service sélectionné (1 = Jardinage, 2 = Ménage)
const selectedTaskId = ref(null); // ID de la tâche/sous-service sélectionné
const selectedIntervenantData = ref(null); // Données complètes de l'intervenant sélectionné
const selectedIntervenantId = ref(null); // ID de l'intervenant (pour fallback API)

// ============================================
// ÉTAT DES MODALS
// ============================================
const showLoginModal = ref(false);
const showSignupModal = ref(false);

// ============================================
// COMPUTED - TYPE DE SERVICE
// ============================================
// Convertit l'ID du service en type (pour les couleurs du profil)
const serviceType = computed(() => {
  if (selectedService.value === 1) return "jardinage";
  if (selectedService.value === 2) return "menage";
  return "menage"; // fallback par défaut
});

// ============================================
// NAVIGATION - PAGE D'ACCUEIL
// ============================================
const handleSearch = () => {
  console.log("Search clicked");
  // TODO: Implémenter la recherche
};

const handleServiceClick = (serviceId) => {
  console.log("Service clicked:", serviceId);
  selectedService.value = serviceId;
  currentPage.value = "service-detail";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const handleBack = () => {
  // Retour vers l'accueil depuis la page de détail
  selectedService.value = null;
  selectedTaskId.value = null;
  selectedIntervenantData.value = null;
  selectedIntervenantId.value = null;
  currentPage.value = "home";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

// ============================================
// NAVIGATION - PAGE DE DÉTAIL DU SERVICE
// ============================================
const handleViewAllIntervenants = () => {
  console.log("View all intervenants for service:", selectedService.value);
  previousPage.value = "service-detail"; // Sauvegarder la page actuelle
  currentPage.value = "all-intervenants";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const handleViewProfileFromDetail = (intervenantId) => {
  console.log("View profile from detail page:", intervenantId);

  // Depuis la page de détail, on n'a pas les données complètes
  // donc on passe l'ID et le composant fera un appel API
  selectedIntervenantData.value = null; // Forcer le chargement API
  selectedIntervenantId.value = intervenantId;
  previousPage.value = "service-detail";
  currentPage.value = "intervenant-profile";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const handleTaskClick = (taskData) => {
  console.log("Task clicked:", taskData, "for service:", selectedService.value);
  // taskData contient { taskId, taskName }
  selectedTaskId.value = taskData.taskId;
  previousPage.value = "service-detail";
  currentPage.value = "task-intervenants";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

// ============================================
// NAVIGATION - PAGE DE TOUS LES INTERVENANTS
// ============================================
const handleBackFromAllIntervenants = () => {
  // Retour vers la page de détail du service
  selectedIntervenantData.value = null;
  selectedIntervenantId.value = null;
  currentPage.value = "service-detail";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

// ============================================
// NAVIGATION - PAGE DES INTERVENANTS DU SOUS-SERVICE
// ============================================
const handleBackFromTaskIntervenants = () => {
  // Retour vers la page de détail du service
  selectedTaskId.value = null;
  selectedIntervenantData.value = null;
  selectedIntervenantId.value = null;
  currentPage.value = "service-detail";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const handleViewProfileFromTask = (payload) => {
  console.log("View profile from task page:", payload);

  // IMPORTANT: Stocker toutes les données de l'intervenant
  // payload contient { id: number, data: object }
  selectedIntervenantData.value = payload.data;
  selectedIntervenantId.value = payload.id;

  // Sauvegarder d'où on vient
  previousPage.value = "task-intervenants";

  // Naviguer vers le profil
  currentPage.value = "intervenant-profile";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const handleViewProfileFromList = (payload) => {
  console.log("View profile from list:", payload);

  // IMPORTANT: Stocker toutes les données de l'intervenant
  // payload contient { id: number, data: object }
  selectedIntervenantData.value = payload.data;
  selectedIntervenantId.value = payload.id;

  // Sauvegarder d'où on vient
  previousPage.value = "all-intervenants";

  // Naviguer vers le profil
  currentPage.value = "intervenant-profile";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

// ============================================
// NAVIGATION - PAGE DE PROFIL
// ============================================
const handleBackFromProfile = () => {
  // Retourner à la page précédente (soit detail, soit all-intervenants, soit task-intervenants)
  currentPage.value = previousPage.value;
  selectedIntervenantData.value = null;
  selectedIntervenantId.value = null;
  // Ne pas réinitialiser selectedTaskId car on peut vouloir revenir à la page task-intervenants
  window.scrollTo({ top: 0, behavior: "smooth" });
};

// ============================================
// MODALS
// ============================================
const handleSwitchToSignup = () => {
  showLoginModal.value = false;
  showSignupModal.value = true;
};
</script>

<style>
/* Styles globaux si nécessaire */
</style>
