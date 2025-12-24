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

    <!-- Header -->
    <h2 class="text-xl font-semibold mb-4" style="color: #2F4F4F">
      Gestion des Clients
    </h2>

    <!-- Search and Filters -->
    <div class="flex gap-3 mb-4 flex-wrap items-center">
      <div class="flex-1 min-w-64 relative">
        <input
          type="text"
          placeholder="Rechercher par nom, prénom ou email..."
          v-model="searchTerm"
          class="w-full pl-3 pr-3 py-2 border rounded-lg focus:outline-none text-xs focus:ring-2 focus:ring-blue-200"
          style="border-color: #D1D5DB; background-color: #F9FAFB"
        />
      </div>
      
      <div class="flex gap-1.5">
        <button
          v-for="filter in statusFilters"
          :key="filter.value"
          @click="filterStatus = filter.value"
          class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all whitespace-nowrap"
          :style="{ 
            backgroundColor: filterStatus === filter.value ? filter.activeColor : 'white',
            color: filterStatus === filter.value ? 'white' : '#6B7280',
            border: `1px solid ${filterStatus === filter.value ? filter.activeColor : '#D1D5DB'}`
          }"
        >
          {{ filter.label }} ({{ filter.count }})
        </button>
      </div>
    </div>

    <!-- Loading State (minimal) -->
    <div v-if="loading" class="text-center py-4">
      <div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
    </div>

    <!-- Clients Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div 
        v-for="client in paginatedClients" 
        :key="client.id"
        class="bg-white rounded-xl p-4 border transition-all"
        style="border-color: #E5E7EB; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05)"
      >
        <!-- Header with Photo and Badge -->
        <div class="flex items-start gap-3 mb-3">
          <!-- Profile Photo Placeholder -->
          <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 relative" 
               style="background-color: #E5E7EB">
            <User :size="24" style="color: #9CA3AF" />
            <!-- Online Status Indicator (if active) -->
            <div v-if="client.statut === 'actif'" 
                 class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white"
                 style="background-color: #5EAD5F">
            </div>
          </div>
          
          <div class="flex-1 min-w-0">
            <!-- Name -->
            <h3 class="text-sm font-semibold mb-0.5" style="color: #1F2937">
              {{ client.prenom }} {{ client.nom }}
            </h3>
            <!-- Email -->
            <p class="text-xs mb-0.5" style="color: #6B7280">
              {{ client.email }}
            </p>
            <!-- Phone -->
            <p class="text-xs flex items-center gap-1" style="color: #6B7280">
              <Phone :size="10" />
              {{ client.telephone }}
            </p>
          </div>

          <!-- Status Badge -->
          <span 
            class="px-2 py-0.5 rounded-md text-xs font-medium text-white flex-shrink-0"
            :style="{ 
              backgroundColor: client.statut === 'actif' ? '#5EAD5F' : '#5B7C99'
            }"
          >
            {{ client.statut }}
          </span>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-2 mb-3 rounded-lg p-2.5" style="background-color: #F9FAFB">
          <div class="text-center">
            <p class="text-xs mb-0.5" style="color: #6B7280">Réservations</p>
            <p class="text-lg font-semibold" style="color: #1F2937">{{ client.reservations }}</p>
          </div>
          <div class="text-center">
            <p class="text-xs mb-0.5" style="color: #6B7280">Dépenses</p>
            <p class="text-base font-semibold" style="color: #5EAD5F">{{ client.montantTotal }}DH</p>
          </div>
          <div class="text-center">
            <p class="text-xs mb-0.5" style="color: #6B7280">Avis</p>
            <p class="text-lg font-semibold" style="color: #1F2937">{{ client.avis || 0 }}</p>
          </div>
        </div>

        <!-- Action Button -->
        <button
          class="w-full py-2 rounded-lg text-xs font-medium transition-all text-white hover:opacity-90"
          :style="{ 
            backgroundColor: client.statut === 'actif' ? '#5EAD5F' : '#5B7C99'
          }"
          @click.stop="viewClientDetails(client)"
        >
          Voir les détails
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredClients.length === 0" class="text-center py-8">
      <p class="text-gray-500 text-base">Aucun client trouvé</p>
    </div>

    <!-- Pagination -->
    <div v-if="filteredClients.length > 0 && totalPages > 1" class="mt-4 flex items-center justify-between flex-wrap gap-3">
      <!-- Info -->
      <div class="text-xs text-gray-600">
        Affichage de {{ startIndex + 1 }} à {{ endIndex }} sur {{ filteredClients.length }} client{{ filteredClients.length > 1 ? 's' : '' }}
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
import { User, Phone } from 'lucide-vue-next'
import adminService from '@/services/adminService'
import { useAdminRealtimeSync } from '@/composables/useAdminRealtimeSync'

