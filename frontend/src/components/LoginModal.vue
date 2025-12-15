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
                  <a 
            href="#" 
            @click.prevent="handleLogoClick"
            class="transition-transform hover:scale-105 duration-300"
          >
            <img src="../assets/logo.png" alt="Logo" class="h-16 w-auto" />
          </a>
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

            <!-- Login Form -->
            <form v-if="view === 'login'" @submit.prevent="handleSubmit" class="space-y-4">
              <!-- Email Field -->
              <div class="relative group">
                <label class="block text-xs font-medium mb-1.5 flex items-center gap-1.5 text-gray-700">
                  Email <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.email"
                  type="email"
                  required
                  class="w-full px-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all focus:border-[#92B08B]"
                  placeholder="votre.email@exemple.com"
                />
              </div>

              <!-- Password Field -->
              <div class="relative group">
                <label class="block text-xs font-medium mb-1.5 flex items-center gap-1.5 text-gray-700">
                  Mot de passe <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.password"
                  type="password"
                  required
                  class="w-full px-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all focus:border-[#92B08B]"
                  placeholder="••••••••"
                />
              </div>

              <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer group">
                  <input v-model="formData.rememberMe" type="checkbox" class="accent-[#92B08B]" />
                  <span class="text-xs text-gray-600">Se souvenir</span>
                </label>
                <button 
                  type="button" 
                  @click="view = 'forgot-email'"
                  class="text-xs font-medium text-[#4682B4] hover:underline"
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

              <div class="relative flex items-center gap-4 py-2">
                <div class="flex-grow border-t border-gray-200"></div>
                <span class="text-xs text-gray-400 font-medium">OU</span>
                <div class="flex-grow border-t border-gray-200"></div>
              </div>

              <!-- Google Login Button -->
              <button
                type="button"
                @click="handleGoogleLogin"
                class="w-full flex items-center justify-center gap-3 px-4 py-3 border-2 border-gray-200 rounded-xl hover:bg-gray-50 transition-all group"
              >
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5" alt="Google logo" />
                <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">
                  Continuer avec Google
                </span>
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

            <!-- Forgot Password - Step 1: Email -->
            <form v-else-if="view === 'forgot-email'" @submit.prevent="handleSendCode" class="space-y-4">
               <div>
                  <h3 class="text-lg font-bold text-gray-800 mb-2">Réinitialisation</h3>
                  <p class="text-xs text-gray-600 mb-4">Entrez votre email pour recevoir le code.</p>
               </div>
               <div class="relative group">
                <label class="block text-xs font-medium mb-1.5 text-gray-700">Email</label>
                <input
                  v-model="resetData.email"
                  type="email"
                  required
                  class="w-full px-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all focus:border-[#92B08B]"
                  placeholder="votre.email@exemple.com"
                />
              </div>
              <button
                type="submit"
                class="w-full text-white py-3 text-sm rounded-xl transition-all hover:scale-[1.02] shadow-lg"
                style="background: linear-gradient(135deg, #92B08B 0%, #B8D99C 50%, #F3E293 100%);"
              >
                Envoyer le code
              </button>
              <button type="button" @click="view = 'login'" class="w-full text-xs text-gray-500 hover:text-gray-800">Retour</button>
            </form>

            <!-- Forgot Password - Step 2: Code -->
            <form v-else-if="view === 'forgot-code'" @submit.prevent="handleVerifyCode" class="space-y-4">
               <div>
                  <h3 class="text-lg font-bold text-gray-800 mb-2">Vérification</h3>
                  <p class="text-xs text-gray-600 mb-4">Entrez le code à 6 chiffres envoyé à {{ resetData.email }}.</p>
               </div>
               <div class="relative group">
                <label class="block text-xs font-medium mb-1.5 text-gray-700">Code</label>
                <input
                  v-model="resetData.code"
                  type="text"
                  required
                  maxlength="6"
                  class="w-full px-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all focus:border-[#92B08B] tracking-widest text-center text-lg"
                  placeholder="000000"
                />
              </div>
              <button
                type="submit"
                class="w-full text-white py-3 text-sm rounded-xl transition-all hover:scale-[1.02] shadow-lg"
                style="background: linear-gradient(135deg, #92B08B 0%, #B8D99C 50%, #F3E293 100%);"
              >
                Vérifier
              </button>
              <button type="button" @click="view = 'forgot-email'" class="w-full text-xs text-gray-500 hover:text-gray-800">Retour</button>
            </form>

            <!-- Forgot Password - Step 3: New Password -->
            <form v-else-if="view === 'forgot-password'" @submit.prevent="handleResetPassword" class="space-y-4">
               <div>
                  <h3 class="text-lg font-bold text-gray-800 mb-2">Nouveau mot de passe</h3>
                  <p class="text-xs text-gray-600 mb-4">Choisissez un nouveau mot de passe sécurisé.</p>
               </div>
               <div class="relative group">
                <label class="block text-xs font-medium mb-1.5 text-gray-700">Nouveau mot de passe</label>
                <input
                  v-model="resetData.password"
                  type="password"
                  required
                  class="w-full px-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all focus:border-[#92B08B]"
                />
              </div>
               <div class="relative group">
                <label class="block text-xs font-medium mb-1.5 text-gray-700">Confirmer le mot de passe</label>
                <input
                  v-model="resetData.confirmPassword"
                  type="password"
                  required
                  class="w-full px-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all focus:border-[#92B08B]"
                />
              </div>
              <button
                type="submit"
                class="w-full text-white py-3 text-sm rounded-xl transition-all hover:scale-[1.02] shadow-lg"
                style="background: linear-gradient(135deg, #92B08B 0%, #B8D99C 50%, #F3E293 100%);"
              >
                Réinitialiser
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import authService from '@/services/authService'

