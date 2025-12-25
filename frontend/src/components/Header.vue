<template>
  <header class="bg-white/90 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-gray-100/50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-2 h-20">
        <!-- Logo avec redirection -->
        <div class="flex items-center">
          <a 
            href="#" 
            @click.prevent="handleLogoClick"
            class="transition-transform hover:scale-105 duration-300 flex items-center"
          >
            <img src="../assets/logo.png" alt="Logo" class="h-14 w-auto drop-shadow-sm" />
          </a>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-1 lg:space-x-4 ml-8">
          <a
            href="#home"
            @click.prevent="handleNavClick('home')"
            class="px-4 py-2 rounded-full transition-all duration-300 font-medium text-sm group relative overflow-hidden"
            :class="{
              'text-[#4682B4] bg-blue-50/50': activeLink === 'home',
              'text-gray-600 hover:text-[#4682B4] hover:bg-gray-50/50': activeLink !== 'home'
            }"
          >
            Accueil
          </a>

          <div class="relative group">
            <button
              @click.prevent="handleNavClick('services')"
              class="px-4 py-2 rounded-full transition-all duration-300 flex items-center gap-1.5 font-medium text-sm"
              :class="{
                'text-[#4682B4] bg-blue-50/50': activeLink === 'services',
                'text-gray-600 hover:text-[#4682B4] hover:bg-gray-50/50': activeLink !== 'services'
              }"
            >
              Nos Services
              <ChevronDown 
                class="w-4 h-4 transition-transform duration-300 group-hover:rotate-180"
                :stroke-width="2.5"
              />
            </button>

            <!-- Dropdown amélioré -->
            <div
              class="absolute top-full left-0 mt-3 w-56 bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-100/50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 overflow-hidden translate-y-2 group-hover:translate-y-0"
            >
              <div class="p-1.5">
                <a
                  href="#services"
                  @click.prevent="handleServiceClick(1)"
                  class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 hover:bg-[#F3E293]/20 group/item"
                >
                  <div class="w-2 h-2 rounded-full bg-[#92B08B] transition-transform group-hover/item:scale-125"></div>
                  <span class="font-medium text-gray-700">Jardinage</span>
                </a>
                <a
                  href="#services"
                  @click.prevent="handleServiceClick(2)"
                  class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 hover:bg-[#4682B4]/10 group/item"
                >
                  <div class="w-2 h-2 rounded-full bg-[#4682B4] transition-transform group-hover/item:scale-125"></div>
                  <span class="font-medium text-gray-700">Ménage</span>
                </a>
              </div>
            </div>
          </div>

          <!-- Mes réservations (Seulement pour client connecté) -->
          <template v-if="user && user.client">
            <a
              href="#reservations"
              @click.prevent="handleReservationsClick"
              class="px-4 py-2 rounded-full transition-all duration-300 font-medium text-sm"
              :class="{
                'text-[#4682B4] bg-blue-50/50': activeLink === 'reservations',
                'text-gray-600 hover:text-[#4682B4] hover:bg-gray-50/50': activeLink !== 'reservations'
              }"
            >
              Mes réservations
            </a>
          </template>

          <!-- Afficher Contact et Devenir un intervenant seulement si l'utilisateur n'est PAS connecté comme client -->
          <template v-if="!user || !user.client">
            <a
              href="#contact"
              @click="handleContactClick"
              class="px-4 py-2 rounded-full transition-all duration-300 font-medium text-sm"
              :class="{
                'text-[#4682B4] bg-blue-50/50': activeLink === 'contact',
                'text-gray-600 hover:text-[#4682B4] hover:bg-gray-50/50': activeLink !== 'contact'
              }"
            >
              Contact
            </a>
            <a
              href="#"
              @click.prevent="handleSignupClick"
              class="px-4 py-2 rounded-full transition-all duration-300 font-medium text-sm text-gray-600 hover:text-[#4682B4] hover:bg-gray-50/50"
            >
              Devenir intervenant
            </a>
          </template>
        </nav>

        <!-- Boutons d'action -->
        <div class="hidden md:flex items-center space-x-3">
          <template v-if="!user">
            <button
              @click="handleLoginClick"
              class="text-gray-600 font-semibold px-5 py-2.5 rounded-xl transition-all duration-300 hover:text-[#4682B4] hover:bg-gray-50/80"
            >
              Connexion
            </button>
            <button
              @click="handleSignupClick"
              class="text-white font-bold px-6 py-2.5 rounded-xl transition-all duration-300 shadow-lg shadow-[#4682B4]/20 hover:shadow-xl hover:shadow-[#4682B4]/30 transform hover:-translate-y-0.5 active:scale-95"
              style="background-color: #4682B4"
            >
              S'inscrire
            </button>
          </template>
          
          <template v-else-if="user.client">
            <!-- Actions pour client connecté - Style Premium Icon-based -->
            <div class="flex items-center gap-2 p-1.5 bg-gray-50/50 backdrop-blur-sm rounded-2xl border border-gray-100/50">
              
              <!-- User Pill -->
              <div class="flex items-center gap-2.5 px-3 py-1.5 hover:bg-white transition-colors rounded-xl group/user cursor-default">
                <div class="relative">
                  <img
                    :src="getUserAvatar(user)"
                    :alt="getUserName(user)"
                    class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-sm transition-transform group-hover/user:scale-105"
                  />
                  <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></div>
                </div>
                <div class="hidden lg:block">
                  <p class="text-[13px] font-bold text-gray-800 leading-none mb-0.5">{{ getUserName(user) }}</p>
                  <p class="text-[10px] uppercase tracking-wider font-extrabold text-[#4682B4]/70">Client</p>
                </div>
              </div>

              <div class="w-px h-8 bg-gray-200/60 mx-1"></div>
              
              <div class="flex items-center gap-1">
                <!-- Icon Mon Profil -->
                <button
                  @click="$emit('navigate', 'profile')"
                  class="p-2.5 rounded-xl text-gray-500 hover:text-[#4682B4] hover:bg-white transition-all duration-200 group"
                  title="Mon Profil"
                >
                  <User class="w-5 h-5 group-hover:scale-110" />
                </button>
                
                <!-- Icon Déconnexion -->
                <button
                  @click="onLogoutClick"
                  class="p-2.5 rounded-xl text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all duration-200 group"
                  title="Déconnexion"
                >
                  <LogOut class="w-5 h-5 group-hover:translate-x-0.5" />
                </button>
              </div>
            </div>
          </template>
        </div>

        <!-- Mobile menu button -->
        <button
          class="md:hidden p-2.5 rounded-xl hover:bg-gray-100 transition-colors"
          @click="isMenuOpen = !isMenuOpen"
        >
          <Menu v-if="!isMenuOpen" class="w-6 h-6 text-gray-600" />
          <X v-else class="w-6 h-6 text-gray-600" />
        </button>
      </div>

      <!-- Mobile Navigation -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-4"
      >
        <nav v-if="isMenuOpen" class="md:hidden pb-6 pt-2 space-y-1.5">
          <a
            v-for="link in ['home', 'services', 'about']"
            :key="link"
            href="#"
            @click.prevent="handleNavClick(link)"
            class="block py-3 px-4 rounded-xl text-gray-600 font-semibold transition-all hover:bg-gray-50 hover:text-[#4682B4]"
            :class="{ 'bg-blue-50 text-[#4682B4]': activeLink === link }"
          >
            {{ link === 'home' ? 'Accueil' : link === 'services' ? 'Nos Services' : 'À Propos' }}
          </a>

          <template v-if="!user || !user.client">
            <a
              href="#contact"
              @click.prevent="handleContactClick"
              class="block py-3 px-4 rounded-xl text-gray-600 font-semibold transition-all hover:bg-gray-50 hover:text-[#4682B4]"
            >
              Contact
            </a>
          </template>

          <div class="pt-4 mt-4 border-t border-gray-100 space-y-2">
            <template v-if="!user">
              <button
                @click="handleLoginClick"
                class="block w-full py-3 px-4 text-center rounded-xl font-bold bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors"
              >
                Connexion
              </button>
              <button
                class="block w-full py-3 px-4 text-center text-white font-bold rounded-xl shadow-lg transition-transform active:scale-95"
                style="background-color: #4682B4"
                @click="handleSignupClick"
              >
                Créer un compte
              </button>
            </template>
            
            <template v-else-if="user.client">
              <div class="p-4 bg-gray-50 rounded-2xl flex items-center gap-3 mb-4">
                <img
                  :src="getUserAvatar(user)"
                  :alt="getUserName(user)"
                  class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm"
                />
                <div>
                  <p class="font-bold text-gray-800">{{ getUserName(user) }}</p>
                  <p class="text-xs text-gray-500 font-medium">Client Connecté</p>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-2">
                <button
                  @click="$emit('navigate', 'reservations')"
                  class="flex items-center justify-center gap-2 py-3 rounded-xl bg-white border border-gray-100 text-gray-700 font-bold hover:bg-gray-50 transition-colors"
                >
                  <Calendar class="w-5 h-5" />
                  <span>Réservations</span>
                </button>
                <button
                  @click="$emit('navigate', 'profile')"
                  class="flex items-center justify-center gap-2 py-3 rounded-xl bg-white border border-gray-100 text-gray-700 font-bold hover:bg-gray-50 transition-colors"
                >
                  <User class="w-5 h-5" />
                  <span>Profil</span>
                </button>
              </div>
              <button
                @click="onLogoutClick"
                class="block w-full mt-2 py-3 px-4 text-center text-red-600 font-bold rounded-xl bg-red-50 hover:bg-red-100 transition-colors"
              >
                Déconnexion
              </button>
            </template>

            <button
              v-else
              @click="$emit('dashboard-click')"
              class="block w-full py-3 px-4 text-center text-white font-bold rounded-xl shadow-lg"
              style="background-color: #92B08B"
            >
              Mon Tableau de bord
            </button>
          </div>
        </nav>
      </transition>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue'
import { 
  User, 
  LogOut, 
  Calendar, 
  ChevronDown, 
  Menu, 
  X
} from 'lucide-vue-next'
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

const handleReservationsClick = () => {
  activeLink.value = 'reservations'
  isMenuOpen.value = false
  emit('navigate', 'reservations')
}

const onLogoutClick = () => {
  console.log("Header: Logout clicked");
  emit('logout');
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