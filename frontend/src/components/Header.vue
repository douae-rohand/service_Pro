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
                @click.prevent="handleNavClick('services')"
                class="block px-5 py-4 transition-all duration-200 hover:bg-[#F3E293] hover:pl-7 border-l-4 border-transparent hover:border-[#92B08B]"
              >
                <div class="flex items-center gap-3">
                  <span class="font-medium text-gray-800">Jardinage</span>
                </div>
              </a>
              <a
                href="#services"
                @click.prevent="handleNavClick('services')"
                class="block px-5 py-4 transition-all duration-200 hover:bg-[#F3E293] hover:pl-7 border-l-4 border-transparent hover:border-[#4682B4]"
              >
                <div class="flex items-center gap-3">
                  <span class="font-medium text-gray-800">Ménage</span>
                </div>
              </a>
            </div>
          </div>

          <a
            href="#about"
            @click.prevent="handleNavClick('about')"
            class="px-2 py-2 transition-all duration-300 font-medium relative group"
            :class="{
              'text-[#4682B4]': activeLink === 'about',
              'text-gray-700 hover:text-[#4682B4]': activeLink !== 'about'
            }"
          >
            À Propos
            <span 
              class="absolute bottom-0 left-0 h-0.5 bg-[#4682B4] transition-all duration-300"
              :class="activeLink === 'about' ? 'w-full' : 'w-0 group-hover:w-full'"
            ></span>
          </a>
          <a
            href="#contact"
            @click.prevent="handleNavClick('contact')"
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
        </nav>

        <!-- Boutons d'action -->
        <div class="hidden md:flex items-center space-x-3">
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
        <a
          href="#contact"
          @click.prevent="handleNavClick('contact')"
          class="block py-3 px-4 text-gray-700 rounded-lg transition-all hover:bg-[#4682B4] hover:text-white font-medium"
        >
          Contact
        </a>
        <div class="pt-4 space-y-2 border-t border-gray-200">
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
        </div>
      </nav>
    </div>

    <!-- Modals -->
    <SignupModal
      :is-open="isSignupModalOpen"
      @close="isSignupModalOpen = false"
    />
    <LoginModal
      :is-open="isLoginModalOpen"
      @close="isLoginModalOpen = false"
      @signup-click="handleSignupClick"
    />
  </header>
</template>

<script setup>
import { ref } from 'vue'
import SignupModal from './SignupModal.vue'
import LoginModal from './LoginModal.vue'

const props = defineProps({
  onLoginClick: Function,
  onSignupClick: Function,
  onNavigateHome: Function, // Nouvelle prop pour la navigation vers home
})

const isMenuOpen = ref(false)
const isSignupModalOpen = ref(false)
const isLoginModalOpen = ref(false)
const activeLink = ref('home')

const handleLogoClick = () => {
  // Rediriger vers la landing page (home)
  if (props.onNavigateHome) {
    props.onNavigateHome()
  }
  activeLink.value = 'home'
  isMenuOpen.value = false
}

const handleNavClick = (link) => {
  activeLink.value = link
  isMenuOpen.value = false
  
  // Si on clique sur Accueil, rediriger vers home
  if (link === 'home' && props.onNavigateHome) {
    props.onNavigateHome()
  }
}

const handleLoginClick = () => {
  isMenuOpen.value = false
  if (props.onLoginClick) {
    props.onLoginClick()
  } else {
    isLoginModalOpen.value = true
  }
}

const handleSignupClick = () => {
  isMenuOpen.value = false
  if (props.onSignupClick) {
    props.onSignupClick()
  } else {
    isSignupModalOpen.value = true
  }
}
const handleNavigateHome = () => {
  // Réinitialiser tout l'état et retourner à la page d'accueil
  currentPage.value = 'home'
  selectedService.value = null
  selectedTaskId.value = null
  selectedIntervenantData.value = null
  selectedIntervenantId.value = null
  previousPage.value = 'home'
  window.scrollTo({ top: 0, behavior: 'smooth' })
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