<template>
  <div class="min-h-screen bg-gray-50 font-sans pb-20">
    <!-- Header / Back Navigation -->
    <div class="bg-white shadow-sm sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
        <button 
          @click="$emit('back')" 
          class="flex items-center gap-2 text-gray-600 hover:text-blue-600 transition-colors font-medium"
        >
          <ArrowLeft size="20" />
          Retour
        </button>
        <div class="text-xl font-bold text-gray-800">Profil Intervenant</div>
        <div class="w-20"></div> <!-- Spacer for balance -->
      </div>
    </div>

    <div v-if="loading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="intervenant" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 animate-fade-in-up">
      
      <!-- Profile Header Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 h-32 md:h-48 relative">
          <!-- Favorite Button -->
          <button 
            @click="toggleFavorite"
            class="absolute top-4 right-4 p-3 rounded-full bg-white/20 backdrop-blur-sm hover:bg-white/30 transition-all text-white"
            :class="{ 'text-red-500 bg-white shadow-lg': isFavorite }"
          >
            <Heart :fill="isFavorite ? 'currentColor' : 'none'" size="24" />
          </button>
        </div>
        
        <div class="px-8 pb-8 flex flex-col md:flex-row items-start gap-6 -mt-16 relative z-0">
          <!-- Avatar -->
          <div class="w-32 h-32 rounded-full p-1 bg-white shadow-lg flex-shrink-0">
            <img 
              :src="intervenant.utilisateur?.url || defaultAvatar" 
              class="w-full h-full rounded-full object-cover border-4 border-white"
              alt="Avatar"
            />
          </div>
          
          <!-- Info -->
          <div class="flex-grow md:mt-16 pt-2">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
              <div>
                <h1 class="text-3xl font-bold text-gray-800">
                  {{ intervenant.utilisateur?.prenom }} {{ intervenant.utilisateur?.nom }}
                </h1>
                <div class="flex items-center gap-4 mt-2 text-gray-600">
                  <span class="flex items-center gap-1">
                    <MapPin size="16" />
                    {{ intervenant.ville || intervenant.address || 'Localisation non spécifiée' }}
                  </span>
                  <span class="flex items-center gap-1 text-yellow-500 font-medium">
                    <Star fill="currentColor" size="16" />
                    {{ stats?.average_rating || 4.5 }}/5
                  </span>
                  <span class="text-sm text-gray-400">
                    ({{ stats?.total_reviews || 0 }} avis)
                  </span>
                </div>
              </div>
              

            </div>
            
            <p class="mt-6 text-gray-600 max-w-3xl leading-relaxed">
              {{ intervenant.bio || "Aucune biographie disponible pour cet intervenant." }}
            </p>
          </div>
        </div>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Services & Stats -->
        <div class="space-y-8">
          <!-- Key Stats -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6">Performance</h3>
            <div class="grid grid-cols-2 gap-4">
              <div class="p-4 bg-gray-50 rounded-xl text-center">
                <div class="text-2xl font-bold text-blue-600">{{ intervenant.missions_completees || 0 }}</div>
                <div class="text-xs text-gray-500 uppercase tracking-wide mt-1">Missions</div>
              </div>
              <div class="p-4 bg-gray-50 rounded-xl text-center">
                <div class="text-2xl font-bold text-green-600">{{ stats?.average_rating || 'N/A' }}</div>
                <div class="text-xs text-gray-500 uppercase tracking-wide mt-1">Note Moyenne</div>
              </div>
            </div>
          </div>
          
          <!-- Services -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6">Services Proposés</h3>
            <div class="space-y-3">
              <div v-for="service in services" :key="service.id" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg">
                  {{ service.nom_service.charAt(0) }}
                </div>
                <div>
                  <div class="font-bold text-gray-700">{{ service.nom_service }}</div>
                  <div class="text-xs text-gray-500">{{ service.taches_count }} tâches disponibles</div>
                </div>
              </div>
              <div v-if="services.length === 0" class="text-gray-500 text-sm italic">
                Aucun service listé.
              </div>
            </div>
          </div>
        </div>
        
        <!-- Right Column: Reviews -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
              Avis et Évaluations
              <span class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 text-sm font-normal">
                {{ stats?.total_reviews || 0 }}
              </span>
            </h3>
            
            <div v-if="reviews.length === 0" class="text-center py-12 text-gray-500 bg-gray-50 rounded-xl border border-dashed border-gray-200">
              <MessageSquare size="48" class="mx-auto text-gray-300 mb-3" />
              <p>Aucun avis pour le moment.</p>
            </div>
            
            <div v-else class="space-y-6">
              <div v-for="review in reviews" :key="review.id" class="border-b border-gray-100 last:border-0 pb-6 last:pb-0">
                <div class="flex items-start justify-between mb-3">
                  <div class="flex items-center gap-3">
                    <img 
                      :src="review.client_avatar || defaultAvatar" 
                      class="w-10 h-10 rounded-full object-cover border border-gray-100"
                      alt="Client"
                    />
                    <div>
                      <div class="font-bold text-gray-800">{{ review.client_name }}</div>
                      <div class="text-xs text-gray-500">{{ formatDate(review.date) }}</div>
                    </div>
                  </div>
                  <div class="flex items-center gap-0.5 text-yellow-500 bg-yellow-50 px-2 py-1 rounded-lg">
                    <Star fill="currentColor" size="14" />
                    <span class="font-bold text-sm">{{ review.rating }}</span>
                  </div>
                </div>
                
                <p class="text-gray-600 leading-relaxed text-sm mb-3">
                  {{ review.comment || "Pas de commentaire." }}
                </p>
                
                <div class="flex items-center gap-2 text-xs text-gray-400 bg-gray-50 inline-flex px-3 py-1 rounded-md">
                  <Tag size="12" />
                  <span>{{ review.service_name }}</span>
                  <span v-if="review.task_name">• {{ review.task_name }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps, defineEmits } from 'vue';
