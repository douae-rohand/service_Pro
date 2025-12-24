<template>
  <!-- Authentication Error State -->
    <div v-if="authError && !isLoadingUser" class="flex items-center justify-center min-h-[400px]">
      <div class="text-center">
        <div class="text-red-500 mb-4">{{ authError }}</div>
        <button @click="$router.push('/')" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
          Retour à l'accueil
        </button>
      </div>
    </div>

    <!-- Loading State (User Auth only) - REMOVED to show skeletons immediately -->
    <div v-if="isLoadingUser" class="hidden"></div>

    <!-- Main Content -->
    <div v-if="!authError && (currentUser || isLoadingUser)" class="max-w-5xl">
    <!-- Stats Cards -->
    <div class="grid md:grid-cols-4 gap-4 mb-6">
      <template v-if="isLoadingReviews || isLoadingUser">
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-gray-100">
           <div class="flex justify-between mb-2">
             <div class="skeleton-text w-20 h-4"></div>
             <div class="skeleton-box w-5 h-5 rounded-full"></div>
           </div>
           <div class="skeleton-text w-16 h-8 mb-1"></div>
           <div class="skeleton-text w-12 h-3"></div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-gray-100">
           <div class="flex justify-between mb-2">
             <div class="skeleton-text w-20 h-4"></div>
             <div class="skeleton-box w-5 h-5 rounded-full"></div>
           </div>
           <div class="skeleton-text w-16 h-8 mb-1"></div>
           <div class="skeleton-text w-12 h-3"></div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-gray-100">
           <div class="flex justify-between mb-2">
             <div class="skeleton-text w-20 h-4"></div>
             <div class="skeleton-box w-5 h-5 rounded-full"></div>
           </div>
           <div class="skeleton-text w-16 h-8 mb-1"></div>
           <div class="skeleton-text w-12 h-3"></div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-gray-100">
           <div class="flex justify-between mb-2">
             <div class="skeleton-text w-20 h-4"></div>
             <div class="skeleton-box w-5 h-5 rounded-full"></div>
           </div>
           <div class="skeleton-text w-16 h-8 mb-1"></div>
           <div class="skeleton-text w-12 h-3"></div>
        </div>
      </template>
      <template v-else>
        <div 
          class="bg-white rounded-lg shadow-sm p-4 border-l-4" 
          style="border-left-color: #FEE347"
        >
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-600">Note Moyenne</p>
            <Star :size="18" style="color: #FEE347" fill="#FEE347" />
          </div>
          <p class="text-3xl" style="color: #2F4F4F">{{ stats.averageRating }}</p>
          <p class="text-xs text-gray-500 mt-1">sur 5.0</p>
        </div>

        <div 
          class="bg-white rounded-lg shadow-sm p-4 border-l-4" 
          style="border-left-color: #92B08B"
        >
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-600">Total Avis</p>
            <ThumbsUp :size="18" style="color: #92B08B" />
          </div>
          <p class="text-3xl" style="color: #92B08B">{{ stats.totalReviews }}</p>
          <p class="text-xs text-gray-500 mt-1">avis clients</p>
        </div>

        <div 
          class="bg-white rounded-lg shadow-sm p-4 border-l-4" 
          style="border-left-color: #1A5FA3"
        >
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-600">Missions</p>
            <Award :size="18" style="color: #1A5FA3" />
          </div>
          <p class="text-3xl" style="color: #1A5FA3">{{ stats.completedMissions }}</p>
          <p class="text-xs text-gray-500 mt-1">complétées</p>
        </div>

        <div 
          class="bg-white rounded-lg shadow-sm p-4 border-l-4" 
          style="border-left-color: #92B08B"
        >
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-600">Revenus totaux</p>
            <Banknote :size="18" style="color: #92B08B" />
          </div>
          <p class="text-3xl" style="color: #92B08B">{{ totalAmount }} DH</p>
          <p class="text-xs text-gray-500 mt-1">total gagné</p>
        </div>
      </template>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
      <div class="flex items-center gap-4">
        <label class="text-sm font-medium" style="color: #2F4F4F">Filtrer par service:</label>
        <select 
          v-model="selectedService" 
          @change="filterReviewsByService"
          class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Tous les services</option>
          <option v-for="service in availableServices" :key="service" :value="service">
            {{ service }}
          </option>
        </select>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
      <!-- Rating Distribution -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h2 class="text-lg mb-4" style="color: #2F4F4F">Distribution des notes</h2>
          
          <div v-if="isLoadingReviews || isLoadingUser" class="space-y-3">
             <div class="space-y-3">
                <div v-for="n in 5" :key="n" class="flex items-center gap-3">
                  <div class="skeleton-text w-8 h-4"></div>
                  <div class="flex-1 skeleton-box h-2 rounded-full"></div>
                  <div class="skeleton-text w-8 h-3"></div>
                </div>
             </div>
             <div class="mt-8 p-4 rounded-lg bg-gray-50 text-center">
                <div class="skeleton-box w-8 h-8 rounded-full mx-auto mb-2"></div>
                <div class="skeleton-text w-32 h-4 mx-auto mb-1"></div>
                <div class="skeleton-text w-24 h-3 mx-auto"></div>
             </div>
          </div>

          <div v-else class="space-y-3">
            <div 
              v-for="item in sortedDistribution" 
              :key="item.stars" 
              class="flex items-center gap-3"
            >
              <span class="text-sm text-gray-600 w-8">{{ item.stars }} ★</span>
              <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all"
                  :style="{
                    width: `${calculatePercentage(item.count)}%`,
                    backgroundColor: '#FEE347',
                  }"
                />
              </div>
              <span class="text-xs text-gray-500 w-12 text-right">
                {{ calculatePercentage(item.count) }}%
              </span>
            </div>
             <!-- Excellence Badge -->
            <div 
                class="mt-6 p-4 rounded-lg text-center" 
                style="background-color: #E8F5E9"
            >
                <Award :size="32" style="color: #92B08B" class="mx-auto mb-2" />
                <p class="text-sm" style="color: #92B08B">Performance d'Excellence</p>
                <p class="text-xs text-gray-600 mt-1">
                {{ excellencePercentage }}% d'avis positifs
                </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Reviews List -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h2 class="text-lg mb-4" style="color: #2F4F4F">
            Avis récents ({{ filteredReviews.length }})
          </h2>
          
          <div v-if="isLoadingReviews || isLoadingUser" class="space-y-4">
             <div v-for="n in 3" :key="n" class="p-4 rounded-lg border border-gray-200">
                <div class="flex items-start gap-3 mb-3">
                   <div class="skeleton-box w-10 h-10 rounded-full"></div>
                   <div class="flex-1">
                      <div class="flex justify-between mb-1">
                         <div class="skeleton-text w-32 h-5"></div>
                         <div class="skeleton-text w-24 h-5"></div>
                      </div>
                      <div class="mb-3"><div class="skeleton-text w-20 h-5 rounded-full"></div></div>
                      <div class="flex gap-4">
                         <div class="skeleton-text w-24 h-4"></div>
                         <div class="skeleton-text w-32 h-4"></div>
                      </div>
                   </div>
                </div>
                <div class="skeleton-text w-full h-4 mb-2"></div>
                <div class="skeleton-text w-3/4 h-4"></div>
             </div>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="review in filteredReviews"
              :key="review.id"
              class="p-4 rounded-lg border border-gray-200 hover:shadow-sm transition-shadow"
            >
              <div class="flex items-start gap-3 mb-3">
                <img
                  :src="review.clientImage"
                  :alt="review.clientName"
                  class="w-10 h-10 rounded-full object-cover"
                />
                <div class="flex-1">
                  <div class="flex items-center justify-between mb-1">
                    <h3 class="text-base font-semibold" style="color: #1A5FA3">
                      {{ review.clientName }}
                    </h3>
                    <div class="flex items-center gap-2">
                      <span class="text-sm font-bold" style="color: #2F4F4F">{{ review.rating }}</span>
                      <div class="flex gap-0.5">
                        <Star
                          v-for="star in 5"
                          :key="star"
                          :size="16"
                          :fill="star <= review.rating ? '#FEE347' : 'none'"
                          :style="{ color: star <= review.rating ? '#FEE347' : '#D1D5DB' }"
                        />
                      </div>
                    </div>
                  </div>
                  <div class="mb-3">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                      {{ review.service }}
                    </span>
                  </div>
                  <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5" style="color: #1A5FA3">
                      <Calendar :size="14" />
                      <span class="text-sm">
                        {{ new Date(review.date).toLocaleDateString('fr-FR', {
                          day: 'numeric',
                          month: 'long',
                          year: 'numeric',
                        }) }}
                      </span>
                    </div>
                    <div class="flex items-center gap-1.5" style="color: #E8793F">
                      <MapPin :size="14" />
                      <span class="text-sm">{{ review.location }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <p class="text-sm text-gray-700 mb-2">{{ review.comment }}</p>

              <!-- Details Toggle -->
              <div v-if="review.details && review.details.length > 0">
                <button 
                  @click="review.expanded = !review.expanded"
                  class="text-xs font-medium flex items-center gap-1 transition-colors"
                  :style="{ color: review.expanded ? '#2F4F4F' : '#6B7280' }"
                >
                  {{ review.expanded ? 'Masquer les détails' : 'Voir les détails' }}
                  <span :class="{'rotate-180': review.expanded}" class="transition-transform duration-200">▼</span>
                </button>

                <!-- Detailed Criteria -->
              <!-- Details Section -->
            <div v-if="review.expanded" class="mt-4 pt-4 border-t border-gray-100">
              <h4 class="text-sm font-semibold text-gray-700 mb-2">Détails de l'évaluation</h4>
              <div class="space-y-2">
                <div 
                  v-for="(detail, idx) in review.details" 
                  :key="idx"
                  class="flex items-center justify-between text-sm"
                >
                  <span class="text-gray-600">{{ detail.criteriaName }}</span>
                  <div class="flex items-center gap-2">
                    <div class="flex gap-0.5">
                      <Star
                        v-for="star in 5"
                        :key="star"
                        :size="12"
                        :fill="star <= detail.rating ? '#FEE347' : 'none'"
                        :style="{ color: star <= detail.rating ? '#FEE347' : '#D1D5DB' }"
                      />
                    </div>
                    <span class="text-xs font-medium text-gray-500">({{ detail.rating }}/5)</span>
                  </div>
                </div>
              </div>

            </div>
              </div>
            </div>
          </div>

          <!-- Load More -->
          <button
            v-if="filteredReviews.length > 3"
            class="w-full mt-4 py-2 rounded-lg text-sm transition-colors"
            style="background-color: #F3E293; color: #2F4F4F"
            @mouseenter="(e) => (e.currentTarget.style.backgroundColor = '#FEE347')"
            @mouseleave="(e) => (e.currentTarget.style.backgroundColor = '#F3E293')"
          >
            Voir plus d'avis
          </button>
        </div>
      </div>
    </div>
  </div>

</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Star, TrendingUp, Award, ThumbsUp, Calendar, MapPin, Banknote } from 'lucide-vue-next'
import authService from '@/services/authService'
import statsService from '@/services/statsService'
import axios from '@/services/api' // Use the configured api service
import SkeletonLoader from './SkeletonLoader.vue'

