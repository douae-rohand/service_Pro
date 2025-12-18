<template>
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between">
        <!-- Left: Toggle Button & Branding -->
        <div class="flex items-center gap-4">
          <button
            @click="handleToggleSidebar"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors lg:hidden"
            aria-label="Toggle sidebar"
          >
            <Menu v-if="!sidebarVisible" :size="24" style="color: #92b08b" />
            <X v-else :size="24" style="color: #92b08b" />
          </button>
          <button
            @click="handleToggleSidebar"
            class="hidden lg:flex p-2 rounded-lg hover:bg-gray-100 transition-colors"
            aria-label="Toggle sidebar"
          >
            <Menu v-if="!sidebarVisible" :size="24" style="color: #92b08b" />
            <X v-else :size="24" style="color: #92b08b" />
          </button>
          <h1 class="text-3xl font-bold" style="color: #92b08b">ServicePro</h1>
          <span class="text-gray-500 text-sm">Espace Client</span>
        </div>

        <!-- Right: User Profile & Logout -->
        <div class="flex items-center gap-4">
          <img
            :src="user.avatar"
            :alt="user.name"
            class="w-10 h-10 rounded-full object-cover border-2"
            style="border-color: #92b08b"
          />
          <div class="text-right">
            <p class="font-bold text-sm" style="color: #2f4f4f">{{ user.name }}</p>
            <p class="text-xs text-gray-500">{{ user.quartier }}</p>
          </div>
          <button
            @click="handleLogout"
            class="px-4 py-2 rounded-lg hover:shadow-md transition-all flex items-center gap-2 font-medium"
            style="background-color: #FFE5E5; color: #E8793F"
          >
            <ArrowRight :size="16" style="color: #E8793F" />
            <span>Déconnexion</span>
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import { ArrowRight, Menu, X } from 'lucide-vue-next';

export default {
  name: 'ClientHeader',
  components: {
    ArrowRight,
    Menu,
    X
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
  emits: ['logout', 'toggle-sidebar'],
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
/* Additional styles if needed */
</style>

