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
                <span class="px-4 py-1.5 bg-blue-700 text-white text-sm font-bold rounded-lg shadow-sm">
                  <!-- Mock price if missing, or range -->
                  {{ formatPrice(task.baseRate) }} DH/h
                </span>
                <span class="text-xs text-gray-400 font-medium">1-2 heures</span>
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

      <div v-else-if="intervenants.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="iv in intervenants" :key="iv.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 flex flex-col items-center p-6 text-center">
          
          <!-- Avatar -->
          <div class="relative mb-4">
            <div class="w-24 h-24 rounded-full p-1 bg-gradient-to-tr from-green-400 to-blue-500">
              <img 
                :src="iv.utilisateur?.url || defaultAvatar" 
                class="w-full h-full rounded-full object-cover border-4 border-white"
                alt="Avatar"
              />
            </div>
          </div>

          <!-- Info -->
          <h3 class="text-xl font-bold text-gray-800">{{ iv.utilisateur?.nom }} {{ iv.utilisateur?.prenom }}</h3>
          
          <div class="flex items-center gap-1 my-2 text-yellow-500 font-medium">
            <span>⭐ {{ iv.note_moyenne || 'N/A' }}</span>
            <span class="text-gray-400 text-sm font-normal">({{ iv.nombre_avis || 0 }} avis)</span>
          </div>
          
          <div class="flex items-center gap-2 text-gray-500 text-sm mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            {{ iv.ville || iv.address || 'Non spécifié' }}
          </div>

          <!-- Stats Row -->
          <div class="w-full flex justify-between items-center border-t border-gray-100 pt-4 mb-6 text-sm">
            <div class="flex flex-col items-start">
              <span class="text-gray-400">Missions complétées:</span>
              <span class="font-bold text-gray-700">{{ iv.missions_completees || 0 }}</span>
            </div>
            <div class="flex flex-col items-end">
              <span class="text-gray-400">Tarif:</span>
              <span class="bg-blue-800 text-white px-3 py-1 rounded-md font-bold text-xs">
                 {{ iv.pivot?.prix_tache || 'N/A' }} DH/h
              </span>
            </div>
          </div>

          <!-- Action Button -->
          <button 
            @click="chooseIntervenant(iv)"
            class="w-full py-3 bg-green-600 bg-opacity-90 hover:bg-opacity-100 text-white font-semibold rounded-xl transition-all shadow-md transform active:scale-95"
          >
            Demander ce service
          </button>

        </div>
      </div>
      
      <div v-else class="text-center py-20 bg-white rounded-xl shadow-sm">
        <p class="text-xl text-gray-500">Aucun intervenant disponible pour cette tâche actuellement.</p>
        <button @click="backToServices" class="mt-4 text-blue-600 hover:underline">Rechercher une autre tâche</button>
      </div>

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

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import serviceService from '@/services/serviceService'
import intervenantService from '@/services/intervenantService'
import ImprovedServiceRequestModal from './ImprovedServiceRequestModal.vue'

const props = defineProps({ clientId: { type: Number, required: true } })

// State
const currentView = ref('services') // 'services' | 'intervenants'
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

const defaultAvatar = 'https://ui-avatars.com/api/?name=User&background=random' // Fallback

// Helpers
function formatPrice(price) {
  // Mock price range logic or display exact
  return price ? Math.round(price) : '80-120'
}

function setCategory(cat) {
  selectedCategory.value = cat
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
  loadingIntervenants.value = true
  intervenants.value = []
  try {
    const res = await intervenantService.getIntervenantByTask(taskId)
    const payload = res.data?.data ?? res.data
    // Backend returns { intervenants: [...] } or just array in some fallback? 
    // My backend change: `return response()->json(['intervenants' => $intervenants]);`
    // JS service: `return { data: res.data?.intervenants ?? [] }`
    // So payload here is the array.
    
    intervenants.value = Array.isArray(payload) ? payload : (payload.intervenants || [])
  } catch (e) {
    console.error('Failed to fetch intervenants', e)
  } finally {
    loadingIntervenants.value = false
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

function chooseIntervenant(iv) {
  selectedIntervenant.value = iv
  showRequestModal.value = true
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
