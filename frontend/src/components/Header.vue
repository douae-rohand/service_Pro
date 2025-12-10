<template>
  <header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-4">
        <div class="flex items-center">
          <div class="text-white px-3 py-2 rounded" style="background-color: #92B08B">
            <span>ServicePro</span>
          </div>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-8">
          <a
            href="#home"
            @click="setActiveLink('home')"
            class="px-4 py-2 rounded-lg transition-all"
            :style="{
              backgroundColor: activeLink === 'home' ? '#92B08B' : 'transparent',
              color: activeLink === 'home' ? '#FFFFFF' : '#4B5563',
            }"
            @mouseenter="handleLinkHover($event, 'home')"
            @mouseleave="handleLinkLeave($event, 'home')"
          >
            Accueil
          </a>

          <div class="relative group">
            <button
              @click="setActiveLink('services')"
              class="px-4 py-2 rounded-lg transition-all flex items-center gap-1"
              :style="{
                backgroundColor: activeLink === 'services' ? '#92B08B' : 'transparent',
                color: activeLink === 'services' ? '#FFFFFF' : '#4B5563',
              }"
              @mouseenter="handleLinkHover($event, 'services')"
              @mouseleave="handleLinkLeave($event, 'services')"
            >
              Nos Services
              <svg
                class="w-4 h-4 group-hover:rotate-180 transition-transform"
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
            </button>

            <!-- Dropdown -->
            <div
              class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50"
            >
              <a
                href="#services"
                class="block px-4 py-3 hover:bg-gray-50 transition-colors first:rounded-t-lg"
                style="color: #92B08B"
                @mouseenter="handleDropdownHover($event)"
                @mouseleave="handleDropdownLeave($event)"
              >
                Jardinage
              </a>
              <a
                href="#services"
                class="block px-4 py-3 hover:bg-gray-50 transition-colors last:rounded-b-lg"
                style="color: #A5D4E6"
                @mouseenter="handleDropdownHover($event)"
                @mouseleave="handleDropdownLeave($event)"
              >
                Ménage
              </a>
            </div>
          </div>

          <a
            href="#about"
            @click="setActiveLink('about')"
            class="px-4 py-2 rounded-lg transition-all"
            :style="{
              backgroundColor: activeLink === 'about' ? '#92B08B' : 'transparent',
              color: activeLink === 'about' ? '#FFFFFF' : '#4B5563',
            }"
            @mouseenter="handleLinkHover($event, 'about')"
            @mouseleave="handleLinkLeave($event, 'about')"
          >
            À Propos
          </a>
          <a
            href="#contact"
            @click="setActiveLink('contact')"
            class="px-4 py-2 rounded-lg transition-all"
            :style="{
              backgroundColor: activeLink === 'contact' ? '#92B08B' : 'transparent',
              color: activeLink === 'contact' ? '#FFFFFF' : '#4B5563',
            }"
            @mouseenter="handleLinkHover($event, 'contact')"
            @mouseleave="handleLinkLeave($event, 'contact')"
          >
            Contact
          </a>
        </nav>

        <div class="hidden md:flex items-center space-x-4">
          <button
            @click="handleLoginClick"
            class="text-gray-700 transition-colors"
            @mouseenter="handleButtonHover($event)"
            @mouseleave="handleButtonLeave($event)"
          >
            Se connecter
          </button>
          <button
            @click="handleSignupClick"
            class="text-white px-6 py-2 rounded-lg transition-colors"
            style="background-color: #92B08B"
            @mouseenter="handleSignupHover($event)"
            @mouseleave="handleSignupLeave($event)"
          >
            S'inscrire
          </button>
        </div>

        <!-- Mobile menu button -->
        <button
          class="md:hidden"
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
      <nav v-if="isMenuOpen" class="md:hidden pb-4 space-y-2">
        <a
          href="#home"
          class="block py-2 text-gray-700 transition-colors"
          @mouseenter="handleButtonHover($event)"
          @mouseleave="handleButtonLeave($event)"
          @click="setActiveLink('home')"
        >
          Accueil
        </a>
        <a
          href="#services"
          class="block py-2 text-gray-700 transition-colors"
          @mouseenter="handleButtonHover($event)"
          @mouseleave="handleButtonLeave($event)"
          @click="setActiveLink('services')"
        >
          Nos Services
        </a>
        <a
          href="#about"
          class="block py-2 text-gray-700 transition-colors"
          @mouseenter="handleButtonHover($event)"
          @mouseleave="handleButtonLeave($event)"
          @click="setActiveLink('about')"
        >
          À Propos
        </a>
        <a
          href="#contact"
          class="block py-2 text-gray-700 transition-colors"
          @mouseenter="handleButtonHover($event)"
          @mouseleave="handleButtonLeave($event)"
          @click="setActiveLink('contact')"
        >
          Contact
        </a>
        <div class="pt-4 space-y-2">
          <button
            @click="handleLoginClick"
            class="block w-full text-left py-2 text-gray-700 transition-colors"
            @mouseenter="handleButtonHover($event)"
            @mouseleave="handleButtonLeave($event)"
          >
            Se connecter
          </button>
          <button
            class="block w-full text-white px-6 py-2 rounded-lg transition-colors"
            style="background-color: #92B08B"
            @mouseenter="handleSignupHover($event)"
            @mouseleave="handleSignupLeave($event)"
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
})

const isMenuOpen = ref(false)
const isSignupModalOpen = ref(false)
const isLoginModalOpen = ref(false)
const activeLink = ref('home')

const setActiveLink = (link) => {
  activeLink.value = link
}

const handleLoginClick = () => {
  if (props.onLoginClick) {
    props.onLoginClick()
  } else {
    isLoginModalOpen.value = true
  }
}

const handleSignupClick = () => {
  if (props.onSignupClick) {
    props.onSignupClick()
  } else {
    isSignupModalOpen.value = true
  }
}

const handleLinkHover = (e, link) => {
  if (activeLink.value !== link) {
    e.currentTarget.style.backgroundColor = '#92B08B'
    e.currentTarget.style.color = '#FFFFFF'
  }
}

const handleLinkLeave = (e, link) => {
  if (activeLink.value !== link) {
    e.currentTarget.style.backgroundColor = 'transparent'
    e.currentTarget.style.color = '#4B5563'
  }
}

const handleButtonHover = (e) => {
  e.currentTarget.style.color = '#92B08B'
}

const handleButtonLeave = (e) => {
  e.currentTarget.style.color = ''
}

const handleSignupHover = (e) => {
  e.currentTarget.style.backgroundColor = '#7F9A78'
}

const handleSignupLeave = (e) => {
  e.currentTarget.style.backgroundColor = '#92B08B'
}

const handleDropdownHover = (e) => {
  e.currentTarget.style.backgroundColor = '#F3E293'
}

const handleDropdownLeave = (e) => {
  e.currentTarget.style.backgroundColor = ''
}
</script>

