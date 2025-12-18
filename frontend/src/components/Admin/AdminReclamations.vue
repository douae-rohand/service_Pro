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
        Réclamations et Signalements
      </h2>
      <div class="flex gap-1.5">
        <button
          @click="showArchived = false; loadReclamations()"
          :class="!showArchived ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
          class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
        >
          Actives
        </button>
        <button
          @click="showArchived = true; loadArchivedReclamations()"
          :class="showArchived ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
          class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
        >
          Archivées
        </button>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-xl p-2.5 shadow-sm border border-gray-100 mb-3">
      <div class="flex flex-wrap items-end gap-2.5">
        <!-- Statut Filter -->
        <div class="flex-1 min-w-[150px]">
          <label class="block text-xs font-medium text-gray-700 mb-1">Statut</label>
          <select
            v-model="filters.statut"
            @change="applyFilters"
            class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-offset-0"
            :style="{ '--tw-ring-color': '#5B7C99' }"
          >
            <option value="all">Tous les statuts</option>
            <option value="en_attente">En attente</option>
            <option value="en_cours">En cours</option>
            <option value="resolu">Résolu</option>
          </select>
        </div>

        <!-- Priorité Filter -->
        <div class="flex-1 min-w-[150px]">
          <label class="block text-xs font-medium text-gray-700 mb-1">Priorité</label>
          <select
            v-model="filters.priorite"
            @change="applyFilters"
            class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-offset-0"
            :style="{ '--tw-ring-color': '#5B7C99' }"
          >
            <option value="all">Toutes les priorités</option>
            <option value="haute">Haute</option>
            <option value="moyenne">Moyenne</option>
            <option value="basse">Basse</option>
          </select>
        </div>

        <!-- Date début Filter -->
        <div class="flex-1 min-w-[150px]">
          <label class="block text-xs font-medium text-gray-700 mb-1">Date début</label>
          <input
            v-model="filters.dateDebut"
            @change="applyFilters"
            type="date"
            class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-offset-0"
            :style="{ '--tw-ring-color': '#5B7C99' }"
          />
        </div>

        <!-- Date fin Filter -->
        <div class="flex-1 min-w-[150px]">
          <label class="block text-xs font-medium text-gray-700 mb-1">Date fin</label>
          <input
            v-model="filters.dateFin"
            @change="applyFilters"
            type="date"
            class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-offset-0"
            :style="{ '--tw-ring-color': '#5B7C99' }"
          />
        </div>

        <!-- Reset Filters Button -->
        <div class="flex items-end">
          <button
            @click="resetFilters"
            class="px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors whitespace-nowrap"
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

    <!-- Reclamations List -->
    <div v-else-if="(!showArchived && reclamations.length > 0) || (showArchived && archivedReclamations.length > 0)" class="space-y-2.5">
      <!-- Active Reclamations -->
      <template v-if="!showArchived">
      <div
        v-for="reclamation in reclamations"
        :key="reclamation.id"
        class="bg-white rounded-lg p-3 shadow-sm border border-gray-100 hover:shadow-md transition-all"
      >
        <div class="flex items-start gap-2.5 mb-2.5">
          <div
            class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
            style="background-color: #FFEBEE"
          >
            <AlertTriangle :size="16" style="color: #D32F2F" />
          </div>

          <div class="flex-1">
            <div class="flex items-center gap-1.5 mb-1 flex-wrap">
              <h3 class="text-sm font-semibold" style="color: #2F4F4F">
                Signalé il y a {{ formatTimeAgo(reclamation.date) }}
              </h3>
              <span
                class="px-1.5 py-0.5 rounded text-xs"
                :style="{
                  backgroundColor: getPriorityColor(reclamation.priorite),
                  color: '#FFFFFF'
                }"
              >
                Priorité {{ reclamation.priorite }}
              </span>
              <span
                class="px-1.5 py-0.5 rounded text-xs"
                :style="{
                  backgroundColor: getStatusColor(reclamation.statut),
                  color: '#FFFFFF'
                }"
              >
                {{ formatStatus(reclamation.statut) }}
              </span>
            </div>
            <p class="text-xs text-gray-600 mb-0.5">
              Signalé par : <span style="color: #2F4F4F">{{ reclamation.signalePar }}</span>
            </p>
            <p class="text-xs text-gray-600">
              Raison : <span style="color: #2F4F4F">{{ reclamation.raison }}</span>
            </p>
          </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-2.5 mb-2.5">
          <p class="text-xs text-gray-600 mb-1">Message du rapporteur :</p>
          <p class="text-xs italic" style="color: #2F4F4F">"{{ reclamation.message }}"</p>
        </div>

        <div class="flex items-center gap-2.5 mb-2.5">
          <div class="flex-1">
            <div class="flex items-center gap-1.5 mb-1">
              <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs"
                   :style="{ backgroundColor: '#92B08B' }">
                {{ getInitials(reclamation.signalePar) }}
              </div>
              <div>
                <p class="text-xs font-medium" style="color: #2F4F4F">{{ reclamation.signalePar }}</p>
                <p class="text-xs text-gray-500">{{ reclamation.signaleParType === 'Client' ? 'Cliente' : 'Intervenante' }}</p>
              </div>
            </div>
            <div v-if="reclamation.signaleParType === 'Intervenant' && reclamation.signaleParNote !== null" class="flex items-center gap-1 text-xs ml-8 mb-0.5">
              <Star :size="9" fill="#FEE347" style="color: #FEE347" />
              <span style="color: #2F4F4F">{{ reclamation.signaleParNote }}</span>
              <span class="text-gray-500">({{ reclamation.signaleParNbAvis }} avis)</span>
            </div>
            <button
              @click="viewProfile(reclamation.signaleParId, reclamation.signaleParType)"
              class="text-xs ml-8 hover:underline"
              :class="reclamation.signaleParType === 'Intervenant' && reclamation.signaleParNote !== null ? '' : 'mt-0.5'"
              style="color: #1A5FA3"
            >
              Voir profil complet
            </button>
          </div>

          <div class="flex-1 px-2.5 border-l" style="border-color: #E5E7EB">
            <div class="flex items-center gap-1.5 mb-1">
              <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs"
                   :style="{ backgroundColor: '#1A5FA3' }">
                {{ getInitials(reclamation.concernant) }}
              </div>
              <div>
                <p class="text-xs font-medium" style="color: #2F4F4F">{{ reclamation.concernant }}</p>
                <p class="text-xs text-gray-500">{{ reclamation.concernantType === 'Client' ? 'Cliente' : 'Intervenante' }}</p>
              </div>
            </div>
            <div v-if="reclamation.concernantType === 'Intervenant' && reclamation.concernantNote !== null" class="flex items-center gap-1 text-xs ml-8 mb-0.5">
              <Star :size="9" fill="#FEE347" style="color: #FEE347" />
              <span style="color: #2F4F4F">{{ reclamation.concernantNote }}</span>
              <span class="text-gray-500">({{ reclamation.concernantNbAvis }} avis)</span>
            </div>
            <button
              @click="viewProfile(reclamation.concernantId, reclamation.concernantType)"
              class="text-xs ml-8 hover:underline"
              :class="reclamation.concernantType === 'Intervenant' && reclamation.concernantNote !== null ? '' : 'mt-0.5'"
              style="color: #1A5FA3"
            >
              Voir profil complet
            </button>
          </div>
        </div>

        <!-- Notes Input -->
        <div class="mb-2.5">
          <textarea
            v-model="reclamation.notes"
            placeholder="Notes pour l'administration..."
            class="w-full p-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-offset-0"
            rows="2"
            :style="{ '--tw-ring-color': '#92B08B' }"
          />
        </div>

        <!-- Action Buttons ou Statut Résolu -->
        <!-- Si statut = Résolu: afficher uniquement "Résolu" -->
        <!-- Sinon: afficher les boutons d'action -->
        <div v-if="reclamation.statut === 'resolu' && !reclamation.archived" class="text-center py-1.5">
          <span
            class="px-3 py-1.5 rounded-lg text-xs font-medium"
            :style="{
              backgroundColor: '#E8F5E9',
              color: '#4CAF50'
            }"
          >
            Résolu
          </span>
        </div>
        <div v-else-if="!reclamation.archived" class="flex gap-1.5">
          <button
            @click="handleReclamation(reclamation.id, 'reply')"
            class="flex-1 px-3 py-1.5 rounded-lg flex items-center justify-center gap-1 transition-all text-xs hover:shadow-md"
            style="background-color: #B8E6AF; color: #2F4F4F"
          >
            <CheckCheck :size="12" />
            Repondre
          </button>
          <button
            @click="showMarkModal(reclamation.id)"
            class="flex-1 px-3 py-1.5 rounded-lg flex items-center justify-center gap-1 transition-all text-xs hover:shadow-md"
            style="background-color: #FFE5B4; color: #8B6914"
          >
            <Ban :size="12" />
            Marquer
          </button>
          <button
            @click="handleReclamation(reclamation.id, 'archive')"
            class="flex-1 px-3 py-1.5 rounded-lg flex items-center justify-center gap-1 transition-all text-xs hover:shadow-md"
            style="background-color: #FFD4D4; color: #8B4545"
          >
            <XCircle :size="12" />
            Archiver
          </button>
        </div>
      </div>
      </template>

      <!-- Archived Reclamations -->
      <template v-else>
        <div
          v-for="reclamation in archivedReclamations"
          :key="reclamation.id"
          class="bg-white rounded-lg p-3 shadow-sm border border-gray-100 hover:shadow-md transition-all opacity-75"
        >
          <div class="flex items-start gap-2.5 mb-2.5">
            <div
              class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
              style="background-color: #FFEBEE"
            >
              <AlertTriangle :size="16" style="color: #D32F2F" />
            </div>

            <div class="flex-1">
              <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                <h3 class="text-sm font-semibold" style="color: #2F4F4F">
                  Signalé il y a {{ formatTimeAgo(reclamation.date) }}
                </h3>
                <span
                  class="px-1.5 py-0.5 rounded text-xs"
                  :style="{
                    backgroundColor: getPriorityColor(reclamation.priorite),
                    color: '#FFFFFF'
                  }"
                >
                  Priorité {{ reclamation.priorite }}
                </span>
                <span
                  class="px-1.5 py-0.5 rounded text-xs"
                  :style="{
                    backgroundColor: getStatusColor(reclamation.statut),
                    color: '#FFFFFF'
                  }"
                >
                  {{ formatStatus(reclamation.statut) }}
                </span>
                <span class="px-1.5 py-0.5 rounded text-xs bg-gray-400 text-white">
                  Archivée
                </span>
              </div>
              <p class="text-xs text-gray-600 mb-0.5">
                Signalé par : <span style="color: #2F4F4F">{{ reclamation.signalePar }}</span>
              </p>
              <p class="text-xs text-gray-600">
                Raison : <span style="color: #2F4F4F">{{ reclamation.raison }}</span>
              </p>
            </div>
          </div>

          <div class="bg-gray-50 rounded-lg p-2.5 mb-2.5">
            <p class="text-xs text-gray-600 mb-1">Message du rapporteur :</p>
            <p class="text-xs italic" style="color: #2F4F4F">"{{ reclamation.message }}"</p>
          </div>

          <div class="flex items-center gap-2.5 mb-2.5">
            <div class="flex-1">
              <div class="flex items-center gap-1.5 mb-1">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs"
                     :style="{ backgroundColor: '#92B08B' }">
                  {{ getInitials(reclamation.signalePar) }}
                </div>
                <div>
                  <p class="text-xs font-medium" style="color: #2F4F4F">{{ reclamation.signalePar }}</p>
                  <p class="text-xs text-gray-500">{{ reclamation.signaleParType === 'Client' ? 'Cliente' : 'Intervenante' }}</p>
                </div>
              </div>
            </div>

            <div class="flex-1 px-2.5 border-l" style="border-color: #E5E7EB">
              <div class="flex items-center gap-1.5 mb-1">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs"
                     :style="{ backgroundColor: '#1A5FA3' }">
                  {{ getInitials(reclamation.concernant) }}
                </div>
                <div>
                  <p class="text-xs font-medium" style="color: #2F4F4F">{{ reclamation.concernant }}</p>
                  <p class="text-xs text-gray-500">{{ reclamation.concernantType === 'Client' ? 'Cliente' : 'Intervenante' }}</p>
                </div>
              </div>
            </div>
          </div>

          <div v-if="reclamation.notes" class="bg-gray-50 rounded-lg p-2.5 mb-2.5">
            <p class="text-xs text-gray-600 mb-1">Notes de l'administration :</p>
            <p class="text-xs" style="color: #2F4F4F">{{ reclamation.notes }}</p>
          </div>

          <!-- Unarchive Button -->
          <div class="flex justify-end mt-2.5">
            <button
              @click="handleReclamation(reclamation.id, 'unarchive')"
              class="px-3 py-1.5 rounded-lg flex items-center justify-center gap-1 transition-all text-xs hover:shadow-md"
              style="background-color: #E3F2FD; color: #1565C0"
            >
              <CheckCheck :size="12" />
              Désarchiver
            </button>
          </div>
        </div>
      </template>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading" class="text-center py-8">
      <AlertTriangle :size="48" class="mx-auto mb-3 text-gray-300" />
      <p class="text-gray-500 text-base">
        {{ showArchived ? 'Aucune réclamation archivée trouvée' : 'Aucune réclamation trouvée' }}
      </p>
      <p v-if="hasActiveFilters" class="text-gray-400 text-xs mt-2">
        Essayez de modifier vos filtres
      </p>
    </div>

    <!-- Pagination -->
    <div v-if="!loading && (reclamations.length > 0 || archivedReclamations.length > 0 || pagination.total > 0)" class="mt-6 flex items-center justify-between flex-wrap gap-4">
      <!-- Info -->
      <div class="text-sm text-gray-600">
        <span v-if="pagination.total > 0">
          Affichage de {{ pagination.from || 0 }} à {{ pagination.to || 0 }} sur {{ pagination.total }} réclamation{{ pagination.total > 1 ? 's' : '' }}
        </span>
        <span v-else-if="!showArchived && reclamations.length > 0">
          {{ reclamations.length }} réclamation{{ reclamations.length > 1 ? 's' : '' }}
        </span>
        <span v-else-if="showArchived && archivedReclamations.length > 0">
          {{ archivedReclamations.length }} réclamation{{ archivedReclamations.length > 1 ? 's' : '' }} archivée{{ archivedReclamations.length > 1 ? 's' : '' }}
        </span>
      </div>

      <!-- Contrôles de pagination -->
      <div v-if="pagination.last_page > 1" class="flex items-center gap-2">
        <!-- Bouton Précédent -->
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="px-4 py-2 rounded-lg text-sm font-medium transition-all border"
          :style="{
            borderColor: pagination.current_page === 1 ? '#D1D5DB' : '#5B7C99',
            color: pagination.current_page === 1 ? '#9CA3AF' : '#5B7C99',
            cursor: pagination.current_page === 1 ? 'not-allowed' : 'pointer',
            opacity: pagination.current_page === 1 ? 0.5 : 1
          }"
        >
          Précédent
        </button>

        <!-- Numéros de page -->
        <div class="flex items-center gap-1">
          <template v-for="(page, index) in visiblePages" :key="`page-${index}-${page}`">
            <button
              v-if="page !== '...'"
              @click="changePage(page)"
              class="w-10 h-10 rounded-lg text-sm font-medium transition-all"
              :style="{
                backgroundColor: page === pagination.current_page ? '#5B7C99' : 'transparent',
                color: page === pagination.current_page ? 'white' : '#5B7C99',
                border: `1px solid ${page === pagination.current_page ? '#5B7C99' : '#D1D5DB'}`
              }"
            >
              {{ page }}
            </button>
            <span v-else class="px-2 text-gray-500">...</span>
          </template>
        </div>

        <!-- Bouton Suivant -->
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="px-4 py-2 rounded-lg text-sm font-medium transition-all border"
          :style="{
            borderColor: pagination.current_page === pagination.last_page ? '#D1D5DB' : '#5B7C99',
            color: pagination.current_page === pagination.last_page ? '#9CA3AF' : '#5B7C99',
            cursor: pagination.current_page === pagination.last_page ? 'not-allowed' : 'pointer',
            opacity: pagination.current_page === pagination.last_page ? 0.5 : 1
          }"
        >
          Suivant
        </button>
      </div>

      <!-- Sélecteur d'éléments par page -->
      <div class="flex items-center gap-2">
        <span class="text-sm text-gray-600">Par page:</span>
        <select
          v-model="pagination.per_page"
          @change="changePerPage"
          class="px-3 py-2 rounded-lg text-sm border"
          style="border-color: #D1D5DB; color: #2F4F4F"
        >
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
        </select>
      </div>
    </div>

    <!-- Intervenant Profile Modal -->
    <AdminIntervenantProfile
      :is-open="showIntervenantProfile"
      :intervenant="selectedIntervenant"
      @close="showIntervenantProfile = false"
    />

    <!-- Client Details Modal -->
    <AdminClientDetails
      :is-open="showClientDetails"
      :client="selectedClient"
      @close="showClientDetails = false"
    />

    <!-- Mark Status Modal -->
    <div
      v-if="showMarkStatusModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="showMarkStatusModal = false"
    >
      <div class="bg-white rounded-xl p-4 max-w-md w-full mx-4">
        <h3 class="text-base font-semibold mb-3" style="color: #2F4F4F">Marquer la réclamation</h3>
        <div class="mb-3">
          <label class="block text-xs font-medium text-gray-700 mb-1.5">Statut</label>
          <select
            v-model="selectedMarkStatus"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-0"
            :style="{ '--tw-ring-color': '#5B7C99' }"
          >
            <option value="en_cours">En cours</option>
            <option value="en_attente">En attente</option>
            <option value="resolu">Résolue</option>
          </select>
        </div>
        <div class="flex gap-2 justify-end">
          <button
            @click="showMarkStatusModal = false"
            class="px-3 py-1.5 text-xs text-gray-600 hover:text-gray-800 transition-colors"
          >
            Annuler
          </button>
          <button
            @click="confirmMark"
            class="px-3 py-1.5 text-xs rounded-lg text-white transition-all"
            style="background-color: #5B7C99"
          >
            Confirmer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { AlertTriangle, Star, CheckCheck, Ban, XCircle } from 'lucide-vue-next'
