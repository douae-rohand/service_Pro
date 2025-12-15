<template>
  <!-- Modal Overlay -->
  <div 
    v-if="isOpen" 
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    @click.self="$emit('close')"
  >
    <!-- Modal Content -->
    <div class="bg-white rounded-xl w-full max-w-4xl max-h-[95vh] flex flex-col">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b flex-shrink-0" style="border-color: #E5E7EB">
        <h2 class="text-lg font-semibold" style="color: #1F2937">
          Détails du Client
        </h2>
        <button 
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <X :size="20" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-3 flex-1 flex flex-col min-h-0">
        <!-- Client Header -->
        <div class="flex items-start gap-2 mb-2.5">
          <!-- Profile Photo with Initials -->
          <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 text-white text-sm font-semibold"
               :style="{ backgroundColor: '#5EAD5F' }">
            {{ getInitials(client.prenom, client.nom) }}
          </div>

          <div class="flex-1">
            <!-- Name and Status -->
            <div class="flex items-center gap-1.5 mb-1">
              <h3 class="text-sm font-semibold" style="color: #1F2937">
                {{ client.prenom }} {{ client.nom }}
              </h3>
              <span 
                class="px-1.5 py-0.5 rounded text-xs font-medium text-white"
                :style="{ backgroundColor: client.statut === 'actif' ? '#5EAD5F' : '#5B7C99' }"
              >
                {{ client.statut }}
              </span>
            </div>

            <!-- Contact Info -->
            <div class="space-y-0 text-xs" style="color: #6B7280; line-height: 1.4;">
              <div class="flex items-center gap-1">
                <Mail :size="10" />
                <span class="truncate">{{ client.email }}</span>
              </div>
              <div class="flex items-center gap-1">
                <Phone :size="10" />
                <span>{{ client.telephone }}</span>
              </div>
              <div class="flex items-center gap-1">
                <MapPin :size="10" />
                <span v-if="client.adresse" class="truncate">{{ client.adresse }}</span>
                <span v-else class="text-gray-400 italic">Adresse non renseignée</span>
              </div>
              <div class="flex items-center gap-1">
                <Calendar :size="10" />
                <span>Inscrit le {{ formatDateShort(client.dateInscription) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-3 gap-2 mb-3">
          <!-- Réservations -->
          <div class="text-center p-2 rounded-lg" style="background-color: #EFF6FF">
            <p class="text-xs mb-0.5" style="color: #6B7280">Réservations</p>
            <p class="text-lg font-semibold" style="color: #1F2937">
              {{ client.reservations }}
            </p>
          </div>

          <!-- Dépenses totales -->
          <div class="text-center p-2 rounded-lg" style="background-color: #F0FDF4">
            <p class="text-xs mb-0.5" style="color: #6B7280">Dépenses totales</p>
            <p class="text-base font-semibold" style="color: #5EAD5F">
              {{ client.montantTotal }}DH
            </p>
          </div>

          <!-- Dernier service -->
          <div class="text-center p-2 rounded-lg" style="background-color: #FEF9E7">
            <p class="text-xs mb-0.5" style="color: #6B7280">Dernier service</p>
            <p class="text-xs font-semibold" style="color: #1F2937">
              {{ formatDateShort(client.dernierService) }}
            </p>
          </div>
        </div>

        <!-- Feedbacks Section -->
        <div v-if="client.feedbacks && client.feedbacks.length > 0" class="mb-4 flex-1 flex flex-col min-h-0">
          <h4 class="text-xs font-semibold mb-2.5 flex-shrink-0" style="color: #1F2937">
            Feedbacks du client ({{ client.feedbacks.length }})
          </h4>

          <!-- Zone scrollable pour les feedbacks uniquement -->
          <div 
            class="space-y-2.5 flex-1 overflow-y-auto pr-2 feedbacks-scrollable"
            style="max-height: 500px;"
          >
            <div 
              v-for="feedback in paginatedFeedbacks" 
              :key="feedback.id"
              class="p-3 rounded-lg border flex-shrink-0"
              style="border-color: #E5E7EB; background-color: #FAFAFA"
            >
              <!-- Feedback Header -->
              <div class="flex items-start justify-between mb-1.5">
                <div class="flex-1">
                  <div class="flex items-center gap-1.5 mb-1">
                    <span class="text-xs font-medium" style="color: #1F2937">
                      Pour {{ feedback.intervenantNom }}
                    </span>
                    <span 
                      class="px-1.5 py-0.5 rounded text-xs font-medium text-white"
                      :style="{ backgroundColor: feedback.service === 'Jardinage' ? '#5EAD5F' : '#5B7C99' }"
                    >
                      {{ feedback.service }}
                    </span>
                  </div>
                  <p class="text-xs" style="color: #6B7280">
                    {{ formatDateFull(feedback.date) }}
                  </p>
                </div>

                <!-- Star Rating -->
                <div class="flex items-center gap-0.5">
                  <Star 
                    v-for="i in 5" 
                    :key="i"
                    :size="12"
                    :fill="i <= feedback.note ? '#FFC107' : 'none'"
                    :stroke="i <= feedback.note ? '#FFC107' : '#D1D5DB'"
                  />
                </div>
              </div>

              <!-- Comment -->
              <p class="text-xs italic" style="color: #4B5563">
                "{{ feedback.commentaire }}"
              </p>
            </div>
          </div>

          <!-- Pagination Controls (hors de la zone scrollable) -->
          <div 
            v-if="totalPages > 1"
            class="flex items-center justify-between mt-3 pt-3 border-t flex-shrink-0"
            style="border-color: #E5E7EB"
          >
            <button
              @click="currentPage = currentPage - 1"
              :disabled="currentPage === 1"
              class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              :style="{
                backgroundColor: currentPage === 1 ? '#E5E7EB' : '#5B7C99',
                color: currentPage === 1 ? '#9CA3AF' : 'white'
              }"
            >
              ← Précédent
            </button>
            
            <div class="flex items-center gap-2">
              <span class="text-xs" style="color: #6B7280">
                Page {{ currentPage }} / {{ totalPages }}
              </span>
              <div class="flex gap-1">
                <div
                  v-for="page in totalPages"
                  :key="page"
                  @click="currentPage = page"
                  class="w-2 h-2 rounded-full cursor-pointer transition-all"
                  :style="{
                    backgroundColor: currentPage === page ? '#5B7C99' : '#D1D5DB',
                    width: currentPage === page ? '24px' : '8px'
                  }"
                ></div>
              </div>
            </div>
            
            <button
              @click="currentPage = currentPage + 1"
              :disabled="currentPage === totalPages"
              class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              :style="{
                backgroundColor: currentPage === totalPages ? '#E5E7EB' : '#5B7C99',
                color: currentPage === totalPages ? '#9CA3AF' : 'white'
              }"
            >
              Suivant →
            </button>
          </div>
        </div>

        <!-- No Feedbacks -->
        <div v-else class="mb-4 p-3 rounded-lg text-center" style="background-color: #F9FAFB">
          <p class="text-xs" style="color: #6B7280">Aucun feedback pour le moment</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2 flex-shrink-0">
          <button
            v-if="client.statut === 'actif'"
            @click="$emit('suspend', client)"
            class="flex-1 py-2 rounded-lg text-xs font-medium text-white transition-all hover:opacity-90 flex items-center justify-center gap-1.5"
            style="background-color: #EF4444"
          >
            <AlertCircle :size="14" />
            Suspendre le compte
          </button>
          
          <button
            v-else
            @click="$emit('activate', client)"
            class="flex-1 py-2 rounded-lg text-xs font-medium text-white transition-all hover:opacity-90 flex items-center justify-center gap-1.5"
            style="background-color: #5EAD5F"
          >
            <CheckCircle :size="14" />
            Activer le compte
          </button>

          <button
            @click="$emit('close')"
            class="flex-1 py-2 rounded-lg text-xs font-medium text-white transition-all hover:opacity-90"
            style="background-color: #5B7C99"
          >
            Fermer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { X, Mail, Phone, MapPin, Calendar, Star, AlertCircle, CheckCircle } from 'lucide-vue-next'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  client: {
    type: Object,
    required: true,
    default: () => ({})
  }
})

