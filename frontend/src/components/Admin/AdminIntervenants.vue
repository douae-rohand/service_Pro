<template>
  <div>
    <!-- Back Button -->
    <button
      @click="$emit('back')"
      class="flex items-center gap-2 mb-4 text-xs transition-colors hover:underline"
      style="color: #5B7C99"
    >
    ← Retour au tableau de bord
    </button>

    <div class="mb-4">
      <h2 class="text-xl font-semibold mb-4" style="color: #2F4F4F">Gestion des Intervenants</h2>
      
      <!-- Search Bar and Filters on same line -->
      <div class="flex gap-2 flex-wrap items-center">
        <!-- Search Bar -->
        <div class="flex-1 min-w-64 relative">
          <Search :size="16" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
          <input
            type="text"
            placeholder="Rechercher par nom, prénom ou email..."
            v-model="searchTerm"
            class="w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-gray-300 text-xs"
            style="background-color: #FAFAFA"
          />
        </div>

        <!-- Service Filter Dropdown -->
        <div class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-600 whitespace-nowrap">Service:</label>
          <select
            v-model="filterService"
            @change="currentPage = 1"
            class="px-2 py-1 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
            style="border-color: #3B82F6"
          >
            <option value="tous">Tous les services ({{ props.intervenants.length }})</option>
            <option
              v-for="service in allServices"
              :key="service.id"
              :value="service.nom"
            >
              {{ service.nom }} ({{ getServiceCount(service.nom) }})
            </option>
          </select>
        </div>

        <!-- Status Filter Dropdown -->
        <div class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-600 whitespace-nowrap">Statut:</label>
          <select
            v-model="filterStatus"
            @change="currentPage = 1"
            class="px-2 py-1 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
            style="border-color: #3B82F6"
          >
            <option
              v-for="filter in statusFilters"
              :key="filter.value"
              :value="filter.value"
            >
              {{ filter.label }} ({{ filter.count }})
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Intervenants Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <div 
        v-for="intervenant in paginatedIntervenants" 
        :key="intervenant.id"
        class="bg-white rounded-xl p-4 border transition-all hover:shadow-md"
        :style="{ 
          borderColor: getServiceColor(intervenant),
          borderWidth: '2px'
        }"
      >
        <!-- Header -->
        <div class="flex items-start justify-between mb-3">
          <div class="flex items-center gap-3">
            <!-- Profile Photo Placeholder -->
            <div class="relative">
              <div 
                class="w-12 h-12 rounded-full flex items-center justify-center text-xl text-gray-400 border-3"
                :style="{ 
                  backgroundColor: '#E5E7EB',
                  borderColor: getServiceColor(intervenant),
                  borderWidth: '3px'
                }"
              >
                <User :size="24" />
              </div>
              <!-- Status Badge -->
              <div 
                class="absolute -bottom-0.5 -right-0.5 w-5 h-5 rounded-full border-3 border-white flex items-center justify-center"
                :style="{ backgroundColor: intervenant.statut === 'actif' ? '#4CAF50' : '#94A3B8', borderWidth: '3px' }"
              >
                <Check v-if="intervenant.statut === 'actif'" :size="10" color="white" />
              </div>
            </div>

            <!-- Name and Service -->
            <div>
              <h3 class="text-base font-semibold mb-1" style="color: #2F4F4F">
                {{ intervenant.prenom }} {{ intervenant.nom }}
              </h3>
              <!-- Afficher les services et la note SEULEMENT si l'intervenant a des services -->
              <div v-if="intervenant.allServices && intervenant.allServices.length > 0" class="flex items-center gap-1.5 flex-wrap">
                <!-- Display all services -->
                <span 
                  v-for="service in intervenant.allServices"
                  :key="service"
                  class="px-2 py-0.5 rounded text-xs font-medium text-white"
                  :style="{ backgroundColor: service === 'Jardinage' ? '#92B08B' : '#5B7C99' }"
                >
                  {{ service }}
                </span>
                <!-- Afficher la note seulement si l'intervenant a des services -->
                <div class="flex items-center gap-1 text-xs">
                  <Star :size="12" fill="#FFC107" stroke="#FFC107" />
                  <span class="font-medium">{{ intervenant.note }}</span>
                  <span class="text-gray-400">({{ intervenant.nbAvis }})</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Status Badge Top Right -->
          <span 
            class="px-2 py-0.5 rounded-full text-xs font-medium text-white"
            :style="{ 
              backgroundColor: intervenant.statut === 'actif' ? '#92B08B' : '#5B7C99'
            }"
          >
            {{ intervenant.statut === 'actif' ? 'Actif' : 'Suspendu' }}
          </span>
        </div>

        <!-- Contact Info -->
        <div class="space-y-1.5 mb-3 text-xs text-gray-600">
          <div class="flex items-center gap-1.5">
            <Mail :size="12" />
            {{ intervenant.email }}
          </div>
          <div class="flex items-center gap-1.5">
            <Phone :size="12" />
            {{ intervenant.telephone }}
          </div>
        </div>

        <!-- Stats Grid - Afficher SEULEMENT pour les intervenants avec services -->
        <!-- Si l'intervenant n'a pas de services, cette section n'est pas affichée -->
        <div v-if="intervenant.allServices && intervenant.allServices.length > 0" class="grid grid-cols-3 gap-2 mb-3">
          <div class="text-center p-3 rounded-lg" style="background-color: #F9FAFB">
            <p class="text-xs text-gray-500 mb-0.5">Missions</p>
            <p class="text-xl font-semibold" :style="{ color: getServiceColor(intervenant) }">
              {{ intervenant.missions }}
            </p>
          </div>
          <div class="text-center p-3 rounded-lg" style="background-color: #F9FAFB">
            <p class="text-xs text-gray-500 mb-0.5">Tâches</p>
            <p class="text-xl font-semibold" style="color: #2F4F4F">
              {{ intervenant.taches.length }}
            </p>
          </div>
          <div class="text-center p-3 rounded-lg" style="background-color: #F9FAFB">
            <p class="text-xs text-gray-500 mb-0.5">Note</p>
            <p class="text-xl font-semibold" style="color: #FFC107">
              {{ intervenant.note }}
            </p>
          </div>
        </div>

        <!-- Action Button -->
        <button
          class="w-full py-2 rounded-lg text-xs font-medium transition-all text-white"
          :style="{ backgroundColor: getServiceColor(intervenant) }"
          @click="$emit('view-intervenant', intervenant)"
        >
          Voir le profil complet
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredIntervenants.length === 0" class="text-center py-8">
      <UserCheck :size="48" class="mx-auto mb-3 text-gray-300" />
      <p class="text-gray-500 text-base">Aucun intervenant trouvé</p>
    </div>

    <!-- Pagination -->
    <div v-if="filteredIntervenants.length > 0 && totalPages > 1" class="mt-4 flex items-center justify-between flex-wrap gap-3">
      <!-- Info -->
      <div class="text-xs text-gray-600">
        Affichage de {{ startIndex + 1 }} à {{ endIndex }} sur {{ filteredIntervenants.length }} intervenant{{ filteredIntervenants.length > 1 ? 's' : '' }}
      </div>

      <!-- Contrôles de pagination -->
      <div class="flex items-center gap-2">
        <!-- Bouton Précédent -->
        <button
          @click="currentPage = Math.max(1, currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all border"
          :style="{
            borderColor: currentPage === 1 ? '#D1D5DB' : '#5B7C99',
            color: currentPage === 1 ? '#9CA3AF' : '#5B7C99',
            cursor: currentPage === 1 ? 'not-allowed' : 'pointer',
            opacity: currentPage === 1 ? 0.5 : 1
          }"
        >
          Précédent
        </button>

        <!-- Numéros de page -->
        <div class="flex items-center gap-1">
          <template v-for="page in visiblePages" :key="page">
            <button
              v-if="page !== '...'"
              @click="currentPage = page"
              class="w-8 h-8 rounded-lg text-xs font-medium transition-all"
              :style="{
                backgroundColor: currentPage === page ? '#5B7C99' : 'transparent',
                color: currentPage === page ? 'white' : '#5B7C99',
                border: `1px solid ${currentPage === page ? '#5B7C99' : '#D1D5DB'}`
              }"
            >
              {{ page }}
            </button>
            <span v-else class="px-1 text-gray-500 text-xs">...</span>
          </template>
        </div>

        <!-- Bouton Suivant -->
        <button
          @click="currentPage = Math.min(totalPages, currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all border"
          :style="{
            borderColor: currentPage === totalPages ? '#D1D5DB' : '#5B7C99',
            color: currentPage === totalPages ? '#9CA3AF' : '#5B7C99',
            cursor: currentPage === totalPages ? 'not-allowed' : 'pointer',
            opacity: currentPage === totalPages ? 0.5 : 1
          }"
        >
          Suivant
        </button>
      </div>

      <!-- Sélecteur d'éléments par page -->
      <div class="flex items-center gap-2">
        <span class="text-xs text-gray-600">Par page:</span>
        <select
          v-model="itemsPerPage"
          @change="currentPage = 1"
          class="px-2 py-1.5 rounded-lg text-xs border"
          style="border-color: #D1D5DB; color: #2F4F4F"
        >
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { ArrowLeft, Search, User, Check, Star, Mail, Phone, UserCheck } from 'lucide-vue-next'
import adminService from '@/services/adminService'