import adminService from '@/services/adminService'
import AdminIntervenantProfile from './AdminIntervenantProfile.vue'
import AdminClientDetails from './AdminClientDetails.vue'
import { useNotifications } from '@/composables/useNotifications'

const emit = defineEmits(['back', 'view-profile'])
const { success, error, confirm: confirmDialog } = useNotifications()

const reclamations = ref([])
const archivedReclamations = ref([])
const loading = ref(false)
const showIntervenantProfile = ref(false)
const selectedIntervenant = ref(null)
const showClientDetails = ref(false)
const selectedClient = ref(null)
const showMarkStatusModal = ref(false)
const selectedReclamationId = ref(null)
const selectedMarkStatus = ref('en_cours')
const showArchived = ref(false)

// Filters
const filters = ref({
  statut: 'all',
  priorite: 'all',
  dateDebut: '',
  dateFin: ''
})

// Pagination
const pagination = ref({
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1,
  from: 0,
  to: 0
})

// Computed
const hasActiveFilters = computed(() => {
  return filters.value.statut !== 'all' || 
         filters.value.priorite !== 'all' || 
         filters.value.dateDebut !== '' || 
         filters.value.dateFin !== ''
})

const visiblePages = computed(() => {
  const pages = []
  const current = parseInt(pagination.value.current_page) || 1
  const last = parseInt(pagination.value.last_page) || 1
  
  // Si pas de pages ou une seule page
  if (last <= 1) {
    return []
  }
  
  if (last <= 7) {
    // Afficher toutes les pages si <= 7
    for (let i = 1; i <= last; i++) {
      pages.push(i)
    }
  } else {
    // Afficher avec ellipsis
    if (current <= 3) {
      // Début : 1, 2, 3, 4, 5, ..., last
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
      if (last > 6) {
        pages.push('...')
      }
      pages.push(last)
    } else if (current >= last - 2) {
      // Fin : 1, ..., last-4, last-3, last-2, last-1, last
      pages.push(1)
      if (last > 6) {
        pages.push('...')
      }
      for (let i = Math.max(last - 4, 2); i <= last; i++) {
        pages.push(i)
      }
    } else {
      // Milieu : 1, ..., current-1, current, current+1, ..., last
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(last)
    }
  }
  
  return pages
})