const props = defineProps({})

const emit = defineEmits(['back', 'view-client', 'suspend-client'])



const clients = ref([])
const loading = ref(false)

const searchTerm = ref('')
const filterStatus = ref('tous')

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)

const statusFilters = computed(() => {
  const actifsCount = clients.value.filter(c => c.statut && c.statut === 'actif').length
  const suspendusCount = clients.value.filter(c => c.statut && c.statut === 'suspendu').length
  
  return [
    { 
      value: 'tous', 
      label: 'Tous', 
      count: clients.value.length, 
      activeColor: '#FBBF24' 
    },
    { 
      value: 'actif', 
      label: 'Actifs', 
      count: actifsCount, 
      activeColor: '#5EAD5F' 
    },
    { 
      value: 'suspendu', 
      label: 'Suspendus', 
      count: suspendusCount, 
      activeColor: '#5B7C99' 
    }
  ]
})

const filteredClients = computed(() => {
  return clients.value.filter(client => {
    const matchesSearch = (client.nom || '').toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                         (client.prenom || '').toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                         (client.email || '').toLowerCase().includes(searchTerm.value.toLowerCase())
    const matchesStatus = filterStatus.value === 'tous' || client.statut === filterStatus.value
    return matchesSearch && matchesStatus
  })
})

// Pagination computed properties
const totalPages = computed(() => {
  return Math.ceil(filteredClients.value.length / itemsPerPage.value)
})

const startIndex = computed(() => {
  return (currentPage.value - 1) * itemsPerPage.value
})

const endIndex = computed(() => {
  return Math.min(startIndex.value + itemsPerPage.value, filteredClients.value.length)
})

const paginatedClients = computed(() => {
  return filteredClients.value.slice(startIndex.value, endIndex.value)
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
watch([searchTerm, filterStatus], () => {
  currentPage.value = 1
})

const viewClientDetails = (client) => {
  emit('view-client', client)
}

const loadClients = async (options = {}) => {
  const { silent = false } = options
  try {
    if (!silent) {
      loading.value = true
    }
    const response = await adminService.getClients()
    const newClients = response.data?.data || response.data || []
    
    if (silent) {
      // Mise à jour intelligente
      // 1. Créer une map des clients existants
      const clientsMap = new Map(clients.value.map(c => [c.id, c]))
      
      // 2. Construire la nouvelle liste en conservant l'ordre de l'API
      const mergedClients = newClients.map(newClient => {
        const existing = clientsMap.get(newClient.id)
        if (existing) {
          // Client existant : mettre à jour
          const hasChanges = Object.keys(newClient).some(key => existing[key] !== newClient[key])
          if (hasChanges) {
            Object.assign(existing, newClient)
          }
          return existing
        } else {
          // Nouveau client
          return newClient
        }
      })
      
      // 3. Remplacer la liste complète pour garantir l'ordre
      clients.value = mergedClients
    } else {
      clients.value = newClients
    }
  } catch (error) {
    console.error('Erreur chargement clients:', error)
    if (!silent) {
      clients.value = []
    }
  } finally {
    if (!silent) {
      loading.value = false
    }
  }
}

// Synchronisation en temps réel (3s polling)
useAdminRealtimeSync(async () => {
  await loadClients({ silent: true })
}, { enabled: true })

onMounted(() => {
  loadClients()
})
</script>
