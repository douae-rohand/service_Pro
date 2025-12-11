<template>
  <aside class="w-64 bg-white border-r border-gray-200 min-h-screen">
    <div class="p-6">
      <!-- Branding -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold mb-1" style="color: #92b08b">ServicePro</h1>
        <p class="text-sm text-gray-500">Espace Client</p>
      </div>

      <!-- Navigation Items -->
      <nav class="space-y-1">
        <button
          v-for="item in navigationItems"
          :key="item.id"
          @click="handleNavClick(item.id)"
          class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-left"
          :class="
            activeTab === item.id
              ? 'font-bold'
              : ''
          "
          :style="
            activeTab === item.id
              ? {
                  backgroundColor: '#E8F5E9',
                  color: '#2F4F4F'
                }
              : {
                  color: '#4B5563',
                  backgroundColor: 'transparent'
                }
          "
        >
          <component :is="item.icon" :size="20" :style="{ color: activeTab === item.id ? '#2F4F4F' : '#4B5563' }" />
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
  Star
} from 'lucide-vue-next';

export default {
  name: 'ClientSidebar',
  components: {
    Home,
    Search,
    Users,
    FileText,
    Heart,
    Star
  },
  props: {
    activeTab: {
      type: String,
      default: 'home'
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

