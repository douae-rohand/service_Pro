<template>
  <header class="bg-white shadow-md sticky top-0 z-50 animate-slide-down border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-2 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between">
        <!-- Left: Toggle Button & Branding -->
        <div class="flex items-center gap-4">
          <button
            @click="handleToggleSidebar"
            class="p-2 rounded-lg hover:bg-gray-50 transition-colors lg:hidden"
            aria-label="Toggle sidebar"
          >
            <Menu v-if="!sidebarVisible" :size="24" :style="{ color: '#4682B4' }" />
            <X v-else :size="24" :style="{ color: '#4682B4' }" />
          </button>
          <button
            @click="handleToggleSidebar"
            class="hidden lg:flex p-2 rounded-lg hover:bg-gray-50 transition-colors"
            aria-label="Toggle sidebar"
          >
            <Menu v-if="!sidebarVisible" :size="24" :style="{ color: '#4682B4' }" />
            <X v-else :size="24" :style="{ color: '#4682B4' }" />
          </button>
          
          <!-- Logo from Landing Page -->
          <div class="flex items-center gap-3">
             <img src="../../assets/logo.png" alt="Logo" class="h-12 w-auto transition-transform hover:scale-105 duration-300 cursor-pointer" @click="$emit('nav-change', 'home')" />
             <div class="hidden sm:block h-8 w-px bg-gray-200 mx-2"></div>
             <span class="hidden sm:inline-block text-[#4682B4] font-semibold text-sm tracking-wide uppercase">Espace Client</span>
          </div>
        </div>

        <!-- Right: User Profile & Logout -->
        <div class="flex items-center gap-6">
          <div class="flex items-center gap-3 bg-gray-50 px-3 py-1.5 rounded-full border border-gray-100">
            <img
              :src="user.avatar"
              :alt="user.name"
              class="w-8 h-8 rounded-full object-cover border-2 shadow-sm"
              :style="{ borderColor: '#4682B4' }"
            />
            <div class="hidden md:block text-left">
              <p class="font-bold text-xs text-gray-800 leading-tight">{{ user.name }}</p>
              <p class="text-[10px] text-gray-500 font-medium">{{ user.quartier }}</p>
            </div>
          </div>
          
          <button
            @click="handleLogout"
            class="p-2 rounded-full hover:bg-red-50 transition-all group relative"
            title="Déconnexion"
          >
            <LogOut :size="20" class="text-gray-400 group-hover:text-red-500 transition-colors" />
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import { Menu, X, LogOut } from 'lucide-vue-next';

export default {
  name: 'ClientHeader',
  components: {
    Menu,
    X,
    LogOut
  },
  props: {
    user: {
      type: Object,
      required: true,
      default: () => ({
        name: 'Utilisateur',
        quartier: '',
        avatar: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop'
      })
    },
    sidebarVisible: {
      type: Boolean,
      default: true
    }
  },
  emits: ['logout', 'toggle-sidebar', 'nav-change'],
  methods: {
    handleLogout() {
      this.$emit('logout');
    },
    handleToggleSidebar() {
      this.$emit('toggle-sidebar');
    }
  }
};
</script>

<style scoped>
@keyframes slideDown {
  from {
    transform: translateY(-100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.animate-slide-down {
  animation: slideDown 0.5s ease-out;
}

header {
  backdrop-filter: blur(8px);
  background-color: rgba(255, 255, 255, 0.95);
}
</style>

