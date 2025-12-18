<template>
  <div v-if="show" class="modal-overlay" @click="close">
    <div class="client-profile-modal" @click.stop>
      <!-- Close Button Corner -->
      <button @click="close" class="dismiss-btn">
        <X :size="20" />
      </button>
      <div v-if="loading" class="loading-state">
        <p>Chargement du profil...</p>
      </div>
      
      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button @click="close" class="close-btn">Fermer</button>
      </div>
      
      <div v-else-if="profileData" class="profile-content">
        <!-- Header -->
        <div class="profile-header">
          <img :src="profileData.client.photo" :alt="profileData.client.name" class="profile-photo" />
          <div class="profile-info">
            <h2>{{ profileData.client.name }}</h2>
            <p class="member-since">Membre depuis {{ formatDate(profileData.client.member_since) }}</p>
            <!-- Privacy: Contact info hidden in profile view -->
            <div class="profile-privacy-box">
              <p class="privacy-note">
                <Lock :size="14" />
                Les coordonnées personnelles sont masquées pour des raisons de confidentialité.
              </p>
            </div>
          </div>
        </div>

        <!-- Overall Rating -->
        <div class="rating-summary">
          <h3>Note globale</h3>
          <div class="overall-rating">
            <span class="rating-number">{{ profileData.rating_summary.overall_average }}</span>
            <div class="stars-display">
              <Star 
                v-for="n in 5" 
                :key="n" 
                :size="28" 
                :fill="n <= Math.round(profileData.rating_summary.overall_average) ? 'currentColor' : 'none'"
                class="star"
                :class="{ 'star-filled': n <= Math.round(profileData.rating_summary.overall_average) }"
              />
            </div>
            <p class="rating-count">{{ profileData.rating_summary.total_rated_interventions }} intervention(s) notée(s)</p>
          </div>
        </div>

        <!-- Criteria Breakdown -->
        <div v-if="profileData.rating_summary.criteria_averages.length > 0" class="criteria-section">
          <h3>Notes par critère</h3>
          <div class="criteria-list">
            <div v-for="crit in profileData.rating_summary.criteria_averages" :key="crit.id" class="criteria-item">
              <span class="criteria-name">{{ crit.nom }}</span>
              <div class="criteria-rating">
                <div class="stars-small">
                  <Star 
                    v-for="n in 5" 
                    :key="n" 
                    :size="18" 
                    :fill="n <= Math.round(crit.average) ? 'currentColor' : 'none'"
                    class="star"
                    :class="{ 'star-filled': n <= Math.round(crit.average) }"
                  />
                </div>
                <span class="avg-number">{{ crit.average }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Past Interventions -->
        <div class="interventions-section">
          <h3>Historique des interventions ({{ profileData.past_interventions.length }})</h3>
          <div v-if="profileData.past_interventions.length > 0" class="interventions-list">
            <div 
              v-for="(intervention, index) in profileData.past_interventions.slice(0, visibleInterventionsCount)" 
              :key="intervention.id" 
              class="intervention-card"
              :class="{ 'mine-card': intervention.is_me }"
            >
              <div class="intervention-header">
                <span class="intervention-date">{{ formatDate(intervention.date) }}</span>
                <span v-if="intervention.rating > 0 && (intervention.is_public || intervention.is_me)" class="intervention-rating-brief">
                  <Star :size="14" fill="currentColor" class="star-filled" />
                  <span>{{ intervention.rating }}</span>
                </span>
                <span v-else class="intervention-status">{{ intervention.status }}</span>
              </div>
              <p class="intervention-details">
                <strong>{{ intervention.service }}</strong> - {{ intervention.task }}
              </p>
              
              <!-- Detailed Ratings (Expandable) -->
              <div v-if="expandedInterventions.includes(intervention.id)" class="detailed-ratings-box">
                <div v-for="evalItem in intervention.detailed_ratings" :key="evalItem.criteria" class="eval-detail-row">
                  <span class="eval-criteria">{{ evalItem.criteria }}</span>
                  <div class="eval-stars">
                    <Star 
                      v-for="n in 5" 
                      :key="n" 
                      :size="12" 
                      :fill="n <= evalItem.note ? 'currentColor' : 'none'"
                      class="star"
                      :class="{ 'star-filled': n <= evalItem.note }"
                    />
                  </div>
                </div>
              </div>

              <div class="intervention-meta-footer">
                <span class="intervention-author">Par {{ intervention.intervenant_name }}</span>
                <div class="intervention-actions">
                  <button 
                    v-if="intervention.has_evaluation && (intervention.is_public || intervention.is_me)" 
                    @click="toggleExpand(intervention.id)"
                    class="view-eval-btn"
                  >
                    {{ expandedInterventions.includes(intervention.id) ? 'Cacher détails' : 'Détails notes' }}
                    <ChevronDown v-if="!expandedInterventions.includes(intervention.id)" :size="16" />
                    <ChevronUp v-else :size="16" />
                  </button>
                  <span v-else-if="intervention.has_evaluation && !intervention.is_public" class="eval-pending-tag" title="Sera visible une fois que le client aura noté ou après 7 jours">
                    Évaluation en attente
                  </span>
                </div>
              </div>
            </div>

            <!-- Show More Interventions -->
            <button 
              v-if="profileData.past_interventions.length > visibleInterventionsCount" 
              @click="visibleInterventionsCount += 5" 
              class="show-more-btn"
            >
              Voir plus d'interventions
              <ChevronDown :size="18" />
            </button>
          </div>
          <p v-else class="no-data">Aucune intervention complétée avec ce client</p>
        </div>

        <!-- Comments History -->
        <div v-if="profileData.comments.length > 0" class="comments-section">
          <h3>Commentaires des intervenants ({{ profileData.comments.length }})</h3>
          <div class="comments-list">
            <div 
              v-for="(comment, index) in profileData.comments.slice(0, visibleCommentsCount)" 
              :key="index" 
              class="comment-item"
            >
              <div class="comment-header">
                <span class="comment-author">Par {{ comment.author }}</span>
                <span class="comment-date">{{ formatDate(comment.date_intervention) }}</span>
              </div>
              <p class="comment-text">{{ comment.commentaire }}</p>
            </div>
          </div>
          
          <!-- Show More Comments -->
          <button 
            v-if="profileData.comments.length > visibleCommentsCount" 
            @click="visibleCommentsCount += 5" 
            class="show-more-btn"
          >
            Voir plus de commentaires
            <ChevronDown :size="18" />
          </button>
        </div>

        <!-- Close Button -->
        <div class="modal-actions">
          <button @click="close" class="close-modal-btn">Fermer</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Star, ChevronDown, ChevronUp, X, Lock } from 'lucide-vue-next'
