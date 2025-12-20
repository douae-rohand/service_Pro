<!-- App.vue - Navigation hybride: Manuel pour home, Router pour dashboard -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page d'administration -->
    <AdminDashboard 
      v-if="currentPage === 'admin'"
      @logout="handleAdminLogout"
    />

    <!-- Dashboard Intervenant (géré par le Router) -->
    <router-view v-else-if="isDashboardRoute" />

    <!-- Client Pages with Persistent Header -->
    <template v-else>
      <Header
        :user="currentUser"
        @login-click="showLoginModal = true"
        @signup-click="showSignupModal = true"
        @navigate-home="handleNavigateHome"
        @dashboard-click="handleDashboardClick"
        @service-click="handleServiceClick"
        @navigate="handleClientNavigate"
        @logout="handleLogout"
      />

      <!-- Page d'accueil -->
      <template v-if="currentPage === 'home'">
        <HeroSection @search="handleSearch" />
        <StatsSection />
        <ServicesSection @service-click="handleServiceClick" />
        <TestimonialsSection />
        <Footer @navigate-home="handleNavigateHome" />
      </template>

      <!-- Page de détail du service -->
      <ServiceDetailPage
        v-else-if="currentPage === 'service-detail'"
        :service="selectedService"
        @back="handleBack"
        @view-all-intervenants="handleViewAllIntervenants"
        @view-profile="handleViewProfileFromDetail"
        @task-click="handleTaskClick"
        @login-required="showLoginModal = true"
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
        :task-name="selectedTaskName"
        :service-id="selectedService"
        @back="handleBackFromTaskIntervenants"
        @view-profile="handleViewProfileFromTask"
        @login-required="showLoginModal = true"
        @navigate-booking="handleNavigateToBooking"
      />

      <!-- Profil de l'intervenant -->
      <IntervenantProfile
        v-else-if="currentPage === 'intervenant-profile'"
        :intervenant-data="selectedIntervenantData"
        :intervenant-id="selectedIntervenantId"
        :service="serviceType"
        @back="handleBackFromProfile"
        @login-required="showLoginModal = true"
        @navigate-booking="handleNavigateToBooking"
      />

      <!-- Client Reservations Page -->
      <ClientReservationsPage
        v-else-if="currentPage === 'client-reservations'"
        @back="handleBackFromClientPage"
      />

      <!-- Client Favorites Page -->
      <div v-else-if="currentPage === 'client-favorites'" class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <MyFavoritesTab :client-id="currentUser?.client?.id || currentUser?.id" />
        </div>
      </div>

      <!-- Client Profile Page -->
      <div v-else-if="currentPage === 'client-profile'" class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <ClientProfile
            :client-id="currentUser?.client?.id || currentUser?.id"
            :user="{
              name: currentUser?.name || '',
              email: currentUser?.email || '',
              phone: currentUser?.phone || '',
              location: currentUser?.quartier || '',
              avatar: currentUser?.avatar || '',
              memberSince: memberSince
            }"
            @profile-updated="handleProfileUpdate"
          />
        </div>
      </div>

      <!-- Client Reclamations Page -->
      <div v-else-if="currentPage === 'client-reclamations'" class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <ClientReclamationsTab />
        </div>
      </div>

      <!-- Booking Page -->
      <BookingPage
        v-else-if="currentPage === 'booking'"
        @back="handleBackFromBooking"
        @success="handleBookingSuccess"
        @login-required="showLoginModal = true"
      />
    </template>

    <!-- Modals -->
    <LoginModal
      :is-open="showLoginModal"
      @close="showLoginModal = false"
      @signup-click="handleSwitchToSignup"
      @login-success="handleLoginSuccess"
      @admin-login="handleAdminLogin"
    />

    <SignupModal 
      :is-open="showSignupModal" 
      @close="showSignupModal = false" 
      @open-login-modal="showLoginModal = true"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import authService from '@/services/authService'
import { useRoute, useRouter } from 'vue-router'
import Header from "./components/Header.vue";
import HeroSection from './components/HeroSection.vue'
import StatsSection from './components/StatsSection.vue'
import ServicesSection from './components/ServicesSection.vue'
import TestimonialsSection from './components/TestimonialsSection.vue'
import Footer from './components/Footer.vue'
import ServiceDetailPage from './components/ServiceDetailPage.vue'
import AllIntervenantsPage from './components/AllIntervenantsPage.vue'
import TaskIntervenantsPage from './components/TaskIntervenantsPage.vue'
import IntervenantProfile from './components/IntervenantProfile.vue' 
import LoginModal from './components/LoginModal.vue'
import SignupModal from './components/SignupModal.vue'
import AdminDashboard from './components/Admin/AdminDashboard.vue'
import BookingPage from './components/BookingPage.vue'
import ClientReservationsPage from './components/ClientReservationsPage.vue'
import MyFavoritesTab from './components/MyFavoritesTab.vue'
import ClientProfile from './components/ClientProfile.vue'
import ClientReclamationsTab from './components/client/ClientReclamationsTab.vue'
import { ArrowLeft } from 'lucide-vue-next'


