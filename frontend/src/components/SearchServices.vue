<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-screen bg-gray-50 font-sans">
    
    <!-- HEADER (User & Logout) - Keeping simpler per screenshot 1 (Title only) or Generic App Header? 
         Screenshot shows "Rechercher Services" title and User Profile. 
         I will assume the layout handles the top bar/user profile, and this component is the content.
         But screenshot 1 shows "Rechercher Services" at top left. I will add it.
    -->
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-3xl font-bold text-gray-800">Rechercher Services</h1>
      <!-- User profile and logout would typically be in a layout, but adding placeholder if needed by context -->
    </div>

    <!-- VIEW 1: SERVICE SELECTION -->
    <div v-if="currentView === 'services'">
      
      <!-- Search & Filters Bar -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-8">
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
          <!-- Search Input -->
          <div class="relative flex-grow w-full md:w-auto md:max-w-2xl">
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Rechercher un service ou une tâche..." 
              class="w-full pl-12 pr-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-700 placeholder-gray-400"
            />
            <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          </div>

          <!-- Category Filters -->
          <div class="flex gap-2 w-full md:w-auto overflow-x-auto pb-2 md:pb-0">
            <button 
              v-for="cat in ['Tout', 'Jardinage', 'Ménage', 'Bricolage']" 
              :key="cat"
              @click="setCategory(cat)"
              :class="[
                'px-6 py-2 rounded-lg font-medium transition-colors whitespace-nowrap',
                selectedCategory === cat 
                  ? 'text-white' 
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              ]"
              :style="selectedCategory === cat ? { backgroundColor: serviceColors[cat] || '#1a5fa3' } : {}"
            >
              {{ cat }}
            </button>
          </div>
        </div>
      </div>

      <!-- Services Lists -->
      <div v-if="loadingServices" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <div v-else class="space-y-10">
        <div v-for="service in filteredServices" :key="service.id" class="animate-fade-in-up">
          <!-- Service Header -->
          <div class="flex items-start gap-4 mb-6">
            <div class="p-3 bg-green-50 rounded-2xl">
               <!-- Dynamic Icon based on name? Or generic. Screenshot has a leaf for Jardinage. -->
               <span class="text-3xl" v-if="service.nom_service.toLowerCase().includes('jardin')">🌿</span>
               <span class="text-3xl" v-else-if="service.nom_service.toLowerCase().includes('menage') || service.nom_service.toLowerCase().includes('ménage')">🧹</span>
               <span class="text-3xl" v-else>🛠️</span>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-gray-800">{{ service.nom_service }}</h2>
              <p class="text-gray-500 mt-1">{{ service.description || 'Services professionnels pour votre quotidien' }}</p>
            </div>
          </div>

          <!-- Tasks Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
              v-for="task in service.taches" 
              :key="task.id"
              @click="selectTask(service, task)"
              class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer p-6 flex flex-col justify-between h-full group"
            >
              <div>
                <h3 class="text-lg font-bold text-gray-800 group-hover:text-blue-600 transition-colors">{{ task.nom_tache }}</h3>
                <p class="text-sm text-gray-500 mt-2 mb-4 line-clamp-2">{{ task.description || 'Aucune description disponible.' }}</p>
              </div>
              
              <div class="flex items-center justify-between mt-4">
              
              </div>
            </div>
          </div>
        </div>

        <div v-if="filteredServices.length === 0" class="text-center py-20 text-gray-500">
          Aucun service trouvé pour votre recherche.
        </div>
      </div>

    </div>

    <!-- VIEW 2: INTERVENANT SELECTION -->
    <div v-else-if="currentView === 'intervenants'" class="animate-fade-in-right">
      
      <!-- Back & Title Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 pb-6 border-b border-gray-200">
        <div>
          <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            Intervenants disponibles pour : <span :style="{ color: currentServiceColor }">{{ selectedTask?.nom_tache }}</span>
          </h2>
          <p class="text-gray-500 mt-1">Service: {{ selectedService?.nom_service }}</p>
        </div>
        <button 
          @click="backToServices"
          class="mt-4 md:mt-0 px-6 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2"
        >
          ← Retour aux services
        </button>
      </div>

      <!-- Intervenants Grid -->
      <div v-if="loadingIntervenants" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2" :style="{ borderColor: currentServiceColor }"></div>
      </div>

      <div v-if="intervenants.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div 
          v-for="iv in intervenants" 
          :key="iv.id"
          @click="viewProfile(iv)"
          class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden flex flex-col cursor-pointer"
        >
          <div class="p-8 flex-1 flex flex-col">
            <!-- Top Section: Image & Main Info -->
            <div class="flex gap-6 mb-6">
              <div class="relative">
                <div class="w-24 h-24 rounded-2xl overflow-hidden shadow-lg border-2 border-blue-100">
                  <img
                    :src="iv.utilisateur?.url || defaultAvatar"
                    :alt="`${iv.utilisateur?.nom} ${iv.utilisateur?.prenom}`"
                    class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-110"
                  />
                </div>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <h3 class="text-xl font-bold text-gray-900 truncate pr-2">
                      {{ iv.utilisateur?.prenom }} {{ iv.utilisateur?.nom }}
                    </h3>
                  </div>
                  <div class="flex flex-col items-end gap-1">
                    <div class="flex items-center gap-1 bg-yellow-50 px-2 py-1 rounded-lg border border-yellow-100">
                      <span class="text-yellow-500">⭐</span>
                      <span class="font-bold text-gray-800 text-sm">{{ iv.note_moyenne }}</span>
                    </div>
                    <span class="text-gray-500 text-xs">{{ iv.nombre_avis }} avis</span>
                  </div>
                </div>
                
                <div class="flex flex-wrap gap-x-4 gap-y-2 mb-3">
                  <div class="flex items-center gap-1.5 text-sm text-gray-500 font-medium">
                    <Briefcase :size="14" class="text-blue-500" />
                    <span>{{ iv.missions_completees || 0 }} missions</span>
                  </div>
                  <div class="flex items-center gap-1.5 text-sm text-gray-500 font-medium">
                    <MapPin :size="14" class="text-blue-500" />
                    <span>{{ iv.ville }}</span>
                  </div>
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-100 mb-4">
                  {{ getExperienceLabel(iv) }}
                </div>
              </div>
            </div>

            <!-- Bio -->
            <div v-if="iv.bio" class="mb-6">
              <p class="text-sm text-gray-600 line-clamp-2">{{ iv.bio }}</p>
            </div>
            
            <div class="h-px bg-gray-100 w-full mb-6"></div>

            <!-- Tarif  -->
            <div class="mb-6">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500 font-medium">Tarif horaire</span>
                <div class="text-xl font-bold text-blue-600">
                  {{ iv.tarif }}
                  <span v-if="iv.tarif !== 'Sur devis'" class="text-sm"> DH/h</span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="mt-auto">
              <div class="flex gap-2">
                 <button
                  @click.stop="viewProfile(iv)"
                  class="flex-1 h-12 flex items-center justify-center rounded-xl transition-all duration-300 hover:opacity-90 font-bold text-sm text-white"
                  :style="{ backgroundColor: currentServiceColor }"
                >
                  Voir le profil
                </button>
                <button
                  @click.stop="toggleFavorite(iv, $event)"
                  class="px-4 h-12 rounded-xl border-2 transition-all flex items-center justify-center"
                  :style="{ 
                    borderColor: iv.is_favorite ? '#EF4444' : currentServiceColor, 
                    color: iv.is_favorite ? '#EF4444' : currentServiceColor,
                    backgroundColor: iv.is_favorite ? '#FEF2F2' : 'transparent'
                  }"
                  title="Ajouter aux favoris"
                >
                  <Heart :size="18" :fill="iv.is_favorite ? 'currentColor' : 'none'" />
                </button>
                <button
                  @click.stop="openBookingModal(iv)"
                  class="px-4 h-12 rounded-xl border-2 transition-all hover:bg-green-50 flex items-center justify-center"
                  :style="{ borderColor: '#609B41', color: '#609B41' }"
                  title="Réserver maintenant"
                >
                  <ArrowRight :size="18" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-else class="text-center py-20 bg-white rounded-xl shadow-sm">
        <p class="text-xl text-gray-500">Aucun intervenant disponible pour cette tâche actuellement.</p>
        <button @click="backToServices" class="mt-4 text-blue-600 hover:underline">Rechercher une autre tâche</button>
      </div>

    </div>
    
    <!-- VIEW 3: PROFILE -->
    <div v-else-if="currentView === 'profile'" class="py-6">
      <IntervenantProfile 
        :intervenantId="selectedIntervenantId"
        :clientId="clientId"
        :service="getSelectedServiceName()"
        @back="closeProfile"
        @book="chooseIntervenant"
      />
    </div>

    <!-- MODAL -->
    <ImprovedServiceRequestModal 
      :visible="showRequestModal" 
      :clientId="clientId" 
      :service="selectedService" 
      :task="selectedTask" 
      :intervenant="selectedIntervenant" 
      @close="showRequestModal = false" 
      @submitted="onRequestSubmitted" 
    />

    <!-- MODAL DE RÉSERVATION -->
    <BookingModal
      v-if="selectedIntervenantForBooking"
      :intervenant="selectedIntervenantForBooking"
      :client-id="clientId"
      :preselected-service="selectedService"
      :preselected-task="selectedTask"
      @close="closeBookingModal"
      @success="onBookingSuccess"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted , defineAsyncComponent } from 'vue'