import clientService from '@/services/clientService'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  clientId: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['close', 'view-evaluation'])

const loading = ref(false)
const error = ref(null)
const profileData = ref(null)

const visibleInterventionsCount = ref(5)
const visibleCommentsCount = ref(5)
const expandedInterventions = ref([])

watch(() => props.show, async (newVal) => {
  if (newVal && props.clientId) {
    visibleInterventionsCount.value = 5
    visibleCommentsCount.value = 5
    expandedInterventions.value = []
    await fetchProfile()
  }
})

const toggleExpand = (id) => {
  const index = expandedInterventions.value.indexOf(id)
  if (index > -1) {
    expandedInterventions.value.splice(index, 1)
  } else {
    expandedInterventions.value.push(id)
  }
}

const fetchProfile = async () => {
  loading.value = true
  error.value = null
  
  try {
    const data = await clientService.getClientProfile(props.clientId)
    profileData.value = data
  } catch (err) {
    console.error('Error fetching client profile:', err)
    error.value = err.response?.data?.message || 'Erreur lors du chargement du profil'
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

const close = () => {
  emit('close')
}

const viewEvaluation = (interventionId) => {
  emit('view-evaluation', interventionId)
}
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
  z-index: 9999;
  padding: 1rem;
}

.client-profile-modal {
  background: #FEF9E6;
  border-radius: 1.5rem;
  max-width: 50rem;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border: 4px solid #FEE347;
  position: relative;
}

.dismiss-btn {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  border: none;
  background: white;
  color: #6B7280;
  padding: 0.5rem;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  border: 1px solid #E5E7EB;
}

.dismiss-btn:hover {
  background: #F3F4F6;
  color: #374151;
  transform: rotate(90deg);
}

.loading-state,
.error-state {
  padding: 3rem;
  text-align: center;
}

.profile-content {
  padding: 2rem;
}

/* Header */
.profile-header {
  display: flex;
  gap: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #FEE347;
  margin-bottom: 1.5rem;
}

.profile-photo {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #E8793F;
}

.profile-info h2 {
  margin: 0 0 0.5rem 0;
  color: #E8793F;
  font-size: 1.5rem;
}

.member-since {
  color: #6B7280;
  font-size: 0.875rem;
  margin: 0 0 1rem 0;
  font-style: italic;
}

.profile-privacy-box {
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  background: rgba(232, 121, 63, 0.05);
  border: 1px dashed rgba(232, 121, 63, 0.3);
  border-radius: 0.75rem;
  width: fit-content;
}

.privacy-note {
  font-size: 0.8125rem;
  color: #E8793F;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0;
  font-weight: 500;
}

/* Rating Summary */
.rating-summary {
  background: rgba(255, 255, 255, 0.6);
  border-radius: 0.75rem;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  border: 2px solid rgba(255, 159, 64, 0.3);
}

.rating-summary h3 {
  margin: 0 0 1rem 0;
  color: #E8793F;
  font-size: 1.125rem;
  font-weight: 600;
}

.overall-rating {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.rating-number {
  font-size: 3rem;
  font-weight: 700;
  color: #E8793F;
  line-height: 1;
}

.stars-display {
  display: flex;
  gap: 0.25rem;
}

.star {
  color: #D1D5DB;
}

.star-filled {
  color: #FEE347;
}

.rating-count {
  margin: 0;
  color: #6B7280;
  font-size: 0.875rem;
}

/* Criteria Section */
.criteria-section {
  background: rgba(255, 255, 255, 0.6);
  border-radius: 0.75rem;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  border: 2px solid rgba(255, 159, 64, 0.3);
}

.criteria-section h3 {
  margin: 0 0 1rem 0;
  color: #E8793F;
  font-size: 1.125rem;
  font-weight: 600;
}

.criteria-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.criteria-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: white;
  border-radius: 0.5rem;
}

.criteria-name {
  font-weight: 500;
  color: #374151;
}

.criteria-rating {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stars-small {
  display: flex;
  gap: 0.125rem;
}

.avg-number {
  font-weight: 600;
  color: #E8793F;
  min-width: 2rem;
  text-align: right;
}

/* Interventions Section */
.interventions-section,
.comments-section {
  margin-bottom: 1.5rem;
}

.interventions-section h3,
.comments-section h3 {
  margin: 0 0 1rem 0;
  color: #E8793F;
  font-size: 1.125rem;
  font-weight: 600;
}

.interventions-list,
.comments-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.intervention-card {
  background: rgba(255, 255, 255, 0.8);
  border-radius: 1rem;
  padding: 1.25rem;
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.intervention-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
  background: white;
}

.mine-card {
  border-left: 4px solid #E8793F;
  background: rgba(232, 121, 63, 0.05);
}

.intervention-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.detailed-ratings-box {
  background: #F8FAFC;
  border-radius: 0.75rem;
  padding: 1rem;
  margin: 0.75rem 0;
  border: 1px solid #E2E8F0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.eval-detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.eval-criteria {
  font-size: 0.75rem;
  color: #64748B;
  font-weight: 500;
}

.eval-stars {
  display: flex;
  gap: 2px;
}

.intervention-details {
  margin: 0 0 0.5rem 0;
  color: #374151;
  font-weight: 600;
  font-size: 1rem;
}

.intervention-meta-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid rgba(0,0,0,0.05);
}

.intervention-author {
  font-size: 0.8125rem;
  color: #6B7280;
  font-weight: 500;
}

.view-eval-btn {
  padding: 0.4rem 1rem;
  border: 1.5px solid #E8793F;
  background: white;
  color: #E8793F;
  border-radius: 0.75rem;
  cursor: pointer;
  font-size: 0.75rem;
  font-weight: 700;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.view-eval-btn:hover {
  background: #E8793F;
  color: white;
  box-shadow: 0 4px 6px -1px rgba(232, 121, 63, 0.2);
}

.show-more-btn {
  margin: 1rem auto;
  background: white;
  border: 1.5px solid #FEE347;
  color: #7C2D12;
  padding: 0.6rem 1.5rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.show-more-btn:hover {
  background: #FEE347;
  transform: scale(1.05);
}

.view-eval-btn:hover {
  background: #E8793F;
  color: white;
}

.comment-item {
  background: rgba(255, 255, 255, 0.6);
  border-radius: 0.5rem;
  padding: 1rem;
  border-left: 3px solid #E8793F;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 0.8125rem;
}

.comment-author {
  font-weight: 600;
  color: #E8793F;
}

.comment-date {
  color: #6B7280;
}

.comment-text {
  margin: 0;
  color: #374151;
  line-height: 1.6;
  font-style: italic;
}

.eval-pending-tag {
  font-size: 0.75rem;
  color: #E8793F;
  background: #FFF7ED;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  border: 1px dashed #E8793F;
  cursor: help;
}

.no-data {
  text-align: center;
  color: #9CA3AF;
  font-style: italic;
  padding: 2rem;
}

/* Modal Actions */
.modal-actions {
  display: flex;
  justify-content: center;
  padding-top: 1rem;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.close-modal-btn,
.close-btn {
  padding: 0.75rem 2rem;
  border: 1px solid #D1D5DB;
  background: white;
  color: #374151;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.close-modal-btn:hover,
.close-btn:hover {
  background: #F3F4F6;
  border-color: #9CA3AF;
}

@media (max-width: 768px) {
  .profile-header {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  
  .overall-rating {
    flex-direction: column;
    align-items: center;
  }
  
  .criteria-item {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>
