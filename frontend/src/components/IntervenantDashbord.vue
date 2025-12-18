<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300',
        isSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <!-- Sidebar Header -->
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h2 class="text-2xl" style="color: #92B08B">ServicePro</h2>
          <button
            @click="isSidebarOpen = false"
            class="lg:hidden p-2 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <XIcon :size="20" />
          </button>
        </div>
        <p class="text-sm text-gray-600 mt-1">Espace Intervenant</p>
      </div>

      <!-- Navigation -->
      <nav class="p-4">
        <ul class="space-y-2">
          <li v-for="tab in tabs" :key="tab.id">
            <button
              @click="handleTabClick(tab.id)"
              :class="[
                'w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all',
                activeTab === tab.id ? 'shadow-md' : 'hover:bg-gray-50'
              ]"
              :style="{
                backgroundColor: activeTab === tab.id ? tab.color : 'transparent',
                color: activeTab === tab.id ? 'white' : '#2F4F4F'
              }"
            >
              <component :is="tab.icon" :size="20" />
              <span>{{ tab.label }}</span>
            </button>
          </li>
        </ul>
      </nav>
    </aside>

    <!-- Overlay for mobile -->
    <div
      v-if="isSidebarOpen"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
      @click="isSidebarOpen = false"
    />

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen">
      <!-- Top Navbar -->
      <header class="bg-white shadow-sm sticky top-0 z-30">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between py-4">
            <!-- Mobile Menu Button -->
            <button
              @click="isSidebarOpen = true"
              class="lg:hidden p-2 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <MenuIcon :size="24" />
            </button>

            <!-- Page Title -->
            <div class="flex-1 lg:ml-0 ml-4">
              <h1 class="text-2xl" style="color: #2F4F4F">
                {{ currentTab?.label || 'Tableau de bord' }}
              </h1>
            </div>

            <!-- Right Side - Profile & Logout -->
            <div class="flex items-center gap-3">
              <!-- Profile Dropdown -->
              <div class="relative">
                <button
                  @click="showProfileMenu = !showProfileMenu"
                  class="flex items-center gap-3 px-4 py-2 rounded-lg transition-colors hover:bg-gray-50"
                >
                  <img
                    :src="intervenant.profileImage"
                    :alt="intervenant.name"
                    class="w-10 h-10 rounded-full object-cover border-2"
                    style="border-color: #92B08B"
                  />
                  <div class="hidden md:block text-left">
                    <p class="text-sm" style="color: #2F4F4F">{{ intervenant.name }}</p>
                    <p class="text-xs text-gray-500">{{ intervenant.location }}</p>
                  </div>
                </button>

                <!-- Dropdown Menu -->
                <template v-if="showProfileMenu">
                  <div
                    class="fixed inset-0 z-10"
                    @click="showProfileMenu = false"
                  />
                  <div class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-20">
                    <div class="px-4 py-3 border-b border-gray-200">
                      <p class="text-sm" style="color: #2F4F4F">{{ intervenant.name }}</p>
                      <p class="text-xs text-gray-500">{{ intervenant.email }}</p>
                    </div>
                    <button
                      @click="handleProfileClick"
                      class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-left"
                    >
                      <UserIcon :size="18" style="color: #1A5FA3" />
                      <span class="text-sm">Mon Profil</span>
                    </button>
                    <div class="border-t border-gray-200 mt-2 pt-2">
                      <button
                        @click="$emit('logout')"
                        class="w-full flex items-center gap-3 px-4 py-3 hover:bg-red-50 transition-colors text-left"
                        style="color: #E8793F"
                      >
                        <LogOutIcon :size="18" />
                        <span class="text-sm">Déconnexion</span>
                      </button>
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Tab Content -->
      <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
        <IntervenantProfileTab v-if="activeTab === 'profile'" :intervenant="intervenant" />
        <ServiceSelectionTab v-else-if="activeTab === 'services'" />
        <MyServicesTab v-else-if="activeTab === 'myservices'" />
        <ReservationsTab v-else-if="activeTab === 'reservations'" />
        <AvailabilityTab v-else-if="activeTab === 'availability'" />
        <ClientReviewsTab v-else-if="activeTab === 'reviewclients'" />
        <ReviewsStatsTab v-else-if="activeTab === 'reviewsstats'" />
        <HistoryTab v-else-if="activeTab === 'history'" />
      </main>
    </div>
  </div>
