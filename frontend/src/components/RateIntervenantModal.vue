<template>
  <div class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
      <!-- Header -->
      <div
        class="sticky top-0 p-6 text-white z-10 flex items-center justify-between"
        style="background-color: #1a5fa3"
      >
        <div class="flex items-center gap-4">
          <img
            :src="intervenant.image"
            :alt="intervenant.name"
            class="w-16 h-16 rounded-full object-cover border-4 border-white"
          />
          <div>
            <h2 class="text-2xl">Évaluer la prestation</h2>
            <p class="opacity-90">{{ intervenant.name }}</p>
          </div>
        </div>
        <button
          @click="$emit('close')"
          class="p-2 hover:bg-white hover:bg-opacity-20 rounded-full transition-all"
        >
          <X :size="28" />
        </button>
      </div>

      <div class="p-6 space-y-6">
        <!-- Service Info -->
        <div class="bg-gray-50 rounded-xl p-4">
          <p class="text-sm text-gray-600 mb-1">Service</p>
          <p class="font-bold text-lg" style="color: #2f4f4f">{{ service }} - {{ task }}</p>
        </div>

        <!-- Rating Criteria -->
        <div>
          <h3 class="font-bold text-lg mb-4" style="color: #2f4f4f">
            Évaluez les différents aspects
          </h3>
          
          <!-- Loading State -->
          <div v-if="loading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-2 text-gray-600 text-sm">Chargement des critères...</p>
          </div>
          
          <!-- Error State -->
          <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600 text-sm">
            {{ error }}
          </div>
          
          <!-- Criteria List -->
          <div v-else class="space-y-4">
            <div
              v-for="criteria in intervenantCriteria"
              :key="criteria.id"
              class="bg-gray-50 rounded-lg p-4"
            >
              <div class="flex items-center justify-between mb-2">
                <label class="font-bold text-gray-700">{{ criteria.nom_critaire }}</label>
                <span class="text-2xl font-bold" style="color: #fee347">
                  {{ getRating(criteria.id) }}/5
                </span>
              </div>
              <div class="flex gap-2">
                <button
                  v-for="star in 5"
                  :key="star"
                  type="button"
                  @click="setRating(criteria.id, star)"
                  class="p-2 hover:scale-110 transition-transform"
                >
                  <Star
                    :size="32"
                    :fill="star <= getRating(criteria.id) ? '#FEE347' : 'none'"
                    :color="star <= getRating(criteria.id) ? '#FEE347' : '#D1D5DB'"
                  />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Overall Rating Display -->
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 text-center">
          <p class="text-sm text-gray-600 mb-2">Note globale</p>
          <div class="flex items-center justify-center gap-2 mb-2">
            <Star :size="40" fill="#FEE347" color="#FEE347" />
            <span class="text-5xl font-bold" style="color: #2f4f4f">
              {{ overallRating.toFixed(1) }}
            </span>
            <span class="text-2xl text-gray-500">/5</span>
          </div>
          <div class="flex items-center justify-center gap-1">
            <Star
              v-for="star in 5"
              :key="star"
              :size="20"
              :fill="star <= Math.round(overallRating) ? '#FEE347' : 'none'"
              color="#FEE347"
            />
          </div>
        </div>

        <!-- Comment -->
        <div>
          <label class="font-bold text-gray-700 mb-2 block">
            Commentaire (optionnel)
          </label>
          <textarea
            v-model="comment"
            rows="4"
            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none resize-none"
            placeholder="Partagez votre expérience avec cet intervenant..."
          ></textarea>
          <p class="text-xs text-gray-500 mt-1">{{ comment.length }}/500 caractères</p>
        </div>

        <!-- Would Recommend -->
        <div class="bg-gray-50 rounded-xl p-4">
          <label class="flex items-center gap-3 cursor-pointer">
            <input
              v-model="wouldRecommend"
              type="checkbox"
              class="w-5 h-5 rounded border-gray-300"
              style="accent-color: #92b08b"
            />
            <span class="font-bold text-gray-700">
              Je recommanderais cet intervenant
            </span>
          </label>
        </div>

        <!-- Validation Message -->
        <div v-if="!isValid && intervenantCriteria.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-3 text-red-600 text-sm">
          <p class="font-semibold mb-1">Veuillez noter tous les critères avant de soumettre votre évaluation.</p>
          <p v-if="unratedCriteria.length > 0" class="text-xs">
            Critères manquants : {{ unratedCriteria.map(c => c.nom_critaire).join(', ') }}
          </p>
        </div>
      </div>

      <!-- Footer -->
      <div class="sticky bottom-0 bg-white border-t border-gray-200 p-6 rounded-b-2xl flex gap-3">
        <button
          @click="$emit('close')"
          class="flex-1 px-6 py-3 rounded-xl border-2 font-bold hover:bg-gray-50 transition-all"
          style="border-color: #1a5fa3; color: #1a5fa3"
        >
          Annuler
        </button>
        <button
          @click="submitRating"
          :disabled="!isValid"
          class="flex-1 px-6 py-3 rounded-xl text-white font-bold hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          style="background-color: #92b08b"
        >
          Publier l'évaluation
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { X, Star } from 'lucide-vue-next';
import evaluationService from '@/services/evaluationService';

