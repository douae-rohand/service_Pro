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
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold" style="color: #2F4F4F">
        Historique des Interventions
      </h2>
      <div class="flex items-center gap-2">
        <button
          @click="exportCSV"
          class="px-3 py-1.5 rounded-lg flex items-center gap-1.5 text-white transition-all text-xs hover:shadow-md"
          style="background-color: #4CAF50"
        >
          <Download :size="14" />
          Exporter CSV
        </button>
        <button
          @click="exportPDF"
          class="px-3 py-1.5 rounded-lg flex items-center gap-1.5 text-white transition-all text-xs hover:shadow-md"
          style="background-color: #F44336"
        >
          <Download :size="14" />
          Exporter PDF
        </button>
      </div>
    </div>

    <!-- Compact Filters -->
    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100 mb-4">
      <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-600 whitespace-nowrap">Date début:</label>
          <input
            v-model="filters.dateDebut"
            type="date"
            @change="applyFilters"
            class="px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
        </div>
        <div class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-600 whitespace-nowrap">Date fin:</label>
          <input
            v-model="filters.dateFin"
            type="date"
            @change="applyFilters"
            class="px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
        </div>
        <div class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-600 whitespace-nowrap">Statut:</label>
          <select
            v-model="filters.statut"
            @change="applyFilters"
            class="px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
          >
            <option value="">Tous</option>
            <option value="terminé">Terminé</option>
            <option value="acceptée">Acceptée</option>
            <option value="refusée">Refusée</option>
            <option value="en attente">En attente</option>
          </select>
        </div>
        <div class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-600 whitespace-nowrap">Service:</label>
          <select
            v-model="filters.service"
            @change="applyFilters"
            class="px-2 py-1 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
            style="border-color: #3B82F6"
          >
            <option value="">Tous</option>
            <option
              v-for="service in allServices"
              :key="service.id"
              :value="service.nom_service || service.nom"
            >
              {{ service.nom_service || service.nom }}
            </option>
          </select>
        </div>
        <div class="flex gap-2 ml-auto">
          <button
            @click="resetFilters"
            class="px-3 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors"
          >
            Réinitialiser
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State (minimal) -->
    <div v-if="loading" class="text-center py-4">
      <div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
    </div>

    <!-- History Table -->
    <div v-else-if="historique.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="rounded-t-2xl" style="background: linear-gradient(135deg, #E8F5E9 0%, #E3F2FD 100%);">
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-700">ID</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-700">Client</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-700">Intervenant</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-700">Service</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-700">Tâche</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-700">Date</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-700">Durée</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-700">Montant</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-700">Note</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-700">Statut</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, index) in historique"
              :key="item.id"
              class="border-t transition-colors"
              :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
              style="border-color: #F3F4F6"
            >
              <td class="py-3 px-4 text-sm" style="color: #2F4F4F">#{{ item.id }}</td>
              <td class="py-3 px-4 text-sm" style="color: #2F4F4F">{{ item.client }}</td>
              <td class="py-3 px-4 text-sm" style="color: #2F4F4F">{{ item.intervenant }}</td>
              <td class="py-3 px-4 text-sm">
                <span
                  class="px-2.5 py-1 rounded-full text-xs font-medium"
                  :style="getServiceBadgeColors(item.service, item.service_id, allServices)"
                >
                  {{ item.service }}
                </span>
              </td>
              <td class="py-3 px-4 text-sm" style="color: #2F4F4F">{{ item.tache }}</td>
              <td class="py-3 px-4 text-sm" style="color: #2F4F4F">{{ item.date }}</td>
              <td class="py-3 px-4 text-sm" style="color: #2F4F4F">{{ item.duree }}</td>
              <td class="py-3 px-4 text-sm font-semibold" style="color: #4CAF50">{{ item.montant }}DH</td>
              <td class="py-3 px-4 text-sm">
                <div v-if="item.note !== null && item.note !== undefined" class="flex items-center gap-1">
                  <Star :size="14" :fill="'#FEE347'" style="color: #FEE347" />
                  <span style="color: #2F4F4F">{{ item.note }}</span>
                </div>
                <span v-else style="color: #9E9E9E">-</span>
              </td>
              <td class="py-3 px-4 text-sm">
                <span
                  class="px-2.5 py-1 rounded-full text-xs font-medium"
                  :style="{
                    backgroundColor: getStatusColor(item.statut),
                    color: '#FFFFFF'
                  }"
                >
                  {{ formatStatus(item.statut) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-8">
      <History :size="48" class="mx-auto mb-3 text-gray-300" />
      <p class="text-gray-500 text-base">Aucune intervention trouvée</p>
    </div>

    <!-- Pagination -->
    <div v-if="historique.length > 0 && totalPages > 1" class="mt-4 flex items-center justify-between flex-wrap gap-3">
      <!-- Info -->
      <div class="text-xs text-gray-600">
        Affichage de {{ startIndex + 1 }} à {{ endIndex }} sur {{ totalItems }} résultat{{ totalItems > 1 ? 's' : '' }}
      </div>

      <!-- Contrôles de pagination -->
      <div class="flex items-center gap-2">
        <!-- Bouton Précédent -->
        <button
          @click="currentPage = Math.max(1, currentPage - 1); loadHistorique()"
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
              @click="currentPage = page; loadHistorique()"
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
          @click="currentPage = Math.min(totalPages, currentPage + 1); loadHistorique()"
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
          v-model="perPage"
          @change="changePerPage"
          class="px-2 py-1.5 rounded-lg text-xs border"
          style="border-color: #D1D5DB; color: #2F4F4F"
        >
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { History, Download, Star } from 'lucide-vue-next'
import adminService from '@/services/adminService'
import { useNotifications } from '@/composables/useNotifications'
import { useServiceColor } from '@/composables/useServiceColor'

const emit = defineEmits(['back'])
const { success, error } = useNotifications()
const { getServiceBadgeColors } = useServiceColor()

const historique = ref([])
const loading = ref(false)
const currentPage = ref(1)
const perPage = ref(10)
const totalItems = ref(0)
const allServices = ref([])

const filters = ref({
  dateDebut: '',
  dateFin: '',
  statut: '',
  service: ''
})

// Computed
const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

const startIndex = computed(() => {
  return (currentPage.value - 1) * perPage.value
})

const endIndex = computed(() => {
  return Math.min(currentPage.value * perPage.value, totalItems.value)
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

// Methods
const loadHistorique = async () => {
  try {
    loading.value = true
    // Nettoyer les paramètres pour ne pas envoyer les valeurs vides
    const params = {
      page: currentPage.value,
      per_page: perPage.value
    }
    
    // Ajouter les filtres seulement s'ils ont une valeur
    if (filters.value.dateDebut) {
      params.dateDebut = filters.value.dateDebut
    }
    if (filters.value.dateFin) {
      params.dateFin = filters.value.dateFin
    }
    if (filters.value.statut) {
      params.statut = filters.value.statut
    }
    if (filters.value.service) {
      params.service = filters.value.service
    }
    
    const response = await adminService.getHistorique(params)
    // La réponse Laravel paginate() est encapsulée dans response.data par axios
    // Structure: { data: [...], total: X, per_page: Y, current_page: Z, ... }
    if (response.data && response.data.data) {
      historique.value = response.data.data
      totalItems.value = response.data.total || 0
    } else if (Array.isArray(response.data)) {
      // Fallback si la réponse n'est pas paginée
      historique.value = response.data
      totalItems.value = response.data.length
    } else {
      historique.value = []
      totalItems.value = 0
    }
  } catch (error) {
    console.error('Erreur chargement historique:', error)
    historique.value = []
  } finally {
    loading.value = false
  }
}

// Debounce pour éviter trop de requêtes
let filterTimeout = null

const applyFilters = async () => {
  // Annuler le timeout précédent s'il existe
  if (filterTimeout) {
    clearTimeout(filterTimeout)
  }
  
  // Débouncer l'appel pour éviter trop de requêtes
  filterTimeout = setTimeout(async () => {
    currentPage.value = 1
    await loadHistorique()
  }, 300) // 300ms de délai
}

const resetFilters = async () => {
  filters.value = {
    dateDebut: '',
    dateFin: '',
    statut: '',
    service: ''
  }
  currentPage.value = 1
  await loadHistorique()
}

const exportCSV = async () => {
  try {
    // Nettoyer les paramètres pour ne pas envoyer les valeurs vides
    const params = {}
    if (filters.value.dateDebut) {
      params.dateDebut = filters.value.dateDebut
    }
    if (filters.value.dateFin) {
      params.dateFin = filters.value.dateFin
    }
    if (filters.value.statut) {
      params.statut = filters.value.statut
    }
    if (filters.value.service) {
      params.service = filters.value.service
    }
    
    const response = await adminService.exportHistoriqueCSV(params)
    
    // Generate filename with date and time: historique_interventions_YYYY-MM-DD_HH-MM-SS.csv
    const now = new Date()
    const dateStr = now.toISOString().split('T')[0] // YYYY-MM-DD
    const timeStr = now.toTimeString().split(' ')[0].replace(/:/g, '-') // HH-MM-SS
    const filename = `historique_interventions_${dateStr}_${timeStr}.csv`
    
    // Create blob and download automatically
    const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    link.style.display = 'none'
    document.body.appendChild(link)
    link.click()
    
    // Cleanup
    setTimeout(() => {
      document.body.removeChild(link)
      URL.revokeObjectURL(url)
    }, 100)
  } catch (error) {
    console.error('Erreur export CSV:', error)
    error('Erreur lors de l\'export CSV')
  }
}

const exportPDF = async () => {
  try {
    // Nettoyer les paramètres pour ne pas envoyer les valeurs vides
    const params = {}
    if (filters.value.dateDebut) {
      params.dateDebut = filters.value.dateDebut
    }
    if (filters.value.dateFin) {
      params.dateFin = filters.value.dateFin
    }
    if (filters.value.statut) {
      params.statut = filters.value.statut
    }
    if (filters.value.service) {
      params.service = filters.value.service
    }
    
    const response = await adminService.exportHistoriquePDF(params)
    
    // Generate filename with date and time: historique_interventions_YYYY-MM-DD_HH-MM-SS.pdf
    const now = new Date()
    const dateStr = now.toISOString().split('T')[0] // YYYY-MM-DD
    const timeStr = now.toTimeString().split(' ')[0].replace(/:/g, '-') // HH-MM-SS
    const filename = `historique_interventions_${dateStr}_${timeStr}.pdf`
    
    // Check if response is PDF or HTML by checking Content-Type header
    const contentType = response.headers['content-type'] || response.headers['Content-Type'] || ''
    
    // Always try to download as PDF first
    // Check the blob type or content-type header
    const isPDF = contentType.includes('application/pdf') || 
                  (response.data instanceof Blob && response.data.type === 'application/pdf')
    
    if (isPDF || !contentType.includes('text/html')) {
      // Create blob and download PDF automatically
      const blob = new Blob([response.data], { type: 'application/pdf' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = filename
      link.style.display = 'none'
      document.body.appendChild(link)
      link.click()
      
      // Cleanup
      setTimeout(() => {
        document.body.removeChild(link)
        URL.revokeObjectURL(url)
      }, 100)
    } else {
      // HTML response - download as HTML file with date/time in name (fallback)
      const htmlFilename = `historique_interventions_${dateStr}_${timeStr}.html`
      const blob = new Blob([response.data], { type: 'text/html' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = htmlFilename
      link.style.display = 'none'
      document.body.appendChild(link)
      link.click()
      
      // Cleanup
      setTimeout(() => {
        document.body.removeChild(link)
        URL.revokeObjectURL(url)
      }, 100)
    }
  } catch (error) {
    console.error('Erreur export PDF:', error)
    error('Erreur lors de l\'export PDF')
  }
}

const getStatusColor = (status) => {
  switch (status) {
    case 'terminé': return '#4CAF50'
    case 'acceptée': return '#2196F3'
    case 'refusée': return '#F44336'
    case 'en attente': return '#FF9800'
    default: return '#9E9E9E'
  }
}

const formatStatus = (status) => {
  switch (status) {
    case 'terminé': return 'Terminé'
    case 'acceptée': return 'Acceptée'
    case 'refusée': return 'Refusée'
    case 'en attente': return 'En attente'
    default: return status
  }
}

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

onMounted(async () => {
  await Promise.all([loadServices(), loadHistorique()])
})
</script>