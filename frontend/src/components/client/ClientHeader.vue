<template>
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between">
        <!-- Left: Branding -->
        <div class="flex items-center gap-4">
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
import { ArrowRight } from 'lucide-vue-next';

export default {
  name: 'ClientHeader',
  components: {
    ArrowRight
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
    }
  },
  emits: ['logout'],
  methods: {
    handleLogout() {
      this.$emit('logout');
    }
  }
};
</script>

<style scoped>
/* Additional styles if needed */
</style>