// Methods
const loadReclamations = async () => {
  try {
    loading.value = true
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      archived: 'false'
    }
    
    if (filters.value.statut !== 'all') {
      params.statut = filters.value.statut
    }
    if (filters.value.priorite !== 'all') {
      params.priorite = filters.value.priorite
    }
    if (filters.value.dateDebut) {
      params.dateDebut = filters.value.dateDebut
    }
    if (filters.value.dateFin) {
      params.dateFin = filters.value.dateFin
    }
    
    const response = await adminService.getReclamations(params)
    
    // Gérer la structure de la réponse
    if (response && response.data) {
      if (response.data.data && Array.isArray(response.data.data)) {
        // Structure avec pagination
        reclamations.value = response.data.data
        if (response.data.pagination) {
          // Conserver le per_page actuel ou utiliser celui de la réponse
          const currentPerPage = pagination.value.per_page || 5
          pagination.value = {
            current_page: parseInt(response.data.pagination.current_page) || 1,
            per_page: parseInt(response.data.pagination.per_page) || currentPerPage,
            total: parseInt(response.data.pagination.total) || 0,
            last_page: Math.max(parseInt(response.data.pagination.last_page) || 1, 1),
            from: parseInt(response.data.pagination.from) || 0,
            to: parseInt(response.data.pagination.to) || 0
          }
        } else {
          // Si pas de pagination dans la réponse, calculer depuis les données
          const total = response.data.data.length
          const perPage = pagination.value.per_page || 10
          pagination.value = {
            current_page: 1,
            per_page: perPage,
            total: total,
            last_page: Math.ceil(total / perPage) || 1,
            from: total > 0 ? 1 : 0,
            to: total
          }
        }
      } else if (Array.isArray(response.data)) {
        // Structure sans pagination (fallback)
        reclamations.value = response.data
        const total = response.data.length
        const perPage = pagination.value.per_page || 10
        pagination.value = {
          current_page: 1,
          per_page: perPage,
          total: total,
          last_page: Math.ceil(total / perPage) || 1,
          from: total > 0 ? 1 : 0,
          to: total
        }
      } else {
        reclamations.value = []
        const perPage = pagination.value.per_page || 10
        pagination.value = {
          current_page: 1,
          per_page: perPage,
          total: 0,
          last_page: 1,
          from: 0,
          to: 0
        }
      }
    } else {
      reclamations.value = []
      const perPage = pagination.value.per_page || 10
      pagination.value = {
        current_page: 1,
        per_page: perPage,
        total: 0,
        last_page: 1,
        from: 0,
        to: 0
      }
    }
  } catch (error) {
    console.error('Erreur chargement réclamations:', error)
    reclamations.value = []
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  pagination.value.current_page = 1
  loadReclamations()
}

