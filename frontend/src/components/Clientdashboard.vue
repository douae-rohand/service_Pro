<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <ClientSidebar :active-tab="activeTab" @nav-change="handleNavChange" />

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col">
      <!-- Header -->
      <ClientHeader :user="currentUser" @logout="logout" />

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
            <div v-if="activeTab === 'home'" class="space-y-6">
              <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-4" style="color: #2f4f4f">
                  Bienvenue, {{ currentUser.name }} !
                </h2>
                <p class="text-gray-600 mb-6">
                  Gérez vos demandes de services et suivez leur progression en temps réel.
                </p>

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                  <div
                    class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border-2"
                    style="border-color: #1a5fa3"
                  >
                    <p class="text-sm text-gray-600 mb-1">En attente</p>
                    <p class="text-3xl font-bold" style="color: #1a5fa3">
                      {{ stats.pending }}
                    </p>
                  </div>
                  <div
                    class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border-2"
                    style="border-color: #92b08b"
                  >
                    <p class="text-sm text-gray-600 mb-1">Acceptées</p>
                    <p class="text-3xl font-bold" style="color: #92b08b">
                      {{ stats.accepted }}
                    </p>
                  </div>
                  <div
                    class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border-2 border-purple-300"
                  >
                    <p class="text-sm text-gray-600 mb-1">En cours</p>
                    <p class="text-3xl font-bold text-purple-600">
                      {{ stats.inProgress }}
                    </p>
                  </div>
                  <div
                    class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4 border-2"
                    style="border-color: #fee347"
                  >
                    <p class="text-sm text-gray-600 mb-1">Terminées</p>
                    <p class="text-3xl font-bold" style="color: #e8793f">
                      {{ stats.completed }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Rechercher Services Tab -->
            <div v-if="activeTab === 'search'">
              <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <Search :size="64" class="mx-auto mb-4 text-gray-400" />
                <h3 class="text-xl font-bold mb-2" style="color: #2f4f4f">
                  Rechercher des services
                </h3>
                <p class="text-gray-600 mb-4">
                  Fonctionnalité de recherche à venir...
                </p>
              </div>
            </div>

            <!-- Trouver Intervenants Tab -->
            <div v-if="activeTab === 'intervenants'">
              <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <Users :size="64" class="mx-auto mb-4 text-gray-400" />
                <h3 class="text-xl font-bold mb-2" style="color: #2f4f4f">
                  Trouver des intervenants
                </h3>
                <p class="text-gray-600 mb-4">
                  Fonctionnalité de recherche d'intervenants à venir...
                </p>
              </div>
            </div>

            <!-- Mes Demandes Tab -->
            <div v-if="activeTab === 'demands'">
              <EnhancedMyDemandsTab :client-id="currentUser.id" />
            </div>

            <!-- Mes Favoris Tab -->
            <div v-if="activeTab === 'favorites'">
              <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <Heart :size="64" class="mx-auto mb-4 text-gray-400" />
                <h3 class="text-xl font-bold mb-2" style="color: #2f4f4f">
                  Mes intervenants favoris
                </h3>
                <p class="text-gray-600 mb-4">
                  Aucun favori pour le moment
                </p>
              </div>
            </div>

            <!-- Mon Profil Tab -->
            <div v-if="activeTab === 'profile'">
              <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold mb-4" style="color: #2f4f4f">Mon Profil</h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nom complet</label>
                    <input
                      v-model="currentUser.name"
                      type="text"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      disabled
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                    <input
                      v-model="currentUser.email"
                      type="email"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      disabled
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Téléphone</label>
                    <input
                      v-model="currentUser.phone"
                      type="tel"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      disabled
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Quartier</label>
                    <input
                      v-model="currentUser.quartier"
                      type="text"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      disabled
                    />
                  </div>
                </div>
              </div>
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
import ClientHeader from '@/components/client/ClientHeader.vue';
import ClientSidebar from '@/components/client/ClientSidebar.vue';
import { demandService } from '@/services/demandService';
import authService from '@/services/authService';

export default {
  name: 'ClientDashboard',
  components: {
    Search,
    Users,
    Heart,
    AlertCircle,
    EnhancedMyDemandsTab,
    ClientHeader,
    ClientSidebar
  },
  emits: ['logout'],
  data() {
    return {
      activeTab: 'home',
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
      }
    };
  },
  mounted() {
    this.loadUserData();
    this.loadStats();
  },
  methods: {
    handleNavChange(tabId) {
      this.activeTab = tabId;
    },
    async loadUserData() {
      try {
        const response = await authService.getCurrentUser();
        // API now returns user directly
        const user = response.data;
        
        console.log('User data from API:', user); // Debug log
        
        // Update current user data from API
        if (user) {
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
    }
  }
};
</script>

<style scoped>
/* Styles additionnels si nécessaire */
</style>