// Authentication state
const currentUser = ref(null)
const isLoadingUser = ref(true)
const authError = ref(null)

// Stats and reviews data
const stats = ref({
  averageRating: 0,
  totalReviews: 0,
  responseRate: 0,
  satisfactionRate: 0
})

const reviews = ref([])
const distribution = ref([])
const totalAmount = ref(0)
const isLoadingReviews = ref(true)

// Filter state
const selectedService = ref('')
const availableServices = ref([])
const filteredReviews = ref([])

// Load authenticated user
const loadAuthenticatedUser = async () => {
  try {
    isLoadingUser.value = true
    authError.value = null

    // Check if token exists
    if (!authService.isAuthenticated()) {
      authError.value = 'Vous devez être connecté pour accéder à cette page'
      isLoadingUser.value = false
      return
    }

    // Get current user from API
    const response = await authService.getCurrentUser()
    const user = response.data

    // Check if user is an intervenant
    if (!user.intervenant) {
      authError.value = 'Cette page est réservée aux intervenants'
      isLoadingUser.value = false
      return
    }

    // Set current user
    currentUser.value = {
      id: user.id,
      nom: user.nom,
      prenom: user.prenom,
      email: user.email,
      intervenant: user.intervenant
    }

    isLoadingUser.value = false
  } catch (error) {
    console.error('Erreur lors du chargement de l\'utilisateur:', error)
    
    if (error.status === 401) {
      authError.value = 'Session expirée. Veuillez vous reconnecter'
      authService.setAuthToken(null)
    } else {
      authError.value = 'Erreur lors du chargement de vos informations'
    }
    
    isLoadingUser.value = false
  }
}

