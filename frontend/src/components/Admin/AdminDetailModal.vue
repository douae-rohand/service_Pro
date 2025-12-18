<template>
  <div 
    class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4 backdrop-blur-sm"
    @click.self="$emit('close')"
  >
    <div 
      class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden shadow-2xl"
      style="animation: slideUp 0.3s ease-out"
    >
      <!-- Header -->
      <div 
        class="p-6 text-white relative"
        :style="{ background: headerGradient }"
      >
        <button
          @click="$emit('close')"
          class="absolute top-4 right-4 text-white/80 hover:text-white transition-colors"
        >
          <X :size="24" />
        </button>
        
        <div class="flex items-center gap-4">
          <img 
            :src="avatarUrl"
            :alt="displayName"
            class="w-20 h-20 rounded-full object-cover border-4 border-white/30"
          />
          <div>
            <h2 class="text-2xl font-bold">{{ displayName }}</h2>
            <p class="text-white/80">{{ subtitle }}</p>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="p-6 overflow-y-auto max-h-[60vh]">
        <!-- Client Details -->
        <template v-if="type === 'client'">
          <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="p-4 rounded-xl bg-gray-50">
              <p class="text-xs text-gray-500 mb-1">Email</p>
              <p class="text-sm font-medium">{{ data.email }}</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50">
              <p class="text-xs text-gray-500 mb-1">Téléphone</p>
              <p class="text-sm font-medium">{{ data.telephone }}</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50">
              <p class="text-xs text-gray-500 mb-1">Adresse</p>
              <p class="text-sm font-medium">{{ data.adresse }}</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50">
              <p class="text-xs text-gray-500 mb-1">Date d'inscription</p>
              <p class="text-sm font-medium">{{ formatDate(data.dateInscription) }}</p>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="text-center p-4 rounded-xl" style="background-color: rgba(146, 176, 139, 0.1)">
              <p class="text-3xl font-bold" style="color: #92B08B">{{ data.reservations }}</p>
              <p class="text-xs text-gray-500">Réservations</p>
            </div>
            <div class="text-center p-4 rounded-xl" style="background-color: rgba(26, 95, 163, 0.1)">
              <p class="text-3xl font-bold" style="color: #1A5FA3">{{ data.montantTotal }}DH</p>
              <p class="text-xs text-gray-500">Total dépensé</p>
            </div>
            <div class="text-center p-4 rounded-xl" style="background-color: rgba(254, 227, 71, 0.1)">
              <p class="text-3xl font-bold" style="color: #C4A23D">{{ formatDate(data.dernierService) }}</p>
              <p class="text-xs text-gray-500">Dernier service</p>
            </div>
          </div>
        </template>

        <!-- Intervenant Details -->
        <template v-if="type === 'intervenant'">
          <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="p-4 rounded-xl bg-gray-50">
              <p class="text-xs text-gray-500 mb-1">Email</p>
              <p class="text-sm font-medium">{{ data.email }}</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50">
              <p class="text-xs text-gray-500 mb-1">Téléphone</p>
              <p class="text-sm font-medium">{{ data.telephone }}</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50">
              <p class="text-xs text-gray-500 mb-1">Adresse</p>
              <p class="text-sm font-medium">{{ data.adresse }}</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50">
              <p class="text-xs text-gray-500 mb-1">Disponibilité</p>
              <p class="text-sm font-medium">{{ data.disponibilite }}</p>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="text-center p-4 rounded-xl" style="background-color: rgba(146, 176, 139, 0.1)">
              <p class="text-3xl font-bold" style="color: #92B08B">{{ data.missions }}</p>
              <p class="text-xs text-gray-500">Missions</p>
            </div>
            <div class="text-center p-4 rounded-xl" style="background-color: rgba(254, 227, 71, 0.1)">
              <div class="flex items-center justify-center gap-1">
                <Star :size="20" fill="#FEE347" stroke="#FEE347" />
                <p class="text-3xl font-bold" style="color: #C4A23D">{{ data.note }}</p>
              </div>
              <p class="text-xs text-gray-500">Note</p>
            </div>
            <div class="text-center p-4 rounded-xl" style="background-color: rgba(26, 95, 163, 0.1)">
              <p class="text-3xl font-bold" style="color: #1A5FA3">{{ data.taches?.length || 0 }}</p>
              <p class="text-xs text-gray-500">Tâches</p>
            </div>
          </div>

          <!-- Tâches List -->
          <div v-if="data.taches && data.taches.length > 0" class="mb-6">
            <h3 class="text-lg font-medium mb-3" style="color: #2F4F4F">Tâches proposées</h3>
            <div class="space-y-2">
              <div 
                v-for="tache in data.taches" 
                :key="tache.nom"
                class="p-4 rounded-xl bg-gray-50 flex justify-between items-center"
              >
                <div>
                  <p class="font-medium" style="color: #2F4F4F">{{ tache.nom }}</p>
                  <p v-if="tache.description" class="text-xs text-gray-500">{{ tache.description }}</p>
                  <p v-else class="text-xs text-gray-400 italic">Aucune description</p>
                </div>
              <div class="text-right">
                <p class="font-bold" :style="{ color: getServiceColor(data.service) }">
                  {{ tache.tarif }}DH/h
                </p>
                <p v-if="tache.duree" class="text-xs text-gray-500">{{ tache.duree }}</p>
                <p v-else class="text-xs text-gray-400 italic">Durée non précisée</p>
              </div>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Footer Actions -->
      <div class="p-6 border-t bg-gray-50 flex gap-3">
        <button
          v-if="data.statut === 'actif'"
          @click="$emit('action', { type: 'suspend', id: data.id })"
          class="flex-1 py-3 rounded-xl text-white transition-all hover:shadow-lg"
          style="background-color: #FF6B6B"
        >
          Suspendre le compte
        </button>
        <button
          v-else
          @click="$emit('action', { type: 'activate', id: data.id })"
          class="flex-1 py-3 rounded-xl text-white transition-all hover:shadow-lg"
          style="background-color: #4CAF50"
        >
          Activer le compte
        </button>
        <button
          @click="$emit('close')"
          class="px-6 py-3 rounded-xl transition-all border-2"
          style="border-color: #E5E7EB; color: #5B7C99"
        >
          Fermer
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { X, Star } from 'lucide-vue-next'
import adminService from '@/services/adminService'
import { useServiceColor } from '@/composables/useServiceColor'