const resetFilters = () => {
  filters.value = {
    statut: 'all',
    priorite: 'all',
    dateDebut: '',
    dateFin: ''
  }
  pagination.value.current_page = 1
  loadReclamations()
}

const changePage = (page) => {
  // Vérifier que la page est valide
  if (typeof page !== 'number' || isNaN(page)) {
    return
  }
  
  const lastPage = pagination.value.last_page || 1
  const currentPage = pagination.value.current_page || 1
  
  if (page >= 1 && page <= lastPage && page !== currentPage) {
    pagination.value.current_page = page
    if (showArchived.value) {
      loadArchivedReclamations()
    } else {
      loadReclamations()
    }
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const changePerPage = () => {
  pagination.value.current_page = 1
  if (showArchived.value) {
    loadArchivedReclamations()
  } else {
    loadReclamations()
  }
}

const loadArchivedReclamations = async () => {
  try {
    loading.value = true
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      archived: 'true'
    }
    
    if (filters.value.statut !== 'all') {
      params.statut = filters.value.statut
    }
    if (filters.value.priorite !== 'all') {
      params.priorite = filters.value.priorite
    }
    if (filters.value.dateDebut) {
      params.dateDebut = filters.value.dateDebut
    }
    if (filters.value.dateFin) {
      params.dateFin = filters.value.dateFin
    }
    
    const response = await adminService.getReclamations(params)
    
    // Gérer la structure de la réponse
    if (response && response.data) {
      if (response.data.data && Array.isArray(response.data.data)) {
        archivedReclamations.value = response.data.data
        if (response.data.pagination) {
          const currentPerPage = pagination.value.per_page || 5
          pagination.value = {
            current_page: parseInt(response.data.pagination.current_page) || 1,
            per_page: parseInt(response.data.pagination.per_page) || currentPerPage,
            total: parseInt(response.data.pagination.total) || 0,
            last_page: Math.max(parseInt(response.data.pagination.last_page) || 1, 1),
            from: parseInt(response.data.pagination.from) || 0,
            to: parseInt(response.data.pagination.to) || 0
          }
        }
      } else {
        archivedReclamations.value = []
      }
    } else {
      archivedReclamations.value = []
    }
  } catch (error) {
    console.error('Erreur chargement réclamations archivées:', error)
    archivedReclamations.value = []
  } finally {
    loading.value = false
  }
}

const showMarkModal = (id) => {
  selectedReclamationId.value = id
  selectedMarkStatus.value = 'en_cours'
  showMarkStatusModal.value = true
}

const confirmMark = async () => {
  if (!selectedReclamationId.value) return
  
  const reclamation = reclamations.value.find(r => r.id === selectedReclamationId.value)
  if (!reclamation) return

  try {
    await adminService.handleReclamation(selectedReclamationId.value, 'mark', reclamation.notes || null, selectedMarkStatus.value)
    success('Réclamation marquée avec succès')
    showMarkStatusModal.value = false
    await loadReclamations()
  } catch (error) {
    console.error('Erreur traitement réclamation:', error)
    error('Erreur lors du traitement de la réclamation')
  }
}

const handleReclamation = async (id, action) => {
  let reclamation = reclamations.value.find(r => r.id === id)
  if (!reclamation) {
    reclamation = archivedReclamations.value.find(r => r.id === id)
  }
  if (!reclamation) return

  const actionMessages = {
    'reply': 'envoyer une réponse',
    'archive': 'archiver cette réclamation',
    'unarchive': 'désarchiver cette réclamation'
  }

  const confirmed = await confirmDialog(`Êtes-vous sûr de vouloir ${actionMessages[action]} ?`, 'Confirmation')
  if (confirmed) {
    try {
      await adminService.handleReclamation(id, action, reclamation.notes || null)
      const successMessages = {
        'reply': 'réponse envoyée',
        'archive': 'archivée',
        'unarchive': 'désarchivée'
      }
      success(`Réclamation ${successMessages[action]} avec succès`)
      // Recharger en gardant les filtres et la page actuelle
      if (showArchived.value) {
        await loadArchivedReclamations()
      } else {
        await loadReclamations()
      }
    } catch (error) {
      console.error('Erreur traitement réclamation:', error)
      error('Erreur lors du traitement de la réclamation')
    }
  }
}

const formatTimeAgo = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))

  if (diffInHours < 1) {
    return 'moins d\'une heure'
  } else if (diffInHours < 24) {
    return `${diffInHours} heure${diffInHours > 1 ? 's' : ''}`
  } else {
    const diffInDays = Math.floor(diffInHours / 24)
    return `${diffInDays} jour${diffInDays > 1 ? 's' : ''}`
  }
}

