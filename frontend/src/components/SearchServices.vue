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
                  ? 'bg-blue-700 text-white' 
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              ]"
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
            Intervenants disponibles pour : <span class="text-teal-700">{{ selectedTask?.nom_tache }}</span>
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
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <div v-if="intervenants.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div 
          v-for="iv in intervenants" 
          :key="iv.id" 
          @click="viewProfile(iv)"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 flex flex-col items-center p-6 text-center relative cursor-pointer group"
        >
          <!-- Badges -->
          <div class="absolute top-4 left-4 flex flex-col gap-2 z-10">
             <span v-if="iv.is_active" class="bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm">
               ✓ Vérifié
             </span>
             <span v-if="iv.note_moyenne >= 4.5" class="bg-yellow-400 text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm">
               ⭐ Top
             </span>
          </div>

          <!-- Favorite Button -->
          <button 
            @click.stop="toggleFavorite(iv, $event)"
            class="absolute top-4 right-4 p-2 rounded-full bg-white shadow-sm hover:shadow text-gray-400 hover:text-red-500 transition-all z-10"
            :class="{ 'text-red-500 fill-current': iv.is_favorite }"
          >
            <Heart :fill="iv.is_favorite ? 'currentColor' : 'none'" size="20" />
          </button>
          
          <!-- Avatar -->
          <div class="relative mb-4">
            <div class="w-28 h-28 rounded-full p-1 bg-gradient-to-tr from-green-400 to-blue-500">
              <img 
                :src="iv.utilisateur?.url || defaultAvatar" 
                class="w-full h-full rounded-full object-cover border-4 border-white"
                alt="Avatar"
              />
            </div>
            <!-- Online Indicator (Simulated) -->
            <div class="absolute bottom-2 right-2 w-5 h-5 bg-green-500 border-4 border-white rounded-full"></div>
          </div>

          <!-- Info -->
          <h3 class="text-xl font-bold text-gray-800 mb-1">{{ iv.utilisateur?.nom }} {{ iv.utilisateur?.prenom }}</h3>
          <p class="text-sm text-gray-500 line-clamp-2 px-2 mb-3 h-10">{{ iv.bio || 'Professionnel expérimenté à votre service.' }}</p>
          
          <div class="flex items-center gap-1 mb-4 text-yellow-500 font-medium bg-yellow-50 px-3 py-1 rounded-full">
            <span>⭐ {{ iv.note_moyenne }}</span>
            <span class="text-gray-400 text-sm font-normal">({{ iv.nombre_avis }} avis)</span>
          </div>
          
          <!-- Details Grid -->
          <div class="w-full grid grid-cols-2 gap-y-3 gap-x-2 text-sm text-gray-600 mb-6 px-4">
            <div class="flex items-center gap-2">
              <MapPin :size="16" class="text-blue-500" />
              <span class="truncate">{{ iv.ville }}</span>
            </div>
            <div class="flex items-center gap-2">
              <Briefcase :size="16" class="text-blue-500" />
              <span>{{ getExperienceLabel(iv) }}</span>
            </div>
            <div v-if="iv.utilisateur?.telephone" class="flex items-center gap-2 col-span-2 justify-center">
              <Phone :size="16" class="text-green-500" />
              <span>{{ iv.utilisateur.telephone }}</span>
            </div>
          </div>

        
         <!-- Stats Footer -->
        <div class="w-full flex justify-between items-center border-t border-gray-100 pt-4 mb-4 text-sm px-2">
          <div class="flex flex-col items-start bg-gray-50 px-3 py-2 rounded-lg">
            <span class="text-xs text-gray-400 uppercase font-bold">Missions</span>
            <span class="font-bold text-gray-700">{{ iv.missions_completees }} complétées</span>
          </div>
          <div class="flex flex-col items-end">
            <span class="text-xs text-gray-400 uppercase font-bold mb-1">Tarif horaire</span>
            <span class="bg-blue-600 text-white px-3 py-1 rounded-lg font-bold text-sm shadow-sm">
              {{ iv.tarif }}
              <span v-if="iv.tarif !== 'Sur devis'" class="text-xs ml-0.5">DH/h</span>
            </span>
          </div>
        </div>

      <!-- Action Button -->
      <button 
        @click.stop="openBookingModal(iv)"
        class="w-full py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg transform active:scale-95 flex items-center justify-center gap-2"
      >
        <span>Demander ce service</span>
        <ArrowRight :size="18" />
      </button>
        </div>
      </div>
      
      <div v-else class="text-center py-20 bg-white rounded-xl shadow-sm">
        <p class="text-xl text-gray-500">Aucun intervenant disponible pour cette tâche actuellement.</p>
        <button @click="backToServices" class="mt-4 text-blue-600 hover:underline">Rechercher une autre tâche</button>
      </div>

    </div>
    
    <!-- VIEW 3: PROFILE -->
    <div v-else-if="currentView === 'profile'">
      <IntervenantProfile 
        :intervenantId="viewingIntervenantId"
        :clientId="clientId"
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
const viewingIntervenantId = ref(null)
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
      hourlyRate: intervenant.pivot?.prix_tache || 25,
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