const props = defineProps({
  isOpen: Boolean,
  onSignupClick: Function,
})

const emit = defineEmits(['close', 'signup-click', 'admin-login'])
const router = useRouter()
const view = ref('login') // 'login', 'forgot-email', 'forgot-code', 'forgot-password'

const formData = ref({
  email: '',
  password: '',
  rememberMe: false,
})

const resetData = ref({
  email: '',
  code: '',
  password: '',
  confirmPassword: ''
})

const isLoading = ref(false)

const handleClose = () => {
  emit('close')
  // Reset view after closing
  setTimeout(() => {
     view.value = 'login'
     resetData.value = { email: '', code: '', password: '', confirmPassword: '' }
  }, 300)
}

const handleSignupClick = () => {
  handleClose()
  if (props.onSignupClick) {
    props.onSignupClick()
  } else {
    emit('signup-click')
  }
}

const handleGoogleLogin = () => {
  window.location.href = 'http://localhost:8000/api/auth/google/redirect'
}

const handleSubmit = async () => {
  if (isLoading.value) return
  isLoading.value = true
  
  try {
    const response = await authService.login({
      email: formData.value.email,
      password: formData.value.password,
    })
    
    if (response.data.token) {
      authService.setAuthToken(response.data.token)
      
      // Récupérer les données utilisateur
      const user = response.data.user
      
      handleClose()
      
      // Redirection selon le type
      if (user.intervenant) {
        router.push('/dashboard')
      } else if (user.client) {
        alert('Connexion réussie !')
        window.location.reload()
      } else if (user.admin) {
        alert('Connexion admin réussie !')
      } else {
        alert('Connexion réussie !')
      }
    }
  } catch (error) {
    console.error('Erreur de connexion:', error)
    const errorMessage = error.message || error.response?.data?.message || 'Erreur de connexion. Vérifiez vos identifiants.'
    alert(errorMessage)
  } finally {
    isLoading.value = false
  }
}

// Forgot Password Flow
const handleSendCode = async () => {
  try {
    await authService.forgotPassword(resetData.value.email)
    view.value = 'forgot-code'
  } catch (error) {
    alert(error.response?.data?.message || 'Erreur lors de l\'envoi du code.')
  }
}

const handleVerifyCode = async () => {
  try {
    await authService.verifyCode(resetData.value.email, resetData.value.code)
    view.value = 'forgot-password'
  } catch (error) {
    alert(error.response?.data?.message || 'Code invalide.')
  }
}

const handleResetPassword = async () => {
  if (resetData.value.password !== resetData.value.confirmPassword) {
    alert('Les mots de passe ne correspondent pas.')
    return
  }
  try {
    await authService.resetPassword({
      email: resetData.value.email,
      code: resetData.value.code,
      password: resetData.value.password,
      password_confirmation: resetData.value.confirmPassword
    })
    alert('Mot de passe réinitialisé avec succès ! Vous pouvez maintenant vous connecter.')
    view.value = 'login'
  } catch (error) {
    alert(error.response?.data?.message || 'Erreur lors de la réinitialisation.')
  }
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