</template>

<script>
import { 
  LogOut as LogOutIcon, 
  User as UserIcon, 
  Briefcase, 
  Calendar, 
  Star, 
  ClipboardList, 
  History, 
  Menu as MenuIcon, 
  X as XIcon, 
  MessageCircle 
} from 'lucide-vue-next';
// Import existing or placeholder components
import IntervenantProfileTab from './intervenant/IntervenantProfileTab.vue';
import PlaceholderTab from './intervenant/PlaceholderTab.vue';
import intervenantService from '../services/intervenantService';

export default {
  name: 'IntervenantDashboard',
  components: {
    LogOutIcon,
    UserIcon,
    MenuIcon,
    XIcon,
    // Using placeholders for missing components to ensure build works
    ServiceSelectionTab: PlaceholderTab,
    MyServicesTab: PlaceholderTab,
    ReservationsTab: PlaceholderTab,
    AvailabilityTab: PlaceholderTab,
    ClientReviewsTab: PlaceholderTab,
    IntervenantProfileTab,
    HistoryTab: PlaceholderTab,
    ReviewsStatsTab: PlaceholderTab
  },
  emits: ['logout'],
  data() {
    return {
      activeTab: 'services',
      isSidebarOpen: false,
      showProfileMenu: false,
      loading: true,
      error: null,
      intervenant: {
        id: null,
        name: 'Chargement...',
        email: '',
        phone: '',
        profileImage: '',
        location: '',
        memberSince: ''
      },
      tabs: [
        { id: 'services', label: 'Mes Services', icon: Briefcase, color: '#92B08B' },
        { id: 'myservices', label: 'Sous-services Actifs', icon: ClipboardList, color: '#1A5FA3' },
        { id: 'reservations', label: 'Réservations', icon: Calendar, color: '#E8793F' },
        { id: 'availability', label: 'Disponibilités', icon: Calendar, color: '#A5D4E6' },
        { id: 'reviewclients', label: 'Évaluer Clients', icon: MessageCircle, color: '#FEE347' },
        { id: 'reviewsstats', label: 'Mes Avis & Notes', icon: Star, color: '#E8793F' },
        { id: 'history', label: 'Historique', icon: History, color: '#92B08B' }
      ]
    };
  },
  computed: {
    currentTab() {
      return this.tabs.find(t => t.id === this.activeTab);
    }
  },
  async created() {
    await this.fetchIntervenantData();
  },
  methods: {
    async fetchIntervenantData() {
      this.loading = true;
      try {
        // Hardcoded ID 5 for demo purposes as requested/implied context
        // In a real app, this would come from auth state
        const response = await intervenantService.getById(5);
        const data = response.data;
        
        this.intervenant = {
          id: data.id,
          name: data.utilisateur ? `${data.utilisateur.prenom} ${data.utilisateur.nom}` : '',
          email: data.utilisateur ? data.utilisateur.email : '',
          phone: data.utilisateur ? data.utilisateur.telephone : '',
          profileImage: data.utilisateur && data.utilisateur.url ? data.utilisateur.url : 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=150&h=150&fit=crop',
          location: data.ville || data.address || '',
          memberSince: data.created_at ? new Date(data.created_at).getFullYear() : '2024'
        };
      } catch (err) {
        console.error("Erreur chargement dashboard:", err);
        this.error = "Erreur de chargement";
      } finally {
        this.loading = false;
      }
    },
    handleTabClick(tabId) {
      this.activeTab = tabId;
      this.isSidebarOpen = false;
    },
    handleProfileClick() {
      this.activeTab = 'profile';
      this.showProfileMenu = false;
    }
  }
};
</script>

<style scoped>
/* Styles spécifiques au composant si nécessaire */
</style>