const props = defineProps({
  intervenants: {
    type: Array,
    required: true,
    default: () => []
  },
  loading: Boolean
})

const emit = defineEmits(['back', 'view-intervenant', 'suspend-intervenant'])

const searchTerm = ref('')
const filterService = ref('tous')
const filterStatus = ref('tous')
const allServices = ref([])

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Compte le nombre d'intervenants pour un service donné
const getServiceCount = (serviceName) => {
  if (serviceName === 'tous') return props.intervenants.length
  return props.intervenants.filter(i => {
    if (i.allServices && Array.isArray(i.allServices)) {
      return i.allServices.includes(serviceName)
    }
    return i.service === serviceName
  }).length
}

// Status Filters with counts
// Seulement 2 statuts : actif ou suspendu (pas en_attente)
const statusFilters = computed(() => {
  const actifsCount = props.intervenants.filter(i => i.statut === 'actif').length
  const suspendusCount = props.intervenants.filter(i => i.statut === 'suspendu').length
  
  return [
    { 
      value: 'tous', 
      label: 'Tous statuts', 
      count: props.intervenants.length,
      activeColor: '#FFFACD' 
    },
    { 
      value: 'actif', 
      label: 'Actifs', 
      count: actifsCount, 
      activeColor: '#92B08B' 
    },
    { 
      value: 'suspendu', 
      label: 'Suspendus', 
      count: suspendusCount, 
      activeColor: '#5B7C99' 
    }
  ]
})

