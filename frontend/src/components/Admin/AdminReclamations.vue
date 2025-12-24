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
          @click="changeTab('actives')"
          :class="activeTab === 'actives' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
          class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
        >
          Actives
        </button>
        <button
          @click="changeTab('historique')"
          :class="activeTab === 'historique' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
          class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
        >
          Historique
        </button>
        <button
          @click="changeTab('archivees')"
          :class="activeTab === 'archivees' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
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
    <div v-else-if="currentReclamations.length > 0" class="space-y-2.5">
      <!-- Reclamations Items -->
      <div
        v-for="reclamation in currentReclamations"
        :key="reclamation.id"
        class="bg-white rounded-lg p-3 shadow-sm border border-gray-100 hover:shadow-md transition-all"
        :class="{ 'opacity-75': activeTab === 'archivees' }"
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

        <!-- Linked Intervention -->
        <div v-if="reclamation.intervention" class="bg-blue-50/50 rounded-lg p-2.5 mb-2.5 border border-blue-100">
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
              <div class="bg-blue-100 p-1.5 rounded-lg">
                <Calendar :size="14" class="text-blue-600" />
              </div>
              <div>
                <p class="text-[10px] text-blue-600 font-medium uppercase tracking-wider">Intervention liée</p>
                <p class="text-xs font-semibold text-blue-900">{{ reclamation.intervention.service }}</p>
                <p class="text-[10px] text-blue-700/70">{{ formatDateShort(reclamation.intervention.date) }}</p>
              </div>
            </div>
            <button 
              @click="viewInterventionDetails(reclamation.intervention.id)"
              class="px-2 py-1 text-[10px] bg-white border border-blue-200 text-blue-600 rounded-md hover:bg-blue-50 transition-colors font-medium shadow-sm"
            >
              Détails intervention
            </button>
          </div>

          <!-- Evaluations Section -->
          <div v-if="reclamation.intervention.evaluations" class="mt-2.5 space-y-2">
            <!-- Client Evaluations -->
            <div v-if="reclamation.intervention.evaluations.client && reclamation.intervention.evaluations.client.count > 0" 
                 class="bg-white rounded-lg p-2 border border-gray-200">
              <div class="flex items-center justify-between mb-1.5">
                <p class="text-[10px] font-semibold text-gray-700 uppercase">Évaluation Client</p>
                <div v-if="reclamation.intervention.evaluations.client.average_note" class="flex items-center gap-1">
                  <Star :size="10" fill="#FEE347" style="color: #FEE347" />
                  <span class="text-xs font-semibold text-gray-800">{{ reclamation.intervention.evaluations.client.average_note }}/5</span>
                </div>
              </div>
              
              <!-- Evaluation Details -->
              <div v-if="reclamation.intervention.evaluations.client.details && reclamation.intervention.evaluations.client.details.length > 0" 
                   class="space-y-1 mb-1.5">
                <div v-for="detail in reclamation.intervention.evaluations.client.details" 
                     :key="detail.id" 
                     class="flex items-center justify-between text-[10px]">
                  <span class="text-gray-600">{{ detail.critere }}</span>
                  <div class="flex items-center gap-0.5">
                    <Star v-for="n in 5" :key="n" :size="8" 
                          :fill="n <= detail.note ? '#FEE347' : 'none'"
                          style="color: #FEE347" />
                    <span class="ml-0.5 text-gray-700 font-medium">{{ detail.note }}/5</span>
                  </div>
                </div>
              </div>
              
              <!-- Client Comment -->
              <div v-if="reclamation.intervention.evaluations.client.comment" 
                   class="bg-red-50 rounded p-1.5 border border-red-100">
                <p class="text-[10px] text-red-700 font-medium mb-0.5">Commentaire client:</p>
                <p class="text-[10px] text-red-800 italic">"{{ reclamation.intervention.evaluations.client.comment }}"</p>
              </div>
            </div>

            <!-- Intervenant Evaluations -->
            <div v-if="reclamation.intervention.evaluations.intervenant && reclamation.intervention.evaluations.intervenant.count > 0" 
                 class="bg-white rounded-lg p-2 border border-gray-200">
              <div class="flex items-center justify-between mb-1.5">
                <p class="text-[10px] font-semibold text-gray-700 uppercase">Évaluation Intervenant</p>
                <div v-if="reclamation.intervention.evaluations.intervenant.average_note" class="flex items-center gap-1">
                  <Star :size="10" fill="#FEE347" style="color: #FEE347" />
                  <span class="text-xs font-semibold text-gray-800">{{ reclamation.intervention.evaluations.intervenant.average_note }}/5</span>
                </div>
              </div>
              
              <!-- Evaluation Details -->
              <div v-if="reclamation.intervention.evaluations.intervenant.details && reclamation.intervention.evaluations.intervenant.details.length > 0" 
                   class="space-y-1 mb-1.5">
                <div v-for="detail in reclamation.intervention.evaluations.intervenant.details" 
                     :key="detail.id" 
                     class="flex items-center justify-between text-[10px]">
                  <span class="text-gray-600">{{ detail.critere }}</span>
                  <div class="flex items-center gap-0.5">
                    <Star v-for="n in 5" :key="n" :size="8" 
                          :fill="n <= detail.note ? '#FEE347' : 'none'"
                          style="color: #FEE347" />
                    <span class="ml-0.5 text-gray-700 font-medium">{{ detail.note }}/5</span>
                  </div>
                </div>
              </div>
              
              <!-- Intervenant Comment -->
              <div v-if="reclamation.intervention.evaluations.intervenant.comment" 
                   class="bg-orange-50 rounded p-1.5 border border-orange-100">
                <p class="text-[10px] text-orange-700 font-medium mb-0.5">Commentaire intervenant:</p>
                <p class="text-[10px] text-orange-800 italic">"{{ reclamation.intervention.evaluations.intervenant.comment }}"</p>
              </div>
            </div>

            <!-- No Evaluations Message -->
            <div v-if="(!reclamation.intervention.evaluations.client || reclamation.intervention.evaluations.client.count === 0) && 
                       (!reclamation.intervention.evaluations.intervenant || reclamation.intervention.evaluations.intervenant.count === 0)" 
                 class="text-[10px] text-gray-500 italic text-center py-1">
              Aucune évaluation disponible pour cette intervention
            </div>
          </div>
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
        <!-- Si statut = Résolu: afficher uniquement "Résolu" (sauf si archivé) -->
        <!-- Sinon: afficher les boutons d'action -->
        <div v-if="reclamation.statut === 'resolu' && activeTab === 'historique'" class="flex gap-1.5">
          <div class="flex-1 text-center py-1.5">
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
          <button
            @click="handleReclamation(reclamation.id, 'archive')"
            class="px-3 py-1.5 rounded-lg flex items-center justify-center gap-1 transition-all text-xs hover:shadow-md"
            style="background-color: #FFD4D4; color: #8B4545"
          >
            <XCircle :size="12" />
            Archiver
          </button>
        </div>
        <div v-else-if="activeTab === 'actives'" class="flex gap-1.5">
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

        <!-- Unarchive Button for Archived Tab -->
        <div v-if="activeTab === 'archivees'" class="flex justify-end mt-2.5">
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
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading" class="text-center py-8">
      <AlertTriangle :size="48" class="mx-auto mb-3 text-gray-300" />
      <p class="text-gray-500 text-base">
        {{ emptyStateMessage }}
      </p>
      <p v-if="hasActiveFilters" class="text-gray-400 text-xs mt-2">
        Essayez de modifier vos filtres
      </p>
    </div>

    <!-- Pagination -->
    <div v-if="!loading && (currentReclamations.length > 0 || pagination.total > 0)" class="mt-6 flex items-center justify-between flex-wrap gap-4">
      <!-- Info -->
      <div class="text-sm text-gray-600">
        <span v-if="pagination.total > 0">
          Affichage de {{ pagination.from || 0 }} à {{ pagination.to || 0 }} sur {{ pagination.total }} réclamation{{ pagination.total > 1 ? 's' : '' }}
        </span>
        <span v-else-if="currentReclamations.length > 0">
          {{ currentReclamations.length }} réclamation{{ currentReclamations.length > 1 ? 's' : '' }}
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
import { ref, onMounted, computed, watch } from 'vue'
import { AlertTriangle, Star, CheckCheck, Ban, XCircle, Calendar } from 'lucide-vue-next'
import adminService from '@/services/adminService'
import AdminIntervenantProfile from './AdminIntervenantProfile.vue'
import AdminClientDetails from './AdminClientDetails.vue'
import { useNotifications } from '@/composables/useNotifications'
import { useAdminRealtimeSync } from '@/composables/useAdminRealtimeSync'

