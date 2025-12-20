<template>
  <header class="bg-white shadow-md sticky top-0 z-50 animate-slide-down">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-2">
        <!-- Logo avec redirection -->
        <div class="flex items-center">
          <a 
            href="#" 
            @click.prevent="handleLogoClick"
            class="transition-transform hover:scale-105 duration-300"
          >
            <img src="../assets/logo.png" alt="Logo" class="h-16 w-auto" />
          </a>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-8 ml-12">
          <a
            href="#home"
            @click.prevent="handleNavClick('home')"
            class="px-2 py-2 transition-all duration-300 font-medium relative group"
            :class="{
              'text-[#4682B4]': activeLink === 'home',
              'text-gray-700 hover:text-[#4682B4]': activeLink !== 'home'
            }"
          >
            Accueil
            <span 
              class="absolute bottom-0 left-0 h-0.5 bg-[#4682B4] transition-all duration-300"
              :class="activeLink === 'home' ? 'w-full' : 'w-0 group-hover:w-full'"
            ></span>
          </a>

          <div class="relative group">
            <button
              @click.prevent="handleNavClick('services')"
              class="px-2 py-2 transition-all duration-300 flex items-center gap-2 font-medium relative"
              :class="{
                'text-[#4682B4]': activeLink === 'services',
                'text-gray-700 hover:text-[#4682B4]': activeLink !== 'services'
              }"
            >
              Nos Services
              <svg
                class="w-4 h-4 transition-transform duration-300 group-hover:rotate-180"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
              <span 
                class="absolute bottom-0 left-0 h-0.5 bg-[#4682B4] transition-all duration-300"
                :class="activeLink === 'services' ? 'w-full' : 'w-0 group-hover:w-full'"
              ></span>
            </button>

            <!-- Dropdown amélioré -->
            <div
              class="absolute top-full left-0 mt-2 w-56 bg-white rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 overflow-hidden"
            >
              <a
                href="#services"
                @click.prevent="handleServiceClick(1)"
                class="block px-5 py-4 transition-all duration-200 hover:bg-[#F3E293] hover:pl-7 border-l-4 border-transparent hover:border-[#92B08B]"
              >
                <div class="flex items-center gap-3">
                  <span class="font-medium text-gray-800">Jardinage</span>
                </div>
              </a>
              <a
                href="#services"
                @click.prevent="handleServiceClick(2)"
                class="block px-5 py-4 transition-all duration-200 hover:bg-[#F3E293] hover:pl-7 border-l-4 border-transparent hover:border-[#4682B4]"
              >
                <div class="flex items-center gap-3">
                  <span class="font-medium text-gray-800">Ménage</span>
                </div>
              </a>
            </div>
          </div>

          <!-- Afficher Contact et Devenir un intervenant seulement si l'utilisateur n'est PAS connecté comme client -->
          <template v-if="!user || !user.client">
            <a
              href="#contact"
              @click="handleContactClick"
              class="px-2 py-2 transition-all duration-300 font-medium relative group"
              :class="{
                'text-[#4682B4]': activeLink === 'contact',
                'text-gray-700 hover:text-[#4682B4]': activeLink !== 'contact'
              }"
            >
              Contact
              <span 
                class="absolute bottom-0 left-0 h-0.5 bg-[#4682B4] transition-all duration-300"
                :class="activeLink === 'contact' ? 'w-full' : 'w-0 group-hover:w-full'"
              ></span>
            </a>
            <a
              href="#"
              @click.prevent="handleSignupClick"
              class="px-2 py-2 transition-all duration-300 font-medium relative group text-gray-700 hover:text-[#4682B4]"
            >
              Devenir un intervenant
              <span 
                class="absolute bottom-0 left-0 h-0.5 bg-[#4682B4] transition-all duration-300 w-0 group-hover:w-full"
              ></span>
            </a>
          </template>
        </nav>

        <!-- Boutons d'action -->
        <div class="hidden md:flex items-center space-x-3">
          <template v-if="!user">
            <button
              @click="handleLoginClick"
              class="text-gray-700 font-medium px-5 py-2.5 rounded-lg transition-all duration-300 hover:text-[#4682B4] hover:bg-gray-50"
            >
              Se connecter
            </button>
            <button
              @click="handleSignupClick"
              class="text-white font-medium px-6 py-2.5 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
              style="background-color: #4682B4"
              @mouseenter="$event.currentTarget.style.backgroundColor = '#7F9A78'"
              @mouseleave="$event.currentTarget.style.backgroundColor = '#4682B4'"
            >
              S'inscrire
            </button>
          </template>
          <template v-else-if="user.client">
            <!-- Actions pour client connecté - Design amélioré -->
            <div class="flex items-center gap-3">
              <!-- Avatar et nom -->
              <div class="flex items-center gap-3 bg-gradient-to-r from-blue-50 to-gray-50 px-4 py-2.5 rounded-lg border border-gray-200 shadow-sm">
                <img
                  :src="getUserAvatar(user)"
                  :alt="getUserName(user)"
                  class="w-10 h-10 rounded-full object-cover border-2 shadow-sm"
                  style="border-color: #4682B4"
                />
                <div class="hidden lg:block">
                  <p class="text-sm font-semibold text-gray-800">{{ getUserName(user) }}</p>
                  <p class="text-xs text-gray-500">Client connecté</p>
                </div>
              </div>
              
              <!-- Bouton Mes Réservations -->
              <button
                @click="$emit('navigate', 'reservations')"
                class="text-white font-medium px-5 py-2.5 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center gap-2"
                style="background-color: #4682B4"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span class="hidden xl:inline">Mes Réservations</span>
                <span class="xl:hidden">Réservations</span>
              </button>
              
              <!-- Bouton Mon Profil -->
              <button
                @click="$emit('navigate', 'profile')"
                class="text-gray-700 font-medium px-5 py-2.5 rounded-lg transition-all duration-300 hover:text-[#4682B4] hover:bg-gray-50 flex items-center gap-2 border border-gray-200"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="hidden xl:inline">Mon Profil</span>
                <span class="xl:hidden">Profil</span>
              </button>
              
              <!-- Bouton Déconnexion -->
              <button
                @click="$emit('logout')"
                class="text-red-600 font-medium px-4 py-2.5 rounded-lg transition-all duration-300 hover:bg-red-50 hover:text-red-700 flex items-center gap-2 border border-red-200"
                title="Déconnexion"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="hidden xl:inline">Déconnexion</span>
              </button>
            </div>
          </template>
          <button
            v-else
            @click="$emit('dashboard-click')"
            class="text-white font-medium px-6 py-2.5 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center gap-2"
            style="background-color: #92B08B"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Mon Espace
          </button>
        </div>

        <!-- Mobile menu button -->
        <button
          class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors"
          @click="isMenuOpen = !isMenuOpen"
        >
          <svg v-if="!isMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Mobile Navigation -->
      <nav v-if="isMenuOpen" class="md:hidden pb-4 space-y-2 animate-fade-in">
        <a
          href="#home"
          @click.prevent="handleNavClick('home')"
          class="block py-3 px-4 text-gray-700 rounded-lg transition-all hover:bg-[#4682B4] hover:text-white font-medium"
        >
          Accueil
        </a>
        <a
          href="#services"
          @click.prevent="handleNavClick('services')"
          class="block py-3 px-4 text-gray-700 rounded-lg transition-all hover:bg-[#4682B4] hover:text-white font-medium"
        >
          Nos Services
        </a>
        <a
          href="#about"
          @click.prevent="handleNavClick('about')"
          class="block py-3 px-4 text-gray-700 rounded-lg transition-all hover:bg-[#4682B4] hover:text-white font-medium"
        >
          À Propos
        </a>
        <!-- Afficher Contact et Devenir un intervenant seulement si l'utilisateur n'est PAS connecté comme client -->
        <template v-if="!user || !user.client">
          <a
            href="#contact"
            @click="handleContactClick"
            class="block py-3 px-4 text-gray-700 rounded-lg transition-all hover:bg-[#4682B4] hover:text-white font-medium"
          >
            Contact
          </a>
          <a
            href="#"
            @click.prevent="handleSignupClick"
            class="block py-3 px-4 text-gray-700 rounded-lg transition-all hover:bg-[#4682B4] hover:text-white font-medium"
          >
            Devenir un intervenant
          </a>
        </template>
        <div class="pt-4 space-y-2 border-t border-gray-200">
          <template v-if="!user">
            <button
              @click="handleLoginClick"
              class="block w-full text-left py-3 px-4 text-gray-700 rounded-lg transition-all hover:bg-gray-50 hover:text-[#4682B4] font-medium"
            >
              Se connecter
            </button>
            <button
              class="block w-full text-white px-4 py-3 rounded-lg transition-all shadow-md font-medium"
              style="background-color: #4682B4"
              @click="handleSignupClick"
            >
              S'inscrire
            </button>
          </template>
          <template v-else-if="user.client">
            <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-gray-50 p-3 rounded-lg">
              <img
                :src="getUserAvatar(user)"
                :alt="getUserName(user)"
                class="w-10 h-10 rounded-full object-cover border-2 shadow-sm"
                style="border-color: #4682B4"
              />
              <div>
                <p class="text-sm font-semibold text-gray-800">{{ getUserName(user) }}</p>
                <p class="text-xs text-gray-500">Client connecté</p>
              </div>
            </div>
            <button
              @click="$emit('navigate', 'reservations')"
              class="block w-full text-left py-3 px-4 text-gray-700 rounded-lg transition-all hover:bg-[#4682B4] hover:text-white font-medium"
            >
              Mes Réservations
            </button>
            <button
              @click="$emit('navigate', 'profile')"
              class="block w-full text-left py-3 px-4 text-gray-700 rounded-lg transition-all hover:bg-[#4682B4] hover:text-white font-medium"
            >
              Mon Profil
            </button>
            <button
              @click="$emit('logout')"
              class="block w-full text-left py-3 px-4 text-red-600 rounded-lg transition-all hover:bg-red-50 hover:text-red-700 font-medium border border-red-200 mt-2"
            >
              Déconnexion
            </button>
          </template>
          <button
            v-else
            @click="$emit('dashboard-click')"
            class="block w-full text-white px-4 py-3 rounded-lg transition-all shadow-md font-medium"
            style="background-color: #92B08B"
          >
            Mon Espace
          </button>
        </div>
      </nav>
    </div>

    <!-- Les modals sont maintenant gérés par App.vue pour une meilleure cohérence de l'état -->
  </header>
