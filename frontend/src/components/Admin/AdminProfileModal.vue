<template>
  <!-- Modal Overlay -->
  <div 
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
    @click.self="$emit('close')"
  >
    <!-- Modal Content -->
    <div 
      class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <!-- Header -->
      <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
        <h2 class="text-2xl font-semibold" style="color: #2F4F4F">
          {{ type === 'intervenant' ? 'Profil Intervenant' : 'Profil Client' }}
        </h2>
        <button 
          @click="$emit('close')"
          class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors"
        >
          <X :size="20" class="text-gray-500" />
        </button>
      </div>

      <!-- Content -->
      <div v-if="data" class="p-6">
        <!-- Profile Header -->
        <div class="flex items-start gap-6 mb-8 pb-8 border-b border-gray-200">
          <!-- Avatar -->
          <div class="relative">
            <div 
              class="w-24 h-24 rounded-full flex items-center justify-center text-white text-3xl font-bold"
              :style="{ backgroundColor: getServiceColor(data.service) }"
            >
              {{ data.prenom?.charAt(0) }}{{ data.nom?.charAt(0) }}
            </div>
            <div 
              v-if="type === 'intervenant'"
              class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full border-2 border-white"
              :style="{ backgroundColor: data.statut === 'actif' ? '#4CAF50' : '#FF6B6B' }"
            ></div>
          </div>

          <!-- Info -->
          <div class="flex-1">
            <h3 class="text-2xl font-semibold mb-2" style="color: #2F4F4F">
              {{ data.prenom }} {{ data.nom }}
            </h3>
            <div class="flex items-center gap-3 mb-3">
              <span 
                class="px-3 py-1 rounded-md text-sm font-medium text-white"
                :style="{ backgroundColor: data.statut === 'actif' ? '#4CAF50' : '#FF6B6B' }"
              >
                {{ data.statut }}
              </span>
              <span v-if="type === 'intervenant' && data.service" 
                class="px-3 py-1 rounded-md text-sm font-medium text-white"
                :style="{ backgroundColor: getServiceColor(data.service) }"
              >
                {{ data.service }}
              </span>
            </div>
            
            <!-- Contact Info -->
            <div class="space-y-2 text-sm text-gray-600">
              <div class="flex items-center gap-2">
                <Mail :size="16" style="color: #5B7C99" />
                <a :href="`mailto:${data.email}`" class="hover:underline">{{ data.email }}</a>
              </div>
              <div class="flex items-center gap-2">
                <Phone :size="16" style="color: #5B7C99" />
                <a :href="`tel:${data.telephone}`" class="hover:underline">{{ data.telephone }}</a>
              </div>
              <div v-if="data.adresse" class="flex items-center gap-2">
                <MapPin :size="16" style="color: #5B7C99" />
                <span>{{ data.adresse }}</span>
              </div>
            </div>
          </div>

          <!-- Stats -->
          <div class="text-center">
            <div v-if="type === 'intervenant'" class="mb-3">
              <div class="flex items-center gap-1 mb-1">
                <Star :size="20" class="fill-yellow-400 text-yellow-400" />
                <span class="text-2xl font-bold" style="color: #2F4F4F">{{ data.note || 0 }}</span>
              </div>
              <p class="text-xs text-gray-500">Note moyenne</p>
            </div>
            <div>
              <p class="text-2xl font-bold" style="color: #2F4F4F">
                {{ type === 'intervenant' ? data.missions : data.reservations }}
              </p>
              <p class="text-xs text-gray-500">
                {{ type === 'intervenant' ? 'Missions' : 'Réservations' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
          <div class="flex gap-2 border-b border-gray-200">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              class="px-6 py-3 text-sm font-medium transition-all relative"
              :class="activeTab === tab.id ? '' : 'text-gray-600 hover:text-gray-900'"
              :style="activeTab === tab.id ? { color: '#5B7C99' } : {}"
            >
              {{ tab.label }}
              <div 
                v-if="activeTab === tab.id"
                class="absolute bottom-0 left-0 right-0 h-0.5"
                style="background-color: #5B7C99"
              ></div>
            </button>
          </div>
        </div>

        <!-- Tab Content -->
        <div>
          <!-- À propos Tab -->
          <div v-if="activeTab === 'apropos'">
            <div class="grid grid-cols-2 gap-6">
              <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">Nom complet</h4>
                <p class="text-base" style="color: #2F4F4F">{{ data.prenom }} {{ data.nom }}</p>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">Email</h4>
                <p class="text-base" style="color: #2F4F4F">{{ data.email }}</p>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">Téléphone</h4>
                <p class="text-base" style="color: #2F4F4F">{{ data.telephone }}</p>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">Ville</h4>
                <p v-if="data.ville" class="text-base" style="color: #2F4F4F">{{ data.ville }}</p>
                <p v-else class="text-base text-gray-400 italic">Non renseignée</p>
              </div>
              <div class="col-span-2">
                <h4 class="text-sm font-medium text-gray-500 mb-2">Adresse complète</h4>
                <p v-if="data.adresse" class="text-base" style="color: #2F4F4F">{{ data.adresse }}</p>
                <p v-else class="text-base text-gray-400 italic">Non renseignée</p>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">Date d'inscription</h4>
                <p class="text-base" style="color: #2F4F4F">{{ formatDate(data.dateInscription) }}</p>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">Statut</h4>
                <span 
                  class="px-3 py-1 rounded-md text-sm font-medium text-white inline-block"
                  :style="{ backgroundColor: data.statut === 'actif' ? '#4CAF50' : '#FF6B6B' }"
                >
                  {{ data.statut }}
                </span>
              </div>
              <div class="col-span-2">
                <h4 class="text-sm font-medium text-gray-500 mb-2">Bio</h4>
                <p v-if="data.bio" class="text-base text-gray-700">{{ data.bio }}</p>
                <p v-else class="text-base text-gray-400 italic">Aucune biographie renseignée</p>
              </div>
            </div>
          </div>

          <!-- Services Tab (Intervenant only) -->
          <div v-if="activeTab === 'services' && type === 'intervenant'">
            <div v-if="data.taches && data.taches.length > 0" class="space-y-4">
              <div 
                v-for="tache in data.taches" 
                :key="tache.id"
                class="border border-gray-200 rounded-xl p-4"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h4 class="text-lg font-semibold mb-2" style="color: #2F4F4F">{{ tache.nom }}</h4>
                    <p v-if="tache.description" class="text-sm text-gray-600 mb-3">{{ tache.description }}</p>
                    <p v-else class="text-sm text-gray-400 italic mb-3">Aucune description</p>
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                      <div class="flex items-center gap-1">
                        <Clock :size="16" style="color: #5B7C99" />
                        <span>{{ tache.duree || '2-3 heures' }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="text-2xl font-bold" style="color: #4CAF50">{{ tache.tarif }}DH/h</p>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-12">
              <p class="text-gray-500">Aucun service disponible</p>
            </div>
          </div>

          <!-- Stats Tab -->
          <div v-if="activeTab === 'stats'">
            <div class="grid grid-cols-3 gap-6">
              <div class="bg-gray-50 rounded-xl p-6 text-center">
                <p class="text-3xl font-bold mb-2" style="color: #2F4F4F">
                  {{ type === 'intervenant' ? data.missions : data.reservations }}
                </p>
                <p class="text-sm text-gray-500">
                  {{ type === 'intervenant' ? 'Missions complétées' : 'Réservations' }}
                </p>
              </div>
              <div v-if="type === 'intervenant'" class="bg-gray-50 rounded-xl p-6 text-center">
                <p class="text-3xl font-bold mb-2" style="color: #FFA500">{{ data.note || 0 }}</p>
                <p class="text-sm text-gray-500">Note moyenne</p>
              </div>
              <div v-if="type === 'client'" class="bg-gray-50 rounded-xl p-6 text-center">
                <p class="text-3xl font-bold mb-2" style="color: #4CAF50">{{ data.montantTotal }}DH</p>
                <p class="text-sm text-gray-500">Total dépensé</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-6 text-center">
                <p class="text-3xl font-bold mb-2" style="color: #5B7C99">
                  {{ calculateDaysSince(data.dateInscription) }}
                </p>
                <p class="text-sm text-gray-500">Jours depuis inscription</p>
              </div>
            </div>
          </div>

          <!-- Disponibilité Tab (Intervenant only) -->
          <div v-if="activeTab === 'disponibilite' && type === 'intervenant'">
            <div class="bg-gray-50 rounded-xl p-6">
              <h4 class="text-sm font-medium text-gray-500 mb-3">Horaires de disponibilité</h4>
              <p v-if="data.disponibilite" class="text-base" style="color: #2F4F4F">
                {{ data.disponibilite }}
              </p>
              <p v-else class="text-base text-gray-400 italic">
                Aucune disponibilité renseignée
              </p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
          <button
            @click="toggleStatus"
            class="flex-1 py-3 rounded-lg text-white font-medium transition-all hover:opacity-90"
            :style="{ backgroundColor: data.statut === 'actif' ? '#FF6B6B' : '#4CAF50' }"
          >
            {{ data.statut === 'actif' ? 'Suspendre' : 'Activer' }}
          </button>
          <button
            @click="$emit('close')"
            class="flex-1 py-3 rounded-lg font-medium transition-all border-2"
            style="border-color: #5B7C99; color: #5B7C99"
          >
            Fermer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { X, Mail, Phone, MapPin, Star, Clock } from 'lucide-vue-next'
import adminService from '@/services/adminService'
import { useServiceColor } from '@/composables/useServiceColor'

const { getServiceColor: getServiceColorUtil } = useServiceColor()

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    required: true,
    validator: (value) => ['intervenant', 'client'].includes(value)
  },
  data: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'toggle-status'])

