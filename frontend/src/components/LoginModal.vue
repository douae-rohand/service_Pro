<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4 backdrop-blur-md"
    @click.self="handleClose"
  >
    <div 
      class="bg-white rounded-2xl max-w-3xl w-full overflow-hidden shadow-2xl transform transition-all"
      style="animation: slideUp 0.4s ease-out"
    >
      <div class="grid md:grid-cols-2">
        <!-- Left Side - Enhanced Image Panel -->
        <div class="hidden md:block relative overflow-hidden">
          <div 
            class="w-full h-full flex items-center justify-center relative"
            style="
              background: linear-gradient(135deg, #92B08B 0%, #B8D99C 50%, #F3E293 100%);
              min-height: 500px;
            "
          >
            <!-- Decorative circles -->
            <div class="absolute top-8 left-8 w-24 h-24 rounded-full bg-white/10 backdrop-blur-sm"></div>
            <div class="absolute bottom-8 right-8 w-32 h-32 rounded-full bg-white/10 backdrop-blur-sm"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-48 h-48 rounded-full bg-white/5 backdrop-blur-sm"></div>
            
            <div class="text-center text-white p-6 relative z-10">
              <div class="mb-6 inline-block">
                <div class="p-5 rounded-3xl bg-white/20 backdrop-blur-lg shadow-xl border border-white/30">
                  <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
                    />
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Enhanced Form -->
        <div class="relative bg-gradient-to-br from-white to-gray-50">
          <!-- Close Button -->
          <button
            @click="handleClose"
            class="absolute top-4 right-4 text-gray-400 hover:text-white transition-all rounded-full p-2 hover:rotate-90 duration-300 z-10 bg-gray-100 hover:bg-gradient-to-br hover:from-[#92B08B] hover:to-[#7F9A78] shadow-md"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>

          <!-- Form Content -->
          <div class="p-6 md:p-8">
            <div class="mb-6">
              <div 
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full mb-3 shadow-sm" 
                style="background: linear-gradient(135deg, #E8F5E9 0%, #F3F9F1 100%)"
              >
                <svg class="w-3.5 h-3.5" style="color: #92B08B" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                  />
                </svg>
                <span class="text-xs font-semibold" style="color: #92B08B">Connexion</span>
              </div>
              <h2 class="text-3xl font-bold mb-2 bg-gradient-to-r from-[#92B08B] to-[#7F9A78] bg-clip-text text-transparent">
                Bon retour !
              </h2>
              <p class="text-sm text-gray-600">Connectez-vous pour continuer</p>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-4">
              <!-- Email Field -->
              <div class="relative group">
                <label class="block text-xs font-medium mb-1.5 flex items-center gap-1.5 text-gray-700">
                  <div class="p-1 rounded-lg shadow-sm" style="background: linear-gradient(135deg, #E8F5E9 0%, #F3F9F1 100%)">
                    <svg class="w-3 h-3" style="color: #92B08B" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                      />
                    </svg>
                  </div>
                  Adresse email <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.email"
                  type="email"
                  required
                  class="w-full px-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all bg-white shadow-sm"
                  @focus="handleInputFocus($event)"
                  @blur="handleInputBlur($event)"
                  placeholder="votre.email@exemple.com"
                />
              </div>

              <!-- Password Field -->
              <div class="relative group">
                <label class="block text-xs font-medium mb-1.5 flex items-center gap-1.5 text-gray-700">
                  <div class="p-1 rounded-lg shadow-sm" style="background: linear-gradient(135deg, #E8F5E9 0%, #F3F9F1 100%)">
                    <svg class="w-3 h-3" style="color: #92B08B" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                  class="w-full px-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all bg-white shadow-sm"
                  @focus="handleInputFocus($event)"
                  @blur="handleInputBlur($event)"
                  placeholder="••••••••"
                />
              </div>

              <!-- Remember Me & Forgot Password -->
              <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer group">
                  <div class="relative">
                    <input
                      v-model="formData.rememberMe"
                      type="checkbox"
                      class="w-4 h-4 rounded-md cursor-pointer appearance-none border-2 border-gray-300 checked:border-transparent transition-all"
                      style="accent-color: #92B08B"
                    />
                    <svg 
                      v-if="formData.rememberMe"
                      class="w-4 h-4 absolute top-0 left-0 pointer-events-none"
                      style="color: #92B08B"
                      fill="none" 
                      stroke="currentColor" 
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                  <span class="text-xs text-gray-600 group-hover:text-gray-900 transition-colors">
                    Se souvenir
                  </span>
                </label>
                <button 
                  type="button" 
                  class="text-xs font-medium hover:underline transition-all"
                  style="color: #4682B4"
                >
                  Mot de passe oublié ?
                </button>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                class="w-full text-white py-3 text-sm rounded-xl transition-all transform hover:scale-[1.02] relative overflow-hidden group shadow-lg hover:shadow-2xl"
                style="
                  background: linear-gradient(135deg, #92B08B 0%, #B8D99C 50%, #F3E293 100%);
                  box-shadow: 0 8px 25px rgba(146, 176, 139, 0.4);
                "
              >
                <span class="relative z-10 font-semibold flex items-center justify-center gap-2">
                  Se connecter
                  <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                  </svg>
                </span>
                <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity"></div>
              </button>

              <!-- Signup Link -->
              <div class="text-center pt-2">
                <p class="text-xs text-gray-600">
                  Pas de compte ?
                  <button
                    type="button"
                    @click="handleSignupClick"
                    class="font-semibold hover:underline transition-all ml-1"
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

const emit = defineEmits(['close', 'signup-click', 'login-success'])

const formData = ref({
  email: '',
  password: '',
  rememberMe: false,
})

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
      // Emit event to parent to navigate to dashboard
      emit('login-success', response.data)
      handleClose()
    }
  } catch (error) {
    console.error('Erreur de connexion:', error)
    const errorMessage = error.message || error.response?.data?.message || 'Erreur de connexion. Vérifiez vos identifiants.'
    alert(errorMessage)
  }
}

const handleInputFocus = (e) => {
  e.currentTarget.style.borderColor = '#92B08B'
  e.currentTarget.style.boxShadow = '0 0 0 3px rgba(146, 176, 139, 0.15)'
  e.currentTarget.style.transform = 'translateY(-1px)'
}

const handleInputBlur = (e) => {
  e.currentTarget.style.borderColor = '#E5E7EB'
  e.currentTarget.style.boxShadow = 'none'
  e.currentTarget.style.transform = 'translateY(0)'
}
</script>

<style scoped>
@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
</style>