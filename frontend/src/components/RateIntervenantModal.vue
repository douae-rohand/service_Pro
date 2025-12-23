<template>
  <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
    <!-- Overlay with Glassmorphism -->
    <div 
      class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity animate-fade-in"
      @click="$emit('close')"
    ></div>

    <!-- Modal Container -->
    <div 
      class="relative w-full max-w-2xl max-h-[90vh] bg-white rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col animate-scale-in"
    >
      <!-- Header -->
      <header 
        class="sticky top-0 p-6 sm:p-8 text-white z-10 flex items-center justify-between overflow-hidden shrink-0"
        style="background: linear-gradient(135deg, #305C7D 0%, #1A5FA3 100%)"
      >
        <!-- Background Decoration -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-blue-900/20 rounded-full blur-3xl"></div>

        <div class="relative z-10 flex items-center gap-5">
          <div class="relative">
            <img
              :src="intervenant.image || 'https://via.placeholder.com/150'"
              :alt="intervenant.name"
              class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-4 border-white/20 shadow-xl"
            />
            <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-[#92B08B] rounded-xl flex items-center justify-center border-2 border-white text-white shadow-lg">
              <Star :size="16" fill="white" />
            </div>
          </div>
          <div>
            <h2 class="text-xl sm:text-2xl font-black leading-tight tracking-tight">Donnez votre avis</h2>
            <p class="text-blue-100 font-medium opacity-90">{{ intervenant.name }}</p>
          </div>
        </div>
        
        <button
          @click="$emit('close')"
          class="relative z-10 p-2.5 bg-white/10 hover:bg-white/20 rounded-2xl transition-all duration-300 backdrop-blur-md border border-white/10 group"
        >
          <X :size="24" class="group-hover:rotate-90 transition-transform duration-300" />
        </button>
      </header>

      <!-- Content Area -->
      <div class="flex-1 overflow-y-auto p-6 sm:p-8 space-y-8 no-scrollbar bg-gray-50/50">
        <!-- Service Label -->
        <div class="relative overflow-hidden bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex items-center gap-4">
           <div class="w-10 h-10 rounded-xl bg-[#E8F5E9] flex items-center justify-center text-[#92B08B]">
             <Package :size="20" />
           </div>
           <div>
             <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-0.5">Prestation réalisée</span>
             <p class="font-bold text-[#305C7D]">{{ service }} • {{ task }}</p>
           </div>
        </div>

        <!-- Rating Criteria -->
        <section>
          <div class="flex items-center justify-between mb-6">
            <h3 class="font-black text-lg text-gray-900 tracking-tight">
               Critères d'évaluation
            </h3>
            <span v-if="!loading && !error" class="text-xs font-bold px-3 py-1 bg-white border border-gray-200 rounded-full text-gray-500 shadow-sm">
              {{ Object.values(ratings).filter(r => r > 0).length }}/{{ intervenantCriteria.length }} notés
            </span>
          </div>
          
          <!-- Loading state -->
          <div v-if="loading" class="flex flex-col items-center py-12 bg-white rounded-3xl border border-gray-100 italic">
            <div class="w-12 h-12 border-4 border-[#305C7D]/20 border-t-[#305C7D] rounded-full animate-spin mb-4"></div>
            <p class="text-gray-400 font-medium">Préparation du formulaire...</p>
          </div>
          
          <!-- Error State -->
          <div v-else-if="error" class="bg-red-50 border-2 border-red-100 rounded-2xl p-6 text-center animate-shake">
            <AlertCircle :size="32" class="text-red-500 mx-auto mb-3" />
            <p class="text-red-900 font-bold mb-1">Oups ! Une erreur est survenue</p>
            <p class="text-red-600 text-sm">{{ error }}</p>
          </div>
          
          <!-- Criteria list -->
          <div v-else class="space-y-4">
            <div
              v-for="(criteria, idx) in intervenantCriteria"
              :key="criteria.id"
              class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm transition-all duration-300 hover:shadow-md hover:border-[#92B08B]/20 animate-fade-in-up"
              :style="{ animationDelay: `${idx * 100}ms` }"
            >
              <div class="flex items-center justify-between mb-4">
                <label class="font-bold text-gray-700 tracking-tight">{{ criteria.nom_critaire }}</label>
                <div 
                  class="px-2 py-0.5 rounded-lg text-xs font-black transition-all duration-300"
                  :class="getRating(criteria.id) > 0 ? 'bg-[#92B08B] text-white' : 'bg-gray-100 text-gray-400'"
                >
                  {{ getRating(criteria.id) > 0 ? getRating(criteria.id) + '/5' : '—' }}
                </div>
              </div>
              <div class="flex justify-between sm:justify-start gap-1 sm:gap-3">
                <button
                  v-for="star in 5"
                  :key="star"
                  type="button"
                  @mouseover="hoveredStar = { id: criteria.id, star }"
                  @mouseleave="hoveredStar = null"
                  @click="setRating(criteria.id, star)"
                  class="relative p-1 transition-all duration-300 transform active:scale-90"
                >
                  <Star
                    :size="36"
                    stroke-width="1.5"
                    :class="{
                      'scale-110': hoveredStar?.id === criteria.id && star <= hoveredStar.star,
                      'text-[#FCD34D] fill-[#FCD34D]': star <= getRating(criteria.id),
                      'text-gray-200 fill-transparent': star > getRating(criteria.id)
                    }"
                    class="transition-all duration-300"
                  />
                  <!-- Small glow for active stars -->
                  <div 
                    v-if="star <= getRating(criteria.id)"
                    class="absolute inset-2 bg-[#FCD34D]/20 blur-lg rounded-full -z-10"
                  ></div>
                </button>
              </div>
            </div>
          </div>
        </section>

        <!-- Overall Score Card -->
        <div class="relative overflow-hidden bg-white rounded-3xl p-8 border border-gray-100 shadow-lg text-center">
            <div class="absolute -top-10 -left-10 w-32 h-32 bg-[#FCD34D]/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-[#92B08B]/10 rounded-full blur-3xl"></div>
            
            <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-4">Moyenne calculée</p>
            <div class="flex items-end justify-center gap-1 mb-4">
              <span class="text-6xl font-black text-[#305C7D] tracking-tighter tabular-nums">
                {{ overallRating.toFixed(1) }}
              </span>
              <span class="text-xl font-bold text-gray-300 mb-2">/5</span>
            </div>
            
            <div class="flex items-center justify-center gap-1.5">
              <Star
                v-for="star in 5"
                :key="star"
                :size="18"
                :fill="star <= Math.round(overallRating) ? '#FCD34D' : 'none'"
                :color="star <= Math.round(overallRating) ? '#FCD34D' : '#E5E7EB'"
                class="transition-all duration-500"
                :style="{ transitionDelay: `${star * 50}ms` }"
              />
            </div>
        </div>

        <!-- Commentary Box -->
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
          <div class="flex items-center justify-between">
            <h4 class="font-black text-gray-900 tracking-tight flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500">
                <MessageCircle :size="16" />
              </div>
              Votre commentaire
            </h4>
            <span class="text-[10px] font-mono text-gray-400 font-bold tabular-nums">
              {{ comment.length }}/500
            </span>
          </div>
          <textarea
            v-model="comment"
            rows="4"
            class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent rounded-2xl focus:bg-white focus:border-[#305C7D]/30 focus:ring-4 focus:ring-[#305C7D]/5 transition-all outline-none resize-none text-sm leading-relaxed"
            placeholder="Écrivez quelques mots sur la qualité du service, la ponctualité ou l'échange avec l'intervenant..."
            maxlength="500"
          ></textarea>
        </div>

        <!-- Toggle Recommendation -->
        <label 
          class="flex items-center justify-between p-6 bg-white rounded-3xl border border-gray-100 shadow-sm cursor-pointer group transition-all duration-300 hover:border-[#92B08B]/40"
          :class="{ 'bg-green-50/30 border-[#92B08B]/20': wouldRecommend }"
        >
          <div class="flex items-center gap-4">
            <div 
              class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300"
              :class="wouldRecommend ? 'bg-[#92B08B] text-white shadow-lg shadow-[#92B08B]/20' : 'bg-gray-100 text-gray-400'"
            >
              <ThumbsUp :size="20" />
            </div>
            <div>
              <p class="font-bold text-gray-800">Recommanderiez-vous cet intervenant ?</p>
              <p class="text-xs text-gray-500 font-medium">Votre avis aide les autres clients</p>
            </div>
          </div>
          <div class="relative inline-flex items-center h-6 w-11 shrink-0">
            <input
              v-model="wouldRecommend"
              type="checkbox"
              class="peer sr-only"
            />
            <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-[#92B08B] peer-checked:after:translate-x-full peer-checked:after:border-white transition-colors duration-300"></div>
          </div>
        </label>

        <!-- Validation Alert -->
        <transition name="fade">
          <div v-if="!isValid && intervenantCriteria.length > 0 && Object.values(ratings).some(r => r > 0)" class="bg-amber-50 border-2 border-amber-100 rounded-2xl p-4 flex gap-3 text-amber-900 animate-in fade-in slide-in-from-top-2">
            <AlertCircle :size="18" class="shrink-0 mt-0.5 text-amber-600" />
            <div class="text-xs">
              <p class="font-black mb-1 leading-none uppercase tracking-widest text-[10px]">Note incomplète</p>
              <p class="font-medium opacity-80">
                Veuillez évaluer tous les aspects proposés pour valider le formulaire.
              </p>
            </div>
          </div>
        </transition>
      </div>

      <!-- Footer Buttons -->
      <footer class="p-6 sm:p-8 bg-white border-t border-gray-100 flex gap-4 shrink-0">
        <button
          @click="$emit('close')"
          class="flex-1 px-6 py-4 rounded-2xl font-bold bg-white text-gray-500 border-2 border-gray-100 hover:bg-gray-50 transition-all active:scale-95 flex items-center justify-center gap-2"
        >
          Annuler
        </button>
        <button
          @click="submitRating"
          :disabled="!isValid"
          class="flex-[2] px-6 py-4 rounded-2xl text-white font-black transition-all transform active:scale-95 disabled:grayscale disabled:opacity-30 disabled:cursor-not-allowed shadow-xl shadow-[#92B08B]/20 flex items-center justify-center gap-3 relative overflow-hidden"
          :style="{ background: isValid ? '#92B08B' : '#E5E7EB' }"
        >
          <div v-if="isValid" class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 -translate-x-full animate-shimmer"></div>
          <span v-if="!isValid">Finaliser les notes</span>
          <span v-else>Publier mon avis</span>
          <ArrowRight v-if="isValid" :size="20" />
        </button>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { 
  X, Star, MessageCircle, Package, AlertCircle, 
  ThumbsUp, ArrowRight 
} from 'lucide-vue-next';
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
    type: [Number, String],
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
const hoveredStar = ref(null);

// Computed
const overallRating = computed(() => {
  const ratingValues = Object.values(ratings.value).filter(r => r > 0);
  if (ratingValues.length === 0) return 0;
  return ratingValues.reduce((sum, rating) => sum + rating, 0) / ratingValues.length;
});

const isValid = computed(() => {
  return intervenantCriteria.value.length > 0 && 
         intervenantCriteria.value.every(criteria => (ratings.value[criteria.id] || 0) > 0);
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
  } catch (err) {
    error.value = 'Impossible de charger les critères de notation';
    console.error('❌ Error loading criteria:', err);
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

onMounted(fetchIntervenantCriteria);
</script>

<style scoped>
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes scaleIn { 
  from { opacity: 0; transform: scale(0.95) translateY(20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes shimmer {
  100% { transform: translateX(100%); }
}

.animate-fade-in { animation: fadeIn 0.4s ease-out both; }
.animate-scale-in { animation: scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; }
.animate-fade-in-up { animation: fadeInUp 0.4s ease-out both; }
.animate-shimmer { animation: shimmer 2s infinite; }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>