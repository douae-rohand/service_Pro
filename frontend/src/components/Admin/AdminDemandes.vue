<template>
  <div>
    <!-- Back Button -->
    <button
      @click="$emit('back')"
      class="flex items-center gap-2 mb-6 text-sm transition-colors hover:underline"
      style="color: #5B7C99"
    >
      ← Retour au tableau de bord
    </button>

    <!-- Header -->
    <h2 class="text-xl font-semibold mb-4" style="color: #2F4F4F">
      Demandes d'inscription des Intervenants
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

      <div class="min-w-48">
        <select
          v-model="filterService"
          class="w-full px-3 py-2 border rounded-lg focus:outline-none text-xs focus:ring-2 focus:ring-blue-200 transition-all"
          style="border-color: #D1D5DB; background-color: #F9FAFB; color: #2F4F4F"
        >
          <option value="tous">Tous les services ({{ demandes.length }})</option>
          <option
            v-for="service in allServices"
            :key="service.id"
            :value="service.nom_service || service.nom"
          >
            {{ service.nom_service || service.nom }} ({{ getServiceCount(service.nom_service || service.nom) }})
          </option>
        </select>
      </div>
    </div>

    <!-- Loading State (minimal) -->
    <div v-if="loading" class="text-center py-4">
      <div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
    </div>

    <!-- Demandes List -->
    <div v-else-if="filteredDemandes.length > 0" class="space-y-3">
      <div
        v-for="demande in paginatedDemandes"
        :key="demande.request_id || (demande.id + '_' + demande.service_id)"
        class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all"
      >
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-3">
              <!-- Profile Photo Placeholder -->
              <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                   :style="{ backgroundColor: '#E5E7EB' }">
                <User :size="24" style="color: #9CA3AF" />
              </div>

              <div>
                <h3 class="text-lg font-semibold mb-1" style="color: #2F4F4F">
                  {{ demande.prenom }} {{ demande.nom }}
                </h3>
                <div class="flex items-center gap-2 flex-wrap">
                  <!-- Afficher UN SEUL service (car chaque carte représente une demande pour un service) -->
                  <span
                    class="px-2 py-0.5 rounded-full text-xs font-medium"
                    :style="getServiceBadgeColors(demande.service, demande.service_id, allServices)"
                  >
                    {{ demande.service }}
                  </span>
                  <span class="text-xs text-gray-500">• {{ demande.experience || 'Expérience non précisée' }}</span>
                  <span class="text-xs text-gray-500">• En attente depuis {{ demande.dateAttente }}</span>
                  <!-- Statut de la demande -->
                  <span
                    class="px-2 py-0.5 rounded-full text-xs font-medium"
                    :style="{
                      backgroundColor: getStatutColor(demande.statut).bg,
                      color: getStatutColor(demande.statut).text
                    }"
                  >
                    {{ getStatutLabel(demande.statut) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Contact Info -->
            <div class="grid grid-cols-3 gap-3 mb-3">
              <div>
                <p class="text-xs text-gray-500 mb-0.5">Email</p>
                <p class="text-xs" style="color: #2F4F4F">{{ demande.email }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 mb-0.5">Téléphone</p>
                <p class="text-xs" style="color: #2F4F4F">{{ demande.telephone }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 mb-0.5">Adresse</p>
                <p class="text-xs" style="color: #2F4F4F">{{ demande.adresse || 'Non renseignée' }}</p>
              </div>
            </div>

            <!-- Présentation ou Bio -->
            <div v-if="demande.presentation || demande.bio" class="bg-gray-50 rounded-lg p-3 mb-3">
              <p class="text-xs text-gray-700 italic">
                "{{ demande.presentation || demande.bio }}"
              </p>
              <p v-if="demande.presentation && demande.bio" class="text-xs text-gray-500 mt-1">
                (Présentation spécifique au service "{{ demande.service }}")
              </p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-1.5 ml-3 flex-col">
            <button
              @click="viewDemandeDetails(demande)"
              class="px-4 py-1.5 rounded-lg flex items-center gap-1.5 transition-all text-xs hover:shadow-md whitespace-nowrap"
              style="background-color: #F5F5F5; color: #2F4F4F"
            >
              <Eye :size="14" />
              Détails
            </button>
            <button
              @click="approveDemande(demande.id, demande.service_id)"
              class="px-4 py-1.5 rounded-lg flex items-center gap-1.5 transition-all text-xs hover:shadow-md whitespace-nowrap"
              style="background-color: #B8E6AF; color: #2F4F4F"
            >
              <CheckCircle :size="14" />
              Accepter
            </button>
            <button
              @click="rejectDemande(demande.id, demande.service_id)"
              class="px-4 py-1.5 rounded-lg flex items-center gap-1.5 transition-all text-xs hover:shadow-md whitespace-nowrap"
              style="background-color: #FFD4D4; color: #8B4545"
            >
              <XCircle :size="14" />
              Refuser
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-8">
      <UserCheck :size="48" class="mx-auto mb-3 text-gray-300" />
      <p class="text-gray-500 text-base">Aucune demande en attente</p>
    </div>

    <!-- Pagination -->
    <div v-if="filteredDemandes.length > 0 && totalPages > 1" class="mt-4 flex items-center justify-between flex-wrap gap-3">
      <!-- Info -->
      <div class="text-xs text-gray-600">
        Affichage de {{ startIndex + 1 }} à {{ endIndex }} sur {{ filteredDemandes.length }} demande(s)
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


    <!-- Detail Modal -->
    <div
      v-if="showDetailModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showDetailModal = false"
    >
      <div class="bg-white rounded-xl w-full max-w-3xl max-h-[85vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b">
          <h2 class="text-lg font-semibold" style="color: #2F4F4F">
            Détails de la demande
          </h2>
          <button
            @click="showDetailModal = false"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <X :size="20" />
          </button>
        </div>

        <!-- Content -->
        <div v-if="selectedDemande" class="p-4">
          <!-- Profile Header -->
          <div class="flex items-start gap-3 mb-4 pb-4 border-b">
            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                 :style="{ backgroundColor: '#E5E7EB' }">
              <User :size="24" style="color: #9CA3AF" />
            </div>
            <div class="flex-1 flex items-start justify-between">
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <h3 class="text-xl font-semibold" style="color: #2F4F4F">
                    {{ selectedDemande.prenom }} {{ selectedDemande.nom }}
                  </h3>
                  <!-- Statut de la demande dans la modal -->
                  <span
                    class="px-2 py-1 rounded-full text-xs font-medium"
                    :style="{
                      backgroundColor: getStatutColor(selectedDemande.statut || 'demmande').bg,
                      color: getStatutColor(selectedDemande.statut || 'demmande').text
                    }"
                  >
                    {{ getStatutLabel(selectedDemande.statut || 'demmande') }}
                  </span>
                </div>
              </div>
              <div class="flex flex-col items-end gap-1 text-xs text-gray-600">
                <span>Inscription le {{ formatDate(selectedDemande.dateInscription) }}</span>
                <span class="text-orange-500">En attente depuis {{ selectedDemande.dateAttente }}</span>
              </div>
            </div>
          </div>

          <!-- Informations personnelles -->
          <div class="mb-4">
            <h4 class="text-base font-semibold mb-3" style="color: #2F4F4F">Informations personnelles</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div>
                <p class="text-xs text-gray-500 mb-1">Email</p>
                <p class="text-sm font-medium" style="color: #2F4F4F">{{ selectedDemande.email }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 mb-1">Téléphone</p>
                <p class="text-sm font-medium" style="color: #2F4F4F">{{ selectedDemande.telephone }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 mb-1">Ville</p>
                <p class="text-sm font-medium" style="color: #2F4F4F">{{ selectedDemande.ville || selectedDemande.adresse || 'Non renseignée' }}</p>
              </div>
            </div>
          </div>

          <!-- Expérience professionnelle -->
          <div class="mb-4">
            <h4 class="text-base font-semibold mb-3" style="color: #2F4F4F">Expérience professionnelle</h4>
            <div class="flex flex-wrap items-start gap-4">
              <div>
                <p class="text-xs text-gray-500 mb-1">Expérience</p>
                <p class="text-sm font-medium" style="color: #2F4F4F">{{ selectedDemande.experience }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 mb-1">Service demandé</p>
                <div class="flex flex-wrap gap-2">
                  <span
                    class="px-2 py-1 rounded-full text-xs font-medium"
                    :style="getServiceBadgeColors(selectedDemande.service, selectedDemande.service_id, allServices)"
                  >
                    {{ selectedDemande.service }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Présentation -->
          <div class="mb-4">
            <h4 class="text-base font-semibold mb-3" style="color: #2F4F4F">Présentation</h4>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-sm text-gray-700 leading-relaxed">
                {{ selectedDemande.presentation || selectedDemande.bio || 'Aucune présentation disponible.' }}
              </p>
              <p v-if="selectedDemande.presentation && selectedDemande.bio" class="text-xs text-gray-500 mt-2 italic">
                Note: Cette présentation est spécifique au service "{{ selectedDemande.service }}". 
                La bio générale de l'intervenant est également disponible ci-dessous.
              </p>
            </div>
            <!-- Bio générale (si différente de la présentation) -->
            <div v-if="selectedDemande.bio && selectedDemande.presentation && selectedDemande.bio !== selectedDemande.presentation" class="mt-3 bg-blue-50 rounded-lg p-3">
              <p class="text-xs text-gray-500 mb-1">Bio générale de l'intervenant :</p>
              <p class="text-sm text-gray-700 leading-relaxed italic">
                "{{ selectedDemande.bio }}"
              </p>
            </div>
          </div>

          <!-- Documents de vérification -->
          <div class="mb-4">
            <h4 class="text-base font-semibold mb-3" style="color: #2F4F4F">Documents de vérification</h4>
            <div v-if="uniqueJustificatifs && uniqueJustificatifs.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div
                v-for="(doc, index) in uniqueJustificatifs"
                :key="`doc-${doc.id}-${index}`"
                class="flex items-center justify-between p-3 border-2 border-gray-200 rounded-lg hover:border-gray-300 transition-colors"
              >
                <div class="flex items-center gap-2 flex-1">
                  <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                       :style="{
                         backgroundColor: index % 2 === 0 ? '#E3F2FD' : '#E8F5E9'
                       }">
                    <FileText :size="16" :style="{ color: index % 2 === 0 ? '#1A5FA3' : '#92B08B' }" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-medium text-xs mb-1" style="color: #2F4F4F">{{ doc.type || 'Document' }}</p>
                    <p class="text-xs text-gray-500 truncate">
                      {{ getFileName(doc.chemin_fichier) }} ({{ getFileSize(doc.chemin_fichier) }})
                    </p>
                  </div>
                </div>
                <button
                  @click="downloadJustificatif(doc)"
                  class="ml-2 p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
                  title="Télécharger"
                >
                  <Download :size="16" style="color: #5B7C99" />
                </button>
              </div>
            </div>
            <div v-else class="text-center py-6 text-gray-500">
              <FileText :size="36" class="mx-auto mb-2 text-gray-300" />
              <p class="text-xs">Aucun document justificatif disponible</p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-2 pt-3 border-t">
            <button
              @click="approveDemande(selectedDemande.id, selectedDemande.service_id)"
              class="flex-1 py-2 rounded-lg text-white transition-all hover:opacity-90 flex items-center justify-center gap-2 text-sm font-medium"
              style="background-color: #4CAF50"
            >
              <CheckCircle :size="16" />
              Accepter la demande
            </button>
            <button
              @click="rejectDemande(selectedDemande.id, selectedDemande.service_id)"
              class="flex-1 py-2 rounded-lg text-white transition-all hover:opacity-90 flex items-center justify-center gap-2 text-sm font-medium"
              style="background-color: #F44336"
            >
              <XCircle :size="16" />
              Refuser la demande
            </button>
            <button
              @click="showDetailModal = false"
              class="px-4 py-2 rounded-lg transition-all border-2 text-sm font-medium"
              style="border-color: #5B7C99; color: #5B7C99"
            >
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { User, CheckCircle, XCircle, Eye, UserCheck, X, Download, FileText } from 'lucide-vue-next'
import adminService from '@/services/adminService'
import { useNotifications } from '@/composables/useNotifications'
import { useServiceColor } from '@/composables/useServiceColor'
import { useAdminRealtimeSync } from '@/composables/useAdminRealtimeSync'

const { success, error, info, confirm: confirmDialog } = useNotifications()
const { getServiceBadgeColors } = useServiceColor()

const props = defineProps({
  loading: Boolean
})

const emit = defineEmits(['back', 'stats-updated'])

const demandes = ref([])
// Synchronisation temps réel
useAdminRealtimeSync(async () => {
  await loadDemandes({ silent: true })
}, { enabled: true })

const loading = ref(false)
const searchTerm = ref('')
const filterService = ref('tous')
const showDetailModal = ref(false)
const selectedDemande = ref(null)
const allServices = ref([])

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Notification system
// Utiliser le système de notifications global (déjà importé via useNotifications)

// Compte le nombre de demandes pour un service donné
const getServiceCount = (serviceName) => {
  if (serviceName === 'tous') return demandes.value.length
  return demandes.value.filter(d => d.service === serviceName).length
}

// Filtered Demandes
const filteredDemandes = computed(() => {
  return demandes.value.filter(demande => {
    // Filtrer uniquement les demandes avec status='demmande' pour les intervenants actifs
    const matchesStatus = demande.statut === 'demmande'
    const matchesSearch = (demande.nom || '').toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                         (demande.prenom || '').toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                         (demande.email || '').toLowerCase().includes(searchTerm.value.toLowerCase())
    const matchesService = filterService.value === 'tous' || demande.service === filterService.value
    return matchesStatus && matchesSearch && matchesService
  })
})

// Pagination computed properties
const totalPages = computed(() => {
  return Math.ceil(filteredDemandes.value.length / itemsPerPage.value)
})

const startIndex = computed(() => {
  return (currentPage.value - 1) * itemsPerPage.value
})

const endIndex = computed(() => {
  return Math.min(startIndex.value + itemsPerPage.value, filteredDemandes.value.length)
})

const paginatedDemandes = computed(() => {
  return filteredDemandes.value.slice(startIndex.value, endIndex.value)
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
watch([searchTerm, filterService], () => {
  currentPage.value = 1
})

// Methods
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

onMounted(() => {
  loadServices()
  loadDemandes()
})

onMounted(() => {
  loadServices()
  loadDemandes()

  // Initialize SSE for Admin
  initSse('/sse/stream?type=admin', {
    new_request: (data) => {
      // Show notification
      success(data.message || 'Nouvelle demande d\'activation reçue !')
      
      // Reload list
      loadDemandes()
      
      // Emit update event
      emit('stats-updated')
    }
  })
})

const loadDemandes = async (options = {}) => {
  const { silent = false } = options
  try {
    if (!silent) {
      loading.value = true
    }
    const response = await adminService.getPendingRequests()
    const newDemandes = (response.data || []).map(demande => ({
      ...demande,
      statut: demande.statut || 'demmande' // Par défaut "demmande" si non défini
    }))
    
    if (silent) {
      // Mise à jour intelligente : fusionner les données existantes avec les nouvelles
      const demandesMap = new Map(demandes.value.map(d => [d.id + '_' + d.service_id, d]))
      newDemandes.forEach(newDemande => {
        const key = newDemande.id + '_' + newDemande.service_id
        const existing = demandesMap.get(key)
        if (existing) {
          // Mettre à jour seulement si quelque chose a changé
          const hasChanges = Object.keys(newDemande).some(k => {
            const oldVal = existing[k]
            const newVal = newDemande[k]
            return JSON.stringify(oldVal) !== JSON.stringify(newVal)
          })
          if (hasChanges) {
            Object.assign(existing, newDemande)
          }
        } else {
          // Nouvelle demande : l'ajouter au début
          demandes.value.unshift(newDemande)
        }
      })
      // Retirer les demandes qui n'existent plus
      const newKeys = new Set(newDemandes.map(d => d.id + '_' + d.service_id))
      demandes.value = demandes.value.filter(d => newKeys.has(d.id + '_' + d.service_id))
    } else {
      demandes.value = newDemandes
    }
  } catch (error) {
    console.error('Erreur chargement demandes:', error)
    if (!silent) {
      const errorMessage = error.response?.data?.message || 
                          error.response?.data?.error || 
                          'Erreur lors du chargement des demandes. Veuillez réessayer.'
      demandes.value = []
      error(errorMessage)
    }
  } finally {
    if (!silent) {
      loading.value = false
    }
  }
}

// Dédupliquer les justificatifs par ID et chemin de fichier
const uniqueJustificatifs = computed(() => {
  if (!selectedDemande.value || !selectedDemande.value.justificatifs) return []
  
  const seenIds = new Set()
  const seenPaths = new Set()
  const uniqueDocs = []
  
  for (const doc of selectedDemande.value.justificatifs) {
    const docId = String(doc?.id || '')
    const filePath = String(doc?.chemin_fichier || '')
    
    // Créer une clé unique basée sur l'ID et le chemin
    const uniqueKey = docId ? `id-${docId}` : (filePath ? `path-${filePath}` : null)
    
    // Vérifier si ce document a déjà été vu (par ID ou par chemin)
    const alreadySeen = (docId && seenIds.has(docId)) || (filePath && seenPaths.has(filePath))
    
    if (!alreadySeen && uniqueKey) {
      if (docId) seenIds.add(docId)
      if (filePath) seenPaths.add(filePath)
      uniqueDocs.push(doc)
    }
  }
  
  return uniqueDocs
})

const viewDemandeDetails = (demande) => {
  selectedDemande.value = {
    ...demande,
    statut: demande.statut || 'demmande' // S'assurer que le statut est initialisé
  }
  showDetailModal.value = true
}

const approveDemande = async (intervenantId, serviceId) => {
  // Trouver la demande spécifique (intervenant + service)
  const demande = demandes.value.find(d => d.id === intervenantId && d.service_id === serviceId)
  const nomComplet = demande ? `${demande.prenom} ${demande.nom}` : 'cette demande'
  const serviceName = demande ? demande.service : 'ce service'
  
  const confirmed = await confirmDialog(`Êtes-vous sûr de vouloir approuver la demande de ${nomComplet} pour le service "${serviceName}" ?`, 'Approuver la demande')
  if (confirmed) {
    // Fermer la modal immédiatement après la confirmation
    showDetailModal.value = false
    
    // Mettre à jour le statut localement à "active"
    if (demande) {
      demande.statut = 'active'
    }
    // Mettre à jour aussi selectedDemande si c'est la demande sélectionnée
    if (selectedDemande.value && selectedDemande.value.id === intervenantId && selectedDemande.value.service_id === serviceId) {
      selectedDemande.value.statut = 'active'
    }
    
    try {
      loading.value = true
      const response = await adminService.approveRequest(intervenantId, serviceId)
      
      const message = response.data?.message || 'Demande approuvée avec succès'
      const nouveauService = response.data?.intervenant?.nouveauService
      
      if (nouveauService) {
        success(`${message}. Service activé: ${nouveauService}`)
      } else {
        success(message)
      }
      
      await loadDemandes()
      // Émettre un événement pour recharger les stats (badge de demandes en attente)
      emit('stats-updated')
    } catch (err) {
      console.error('Erreur approbation demande:', err)
      const errorMessage = err.response?.data?.message || 
                          err.response?.data?.error || 
                          'Erreur lors de l\'approbation de la demande'
      error(errorMessage)
      // En cas d'erreur, recharger les demandes pour restaurer le statut correct
      await loadDemandes()
    } finally {
      loading.value = false
    }
  }
}

const rejectDemande = async (intervenantId, serviceId) => {
  // Trouver la demande spécifique (intervenant + service)
  const demande = demandes.value.find(d => d.id === intervenantId && d.service_id === serviceId)
  const nomComplet = demande ? `${demande.prenom} ${demande.nom}` : 'cette demande'
  const serviceName = demande ? demande.service : 'ce service'
  
  const confirmed = await confirmDialog(`Êtes-vous sûr de vouloir rejeter la demande de ${nomComplet} pour le service "${serviceName}" ?\n\nL'intervenant restera dans la liste mais ce service ne sera pas accepté.`)
  if (confirmed) {
    // Fermer la modal immédiatement après la confirmation
    showDetailModal.value = false
    
    // Mettre à jour le statut localement à "desactive"
    if (demande) {
      demande.statut = 'desactive'
    }
    // Mettre à jour aussi selectedDemande si c'est la demande sélectionnée
    if (selectedDemande.value && selectedDemande.value.id === intervenantId && selectedDemande.value.service_id === serviceId) {
      selectedDemande.value.statut = 'desactive'
    }
    
    try {
      loading.value = true
      const response = await adminService.rejectRequest(intervenantId, serviceId)
      
      const message = response.data?.message || 'Demande rejetée avec succès'
      info(message)
      
      await loadDemandes()
      // Émettre un événement pour recharger les stats (badge de demandes en attente)
      emit('stats-updated')
    } catch (error) {
      console.error('Erreur rejet demande:', error)
      const errorMessage = error.response?.data?.message || 
                          error.response?.data?.error || 
                          'Erreur lors du rejet de la demande'
      error(errorMessage)
      // En cas d'erreur, recharger les demandes pour restaurer le statut correct
      await loadDemandes()
    } finally {
      loading.value = false
    }
  }
}

// Télécharger un justificatif
const downloadJustificatif = async (justificatif) => {
  try {
    // Utiliser la route API dédiée pour le téléchargement
    const response = await adminService.downloadJustificatif(justificatif.id)
    
    // Extraire le nom du fichier depuis le chemin ou utiliser le type comme nom
    const fileName = justificatif.chemin_fichier?.split('/').pop() || `${justificatif.type || 'document'}.pdf`
    
    // Créer un URL temporaire pour le blob
    const blobUrl = window.URL.createObjectURL(response.data)
    
    // Créer un élément <a> invisible pour déclencher le téléchargement
    const link = document.createElement('a')
    link.href = blobUrl
    link.download = fileName
    
    // Ajouter au DOM, cliquer, puis supprimer
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
    // Libérer l'URL du blob après un court délai
    setTimeout(() => {
      window.URL.revokeObjectURL(blobUrl)
    }, 100)
    
    success('Document téléchargé avec succès')
  } catch (error) {
    console.error('Erreur détaillée lors du téléchargement:', error)
    
    // Message d'erreur plus informatif
    let errorMessage = 'Erreur lors du téléchargement du document.'
    
    if (error.response) {
      const status = error.response.status
      if (status === 404) {
        errorMessage = 'Le fichier demandé est introuvable sur le serveur.'
      } else if (status === 403 || status === 401) {
        errorMessage = 'Vous n\'êtes pas autorisé à télécharger ce fichier.'
      } else {
        errorMessage = `Erreur ${status}: ${error.response.data?.message || error.response.statusText}`
      }
    } else if (error.message) {
      if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
        errorMessage = 'Impossible de se connecter au serveur. Vérifiez votre connexion internet.'
      } else {
        errorMessage = `Erreur: ${error.message}`
      }
    }
    
    error(errorMessage)
  }
}

// Formater la date
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' })
}

// Obtenir le nom du fichier depuis le chemin
const getFileName = (filePath) => {
  if (!filePath) return 'document.pdf'
  return filePath.split('/').pop() || 'document.pdf'
}

// Obtenir la taille du fichier (simulation - vous pouvez améliorer cela avec une vraie API)
const getFileSize = (filePath) => {
  // Pour l'instant, on retourne une taille par défaut
  // Vous pouvez améliorer cela en ajoutant un champ 'size' dans la réponse API
  return '2.1 MB'
}

// Obtenir le label du statut
const getStatutLabel = (statut) => {
  const labels = {
    'demmande': 'Demande',
    'demande': 'Demande', // Support pour compatibilité
    'active': 'Active',
    'desactive': 'Désactivée'
  }
  return labels[statut] || 'Demande'
}

// Obtenir la couleur du statut
const getStatutColor = (statut) => {
  const colors = {
    'demmande': {
      bg: '#FEF3C7',
      text: '#92400E'
    },
    'demande': { // Support pour compatibilité
      bg: '#FEF3C7',
      text: '#92400E'
    },
    'active': {
      bg: '#D1FAE5',
      text: '#065F46'
    },
    'desactive': {
      bg: '#FEE2E2',
      text: '#991B1B'
    }
  }
  return colors[statut] || colors['demmande']
}

// Synchronisation en temps réel - Recharger les demandes toutes les 3 secondes
useAdminRealtimeSync(async () => {
  await loadDemandes({ silent: true })
}, { enabled: true })


onMounted(async () => {
  await Promise.all([loadServices(), loadDemandes()])
})
</script>