const activeTab = ref('apropos')
const allServices = ref([])

const tabs = computed(() => {
  if (props.type === 'intervenant') {
    return [
      { id: 'apropos', label: 'À propos' },
      { id: 'services', label: 'Services' },
      { id: 'stats', label: 'Statistiques' },
      { id: 'disponibilite', label: 'Disponibilité' }
    ]
  } else {
    return [
      { id: 'apropos', label: 'À propos' },
      { id: 'stats', label: 'Statistiques' }
    ]
  }
})

const loadServices = async () => {
  try {
    const response = await adminService.getServices()
    // Handle paginated response structure (new) or direct array (old for compatibility)
    allServices.value = response.data?.data || response.data || []
  } catch (error) {
    console.error('Erreur chargement services:', error)
    allServices.value = []
  }
}

const getServiceColor = (service) => {
  if (!service) return '#5B7C99'
  // Trouver le service dans la liste chargée pour obtenir son ID
  const serviceData = allServices.value.find(s => (s.nom_service || s.nom) === service)
  return getServiceColorUtil(service, serviceData?.id, allServices.value)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', { 
    day: 'numeric',
    month: 'long', 
    year: 'numeric' 
  })
}

const calculateDaysSince = (dateString) => {
  if (!dateString) return 0
  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date
  return Math.floor(diff / (1000 * 60 * 60 * 24))
}

const toggleStatus = () => {
  emit('toggle-status', props.data.id)
}

// Reset tab when modal opens/closes
watch(() => props.show, (newVal) => {
  if (newVal) {
    activeTab.value = 'apropos'
  }
})

onMounted(async () => {
  await loadServices()
})
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
