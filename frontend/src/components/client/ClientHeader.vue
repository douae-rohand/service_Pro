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

        <!-- Right: Notification & User Profile & Logout -->
        <div class="flex items-center gap-4 sm:gap-6">
          
          <!-- Notifications -->
          <div class="relative">
            <button 
              @click="showNotifications = !showNotifications"
              class="p-2 rounded-full hover:bg-gray-50 transition-colors relative"
            >
              <Bell :size="20" class="text-gray-600" />
              <span v-if="unreadCount > 0" class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
            </button>

            <!-- Dropdown -->
            <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden animate-slide-down origin-top-right">
              <div class="p-4 border-b border-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Notifications</h3>
                <button @click="markAllAsRead" class="text-xs text-[#4682B4] font-medium hover:underline">Tout marquer comme lu</button>
              </div>
              
              <div class="max-h-80 overflow-y-auto">
                <div v-if="notifications.length === 0" class="p-8 text-center text-gray-400 text-sm">
                  Aucune notification
                </div>
                <div 
                  v-for="notification in notifications" 
                  :key="notification.id"
                  class="p-4 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0 cursor-pointer"
                  :class="{ 'bg-blue-50/50': !notification.read_at }"
                  @click="markAsRead(notification)"
                >
                  <div class="flex gap-3">
                    <div class="flex-shrink-0 mt-1">
                      <div class="w-2 h-2 rounded-full bg-[#4682B4]" v-if="!notification.read_at"></div>
                    </div>
                    <div>
                      <p class="text-sm text-gray-800" :class="{ 'font-semibold': !notification.read_at }">{{ notification.message }}</p>
                      <p class="text-xs text-gray-400 mt-1">{{ formatDate(notification.created_at) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- User Profile -->
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
import { Menu, X, LogOut, Bell, Check } from 'lucide-vue-next';
import api from '@/services/api';

export default {
  name: 'ClientHeader',
  components: {
    Menu,
    Menu,
    X,
    LogOut,
    Bell,
    Check
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
  data() {
    return {
      showNotifications: false,
      notifications: [],
      unreadCount: 0,
      pollInterval: null
    };
  },
  mounted() {
    this.fetchNotifications();
    this.pollInterval = setInterval(this.fetchNotifications, 30000); // Poll every 30s
    
    // Close dropdown when clicking outside
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    if (this.pollInterval) clearInterval(this.pollInterval);
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    handleLogout() {
      this.$emit('logout');
    },
    handleToggleSidebar() {
      this.$emit('toggle-sidebar');
    },
    async fetchNotifications() {
      try {
        const response = await api.get('notifications');
        // Ensure data exists and is an array or object with data property
        const data = response.data.data ? response.data.data : (Array.isArray(response.data) ? response.data : []);
        this.notifications = data;
        this.unreadCount = response.data.unread_count || 0;
      } catch (error) {
        console.error('Error fetching notifications (silent)', error);
      }
    },
    async markAsRead(notification) {
      if (notification.read_at) return;
      try {
        await api.post(`notifications/${notification.id}/read`);
        notification.read_at = new Date().toISOString();
        this.unreadCount = Math.max(0, this.unreadCount - 1);
      } catch (e) {
        console.error("Error marking as read", e);
      }
    },
    async markAllAsRead() {
      try {
        await api.post('notifications/read-all');
        this.notifications.forEach(n => n.read_at = new Date().toISOString());
        this.unreadCount = 0;
      } catch (e) {
        console.error("Error marking all as read", e);
      }
    },
    formatDate(date) {
      if (!date) return '';
      const d = new Date(date);
      const now = new Date();
      const diff = Math.floor((now - d) / 1000); // seconds
      
      if (diff < 60) return 'À l\'instant';
      if (diff < 3600) return `Il y a ${Math.floor(diff/60)} min`;
      if (diff < 86400) return `Il y a ${Math.floor(diff/3600)} h`;
      return d.toLocaleDateString('fr-FR');
    },
    handleClickOutside(event) {
      if (this.showNotifications && !this.$el.contains(event.target)) {
        this.showNotifications = false;
      }
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