// Load intervenant reviews and stats
const loadReviewsAndStats = async () => {
  if (!currentUser.value?.intervenant?.id) return
  
  try {
    isLoadingReviews.value = true
    
    // Appeler l'API backend pour les stats et reviews
    const response = await statsService.getIntervenantReviewsStats(currentUser.value.intervenant.id)
    const data = response.data
    
    stats.value = data.stats
    reviews.value = data.reviews.map(review => ({
      ...review,
      expanded: false
    }))
    distribution.value = data.stats.distribution || []
    totalAmount.value = data.stats.totalAmount || 0
    
    // Extract available services from reviews (main services)
    const services = [...new Set(data.reviews.map(review => review.mainService).filter(Boolean))]
    availableServices.value = services
    
    // Initialize filtered reviews
    filteredReviews.value = reviews.value
    
    isLoadingReviews.value = false
  } catch (error) {
    console.error('Erreur lors du chargement des avis:', error)
    isLoadingReviews.value = false
  }
}

// Filter reviews by service
const filterReviewsByService = () => {
  if (!selectedService.value) {
    filteredReviews.value = reviews.value
  } else {
    filteredReviews.value = reviews.value.filter(review => 
      review.mainService === selectedService.value
    )
  }
}

// Component initialization
onMounted(async () => {
  await loadAuthenticatedUser()
  
  if (currentUser.value && currentUser.value.id) {
    await loadReviewsAndStats()
  }
})

