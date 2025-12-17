<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <ClientSidebar 
      :active-tab="activeTab" 
      :is-visible="sidebarVisible"
      @nav-change="handleNavChange" 
    />

    <!-- Overlay for mobile when sidebar is open -->
    <div 
      v-if="sidebarVisible"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
      @click="toggleSidebar"
    ></div>

    <!-- Main Content Area -->
    <div 
      class="flex-1 flex flex-col transition-all duration-300"
      :class="sidebarVisible ? 'lg:ml-64' : 'lg:ml-0'"
    >
      <!-- Header -->
      <ClientHeader 
        :user="currentUser" 
        :sidebar-visible="sidebarVisible"
        @logout="logout"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
          <!-- Loading State -->
          <div v-if="loading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-4 text-gray-600">Chargement...</p>
          </div>

          <!-- Error State -->
          <div
            v-else-if="error"
            class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600"
          >
            <AlertCircle :size="20" class="inline mr-2" />
            {{ error }}
          </div>

          <!-- Content -->
          <div v-else>
            <!-- Accueil Tab -->
            <div v-if="activeTab === 'home'">
              <PageAcceuil :current-user="currentUser" :stats="stats" />
            </div>

            <!-- Rechercher Services Tab -->
            <div v-if="activeTab === 'search'">
              <SearchServices :client-id="currentUser.id" />
            </div>

            <!-- Trouver Intervenants Tab -->
            <div v-if="activeTab === 'intervenants'">
               <TrouverIntervenantsTab :client-id="currentUser.id" />
            </div>

            <!-- Mes Demandes Tab -->
            <div v-if="activeTab === 'demands'">
              <EnhancedMyDemandsTab :client-id="currentUser.id" />
            </div>

            <!-- Mes Favoris Tab -->
            <div v-if="activeTab === 'favorites'">
              <MyFavoritesTab :client-id="currentUser.id" />
            </div>

            <!-- Mon Profil Tab -->
            <div v-if="activeTab === 'profile'">
              <ClientProfile
                :client-id="currentUser.id"
                :user="{
                  name: currentUser.name,
                  email: currentUser.email,
                  phone: currentUser.phone,
                  location: currentUser.quartier,
                  avatar: currentUser.avatar,
                  memberSince: memberSince
                }"
                @profile-updated="handleProfileUpdate"
              />
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import {
  Search,
  Users,
  Heart,
  AlertCircle
} from 'lucide-vue-next';
import EnhancedMyDemandsTab from '@/components/EnhancedMyDemandsTab.vue';
import SearchServices from '@/components/SearchServices.vue';
import ClientHeader from '@/components/client/ClientHeader.vue';
import ClientSidebar from '@/components/client/ClientSidebar.vue';
import { demandService } from '@/services/demandService';
import authService from '@/services/authService';
import TrouverIntervenantsTab from '@/components/TrouverIntervenantsTab.vue';
import MyFavoritesTab from '@/components/MyFavoritesTab.vue';
import ClientProfile from '@/components/ClientProfile.vue';
import PageAcceuil from '@/components/PageAcceuil.vue';

export default {
  name: 'ClientDashboard',
  components: {
    Search,
    Users,
    Heart,
    AlertCircle,
    EnhancedMyDemandsTab,
    SearchServices,
    ClientHeader,
    ClientSidebar,
    TrouverIntervenantsTab,
    MyFavoritesTab,
    ClientProfile,
    PageAcceuil
  },
  emits: ['logout'],
  data() {
    return {
      activeTab: 'home',
      sidebarVisible: true, // Sidebar visible by default on desktop
      loading: false,
      error: null,
      currentUser: {
        id: null,
        name: 'Chargement...',
        email: '',
        phone: '',
        quartier: '',
        avatar: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop'
      },
      stats: {
        pending: 0,
        accepted: 0,
        inProgress: 0,
        completed: 0
      },
      memberSince: '2023'
    };
  },
  mounted() {
    this.loadUserData();
    this.loadStats();
    // Hide sidebar on mobile by default
    if (window.innerWidth < 1024) {
      this.sidebarVisible = false;
    }
  },
  methods: {
    toggleSidebar() {
      this.sidebarVisible = !this.sidebarVisible;
    },
    handleNavChange(tabId) {
      this.activeTab = tabId;
      // Auto-hide sidebar on mobile after navigation
      if (window.innerWidth < 1024) {
        this.sidebarVisible = false;
      }
    },
    async loadUserData() {
      try {
        const response = await authService.getCurrentUser();
        // API now returns user directly
        const user = response.data;
        
        console.log('User data from API:', user); // Debug log
        
        // Update current user data from API
        if (user) {
          // Extract member since year from created_at
          if (user.created_at) {
            const createdDate = new Date(user.created_at);
            this.memberSince = createdDate.getFullYear().toString();
          }
          
          // Check if user is a client
          if (user.client) {
            this.currentUser = {
              id: user.client.id || user.id,
              name: `${user.prenom || ''} ${user.nom || ''}`.trim() || user.name || 'Utilisateur',
              email: user.email || '',
              phone: user.telephone || '',
              quartier: user.client.ville || user.client.address || user.quartier || '',
              avatar: user.url || 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop'
            };
            console.log('Client user loaded:', this.currentUser); // Debug
          } else if (user.prenom || user.nom || user.name) {
            // If user data exists but no client object, use user data directly
            this.currentUser = {
              id: user.id,
              name: `${user.prenom || ''} ${user.nom || ''}`.trim() || user.name || 'Utilisateur',
              email: user.email || '',
              phone: user.telephone || '',
              quartier: user.ville || user.address || user.quartier || '',
              avatar: user.url || 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop'
            };
          } else {
            console.warn('User data structure unexpected:', user);
            this.error = 'Structure de données utilisateur inattendue';
          }
        } else {
          this.error = 'Aucune donnée utilisateur reçue';
        }
      } catch (error) {
        console.error('Error loading user data:', error);
        this.error = 'Erreur lors du chargement des données utilisateur: ' + (error.response?.data?.message || error.message);
      }
    },
    async loadStats() {
      this.loading = true;
      this.error = null;
      try {
        const statistics = await demandService.getClientStatistics(this.currentUser.id);
        this.stats = {
          pending: statistics.pending || 0,
          accepted: statistics.accepted || 0,
          inProgress: statistics.inProgress || 0,
          completed: statistics.completed || 0
        };
      } catch (error) {
        console.error('Erreur lors du chargement des statistiques:', error);
        // Utiliser des valeurs par défaut en cas d'erreur
        this.stats = {
          pending: 1,
          accepted: 1,
          inProgress: 0,
          completed: 3
        };
      } finally {
        this.loading = false;
      }
    },
    logout() {
      localStorage.removeItem('token');
      localStorage.removeItem('authToken');
      // Emit event to parent or redirect to home
      this.$emit('logout');
      // Or use window.location if needed
      window.location.href = '/';
    },
    async handleProfileUpdate() {
      // Reload user data to get updated avatar
      await this.loadUserData();
    }
  }
};
</script>

<style scoped>
/* Styles additionnels si nécessaire */
</style>
