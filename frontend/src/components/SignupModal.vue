<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm"
    @click.self="handleClose"
  >
    <div class="bg-white rounded-3xl max-w-5xl w-full max-h-[95vh] overflow-hidden shadow-2xl transform transition-all">
      <div class="grid md:grid-cols-5">
        <!-- Left Side - Image Panel -->
        <div class="hidden md:block md:col-span-2 relative overflow-hidden">
          <div
            class="w-full h-full flex items-center justify-center transition-all duration-500"
            :style="{
              background:
                userType === 'client'
                  ? 'linear-gradient(135deg, rgba(146, 176, 139, 0.95) 0%, rgba(243, 226, 147, 0.85) 100%)'
                  : 'linear-gradient(135deg, rgba(146, 176, 139, 0.95) 0%, rgba(165, 212, 230, 0.85) 100%)',
            }"
          >
            <div class="h-full flex flex-col justify-center items-center text-white p-8">
              <div class="mb-6 p-4 rounded-full bg-white/20 backdrop-blur-sm">
                <svg v-if="userType === 'client'" class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                  />
                </svg>
                <svg v-else class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                  />
                </svg>
              </div>
              <h3 class="text-3xl mb-4 text-center">
                {{ userType === 'client' ? 'Compte Client' : 'Compte Intervenant' }}
              </h3>
              <p class="text-center text-lg opacity-90 mb-8">
                {{
                  userType === 'client'
                    ? 'Accédez aux meilleurs professionnels pour vos besoins'
                    : 'Développez votre activité avec ServicePro'
                }}
              </p>

              <div class="space-y-3 w-full max-w-xs">
                <div
                  v-for="(benefit, index) in benefits"
                  :key="index"
                  class="flex items-center gap-3 bg-white/20 backdrop-blur-sm rounded-lg p-3"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  <span class="text-sm">{{ benefit }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Form -->
        <div class="md:col-span-3 relative flex flex-col max-h-[95vh]">
          <!-- Header -->
          <div class="sticky top-0 bg-white border-b px-6 py-6 flex items-center justify-between z-10">
            <div>
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5" style="color: #92B08B" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
                  />
                </svg>
                <span class="text-sm" style="color: #92B08B">Inscription</span>
              </div>
              <h2 class="text-3xl" style="color: #92B08B">Créer un compte</h2>
            </div>
            <button
              @click="handleClose"
              class="text-gray-500 hover:text-white transition-all rounded-full p-2 hover:rotate-90 duration-300"
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
          </div>

          <!-- Toggle Switch -->
          <div class="px-6 py-4 flex items-center justify-center gap-4" style="background-color: #F9FAFB">
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5" :style="{ color: userType === 'client' ? '#92B08B' : '#94A3B8' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                />
              </svg>
              <span :class="userType === 'client' ? 'text-gray-900' : 'text-gray-500'" class="text-sm">
                Client
              </span>
            </div>

            <button
              @click="userType = userType === 'client' ? 'intervenant' : 'client'"
              class="relative w-16 h-8 rounded-full transition-all duration-300"
              style="background-color: #92B08B"
            >
              <div
                class="absolute top-1 w-6 h-6 bg-white rounded-full shadow-md transition-all duration-300"
                :style="{ left: userType === 'client' ? '4px' : '36px' }"
              />
            </button>

            <div class="flex items-center gap-3">
              <span :class="userType === 'intervenant' ? 'text-gray-900' : 'text-gray-500'" class="text-sm">
                Intervenant
              </span>
              <svg class="w-5 h-5" :style="{ color: userType === 'intervenant' ? '#92B08B' : '#94A3B8' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <form @submit.prevent="handleSubmit" class="p-6 space-y-5">
              <!-- Common Fields -->
              <div class="grid md:grid-cols-2 gap-4">
                <div class="relative">
                  <label class="block text-sm mb-2">
                    Prénom <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formData.prenom"
                    type="text"
                    required
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                    @focus="handleInputFocus($event)"
                    @blur="handleInputBlur($event)"
                    placeholder="Votre prénom"
                  />
                </div>
                <div class="relative">
                  <label class="block text-sm mb-2">
                    Nom <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formData.nom"
                    type="text"
                    required
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                    @focus="handleInputFocus($event)"
                    @blur="handleInputBlur($event)"
                    placeholder="Votre nom"
                  />
                </div>
              </div>

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
                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                      />
                    </svg>
                  </div>
                  Téléphone <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.telephone"
                  type="tel"
                  required
                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                  @focus="handleInputFocus($event)"
                  @blur="handleInputBlur($event)"
                  placeholder="+33 6 12 34 56 78"
                />
              </div>

              <div class="grid md:grid-cols-2 gap-4">
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
                    Confirmer <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formData.confirmPassword"
                    type="password"
                    required
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                    @focus="handleInputFocus($event)"
                    @blur="handleInputBlur($event)"
                    placeholder="••••••••"
                  />
                </div>
              </div>

              <!-- Intervenant Specific Fields -->
              <template v-if="userType === 'intervenant'">
                <div class="border-t-2 pt-6 mt-6" style="border-color: #F3E293">
                  <h3 class="mb-4 flex items-center gap-2" style="color: #92B08B">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                      />
                    </svg>
                    Informations Professionnelles
                  </h3>
                </div>

                <div class="relative">
                  <label class="block text-sm mb-2 flex items-center gap-2">
                    <div class="p-1 rounded-lg" style="background-color: #E8F5E9">
                      <svg class="w-4 h-4" style="color: #92B08B" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                        />
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                      </svg>
                    </div>
                    Adresse <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formData.adresse"
                    type="text"
                    required
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                    @focus="handleInputFocus($event)"
                    @blur="handleInputBlur($event)"
                    placeholder="123 Rue de la Paix"
                  />
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                  <div class="relative">
                    <label class="block text-sm mb-2">
                      Ville <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="formData.ville"
                      type="text"
                      required
                      class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                      @focus="handleInputFocus($event)"
                      @blur="handleInputBlur($event)"
                      placeholder="Paris"
                    />
                  </div>
                  <div class="relative">
                    <label class="block text-sm mb-2">
                      Code postal <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="formData.codePostal"
                      type="text"
                      required
                      class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                      @focus="handleInputFocus($event)"
                      @blur="handleInputBlur($event)"
                      placeholder="75001"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm mb-2">
                    Service proposé <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="formData.service"
                    required
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                    @focus="handleInputFocus($event)"
                    @blur="handleInputBlur($event)"
                  >
                    <option value="">Sélectionnez un service</option>
                    <option value="jardinage">Jardinage</option>
                    <option value="menage">Ménage</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm mb-2">
                    Années d'expérience <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model.number="formData.experience"
                    type="number"
                    required
                    min="0"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                    @focus="handleInputFocus($event)"
                    @blur="handleInputBlur($event)"
                    placeholder="5"
                  />
                </div>

                <div>
                  <label class="block text-sm mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" style="color: #92B08B" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                      />
                    </svg>
                    Décrivez vos compétences <span class="text-red-500">*</span>
                  </label>
                  <textarea
                    v-model="formData.description"
                    required
                    rows="4"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                    @focus="handleInputFocus($event)"
                    @blur="handleInputBlur($event)"
                    placeholder="Décrivez votre expérience et vos compétences..."
                  ></textarea>
                </div>
              </template>

              <!-- Terms and Submit -->
              <div class="border-t-2 pt-6 mt-6" style="border-color: #F3E293">
                <label class="flex items-start gap-3 mb-6 cursor-pointer group">
                  <input
                    v-model="formData.acceptTerms"
                    type="checkbox"
                    required
                    class="mt-1 w-5 h-5 rounded cursor-pointer"
                    style="accent-color: #92B08B"
                  />
                  <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">
                    J'accepte les
                    <span class="underline" style="color: #92B08B">conditions d'utilisation</span>
                    et la
                    <span class="underline" style="color: #92B08B">politique de confidentialité</span>
                  </span>
                </label>

                <button
                  type="submit"
                  class="w-full text-white py-4 rounded-xl transition-all transform hover:scale-105 hover:shadow-2xl relative overflow-hidden group"
                  style="
                    background: linear-gradient(135deg, #92B08B 0%, #F3E293 100%);
                    box-shadow: 0 10px 25px rgba(146, 176, 139, 0.3);
                  "
                >
                  <span class="relative z-10 flex items-center justify-center gap-2">
                    {{ userType === 'client' ? 'Créer mon compte client' : 'Créer mon compte intervenant' }}
                  </span>
                  <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity"></div>
                </button>

                <div class="mt-6 text-center p-4 rounded-xl" style="background-color: #E8F5E9">
                  <p class="text-sm" style="color: #5A6B4F">
                    Vous avez déjà un compte ?
                    <button type="button" @click="handleClose" class="hover:underline transition-all" style="color: #92B08B">
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

const props = defineProps({
  isOpen: Boolean,
})

const emit = defineEmits(['close'])

const userType = ref('client')

const formData = ref({
  nom: '',
  prenom: '',
  email: '',
  telephone: '',
  password: '',
  confirmPassword: '',
  adresse: '',
  ville: '',
  codePostal: '',
  service: '',
  experience: '',
  description: '',
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
    const registrationData = {
      nom: formData.value.nom,
      prenom: formData.value.prenom,
      email: formData.value.email,
      telephone: formData.value.telephone,
      password: formData.value.password,
      type: userType.value,
    }

    if (userType.value === 'intervenant') {
      registrationData.adresse = formData.value.adresse
      registrationData.ville = formData.value.ville
      registrationData.codePostal = formData.value.codePostal
      registrationData.service = formData.value.service
      registrationData.experience = formData.value.experience
      registrationData.description = formData.value.description
    }

    const response = await authService.register(registrationData)
    
    if (response.data.token) {
      authService.setAuthToken(response.data.token)
      alert(`Inscription ${userType.value} réussie !`)
      handleClose()
      window.location.reload()
    }
  } catch (error) {
    console.error('Erreur d\'inscription:', error)
    alert('Erreur lors de l\'inscription. Veuillez réessayer.')
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

