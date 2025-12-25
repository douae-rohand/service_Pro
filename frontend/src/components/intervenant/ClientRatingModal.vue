<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-body">
        <h2 class="modal-title">Votre évaluation</h2>
        
        <div v-if="loading" class="client-info skeleton-item">
          <div class="skeleton-box client-avatar"></div>
          <div>
            <div class="skeleton-text" style="width: 150px; height: 20px; margin-bottom: 8px;"></div>
            <div class="skeleton-text" style="width: 100px; height: 16px;"></div>
          </div>
        </div>

        <div v-else class="client-info">
          <img :src="reservation.clientImage" :alt="reservation.clientName" class="client-avatar" />
          <div>
            <h3>{{ reservation.clientName }}</h3>
            <p class="task-name">{{ reservation.task }}</p>
          </div>
        </div>

        <div v-if="loading" class="loading-message">
          <div class="spinner"></div>
          <p>Chargement des critères d'évaluation...</p>
        </div>

        <form v-else @submit.prevent="submitRating">
          <fieldset :disabled="hasExistingRatings" class="rating-fieldset">
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
                  @click="!hasExistingRatings && setRating(criteria.id, star)"
                  class="star-btn"
                  :class="{ 
                    'star-active': getRating(criteria.id) >= star,
                    'star-readonly': hasExistingRatings
                  }"
                  :disabled="hasExistingRatings"
                >
                  <Star :size="32" :fill="getRating(criteria.id) >= star ? 'currentColor' : 'none'" />
                </button>
              </div>
            </div>
            </div>
          </fieldset>

          <div class="comment-section">
            <label class="comment-label">
              Commentaire personnel
              <span v-if="averageRating < 3 && averageRating > 0" class="required-badge">Obligatoire</span>
              <span v-else class="optional-badge">Optionnel</span>
            </label>
            <textarea 
              v-model="comment"
              :disabled="hasExistingRatings"
              class="comment-textarea"
              :class="{ 'comment-readonly': hasExistingRatings, 'comment-required': averageRating < 3 && averageRating > 0 }"
              placeholder="Partagez votre expérience avec ce client..."
              rows="4"
            ></textarea>
            <p v-if="averageRating < 3 && averageRating > 0 && !hasExistingRatings" class="comment-hint">
              💡 Une note moyenne inférieure à 3 nécessite un commentaire explicatif
            </p>
          </div>

          <div v-if="validationError" class="error-message">
            {{ validationError }}
          </div>
          
          <div v-if="error" class="error-message">
            {{ error }}
          </div>

          <div class="modal-actions">
            <button 
              v-if="!hasExistingRatings" 
              type="submit" 
              :disabled="loading || !isFormValid" 
              class="submit-btn"
            >
              <span v-if="loading">Envoi en cours...</span>
              <span v-else>Envoyer l'Évaluation</span>
            </button>
            <button 
              type="button" 
              @click="closeModal" 
              class="cancel-btn"
              :class="{ 'cancel-btn-full': hasExistingRatings }"
            >
              {{ hasExistingRatings ? 'Fermer' : 'Annuler' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Star } from 'lucide-vue-next'
import evaluationService from '@/services/evaluationService'

const props = defineProps({
  show: Boolean,
  reservation: Object
})

const emit = defineEmits(['close', 'rating-submitted'])

const clientCriteria = ref([])
const ratings = ref({})
const comment = ref('')
const loading = ref(true)
const error = ref(null)
const hasExistingRatings = ref(false)

const validationError = ref('')

const averageRating = computed(() => {
  const ratingValues = Object.values(ratings.value).filter(r => r > 0)
  if (ratingValues.length === 0) return 0
  return ratingValues.reduce((sum, rating) => sum + rating, 0) / ratingValues.length
})

const isFormValid = computed(() => {
  // All criteria must be rated
  const allRated = clientCriteria.value && clientCriteria.value.length > 0 && 
         clientCriteria.value.every(criteria => ratings.value[criteria.id] > 0)
  
  if (!allRated) return false
  
  // If average rating is below 3, comment is required
  if (averageRating.value < 3) {
    return comment.value.trim().length > 0
  }
  
  return true
})

const fetchClientCriteria = async () => {
  loading.value = true
  error.value = null
  try {
    const criteria = await evaluationService.getClientCriteria()
    const uniqueCriteria = Array.isArray(criteria) ? 
      criteria.filter((item, index, self) => 
        index === self.findIndex((t) => t.id === item.id)
      ) : []
    
    clientCriteria.value = uniqueCriteria
    ratings.value = {}
    clientCriteria.value.forEach(criteria => {
      ratings.value[criteria.id] = 0
    })

    // Load existing ratings if any
    if (props.reservation) {
      const response = await evaluationService.getClientEvaluations(props.reservation.id)
      const existingRatings = response.evaluations || []
      hasExistingRatings.value = existingRatings.length > 0
      
      existingRatings.forEach(rating => {
        ratings.value[rating.critaire_id] = rating.note
      })
      
      // Load comment if exists
      if (response.comment) {
        comment.value = response.comment
      }
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
  // Prevent submission if viewing existing evaluation
  if (hasExistingRatings.value) {
    console.warn('Cannot submit - already evaluated')
    return
  }
  
  validationError.value = ''
  
  // Validate all criteria are rated
  const unratedCriteria = clientCriteria.value.filter(c => !ratings.value[c.id] || ratings.value[c.id] === 0)
  if (unratedCriteria.length > 0) {
    validationError.value = 'Veuillez noter tous les critères avant de soumettre'
    return
  }
  
  // Validate comment if average rating is below 3
  if (averageRating.value < 3 && comment.value.trim().length === 0) {
    validationError.value = 'Un commentaire est obligatoire pour une note moyenne inférieure à 3'
    return
  }

  loading.value = true
  error.value = null

  try {
    const evaluations = Object.entries(ratings.value)
      .filter(([_, note]) => note > 0)
      .map(([criteriaId, note]) => ({
        critaire_id: parseInt(criteriaId),
        note: note
      }))
    
    await evaluationService.storeClientEvaluation(props.reservation.id, evaluations, comment.value)
    
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
    comment.value = ''
    validationError.value = ''
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
  background: white;
  border-radius: var(--radius-xl);
  max-width: 42rem;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  border: 1px solid #E5E7EB;
}

.modal-body {
  padding: var(--spacing-8);
}

.rating-fieldset {
  border: none;
  padding: 0;
  margin: 0;
  min-width: 0;
}

.rating-fieldset:disabled {
  opacity: 1;
}

.modal-title {
  margin: 0 0 var(--spacing-6) 0;
  color: #E8793F;
  font-size: 1.75rem;
  font-weight: 600;
}

.client-info {
  display: flex;
  align-items: center;
  gap: var(--spacing-4);
  margin-bottom: var(--spacing-8);
  padding: var(--spacing-4);
  background: rgba(255, 255, 255, 0.5);
  border-radius: var(--radius-lg);
  border-left: 4px solid #E8793F;
}

.client-avatar {
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #E8793F;
}

.client-info h3 {
  margin: 0 0 var(--spacing-1) 0;
  color: var(--color-dark);
  font-size: 1.125rem;
  font-weight: 600;
}

.task-name {
  color: var(--color-gray-600);
  margin: 0;
  font-size: 0.875rem;
}

.criteria-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-5);
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
  font-size: 0.9375rem;
}

.rating-stars {
  display: flex;
  gap: var(--spacing-2);
}

.star-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 3.25rem;
  height: 3.25rem;
  border: none;
  background: transparent;
  color: #D1D5DB;
  cursor: pointer;
  transition: all 0.2s;
  padding: 0;
}

.star-btn:hover:not(:disabled) {
  transform: scale(1.1);
  color: #FEE347;
}

.star-btn.star-active {
  color: #FEE347;
}

.star-btn.star-readonly {
  cursor: default;
  opacity: 0.7;
}

.star-btn.star-readonly.star-active {
  color: #FCD34D;
}

.star-btn.star-readonly:hover {
  transform: none;
  color: #FCD34D;
}

.comment-section {
  margin-bottom: var(--spacing-6);
}

.comment-label {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  font-weight: 500;
  color: var(--color-gray-700);
  font-size: 0.9375rem;
  margin-bottom: var(--spacing-2);
}

.required-badge {
  display: inline-block;
  padding: 0.125rem 0.5rem;
  background-color: #FEE2E2;
  color: #DC2626;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.optional-badge {
  display: inline-block;
  padding: 0.125rem 0.5rem;
  background-color: #E0E7FF;
  color: #4F46E5;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.comment-textarea {
  width: 100%;
  padding: var(--spacing-3);
  border: 1px solid #D1D5DB;
  border-radius: var(--radius-lg);
  font-family: inherit;
  font-size: 0.9375rem;
  resize: vertical;
  background: white;
  color: var(--color-dark);
  transition: border-color 0.2s;
}

.comment-textarea:focus {
  outline: none;
  border-color: #E8793F;
  box-shadow: 0 0 0 3px rgba(232, 121, 63, 0.1);
}

.comment-textarea.comment-readonly {
  background: #F9FAFB;
  cursor: default;
  opacity: 0.7;
}

.comment-textarea.comment-required {
  border-color: #FCA5A5;
  background: #FEF2F2;
}

.comment-textarea.comment-required:focus {
  border-color: #DC2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.comment-hint {
  margin: var(--spacing-2) 0 0 0;
  padding: var(--spacing-2) var(--spacing-3);
  background: #FEF3C7;
  border-left: 3px solid #F59E0B;
  color: #92400E;
  font-size: 0.875rem;
  border-radius: var(--radius-md);
}

.comment-textarea::placeholder {
  color: #9CA3AF;
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
  justify-content: flex-start;
}

.submit-btn {
  padding: var(--spacing-3) var(--spacing-6);
  background: #92B08B;
  color: var(--color-white);
  border: none;
  border-radius: var(--radius-lg);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9375rem;
}

.submit-btn:hover:not(:disabled) {
  background: #7A9672;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.cancel-btn {
  padding: var(--spacing-3) var(--spacing-6);
  border: 1px solid #D1D5DB;
  background: white;
  color: var(--color-gray-700);
  border-radius: var(--radius-lg);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9375rem;
}

.cancel-btn:hover {
  background: var(--color-gray-50);
  border-color: #9CA3AF;
}

.cancel-btn-full {
  flex: 1;
  text-align: center;
}

@media (max-width: 768px) {
  .modal-content {
    margin: var(--spacing-4);
    max-height: 95vh;
  }
  
  .modal-body {
    padding: var(--spacing-6);
  }
  
  .modal-actions {
    flex-direction: column;
  }
  
  .cancel-btn,
  .submit-btn {
    width: 100%;
  }
  
  .rating-stars {
    gap: var(--spacing-1);
  }
  
  .star-btn {
    width: 2.75rem;
    height: 2.75rem;
  }
}

/* Skeleton Styles */
.skeleton-item {
  border-color: #e5e7eb !important;
  background-color: #ffffff !important;
  pointer-events: none;
}
.skeleton-box {
  background-color: #E2E8F0; /* Darker contrast */
  animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
.skeleton-text {
  background-color: #E2E8F0; /* Darker contrast */
  border-radius: 4px;
  animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.spinner {
  display: inline-block;
  width: 2rem;
  height: 2rem;
  border: 3px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top-color: #3B82F6;
  animation: spin 1s linear infinite;
  margin-bottom: 0.5rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