// Helper function to calculate percentage
const calculatePercentage = (count) => {
  if (!stats.value.totalReviews || stats.value.totalReviews === 0) return 0
  return Math.round((count / stats.value.totalReviews) * 100)
}


// Computed properties for display logic
const sortedDistribution = computed(() => {
  // Create a copy of existing distribution
  const dist = [...distribution.value]
  
  // Check if 0 stars exists, if not add it
  if (!dist.find(d => d.stars === 0)) {
    dist.push({ stars: 0, count: 0 })
  }
  
  // Sort by stars ascending (0 to 5)
  return dist.sort((a, b) => a.stars - b.stars)
})

const excellencePercentage = computed(() => {
  if (!distribution.value || distribution.value.length === 0) return 0
  
  // Update to use the distribution array to find 4 and 5 stars
  const fourStars = distribution.value.find(d => d.stars === 4)?.count || 0
  const fiveStars = distribution.value.find(d => d.stars === 5)?.count || 0
  
  return calculatePercentage(fourStars + fiveStars)
})
</script>

<style scoped>
/* Skeleton Styles */
.skeleton-item {
  border-color: #e5e7eb !important;
  background-color: #ffffff !important;
  pointer-events: none;
}
.skeleton-box {
  background-color: #f3f4f6;
  animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
.skeleton-text {
  background-color: #f3f4f6;
  border-radius: 4px;
  animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>
