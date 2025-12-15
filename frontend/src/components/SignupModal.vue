<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4 backdrop-blur-md"
    @click.self="handleClose"
  >
    <div 
      class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-2xl transform transition-all"
      style="animation: slideUp 0.4s ease-out"
    >
      <div class="grid md:grid-cols-5">
        <!-- Left Side - Enhanced Image Panel -->
        <div class="hidden md:block md:col-span-2 relative overflow-hidden">
          <div
            class="w-full h-full flex items-center justify-center transition-all duration-500 relative"
            :style="{
              background:
                userType === 'client'
                  ? 'linear-gradient(135deg, #92B08B 0%, #B8D99C 50%, #F3E293 100%)'
                  : 'linear-gradient(135deg, #92B08B 0%, #A5D4E6 50%, #7EC8E3 100%)',
            }"
          >
            <!-- Decorative elements -->
            <div class="absolute top-8 right-8 w-28 h-28 rounded-full bg-white/10 backdrop-blur-sm"></div>
            <div class="absolute bottom-8 left-8 w-24 h-24 rounded-full bg-white/10 backdrop-blur-sm"></div>
            
            <div class="h-full flex flex-col justify-center items-center text-white p-6 relative z-10">
              <div class="mb-6">
                <div class="p-5 rounded-3xl bg-white/20 backdrop-blur-lg shadow-xl border border-white/30">
                  <svg v-if="userType === 'client'" class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                    />
                  </svg>
                  <svg v-else class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                    />
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Enhanced Form -->
        <div class="md:col-span-3 relative flex flex-col max-h-[90vh] bg-gradient-to-br from-white to-gray-50">
          <!-- Header -->
          <div class="sticky top-0 bg-white/95 backdrop-blur-md border-b border-gray-200 px-4 py-4 flex items-center justify-between z-10 shadow-sm">
            <div>
              <div class="flex items-center gap-1.5 mb-1.5">
                <div class="p-1 rounded-lg shadow-sm" style="background: linear-gradient(135deg, #E8F5E9 0%, #F3F9F1 100%)">
                  <svg class="w-3 h-3" style="color: #92B08B" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                    />
                  </svg>
                </div>
                <span class="text-xs font-semibold" style="color: #92B08B">Inscription</span>
              </div>
              <h2 class="text-2xl font-bold bg-gradient-to-r from-[#92B08B] to-[#7F9A78] bg-clip-text text-transparent">
                Créer un compte
              </h2>
            </div>
            <button
              @click="handleClose"
              class="text-gray-400 hover:text-white transition-all rounded-full p-2 hover:rotate-90 duration-300 bg-gray-100 hover:bg-gradient-to-br hover:from-[#92B08B] hover:to-[#7F9A78] shadow-md"
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
          </div>

          <!-- Toggle Switch -->
          <div class="px-4 py-3 flex items-center justify-center gap-3 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
            <div class="flex items-center gap-2">
              <svg 
                class="w-4 h-4 transition-colors" 
                :style="{ color: userType === 'client' ? '#92B08B' : '#94A3B8' }" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                />
              </svg>
              <span 
                :class="userType === 'client' ? 'text-gray-900 font-semibold' : 'text-gray-500'" 
                class="text-xs transition-all"
              >
                Client
              </span>
            </div>

            <button
              @click="userType = userType === 'client' ? 'intervenant' : 'client'"
              class="relative w-16 h-8 rounded-full transition-all duration-300 shadow-lg"
              :style="{ 
                background: userType === 'client' 
                  ? 'linear-gradient(135deg, #92B08B 0%, #B8D99C 100%)' 
                  : 'linear-gradient(135deg, #7EC8E3 0%, #A5D4E6 100%)'
              }"
            >
              <div
                class="absolute top-1 w-6 h-6 bg-white rounded-full shadow-md transition-all duration-300"
                :style="{ left: userType === 'client' ? '4px' : '36px' }"
              />
            </button>

            <div class="flex items-center gap-2">
              <span 
                :class="userType === 'intervenant' ? 'text-gray-900 font-semibold' : 'text-gray-500'" 
                class="text-xs transition-all"
              >
                Intervenant
              </span>
              <svg 
                class="w-4 h-4 transition-colors" 
                :style="{ color: userType === 'intervenant' ? '#92B08B' : '#94A3B8' }" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                />
              </svg>
            </div>
          </div>

          <!-- Form -->
          <div class="overflow-y-auto flex-1">
            <form @submit.prevent="handleSubmit" class="p-4 space-y-3">
              <!-- Common Fields -->
              <div class="grid md:grid-cols-2 gap-3">
                <div class="relative">
                  <label class="block text-xs font-medium mb-1.5 text-gray-700">
                    Prénom <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formData.prenom"
                    type="text"
                    required
                    class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all bg-white shadow-sm"
                    @focus="handleInputFocus($event)"
                    @blur="handleInputBlur($event)"
                    placeholder="Prénom"
                  />
                </div>
                <div class="relative">
                  <label class="block text-xs font-medium mb-1.5 text-gray-700">
                    Nom <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formData.nom"
                    type="text"
                    required
                    class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all bg-white shadow-sm"
                    @focus="handleInputFocus($event)"
                    @blur="handleInputBlur($event)"
                    placeholder="Nom"
                  />
                </div>
              </div>

              <div class="relative">
                <label class="block text-xs font-medium mb-1.5 text-gray-700">
                  Email <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.email"
                  type="email"
                  required
                  class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all bg-white shadow-sm"
                  @focus="handleInputFocus($event)"
                  @blur="handleInputBlur($event)"
                  placeholder="email@exemple.com"
                />
              </div>

              <div class="relative">
                <label class="block text-xs font-medium mb-1.5 text-gray-700">
                  Téléphone <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.telephone"
                  type="tel"
                  required
                  class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all bg-white shadow-sm"
                  @focus="handleInputFocus($event)"
                  @blur="handleInputBlur($event)"
                  placeholder="+212 6 12 34 56 78"
                />
              </div>

              <!-- Adresse / Ville uniquement pour Intervenant -->
              <template v-if="userType === 'intervenant'">
                <div class="grid md:grid-cols-2 gap-3">
                  <div class="relative">
                    <label class="block text-xs font-medium mb-1.5 text-gray-700">
                      Adresse <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="formData.adresse"
                      type="text"
                      required
                      class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all bg-white shadow-sm"
                      @focus="handleInputFocus($event)"
                      @blur="handleInputBlur($event)"
                      placeholder="Adresse"
                    />
                  </div>

                  <div class="relative">
                    <label class="block text-xs font-medium mb-1.5 text-gray-700">
                      Ville <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="formData.ville"
                      type="text"
                      required
                      class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all bg-white shadow-sm"
                      @focus="handleInputFocus($event)"
                      @blur="handleInputBlur($event)"
                      placeholder="Ville"
                    />
                  </div>
                </div>
              </template>

              <div class="grid md:grid-cols-2 gap-3">
                <div class="relative">
                  <label class="block text-xs font-medium mb-1.5 text-gray-700">
                    Mot de passe <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formData.password"
                    type="password"
                    required
                    class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all bg-white shadow-sm"
                    @focus="handleInputFocus($event)"
                    @blur="handleInputBlur($event)"
                    placeholder="••••••••"
                  />
                </div>
                <div class="relative">
                  <label class="block text-xs font-medium mb-1.5 text-gray-700">
                    Confirmer <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formData.confirmPassword"
                    type="password"
                    required
                    class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-xl focus:outline-none transition-all bg-white shadow-sm"
                    @focus="handleInputFocus($event)"
                    @blur="handleInputBlur($event)"
                    placeholder="••••••••"
                  />
                </div>
              </div>

              <!-- Terms and Submit -->
              <div class="border-t-2 pt-3 mt-3" style="border-color: #F3E293">
                <label class="flex items-start gap-2 mb-3 cursor-pointer group">
                  <input
                    v-model="formData.acceptTerms"
                    type="checkbox"
                    required
                    class="mt-0.5 w-4 h-4 rounded-md cursor-pointer"
                    style="accent-color: #92B08B"
                  />
                  <span class="text-xs text-gray-600 group-hover:text-gray-900 transition-colors">
                    J'accepte les
                    <span class="font-semibold underline" style="color: #92B08B">conditions</span>
                    et la
                    <span class="font-semibold underline" style="color: #92B08B">politique</span>
                  </span>
                </label>

                <button
                  type="submit"
                  class="w-full text-white py-2.5 text-sm rounded-xl transition-all transform hover:scale-[1.02] relative overflow-hidden group shadow-lg hover:shadow-2xl"
                  style="
                    background: linear-gradient(135deg, #92B08B 0%, #B8D99C 50%, #F3E293 100%);
                    box-shadow: 0 8px 25px rgba(146, 176, 139, 0.4);
                  "
                >
                  <span class="relative z-10 font-semibold flex items-center justify-center gap-2">
                    {{ userType === 'client' ? 'Créer mon compte' : 'Créer mon compte pro' }}
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                  </span>
                  <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity"></div>
                </button>

                <div class="mt-3 text-center p-2.5 rounded-xl shadow-sm" style="background: linear-gradient(135deg, #E8F5E9 0%, #F3F9F1 100%)">
                  <p class="text-xs font-medium" style="color: #5A6B4F">
                    Déjà un compte ?
                    <button type="button" @click="handleClose" class="font-bold hover:underline transition-all ml-1" style="color: #92B08B">
                      Se connecter
                    </button>
                  </p>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import authService from '@/services/authService'
import { useRouter } from 'vue-router'

const props = defineProps({
  isOpen: Boolean,
})

const emit = defineEmits(['close'])

const userType = ref('client')
const router = useRouter()

const formData = ref({
  nom: '',
  prenom: '',
  email: '',
  telephone: '',
  password: '',
  confirmPassword: '',
  adresse: '',
  ville: '',
  acceptTerms: false,
})

const benefits = computed(() => {
  if (userType.value === 'client') {
    return ['Services de qualité', 'Paiement sécurisé', 'Support 24/7']
  } else {
    return ['Plus de clients', 'Paiement garanti', 'Support dédié']
  }
})

const handleClose = () => {
  emit('close')
}

const handleSubmit = async () => {
  if (formData.value.password !== formData.value.confirmPassword) {
    alert('Les mots de passe ne correspondent pas')
    return
  }

  try {
    // Nettoyer les données : convertir les chaînes vides en null
    const cleanValue = (value) => (value === '' || value === null || value === undefined) ? null : value

    const registrationData = {
      nom: cleanValue(formData.value.nom),
      prenom: cleanValue(formData.value.prenom),
      email: cleanValue(formData.value.email),
      telephone: cleanValue(formData.value.telephone),
      password: formData.value.password,
      confirmPassword: formData.value.confirmPassword,
      type: userType.value,
    }
    // Ajouter adresse/ville seulement pour intervenant
    if (userType.value === 'intervenant') {
      registrationData.adresse = cleanValue(formData.value.adresse)
      registrationData.ville = cleanValue(formData.value.ville)
    }

    const response = await authService.register(registrationData)
    
    if (response.data.token) {
      authService.setAuthToken(response.data.token)
      // alert(`Inscription ${userType.value} réussie !`)
      //handleClose()
      //window.location.reload()
      // Redirection selon le type
      emit('signup-success', { email: formData.value.email }) 
      handleClose()      
      if (userType.value === 'intervenant') {
        router.push('/dashboard')
      } else if (userType.value === 'client') {
        alert('Connexion réussie !')
        window.location.reload()
      } 
    }
  } catch (error) {
    console.error('Erreur d\'inscription:', error)
    let errorMessage = 'Erreur lors de l\'inscription. Veuillez réessayer.'
    
    if (error.errors && Object.keys(error.errors).length > 0) {
      // Afficher toutes les erreurs de validation
      const errorMessages = []
      for (const [field, messages] of Object.entries(error.errors)) {
        if (Array.isArray(messages) && messages.length > 0) {
          errorMessages.push(`${field}: ${messages[0]}`)
        }
      }
      errorMessage = errorMessages.length > 0 
        ? `Erreurs de validation:\n${errorMessages.join('\n')}`
        : errorMessage
    } else if (error.message) {
      errorMessage = error.message
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
      if (error.response.data.errors) {
        const errorMessages = []
        for (const [field, messages] of Object.entries(error.response.data.errors)) {
          if (Array.isArray(messages) && messages.length > 0) {
            errorMessages.push(`${field}: ${messages[0]}`)
          }
        }
        if (errorMessages.length > 0) {
          errorMessage += `\n\n${errorMessages.join('\n')}`
        }
      }
    }
    
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