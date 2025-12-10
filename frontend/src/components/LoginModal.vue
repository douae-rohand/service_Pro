<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm"
    @click.self="handleClose"
  >
    <div class="bg-white rounded-3xl max-w-4xl w-full overflow-hidden shadow-2xl transform transition-all">
      <div class="grid md:grid-cols-2">
        <!-- Left Side - Image -->
        <div class="hidden md:block relative overflow-hidden">
          <div class="w-full h-full bg-gradient-to-br from-green-200 to-yellow-200 flex items-center justify-center">
            <div class="text-center text-white p-8">
              <div class="mb-6 p-4 rounded-full bg-white/20 backdrop-blur-sm inline-block">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
                  />
                </svg>
              </div>
              <h3 class="text-3xl mb-4 text-center">Bienvenue sur ServicePro</h3>
              <p class="text-center text-lg opacity-90">
                Connectez-vous pour accéder à tous nos services professionnels
              </p>
              <div class="mt-8 space-y-3 w-full max-w-xs mx-auto">
                <div
                  v-for="(feature, index) in features"
                  :key="index"
                  class="flex items-center gap-3 bg-white/20 backdrop-blur-sm rounded-lg p-3"
                >
                  <div class="w-8 h-8 rounded-full bg-white/30 flex items-center justify-center">✓</div>
                  <span class="text-sm">{{ feature }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Form -->
        <div class="relative">
          <!-- Close Button -->
          <button
            @click="handleClose"
            class="absolute top-4 right-4 text-gray-500 hover:text-white transition-all rounded-full p-2 hover:rotate-90 duration-300 z-10"
            @mouseenter="handleCloseHover($event)"
            @mouseleave="handleCloseLeave($event)"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>

          <!-- Form Content -->
          <div class="p-8 md:p-12">
            <div class="mb-8">
              <div class="inline-block px-4 py-2 rounded-full mb-4" style="background-color: #E8F5E9">
                <span class="text-sm" style="color: #92B08B">Connexion</span>
              </div>
              <h2 class="text-4xl mb-2" style="color: #92B08B">Bon retour !</h2>
              <p class="text-gray-600">Connectez-vous à votre compte</p>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-5">
              <div class="relative">
                <label class="block text-sm mb-2 flex items-center gap-2">
                  <div class="p-1 rounded-lg" style="background-color: #E8F5E9">
                    <svg class="w-4 h-4" style="color: #92B08B" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                      />
                    </svg>
                  </div>
                  Email <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.email"
                  type="email"
                  required
                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                  @focus="handleInputFocus($event)"
                  @blur="handleInputBlur($event)"
                  placeholder="votre.email@exemple.com"
                />
              </div>

              <div class="relative">
                <label class="block text-sm mb-2 flex items-center gap-2">
                  <div class="p-1 rounded-lg" style="background-color: #E8F5E9">
                    <svg class="w-4 h-4" style="color: #92B08B" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                      />
                    </svg>
                  </div>
                  Mot de passe <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.password"
                  type="password"
                  required
                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                  @focus="handleInputFocus($event)"
                  @blur="handleInputBlur($event)"
                  placeholder="••••••••"
                />
              </div>

              <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    v-model="formData.rememberMe"
                    type="checkbox"
                    class="w-4 h-4 rounded cursor-pointer"
                    style="accent-color: #92B08B"
                  />
                  <span class="text-sm text-gray-600">Se souvenir de moi</span>
                </label>
                <button type="button" class="text-sm hover:underline" style="color: #4682B4">
                  Mot de passe oublié ?
                </button>
              </div>

              <button
                type="submit"
                class="w-full text-white py-4 rounded-xl transition-all transform hover:scale-105 hover:shadow-2xl relative overflow-hidden group"
                style="
                  background: linear-gradient(135deg, #92B08B 0%, #F3E293 100%);
                  box-shadow: 0 10px 25px rgba(146, 176, 139, 0.3);
                "
              >
                <span class="relative z-10">Se connecter</span>
                <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity"></div>
              </button>

              <div class="text-center pt-4">
                <p class="text-sm text-gray-600">
                  Vous n'avez pas de compte ?
                  <button
                    type="button"
                    @click="handleSignupClick"
                    class="hover:underline transition-all"
                    style="color: #92B08B"
                  >
                    S'inscrire
                  </button>
                </p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import authService from '@/services/authService'

const props = defineProps({
  isOpen: Boolean,
  onSignupClick: Function,
})

const emit = defineEmits(['close', 'signup-click'])

const formData = ref({
  email: '',
  password: '',
  rememberMe: false,
})

const features = [
  'Accès rapide aux services',
  'Professionnels vérifiés',
  'Satisfaction garantie',
]

const handleClose = () => {
  emit('close')
}

const handleSignupClick = () => {
  handleClose()
  if (props.onSignupClick) {
    props.onSignupClick()
  } else {
    emit('signup-click')
  }
}

const handleSubmit = async () => {
  try {
    const response = await authService.login({
      email: formData.value.email,
      password: formData.value.password,
    })
    
    if (response.data.token) {
      authService.setAuthToken(response.data.token)
      alert('Connexion réussie !')
      handleClose()
      // Rediriger ou mettre à jour l'état de l'application
      window.location.reload()
    }
  } catch (error) {
    console.error('Erreur de connexion:', error)
    alert('Erreur de connexion. Vérifiez vos identifiants.')
  }
}

const handleInputFocus = (e) => {
  e.currentTarget.style.borderColor = '#92B08B'
  e.currentTarget.style.boxShadow = '0 0 0 3px rgba(146, 176, 139, 0.1)'
}

const handleInputBlur = (e) => {
  e.currentTarget.style.borderColor = '#E5E7EB'
  e.currentTarget.style.boxShadow = 'none'
}

const handleCloseHover = (e) => {
  e.currentTarget.style.backgroundColor = '#92B08B'
}

const handleCloseLeave = (e) => {
  e.currentTarget.style.backgroundColor = 'transparent'
}
</script>