import serviceService from '@/services/serviceService'
import intervenantService from '@/services/intervenantService'
import ImprovedServiceRequestModal from './ImprovedServiceRequestModal.vue'

const BookingModal = defineAsyncComponent(() =>
  import('./BookingModal.vue')
)
import api from '@/services/api';
const IntervenantProfile = defineAsyncComponent(() =>
  import('./IntervenantProfile.vue')
)

import { Heart, MapPin, Briefcase, Phone, ArrowRight } from 'lucide-vue-next';

const props = defineProps({ clientId: { type: Number, required: true } })

// State
const currentView = ref('services') // 'services' | 'intervenants' | 'profile'
const selectedIntervenantId = ref(null)
const services = ref([])
const loadingServices = ref(false)
const searchQuery = ref('')
const selectedCategory = ref('Tout')

const selectedService = ref(null)
const selectedTask = ref(null)
const intervenants = ref([])
const loadingIntervenants = ref(false)
const showRequestModal = ref(false)
const selectedIntervenant = ref(null)

const selectedIntervenantForBooking = ref(null)
const showBookingModal = ref(false)

const defaultAvatar = 'https://ui-avatars.com/api/?name=User&background=random' // Fallback

// Helpers
function formatPrice(price) {
  // Mock price range logic or display exact
  return price ? Math.round(price) : '80-120'
}