const getPriorityColor = (priority) => {
  switch (priority) {
    case 'haute': return '#F44336'
    case 'moyenne': return '#FF9800'
    case 'basse': return '#4CAF50'
    default: return '#9E9E9E'
  }
}

const getStatusColor = (status) => {
  switch (status) {
    case 'en_attente': return '#FF9800'
    case 'en_cours': return '#2196F3'
    case 'resolu': return '#4CAF50'
    default: return '#9E9E9E'
  }
}

const formatStatus = (status) => {
  switch (status) {
    case 'en_attente': return 'En attente'
    case 'en_cours': return 'En cours'
    case 'resolu': return 'Résolu'
    default: return status.replace('_', ' ')
  }
}

const getInitials = (name) => {
  const parts = name.split(' ')
  if (parts.length >= 2) {
    return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase()
  }
  return parts[0].charAt(0).toUpperCase()
}

const viewProfile = async (id, type) => {
  if (!id || !type) {
    error('Informations de profil non disponibles')
    return
  }
  
  try {
    // Normaliser le type (peut être "Client", "Intervenant" ou en minuscules)
    const normalizedType = type ? type.toLowerCase() : ''
    
    if (normalizedType === 'client') {
      // Charger les détails complets du client
      const response = await adminService.getClientDetails(id)
      selectedClient.value = response.data
      showClientDetails.value = true
    } else if (normalizedType === 'intervenant') {
      // Charger les détails complets de l'intervenant via l'endpoint admin
      const response = await adminService.getIntervenant(id)
      selectedIntervenant.value = response.data
      showIntervenantProfile.value = true
    } else {
      console.error('Type de profil non reconnu:', type)
      error(`Type de profil non reconnu: ${type}. ID: ${id}`)
    }
  } catch (error) {
    console.error('Erreur chargement profil:', error)
    console.error('Détails:', { id, type, error: error.response?.data || error.message })
    error(`Erreur lors du chargement du profil: ${error.response?.data?.message || error.message}`)
  }
}

onMounted(async () => {
  await loadReclamations()
})
</script>