const { getServiceColor: getServiceColorUtil } = useServiceColor()
const allServices = ref([])

const props = defineProps({
  type: {
    type: String,
    required: true
  },
  data: {
    type: Object,
    required: true
  }
})

defineEmits(['close', 'action'])

const displayName = computed(() => {
  if (!props.data) return ''
  return `${props.data.prenom} ${props.data.nom}`
})

const subtitle = computed(() => {
  if (props.type === 'client') {
    return `Client depuis ${formatDate(props.data.dateInscription)}`
  } else if (props.type === 'intervenant') {
    return `${props.data.service} • ${props.data.missions} missions`
  }
  return ''
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
  const serviceData = allServices.value.find(s => (s.nom_service || s.nom) === service)
  return getServiceColorUtil(service, serviceData?.id, allServices.value)
}

const headerGradient = computed(() => {
  if (props.type === 'client') {
    return 'linear-gradient(135deg, #92B08B 0%, #6B8E63 100%)'
  } else if (props.type === 'intervenant') {
    const serviceColor = getServiceColor(props.data.service)
    // Créer un dégradé basé sur la couleur du service
    // Assombrir la couleur de 20% pour le dégradé
    const darkerColor = serviceColor ? adjustColorBrightness(serviceColor, -20) : '#134B82'
    return `linear-gradient(135deg, ${serviceColor} 0%, ${darkerColor} 100%)`
  }
  return 'linear-gradient(135deg, #5B7C99 0%, #3D5A73 100%)'
})

// Fonction pour assombrir une couleur hexadécimale
const adjustColorBrightness = (hex, percent) => {
  const num = parseInt(hex.replace('#', ''), 16)
  const r = Math.max(0, Math.min(255, (num >> 16) + percent))
  const g = Math.max(0, Math.min(255, ((num >> 8) & 0x00FF) + percent))
  const b = Math.max(0, Math.min(255, (num & 0x0000FF) + percent))
  return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)
}

const avatarUrl = computed(() => {
  if (props.type === 'client') {
    const images = [
      'https://i.pravatar.cc/150?img=1',
      'https://i.pravatar.cc/150?img=2',
      'https://i.pravatar.cc/150?img=3',
      'https://i.pravatar.cc/150?img=5',
      'https://i.pravatar.cc/150?img=9'
    ]
    return images[(props.data.id - 1) % images.length]
  } else {
    const images = [
      'https://i.pravatar.cc/150?img=12',
      'https://i.pravatar.cc/150?img=47',
      'https://i.pravatar.cc/150?img=13',
      'https://i.pravatar.cc/150?img=48'
    ]
    return images[(props.data.id - 1) % images.length]
  }
})

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', { 
    day: 'numeric', 
    month: 'long', 
    year: 'numeric' 
  })
}

onMounted(async () => {
  await loadServices()
})
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