function getExperienceLabel(iv) {
  if (iv.utilisateur?.created_at) {
    const startDate = new Date(iv.utilisateur.created_at);
    const years = new Date().getFullYear() - startDate.getFullYear();
    if (years > 0) return `${years} an${years > 1 ? 's' : ''} exp.`;
  }
  return 'Nouveau';
}

function setCategory(cat) {
  selectedCategory.value = cat
}

  function openBookingModal(intervenant) {
    // Ajoutez les données manquantes pour le modal
    selectedIntervenantForBooking.value = {
      ...intervenant,
      id: intervenant.id,
      // Formatage pour le modal Booking
      name: `${intervenant.utilisateur?.prenom || ''} ${intervenant.utilisateur?.nom || ''}`.trim(),
      image: intervenant.utilisateur?.url || defaultAvatar,
      averageRating: intervenant.note_moyenne || 4.5,
      hourlyRate: typeof intervenant.tarif === 'number' ? intervenant.tarif : (intervenant.pivot?.prix_tache || 25),
      // Données supplémentaires utiles
      ville: intervenant.ville,
      bio: intervenant.bio
    };
    
    showBookingModal.value = true;
  }

function closeBookingModal() {
  selectedIntervenantForBooking.value = null;
  showBookingModal.value = false;
}

function onBookingSuccess() {
  closeBookingModal();
  // Vous pouvez ajouter un feedback ici
  alert('Votre demande de réservation a été envoyée avec succès !');
  
  // Optionnel : Retourner aux services ou recharger les données
  backToServices();
}

const serviceColors = {
  'Jardinage': '#92B08B',
  'Ménage': '#4682B4',
  'Bricolage': '#E8793F',
};

const serviceIdToColor = {
  1: '#92B08B', // Jardinage
  2: '#4682B4', // Ménage
};

// Computed
const currentServiceColor = computed(() => {
  if (selectedService.value) {
    const s = selectedService.value;
    return serviceColors[s.nom_service] || serviceIdToColor[s.id] || '#1a5fa3';
  }
  return '#1a5fa3';
});

const filteredServices = computed(() => {
  return services.value.filter(s => {
    // Search
    const q = searchQuery.value.trim().toLowerCase()
    const matchesSearch = !q || 
      s.nom_service.toLowerCase().includes(q) || 
      (s.taches || []).some(t => t.nom_tache.toLowerCase().includes(q))
    
    // Filter
    // Assumption: 'category' field might separate Garden/Cleaning. 
    // If backend doesn't have it, we map basic names. 
    let matchesCategory = true
    if (selectedCategory.value !== 'Tout') {
      const cat = selectedCategory.value.toLowerCase()
      const sName = s.nom_service.toLowerCase()
      if (cat.includes('jardin')) matchesCategory = sName.includes('jardin')
      else if (cat.includes('ménage')) matchesCategory = sName.includes('ménage') || sName.includes('menage')
      else if (cat.includes('bricolage')) matchesCategory = sName.includes('bricolage') || sName.includes('plomberie') || sName.includes('electricité')
      else matchesCategory = false
    }

    return matchesSearch && matchesCategory
  })
})