import { 
  ArrowLeft, Heart, MapPin, Star, 
  MessageSquare, Tag 
} from 'lucide-vue-next';
import intervenantService from '@/services/intervenantService';
import api from '@/services/api';

const props = defineProps({
  intervenantId: {
    type: Number,
    required: true
  },
  clientId: {
    type: Number,
    required: true
  }
});

const emit = defineEmits(['back', 'book']);

const loading = ref(true);
const intervenant = ref(null);
const services = ref([]);
const reviews = ref([]);
const stats = ref(null);
const isFavorite = ref(false);
const defaultAvatar = 'https://ui-avatars.com/api/?name=User&background=random';

onMounted(async () => {
  await fetchData();
});

async function fetchData() {
  loading.value = true;
  try {
    // 1. Fetch intervenant details
    intervenant.value = await intervenantService.getIntervenant(props.intervenantId);
    
    // 2. Fetch services
    const servicesRes = await intervenantService.getIntervenantServices(props.intervenantId);
    services.value = servicesRes.data || servicesRes;
    
    // 3. Fetch evaluations
    const evalsRes = await intervenantService.getEvaluations(props.intervenantId);
    reviews.value = evalsRes.data;
    stats.value = evalsRes.stats;
    
    // 4. Check favorite status
    await checkFavoriteStatus();
    
  } catch (error) {
    console.error('Error fetching profile data:', error);
  } finally {
    loading.value = false;
  }
}

async function checkFavoriteStatus() {
  try {
    const res = await api.get(`clients/${props.clientId}/favorites`);
    const favorites = res.data?.data || res.data || [];
    isFavorite.value = favorites.some(fav => fav.id === props.intervenantId);
  } catch (error) {
    console.error('Error checking favorite status:', error);
  }
}

async function toggleFavorite() {
  try {
    // We need a service ID to add favorite.
    const serviceId = services.value[0]?.id;
    
    if (!serviceId) {
      alert("Impossible d'ajouter aux favoris: aucun service disponible.");
      return;
    }
    
    // Optimistic UI update
    const previousState = isFavorite.value;
    isFavorite.value = !isFavorite.value;
    
    const res = await api.post(`clients/${props.clientId}/favorites`, {
      intervenant_id: props.intervenantId,
      service_id: serviceId
    });
    
    if (res.data.is_favorite !== undefined) {
      isFavorite.value = res.data.is_favorite;
    }
  } catch (error) {
    console.error('Error toggling favorite:', error);
    isFavorite.value = !isFavorite.value; // Revert on error
  }
}

function formatDate(dateString) {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
}
</script>

<style scoped>
.animate-fade-in-up {
  animation: fadeInUp 0.5s ease-out forwards;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