// Filtered Intervenants
const filteredIntervenants = computed(() => {
  return props.intervenants.filter(intervenant => {
    // Search filter
    const matchesSearch = (intervenant.nom || '').toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                         (intervenant.prenom || '').toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                         (intervenant.email || '').toLowerCase().includes(searchTerm.value.toLowerCase())
    
    // Service filter
    // IMPORTANT : Si un filtre service est appliqué, exclure les intervenants sans services
    let matchesService = filterService.value === 'tous'
    if (!matchesService) {
      // Si un service spécifique est sélectionné, l'intervenant doit avoir ce service
      if (intervenant.allServices && Array.isArray(intervenant.allServices) && intervenant.allServices.length > 0) {
        // L'intervenant a des services : vérifier s'il a le service demandé
        matchesService = intervenant.allServices.includes(filterService.value)
      } else {
        // Intervenant sans services (allServices vide ou null) : exclu du filtre par service
        matchesService = false
      }
    }
    // Si "tous" est sélectionné, on inclut tous les intervenants (avec ou sans services)
    
    // Status filter (actif ou suspendu uniquement)
    const matchesStatus = filterStatus.value === 'tous' || intervenant.statut === filterStatus.value
    
    return matchesSearch && matchesService && matchesStatus
  })
})

// Pagination computed properties
const totalPages = computed(() => {
  return Math.ceil(filteredIntervenants.value.length / itemsPerPage.value)
})

const startIndex = computed(() => {
  return (currentPage.value - 1) * itemsPerPage.value
})

const endIndex = computed(() => {
  return Math.min(startIndex.value + itemsPerPage.value, filteredIntervenants.value.length)
})

const paginatedIntervenants = computed(() => {
  return filteredIntervenants.value.slice(startIndex.value, endIndex.value)
})

const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value
  
  if (total <= 7) {
    // Si moins de 7 pages, afficher toutes
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    // Sinon, afficher intelligemment
    if (current <= 3) {
      // Début
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(total)
    } else if (current >= total - 2) {
      // Fin
      pages.push(1)
      pages.push('...')
      for (let i = total - 4; i <= total; i++) {
        pages.push(i)
      }
    } else {
      // Milieu
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(total)
    }
  }
  
  return pages
})

// Réinitialiser la page quand le filtre change
watch([searchTerm, filterService, filterStatus], () => {
  currentPage.value = 1
})

// Load services from API
const loadServices = async () => {
  try {
    const response = await adminService.getServices()
    allServices.value = response.data || []
  } catch (error) {
    console.error('Erreur chargement services:', error)
    allServices.value = []
  }
}

// Get service color (use first service from allServices if available, otherwise main service)
// Si l'intervenant n'a pas de services, retourne une couleur par défaut
const getServiceColor = (intervenant) => {
  // Si l'intervenant a des services, utiliser le premier pour la couleur
  if (intervenant.allServices && intervenant.allServices.length > 0) {
    const firstService = intervenant.allServices[0]
    // Utiliser la couleur du service depuis allServices si disponible
    const serviceData = allServices.value.find(s => s.nom === firstService)
    if (serviceData && serviceData.couleur) {
      return serviceData.couleur
    }
    // Fallback aux couleurs hardcodées
    return firstService === 'Jardinage' ? '#92B08B' : '#5B7C99'
  }
  
  // Si l'intervenant a un service principal (ancien format)
  if (intervenant.service && intervenant.service !== 'Aucun service') {
    const serviceData = allServices.value.find(s => s.nom === intervenant.service)
    if (serviceData && serviceData.couleur) {
      return serviceData.couleur
    }
    return intervenant.service === 'Jardinage' ? '#92B08B' : '#5B7C99'
  }
  
  // Par défaut pour les intervenants sans services : couleur neutre
  return '#94A3B8'
}

// Load services on mount
onMounted(async () => {
  await loadServices()
})
</script>

<style scoped>
/* Smooth transitions */
button {
  transition: all 0.2s ease;
}

button:hover {
  transform: translateY(-1px);
}

/* Custom scrollbar if needed */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