const emit = defineEmits(['back', 'view-profile'])
const { success, error, confirm: confirmDialog } = useNotifications()

const reclamations = ref([])
const activeTab = ref('actives')

const loading = ref(false)
const showIntervenantProfile = ref(false)
const selectedIntervenant = ref(null)
const showClientDetails = ref(false)
const selectedClient = ref(null)
const showMarkStatusModal = ref(false)
const selectedReclamationId = ref(null)
const selectedMarkStatus = ref('en_cours')

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
  return (activeTab.value === 'actives' && filters.value.statut !== 'all') || 
         filters.value.priorite !== 'all' || 
         filters.value.dateDebut !== '' || 
         filters.value.dateFin !== ''
})

const currentReclamations = computed(() => reclamations.value)

const emptyStateMessage = computed(() => {
  switch (activeTab.value) {
    case 'historique': return 'Aucune réclamation résolue trouvée'
    case 'archivees': return 'Aucune réclamation archivée trouvée'
    default: return 'Aucune réclamation active trouvée'
  }
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
const loadReclamations = async (options = {}) => {
  const { silent = false } = options
  try {
    if (!silent) {
      loading.value = true
    }
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page
    }
    
    // Configurer les paramètres selon l'onglet
    if (activeTab.value === 'actives') {
      params.archived = 'false'
      params.exclude_status = 'resolu'
      if (filters.value.statut !== 'all') {
        params.statut = filters.value.statut
      }
    } else if (activeTab.value === 'historique') {
      params.archived = 'false'
      params.statut = 'resolu'
    } else if (activeTab.value === 'archivees') {
      params.archived = 'true'
      if (filters.value.statut !== 'all') {
        params.statut = filters.value.statut
      }
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
    
    if (response && response.data) {
      const data = response.data.data || response.data
      const newReclamations = Array.isArray(data) ? data : []
      
      if (silent) {
        // Mise à jour intelligente
        const reclamationsMap = new Map(reclamations.value.map(r => [r.id, r]))
        newReclamations.forEach(newRec => {
          const existing = reclamationsMap.get(newRec.id)
          if (existing) {
            const hasChanges = JSON.stringify(existing) !== JSON.stringify(newRec)
            if (hasChanges) {
              Object.assign(existing, newRec)
            }
          } else {
            // Ajouter à la fin ou au début selon le tri (ici newest first unshift)
            reclamations.value.unshift(newRec)
          }
        })
        const newIds = new Set(newReclamations.map(r => r.id))
        reclamations.value = reclamations.value.filter(r => newIds.has(r.id))
      } else {
        reclamations.value = newReclamations
      }

      // Pagination
      if (response.data.pagination) {
        pagination.value = {
          current_page: parseInt(response.data.pagination.current_page) || 1,
          per_page: parseInt(response.data.pagination.per_page) || pagination.value.per_page,
          total: parseInt(response.data.pagination.total) || 0,
          last_page: Math.max(parseInt(response.data.pagination.last_page) || 1, 1),
          from: parseInt(response.data.pagination.from) || 0,
          to: parseInt(response.data.pagination.to) || 0
        }
      } else {
        const total = newReclamations.length
        pagination.value = {
          current_page: 1,
          per_page: pagination.value.per_page,
          total: total,
          last_page: 1,
          from: total > 0 ? 1 : 0,
          to: total
        }
      }
    }
  } catch (error) {
    console.error('Erreur chargement réclamations:', error)
    if (!silent) reclamations.value = []
  } finally {
    if (!silent) loading.value = false
  }
}

const changeTab = (tab) => {
  activeTab.value = tab
  pagination.value.current_page = 1
  // Reset statut filter if switching to/from Historique
  if (tab === 'historique') {
    filters.value.statut = 'all'
  }
  loadReclamations()
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
    loadReclamations()
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const changePerPage = () => {
  pagination.value.current_page = 1
  loadReclamations()
}

// Retirer loadArchivedReclamations car fusionné avec loadReclamations

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
  const reclamation = reclamations.value.find(r => r.id === id)
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
      await loadReclamations()
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
  } catch (err) {
    console.error('Erreur chargement profil:', err)
    error(`Erreur lors du chargement du profil: ${err.response?.data?.message || err.message}`)
  }
}

const formatDateShort = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const viewInterventionDetails = (id) => {
  // Optionnel: implémenter une vue détaillée ou rediriger
  // Pour l'instant, disons qu'on affiche un message ou qu'on utilise un modal existant si dispo
  alert(`Détails de l'intervention #${id}`)
}

// Synchronisation en temps réel (Polling toutes les 3s)
useAdminRealtimeSync(async () => {
    await loadReclamations({ silent: true })
}, { enabled: true })

// Lifecycle
onMounted(async () => {
  await loadReclamations()
})
</script>