<template>
  <aside 
    class="fixed lg:fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 min-h-screen transform transition-transform duration-300 ease-in-out"
    :class="isVisible ? 'translate-x-0' : '-translate-x-full'"
  >
    <div class="p-6 h-full overflow-y-auto">
      <!-- Branding -->
      <div class="mb-8 px-2">
        <div class="flex items-center gap-3 mb-2">
          <div class="p-1.5 rounded-xl bg-gray-50 border border-gray-100 shadow-sm">
            <img src="../../assets/logo.png" alt="Logo" class="w-8 h-8 object-contain" />
          </div>
          <div>
            <h1 class="text-lg font-bold text-gray-800 leading-tight">ServicePro</h1>
            <p class="text-[10px] font-bold text-[#4682B4] uppercase tracking-wider">Espace Client</p>
          </div>
        </div>
      </div>

      <!-- Navigation Items -->
      <nav class="space-y-1.5">
        <button
          v-for="item in navigationItems"
          :key="item.id"
          @click="handleNavClick(item.id)"
          class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-left group"
          :class="
            activeTab === item.id
              ? 'font-bold shadow-sm'
              : 'hover:bg-gray-50'
          "
          :style="
            activeTab === item.id
              ? {
                  backgroundColor: '#F0F7FF',
                  color: '#4682B4',
                  border: '1px solid #E0EFFF'
                }
              : {
                  color: '#64748b',
                  backgroundColor: 'transparent',
                  border: '1px solid transparent'
                }
          "
        >
          <component 
            :is="item.icon" 
            :size="20" 
            :class="activeTab === item.id ? '' : 'group-hover:text-[#4682B4] transition-colors'"
            :style="{ color: activeTab === item.id ? '#4682B4' : 'inherit' }" 
          />
          <span class="text-sm">{{ item.label }}</span>
        </button>
      </nav>
    </div>
  </aside>
</template>

<script>
import {
  Home,
  Search,
  Users,
  FileText,
  Heart,
  Star,
  AlertTriangle
} from 'lucide-vue-next';

export default {
  name: 'ClientSidebar',
  components: {
    Home,
    Search,
    Users,
    FileText,
    Heart,
    Star,
    AlertTriangle
  },
  props: {
    activeTab: {
      type: String,
      default: 'home'
    },
    isVisible: {
      type: Boolean,
      default: true
    }
  },
  emits: ['nav-change'],
  data() {
    return {
      navigationItems: [
        { id: 'home', label: 'Accueil', icon: 'Home' },
        { id: 'search', label: 'Rechercher Services', icon: 'Search' },
        { id: 'intervenants', label: 'Trouver Intervenants', icon: 'Users' },
        { id: 'demands', label: 'Mes Demandes', icon: 'FileText' },
        { id: 'favorites', label: 'Mes Favoris', icon: 'Heart' },
        { id: 'reclamations', label: 'Mes Réclamations', icon: 'AlertTriangle' },
        { id: 'profile', label: 'Mon Profil', icon: 'Star' }
      ]
    };
  },
  methods: {
    handleNavClick(tabId) {
      this.$emit('nav-change', tabId);
    }
  }
};
</script>

<style scoped>
/* Additional styles if needed */
</style>