const route = useRoute()
const router = useRouter()

// Détecte si on est sur une route dashboard
const isDashboardRoute = computed(() => {
  return route.path.startsWith('/dashboard')
})

// ============================================
// ÉTAT DE NAVIGATION
// ============================================
const currentPage = ref("home"); // 'home', 'service-detail', 'all-intervenants', 'task-intervenants', 'intervenant-profile'
const previousPage = ref("home"); // Pour savoir d'où on vient lors du retour
const currentUser = ref(null)
// ============================================
// ÉTAT DES DONNÉES
// ============================================
const selectedService = ref(null); // ID du service sélectionné (1 = Jardinage, 2 = Ménage)
const selectedTaskId = ref(null); // ID de la tâche/sous-service sélectionné
const selectedTaskName = ref(null); // Nom de la tâche/sous-service sélectionné
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
const handleNavigateHome = () => {
  console.log("Navigating to home");
  selectedService.value = null;
  selectedTaskId.value = null;
  selectedIntervenantData.value = null;
  selectedIntervenantId.value = null;
  currentPage.value = "home";
  previousPage.value = "home";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

// Vérifier si l'utilisateur est connecté au chargement
onMounted(async () => {
  const token = authService.getToken()
  if (token) {
    try {
      const response = await authService.getCurrentUser()
      const user = response.data
      
      // Safely check if user exists before accessing properties
      if (user) {
        currentUser.value = user
        
        // Redirection basée sur le rôle
        if (user.admin) {
          currentPage.value = 'admin'
        } else if (user.intervenant) {
          // Les intervenants utilisent le router-view, donc on reste en mode router
          if (!isDashboardRoute.value) {
            router.push('/dashboard')
          }
        } else if (user.client) {
          // Ne plus rediriger vers le dashboard client, rester sur la page d'accueil
          currentPage.value = 'home'
        }
      }
    } catch (error) {
      console.error('Erreur lors de la récupération de l\'utilisateur:', error)
      authService.setAuthToken(null)
    }
  }
  
  // Vérifier l'URL pour accès direct admin
  if (window.location.hash === '#admin' || window.location.pathname.includes('admin')) {
    // Si pas connecté, montrer le modal de login
    if (!currentUser.value?.admin) {
      showLoginModal.value = true
    }
  }
})


const handleSearch = (query) => {
  console.log("Search query:", query);
  if (!query) return;
  
  const lowerQuery = query.toLowerCase();
  
  if (lowerQuery.includes('jardin')) {
    handleServiceClick(1);
  } else if (lowerQuery.includes('menage') || lowerQuery.includes('ménage')) {
    handleServiceClick(2);
  } else {
    // Fallback or alert
    alert("Aucun service correspondant trouvé. Essayez 'Jardinage' ou 'Ménage'.");
  }
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
  console.log("taskData.taskName:", taskData.taskName);
  // taskData contient { taskId, taskName }
  selectedTaskId.value = taskData.taskId;
  selectedTaskName.value = taskData.taskName;
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
  showLoginModal.value = false
  showSignupModal.value = true
}

// Handle successful login
const handleLoginSuccess = (user) => {
  currentUser.value = user
  showLoginModal.value = false
  
  // Vérifier s'il y a une redirection après connexion
  const redirectAfterLogin = localStorage.getItem('redirect_after_login')
  
  if (redirectAfterLogin === 'booking') {
    // Rediriger vers la page de réservation
    previousPage.value = 'task-intervenants' // ou la page d'où on vient
    currentPage.value = 'booking'
    localStorage.removeItem('redirect_after_login')
  } else {
    // Navigation basée sur le rôle
    if (user.admin) {
      currentPage.value = 'admin'
    } else if (user.intervenant) {
      router.push('/dashboard')
  } else if (user.client) {
    // Ne plus rediriger vers le dashboard client, rester sur la page actuelle ou home
    if (redirectAfterLogin === 'booking') {
      // Rediriger vers la page de réservation si nécessaire
      previousPage.value = 'task-intervenants'
      currentPage.value = 'booking'
      localStorage.removeItem('redirect_after_login')
    } else {
      currentPage.value = 'home'
    }
  } else {
      // Fallback if role is unclear
      currentPage.value = 'home'
    }
  }
  
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Handle logout
const handleLogout = async () => {
  console.log("App.vue: handleLogout called");
  try {
    await authService.logout();
    authService.setAuthToken(null);
    currentUser.value = null;
    currentPage.value = 'home';
    window.location.reload(); // Recharger pour effacer l'état
  } catch (error) {
    console.error('Logout error:', error);
    // Even if logout fails on server, clear client-side state and reload
    authService.setAuthToken(null);
    currentUser.value = null;
    currentPage.value = 'home';
    window.location.reload();
  }
};
onMounted(() => {
  // Vérifier s'il y a un token dans l'URL (retour de Google login)
  const urlParams = new URLSearchParams(window.location.search)
  const token = urlParams.get('token')
  const error = urlParams.get('error')

  if (token) {
    authService.setAuthToken(token)
    // Nettoyer l'URL sans recharger la page
    window.history.replaceState({}, document.title, window.location.pathname)
    // Recharger pour mettre à jour l'état de l'application (header, etc.)
    window.location.reload()
  } else if (error) {
    alert('Erreur lors de la connexion Google. Veuillez réessayer.')
    // Nettoyer l'URL
    window.history.replaceState({}, document.title, window.location.pathname)
  }
})

const handleDashboardClick = () => {
  if (currentUser.value?.admin) {
    currentPage.value = 'admin'
  } else if (currentUser.value?.intervenant) {
    router.push('/dashboard')
  } else if (currentUser.value?.client) {
    // Rediriger vers Mes Réservations au lieu du dashboard
    currentPage.value = 'client-reservations'
  }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Gérer la navigation depuis le header pour les clients
const handleClientNavigate = (page) => {
  if (page === 'reservations') {
    currentPage.value = 'client-reservations'
  } else if (page === 'profile') {
    currentPage.value = 'client-profile'
    // Les favoris et réclamations sont maintenant dans le profil
  }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Retour depuis une page client
const handleBackFromClientPage = () => {
  currentPage.value = 'home'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Gérer la mise à jour du profil
const handleProfileUpdate = async () => {
  // Recharger les données utilisateur
  try {
    const response = await authService.getCurrentUser()
    currentUser.value = response.data
  } catch (error) {
    console.error('Erreur lors de la mise à jour du profil:', error)
  }
}

// Calculer memberSince
const memberSince = computed(() => {
  if (currentUser.value?.created_at) {
    const createdDate = new Date(currentUser.value.created_at)
    return createdDate.getFullYear().toString()
  }
  return '2023'
})

// Fonction pour gérer la connexion admin
const handleAdminLogin = (user) => {
  currentUser.value = user
  showLoginModal.value = false
  currentPage.value = 'admin'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Fonction pour gérer la déconnexion admin
const handleAdminLogout = async () => {
  try {
    await authService.logout()
  } catch (error) {
    console.error('Erreur lors de la déconnexion:', error)
  }
  authService.setAuthToken(null)
  currentUser.value = null
  currentPage.value = 'home'
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// ============================================
// NAVIGATION - PAGE DE RÉSERVATION
// ============================================
const handleBackFromBooking = () => {
  // Retourner à la page précédente
  if (previousPage.value === 'task-intervenants') {
    currentPage.value = 'task-intervenants'
  } else if (previousPage.value === 'intervenant-profile') {
    currentPage.value = 'intervenant-profile'
  } else {
    currentPage.value = 'home'
  }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const handleBookingSuccess = () => {
  // Après une réservation réussie, rediriger vers Mes Réservations
  if (currentUser.value?.client) {
    currentPage.value = 'client-reservations'
  } else {
    handleBackFromBooking()
  }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const handleNavigateToBooking = (bookingData) => {
  // Stocker les données de réservation dans localStorage pour la page de réservation
  localStorage.setItem('booking_intervenantId', bookingData.intervenantId);
  if (bookingData.serviceId) localStorage.setItem('booking_serviceId', bookingData.serviceId);
  if (bookingData.taskId) localStorage.setItem('booking_taskId', bookingData.taskId);
  
  // Sauvegarder la page précédente (peut être task-intervenants ou intervenant-profile)
  if (currentPage.value === 'task-intervenants') {
    previousPage.value = 'task-intervenants';
  } else if (currentPage.value === 'intervenant-profile') {
    previousPage.value = 'intervenant-profile';
  } else {
    previousPage.value = 'home';
  }
  
  // Naviguer vers la page de réservation
  currentPage.value = 'booking';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<style>
/* Styles globaux si nécessaire */
</style>
