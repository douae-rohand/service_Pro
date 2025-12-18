<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Évaluer le client</h2>
        <button @click="closeModal" class="close-btn">
          <X :size="20" />
        </button>
      </div>

      <div class="modal-body">
        <div class="client-info">
          <img :src="reservation.clientImage" :alt="reservation.clientName" class="client-avatar" />
          <div>
            <h3>{{ reservation.clientName }}</h3>
            <p class="task-name">{{ reservation.task }}</p>
          </div>
        </div>

        <form @submit.prevent="submitRating">
          <div class="criteria-list">
            <div v-for="criteria in clientCriteria" :key="criteria.id" class="criteria-item">
              <label class="criteria-label">
                {{ criteria.nom_critaire }}
              </label>
              <div class="rating-stars">
                <button
                  v-for="star in 5"
                  :key="star"
                  type="button"
                  @click="setRating(criteria.id, star)"
                  class="star-btn"
                  :class="{ 'star-active': getRating(criteria.id) >= star }"
                >
                  <Star :size="20" />
                </button>
              </div>
            </div>
          </div>

          <div v-if="error" class="error-message">
            {{ error }}
          </div>

          <div class="modal-actions">
            <button type="button" @click="closeModal" class="cancel-btn">
              Annuler
            </button>
            <button type="submit" :disabled="loading || !isFormValid" class="submit-btn">
              <span v-if="loading">Envoi en cours...</span>
              <span v-else-if="hasExistingRatings">Mettre à jour l'évaluation</span>
              <span v-else>Envoyer l'évaluation</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { X, Star } from 'lucide-vue-next'
import evaluationService from '@/services/evaluationService'

const props = defineProps({
  show: Boolean,
  reservation: Object
})

const emit = defineEmits(['close', 'rating-submitted'])

const clientCriteria = ref([])
const ratings = ref({})
const loading = ref(false)
const error = ref(null)
const hasExistingRatings = ref(false)
const votingStatus = ref(null)
const canViewOnly = ref(false)
const daysRemaining = ref(0)

const isFormValid = computed(() => {
  return clientCriteria.value && clientCriteria.value.length > 0 && 
         clientCriteria.value.every(criteria => ratings.value[criteria.id] > 0)
})

const fetchClientCriteria = async () => {
  loading.value = true
  error.value = null
  try {
    const criteria = await evaluationService.getClientCriteria()
    // Remove duplicates based on ID and ensure we have unique criteria
    const uniqueCriteria = Array.isArray(criteria) ? 
      criteria.filter((item, index, self) => 
        index === self.findIndex((t) => t.id === item.id)
      ) : []
    
    clientCriteria.value = uniqueCriteria
    // Initialize ratings with 0 for each criteria
    ratings.value = {}
    clientCriteria.value.forEach(criteria => {
      ratings.value[criteria.id] = 0
    })

    // Load existing ratings if any
    if (props.reservation) {
      const existingRatings = await evaluationService.getClientEvaluations(props.reservation.id)
      hasExistingRatings.value = existingRatings.length > 0
      existingRatings.forEach(rating => {
        ratings.value[rating.critaire_id] = rating.note
      })
    }
  } catch (err) {
    error.value = 'Erreur lors du chargement des critères d\'évaluation'
    clientCriteria.value = []
    ratings.value = {}
    console.error(err)
  } finally {
    loading.value = false
  }
}

const setRating = (criteriaId, rating) => {
  ratings.value[criteriaId] = rating
}

const getRating = (criteriaId) => {
  return ratings.value[criteriaId] || 0
}

const submitRating = async () => {
  if (!isFormValid.value) return

  loading.value = true
  error.value = null

  try {
    const evaluations = Object.entries(ratings.value).map(([criteriaId, note]) => ({
      critaire_id: parseInt(criteriaId),
      note: note
    }))

    await evaluationService.storeClientEvaluation(props.reservation.id, evaluations)
    
    emit('rating-submitted')
    closeModal()
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors de l\'envoi de l\'évaluation'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const closeModal = () => {
  emit('close')
}

watch(() => props.show, (newShow) => {
  if (newShow) {
    fetchClientCriteria()
    ratings.value = {}
  }
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: var(--spacing-4);
}

.modal-content {
  background: var(--color-white);
  border-radius: var(--radius-xl);
  max-width: 32rem;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: var(--shadow-lg);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-6);
  border-bottom: 1px solid var(--color-gray-200);
}

.modal-header h2 {
  margin: 0;
  color: var(--color-dark);
  font-size: 1.5rem;
}

.close-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border: none;
  background: var(--color-gray-100);
  border-radius: var(--radius-lg);
  color: var(--color-gray-600);
  cursor: pointer;
  transition: all 0.2s;
}

.close-btn:hover {
  background: var(--color-gray-200);
  color: var(--color-gray-800);
}

.modal-body {
  padding: var(--spacing-6);
}

.client-info {
  display: flex;
  align-items: center;
  gap: var(--spacing-4);
  margin-bottom: var(--spacing-6);
  padding: var(--spacing-4);
  background: var(--color-gray-50);
  border-radius: var(--radius-lg);
}

.client-avatar {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  object-fit: cover;
}

.client-info h3 {
  margin: 0 0 var(--spacing-1) 0;
  color: var(--color-dark);
  font-size: 1.125rem;
}

.task-name {
  color: var(--color-gray-600);
  margin: 0;
  font-size: 0.875rem;
}

.criteria-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-4);
  margin-bottom: var(--spacing-6);
}

.criteria-item {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-2);
}

.criteria-label {
  font-weight: 500;
  color: var(--color-gray-700);
  font-size: 0.875rem;
}

.rating-stars {
  display: flex;
  gap: var(--spacing-1);
}

.star-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border: 1px solid var(--color-gray-300);
  background: var(--color-white);
  border-radius: var(--radius-lg);
  color: var(--color-gray-400);
  cursor: pointer;
  transition: all 0.2s;
}

.star-btn:hover {
  border-color: var(--color-orange);
  color: var(--color-orange);
}

.star-btn.star-active {
  background: var(--color-orange);
  border-color: var(--color-orange);
  color: var(--color-white);
}

.error-message {
  padding: var(--spacing-3);
  background-color: #FEE2E2;
  color: #DC2626;
  border-radius: var(--radius-lg);
  margin-bottom: var(--spacing-4);
  font-size: 0.875rem;
}

.modal-actions {
  display: flex;
  gap: var(--spacing-3);
  justify-content: flex-end;
}

.cancel-btn {
  padding: var(--spacing-3) var(--spacing-6);
  border: 1px solid var(--color-gray-300);
  background: var(--color-white);
  color: var(--color-gray-700);
  border-radius: var(--radius-lg);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.cancel-btn:hover {
  background: var(--color-gray-50);
}

.submit-btn {
  padding: var(--spacing-3) var(--spacing-6);
  background: var(--color-orange);
  color: var(--color-white);
  border: none;
  border-radius: var(--radius-lg);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.submit-btn:hover:not(:disabled) {
  background: #DC2626;
}

.submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .modal-content {
    margin: var(--spacing-4);
    max-height: 95vh;
  }
  
  .modal-actions {
    flex-direction: column;
  }
  
  .cancel-btn,
  .submit-btn {
    width: 100%;
  }
}
</style>