// Computed
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
    console.log('🔍[DEBUG] fetchIntervenantsForTask called for task:', taskId);
    
    // ⭐ ESSAYEZ D'ABORD LA BONNE MÉTHODE
    const res = await intervenantService.getIntervenantByTask(taskId);
    
    console.log('📦[DEBUG] Raw API Response:', res);
    console.log('📦[DEBUG] Response data structure:', {
      data: res.data,
      dataType: typeof res.data,
      isArray: Array.isArray(res.data),
      keys: res.data ? Object.keys(res.data) : 'no data',
      hasIntervenants: !!res.data?.intervenants,
      intervenantsType: typeof res.data?.intervenants
    });
    
    // ⭐⭐ CORRECTION CRUCIALE - Votre API retourne {intervenants: [...]}
    // Mais votre service getIntervenantByTask() retourne {data: intervenants}
    let intervenantsData = [];
    
    if (res.data?.intervenants) {
      // Si la réponse a une clé 'intervenants'
      intervenantsData = res.data.intervenants;
      console.log('✅ Using res.data.intervenants, count:', intervenantsData.length);
    } else if (Array.isArray(res.data)) {
      // Si c'est directement un tableau
      intervenantsData = res.data;
      console.log('✅ Using res.data (array), count:', intervenantsData.length);
    } else if (res.data?.data) {
      // Si c'est paginé {data: [...]}
      intervenantsData = res.data.data;
      console.log('✅ Using res.data.data, count:', intervenantsData.length);
    }
    
    console.log('👥[DEBUG] First intervenant sample:', intervenantsData[0]);
    
    // Transformation des données
    intervenants.value = intervenantsData.map(iv => {
      console.log('📊 Intervenant raw:', iv);
      
      return {
        ...iv,
        // ⭐ VOTRE API RETOURNE DÉJÀ note_moyenne, nombre_avis, etc.
        // Pas besoin de les créer si elles existent déjà
        note_moyenne: iv.average_rating || iv.note_moyenne || 4.5,
        nombre_avis: iv.review_count || iv.nombre_avis || 12,
        missions_completees: iv.interventions_count || iv.missions_completees || 0,
        ville: iv.ville || iv.utilisateur?.ville || 'Non spécifié',
        // Use top-level mapped field first, then fallback to pivot
        tarif: (iv.tarif_tache || iv.pivot?.prix_tache) 
          ? Math.round(Number(iv.tarif_tache || iv.pivot.prix_tache)) 
          : 'Sur devis'
      };
    });
    
    console.log('✅[DEBUG] Final intervenants:', intervenants.value);
    
  } catch (error) {
    console.error('❌[DEBUG] Error:', error);
    
    // FALLBACK : Testez avec une URL directe
    try {
      console.log('🔄[DEBUG] Testing direct API call...');
      const testRes = await fetch(`http://127.0.0.1:8000/api/taches/${taskId}/intervenants`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });
      const testData = await testRes.json();
      console.log('📡[DEBUG] Direct API result:', testData);
      
      if (testData.intervenants) {
        intervenants.value = testData.intervenants;
      }
    } catch (directError) {
      console.error('❌[DEBUG] Direct call also failed:', directError);
    }
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
  viewingIntervenantId.value = iv.id;
  currentView.value = 'profile';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function closeProfile() {
  viewingIntervenantId.value = null;
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