defineEmits(['close', 'suspend', 'activate'])

// Pagination pour les feedbacks
const currentPage = ref(1)
const itemsPerPage = 4

// Réinitialiser la page quand le client change ou quand le modal s'ouvre
watch([() => props.client?.id, () => props.isOpen], () => {
  currentPage.value = 1
})

// Calculer les feedbacks paginés
const paginatedFeedbacks = computed(() => {
  if (!props.client.feedbacks || props.client.feedbacks.length === 0) return []
  
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return props.client.feedbacks.slice(start, end)
})

// Calculer le nombre total de pages
const totalPages = computed(() => {
  if (!props.client.feedbacks || props.client.feedbacks.length === 0) return 0
  return Math.ceil(props.client.feedbacks.length / itemsPerPage)
})

const getInitials = (prenom, nom) => {
  const p = prenom ? prenom.charAt(0).toUpperCase() : ''
  const n = nom ? nom.charAt(0).toUpperCase() : ''
  return p + n
}

const formatDateFull = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

const formatDateShort = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', { 
    year: 'numeric', 
    month: '2-digit', 
    day: '2-digit' 
  })
}
</script>

<style scoped>
/* Scrollbar personnalisée pour la section Feedbacks uniquement */
.feedbacks-scrollable::-webkit-scrollbar {
  width: 8px;
}

.feedbacks-scrollable::-webkit-scrollbar-track {
  background: #F9FAFB;
  border-radius: 4px;
}

.feedbacks-scrollable::-webkit-scrollbar-thumb {
  background: #5B7C99;
  border-radius: 4px;
}

.feedbacks-scrollable::-webkit-scrollbar-thumb:hover {
  background: #4A6B8A;
}

/* Support pour Firefox */
.feedbacks-scrollable {
  scrollbar-width: thin;
  scrollbar-color: #5B7C99 #F9FAFB;
}
</style>