// Actions
async function fetchServices() {
  loadingServices.value = true
  try {
    const res = await serviceService.getAll()
    const payload = res.data?.data ?? res.data
    services.value = Array.isArray(payload) ? payload : []
  } catch (e) {
    console.error('Failed to fetch services', e)
  } finally {
    loadingServices.value = false
  }
}


async function fetchIntervenantsForTask(taskId) {
  loadingIntervenants.value = true;
  intervenants.value = [];
  
  try {
    console.log('🔍 Fetching intervenants for task:', taskId);
    
    // Get intervenants from API
    const res = await intervenantService.getIntervenantByTask(taskId);
    
    // Service returns {data: array, rawResponse: {...}}
    // Handle different response formats for compatibility
    let intervenantsData = [];
    
    if (Array.isArray(res.data)) {
      // Direct array from service
      intervenantsData = res.data;
    } else if (res.data?.data && Array.isArray(res.data.data)) {
      // Nested data structure
      intervenantsData = res.data.data;
    } else if (res.data?.intervenants && Array.isArray(res.data.intervenants)) {
      // Alternative format with intervenants key
      intervenantsData = res.data.intervenants;
    } else if (res.rawResponse?.data && Array.isArray(res.rawResponse.data)) {
      // Fallback to raw response
      intervenantsData = res.rawResponse.data;
    }
    
    console.log('✅ Found', intervenantsData.length, 'intervenants');
    
    // Transform data for display - use real data from database
    intervenants.value = intervenantsData.map(iv => {
      return {
        ...iv,
        // Use real data from backend, fallback only if null/undefined
        note_moyenne: iv.note_moyenne ?? iv.average_rating ?? null,
        nombre_avis: iv.nombre_avis ?? iv.review_count ?? 0,
        missions_completees: iv.missions_completees ?? iv.interventions_count ?? 0,
        ville: iv.ville ?? iv.utilisateur?.ville ?? 'Non spécifié',
        // Use tarif_tache from backend transformation, fallback to pivot
        tarif: (iv.tarif_tache || iv.pivot?.prix_tache) 
          ? Math.round(Number(iv.tarif_tache || iv.pivot.prix_tache)) 
          : 'Sur devis'
      };
    });
    
  } catch (error) {
    console.error('❌ Error fetching intervenants:', error);
    // Show empty state - intervenants.value is already []
  } finally {
    loadingIntervenants.value = false;
  }
}

function selectTask(service, task) {
  selectedService.value = service
  selectedTask.value = task
  currentView.value = 'intervenants'
  fetchIntervenantsForTask(task.id)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function backToServices() {
  currentView.value = 'services'
  intervenants.value = []
  selectedTask.value = null
}

function getSelectedServiceName() {
  if (selectedService.value) {
    const name = selectedService.value.nom_service.toLowerCase();
    if (name.includes('jardin')) return 'jardinage';
    if (name.includes('ménage') || name.includes('menage')) return 'menage';
  }
  return 'jardinage'; // Default
}

async function toggleFavorite(iv, event) {
  if (event) event.stopPropagation();
  
  console.log('❤️ Toggle favorite for:', iv.id, 'Client:', props.clientId);
  
  try {
    const serviceId = selectedService.value?.id;
    if (!serviceId) {
      console.warn('⚠️ No service selected, cannot favorite');
      return;
    }

    console.log('📤 Sending favorite request for service:', serviceId);

    // Optimistic update
    iv.is_favorite = !iv.is_favorite;

    const res = await api.post(`clients/${props.clientId}/favorites`, {
      intervenant_id: iv.id,
      service_id: serviceId
    });
    
    if (res.data.is_favorite !== undefined) {
      iv.is_favorite = res.data.is_favorite;
    }
  } catch (error) {
    console.error('Error toggling favorite:', error);
    iv.is_favorite = !iv.is_favorite; // Revert
  }
}

function chooseIntervenant(iv) {
  openBookingModal(iv); // Utilisez la nouvelle fonction
}

function viewProfile(iv) {
  selectedIntervenantId.value = iv.id;
  currentView.value = 'profile';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function closeProfile() {
  selectedIntervenantId.value = null;
  currentView.value = 'intervenants';
}

function onRequestSubmitted() {
  showRequestModal.value = false
  backToServices()
  // Could add a toast notification here
  alert('Votre demande a été envoyée avec succès !')
}



// Lifecycle
onMounted(() => {
  fetchServices()
})
</script>

<style scoped>
.animate-fade-in-up {
  animation: fadeInUp 0.5s ease-out forwards;
}

.animate-fade-in-right {
  animation: fadeInRight 0.4s ease-out forwards;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInRight {
  from { opacity: 0; transform: translateX(20px); }
  to { opacity: 1; transform: translateX(0); }
}
</style>