</template>

<script setup>
import { ref } from 'vue'
import SignupModal from './SignupModal.vue'
import LoginModal from './LoginModal.vue'

const props = defineProps({
  user: Object
})

const emit = defineEmits(['login-click', 'signup-click', 'navigate-home', 'dashboard-click', 'service-click', 'navigate', 'logout'])

const isMenuOpen = ref(false)
const activeLink = ref('home')

const handleLogoClick = () => {
  // Rediriger vers la landing page (home)
  emit('navigate-home')
  activeLink.value = 'home'
  isMenuOpen.value = false
}

const handleContactClick = () => {
  activeLink.value = 'contact'
  isMenuOpen.value = false
}

const handleServiceClick = (serviceId) => {
  isMenuOpen.value = false
  activeLink.value = 'services'
  emit('service-click', serviceId)
}

const handleNavClick = (link) => {
  activeLink.value = link
  isMenuOpen.value = false
  
  // Si on clique sur Accueil, rediriger vers home
  if (link === 'home') {
    emit('navigate-home')
  }
}

const handleLoginClick = () => {
  isMenuOpen.value = false
  emit('login-click')
}

const handleSignupClick = () => {
  isMenuOpen.value = false
  emit('signup-click')
}

const handleVerificationSuccess = () => {
  emit('signup-click') // or just close
}

const handleNavigateHome = () => {
  emit('navigate-home')
}

// Helper functions pour obtenir les données utilisateur
const getUserAvatar = (user) => {
  if (!user) return 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop'
  return user.avatar || user.url || user.profile_photo || `https://ui-avatars.com/api/?name=${encodeURIComponent(getUserName(user))}&background=4682B4&color=fff`
}

const getUserName = (user) => {
  if (!user) return 'Client'
  if (user.name) return user.name
  if (user.prenom || user.nom) return `${user.prenom || ''} ${user.nom || ''}`.trim()
  if (user.client?.utilisateur) {
    const u = user.client.utilisateur
    return `${u.prenom || ''} ${u.nom || ''}`.trim() || 'Client'
  }
  return 'Client'
}
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

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slide-down {
  animation: slideDown 0.5s ease-out;
}

.animate-fade-in {
  animation: fadeIn 0.3s ease-out;
}
</style>