const props = defineProps({
  intervenant: {
    type: Object,
    required: true
  },
  service: {
    type: String,
    required: true
  },
  task: {
    type: String,
    required: true
  },
  demandId: {
    type: Number,
    required: false
  }
});

const emit = defineEmits(['close', 'submit']);

// State
const intervenantCriteria = ref([]);
const ratings = ref({});
const comment = ref('');
const wouldRecommend = ref(false);
const loading = ref(false);
const error = ref(null);

// Computed
const overallRating = computed(() => {
  const ratingValues = Object.values(ratings.value).filter(r => r > 0);
  if (ratingValues.length === 0) return 0;
  return ratingValues.reduce((sum, rating) => sum + rating, 0) / ratingValues.length;
});

const isValid = computed(() => {
  // All criteria must be rated
  return intervenantCriteria.value.length > 0 && 
         intervenantCriteria.value.every(criteria => ratings.value[criteria.id] > 0);
});

const unratedCriteria = computed(() => {
  return intervenantCriteria.value.filter(c => !ratings.value[c.id] || ratings.value[c.id] === 0);
});

// Methods
const fetchIntervenantCriteria = async () => {
  loading.value = true;
  error.value = null;
  try {
    const criteria = await evaluationService.getIntervenantCriteria();
    const uniqueCriteria = Array.isArray(criteria) ? 
      criteria.filter((item, index, self) => 
        index === self.findIndex((t) => t.id === item.id)
      ) : [];
    
    intervenantCriteria.value = uniqueCriteria;
    
    // Initialize ratings object
    ratings.value = {};
    intervenantCriteria.value.forEach(criteria => {
      ratings.value[criteria.id] = 0;
    });
    
    console.log('✅ Loaded intervenant criteria:', intervenantCriteria.value);
  } catch (err) {
    error.value = 'Erreur lors du chargement des critères d\'évaluation';
    intervenantCriteria.value = [];
    ratings.value = {};
    console.error('❌ Error loading intervenant criteria:', err);
  } finally {
    loading.value = false;
  }
};

const setRating = (criteriaId, rating) => {
  ratings.value[criteriaId] = rating;
};

const getRating = (criteriaId) => {
  return ratings.value[criteriaId] || 0;
};

const submitRating = () => {
  if (!isValid.value) return;

  const ratingData = {
    criteriaRatings: Object.entries(ratings.value)
      .filter(([_, note]) => note > 0)
      .reduce((acc, [criteriaId, note]) => {
        acc[criteriaId] = note;
        return acc;
      }, {}),
    overallRating: parseFloat(overallRating.value.toFixed(1)),
    comment: comment.value.trim(),
    wouldRecommend: wouldRecommend.value
  };

  emit('submit', ratingData);
};

// Lifecycle
onMounted(() => {
  fetchIntervenantCriteria();
});
</script>
```