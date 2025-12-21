<template>
  <div class="min-h-screen bg-gray-50/50 animate-fade-in flex flex-col">

    <!-- 1. Hero / Welcome Section -->
    <div class="bg-white border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <span class="inline-block px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-semibold tracking-wide uppercase mb-2">Espace Client</span>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800 tracking-tight">
              Bonjour, <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">{{ currentUser?.name || 'Client' }}</span> 👋
            </h1>
            <p class="text-slate-500 mt-2 text-lg">Prêt à simplifier votre quotidien ? Choisissez un service ci-dessous.</p>
          </div>
          <!-- Quick Status Badge (Optional) -->
          <div class="hidden md:flex items-center gap-2 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
            <span class="text-sm font-medium text-slate-600">Système opérationnel</span>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
       <!-- 2. Services Section (Main Action) - Overlapping the header slightly for depth -->
       <div class="grid md:grid-cols-2 gap-6 mb-12">
        
        <!-- JARDINAGE CARD -->
        <div 
          @click="$emit('service-click', 1)"
          class="group relative bg-white rounded-[2rem] p-8 shadow-lg hover:shadow-2xl transition-all duration-500 cursor-pointer overflow-hidden border border-gray-100 transform hover:-translate-y-1"
        >
          <div class="absolute top-0 right-0 w-64 h-64 bg-green-50 rounded-full -mr-16 -mt-16 opacity-50 group-hover:scale-125 transition-transform duration-700"></div>
          <div class="relative z-10 flex flex-col h-full justify-between">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2 group-hover:text-green-700 transition-colors">Jardinage</h3>
                <p class="text-slate-500 text-sm leading-relaxed max-w-xs">Entretien de jardins, taille, tonte et aménagement paysager par des experts.</p>
              </div>
              <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center text-green-600 shrink-0 group-hover:rotate-12 transition-transform duration-500">
                 <!-- Icone Feuille/Plante -->
                 <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/></svg>
              </div>
            </div>
            
            <div class="mt-8 flex items-end justify-between">
               <span class="text-sm font-semibold text-green-600 group-hover:underline">Explorer 12 services</span>
               <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center group-hover:bg-green-600 group-hover:text-white transition-colors">
                 <ArrowRight :size="20" />
               </div>
            </div>
          </div>
        </div>

        <!-- MENAGE CARD -->
        <div 
          @click="$emit('service-click', 2)"
           class="group relative bg-white rounded-[2rem] p-8 shadow-lg hover:shadow-2xl transition-all duration-500 cursor-pointer overflow-hidden border border-gray-100 transform hover:-translate-y-1"
        >
          <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-full -mr-16 -mt-16 opacity-50 group-hover:scale-125 transition-transform duration-700"></div>
          <div class="relative z-10 flex flex-col h-full justify-between">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2 group-hover:text-blue-700 transition-colors">Ménage</h3>
                <p class="text-slate-500 text-sm leading-relaxed max-w-xs">Nettoyage résidentiel et commercial, entretien régulier et soin du linge.</p>
              </div>
              <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 shrink-0 group-hover:rotate-12 transition-transform duration-500">
                 <!-- Icone Sparkles/Clean -->
                 <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M9 5H1"/><path d="M5 1v8"/></svg>
              </div>
            </div>
            
            <div class="mt-8 flex items-end justify-between">
               <span class="text-sm font-semibold text-blue-600 group-hover:underline">Explorer 8 services</span>
               <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                 <ArrowRight :size="20" />
               </div>
            </div>
          </div>
        </div>

       </div>

       <!-- 3. Stats & Activity Section -->
       <div class="mb-12">
         <div class="flex items-center justify-between mb-6">
           <h2 class="text-xl font-bold text-slate-800">Mon Activité</h2>
           <button class="text-sm text-blue-600 font-medium hover:text-blue-700 transition-colors">Voir l'historique</button>
         </div>

         <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
           <!-- Stat Card 1 -->
           <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow group">
             <div class="flex items-center gap-3 mb-2">
               <div class="p-2 bg-yellow-50 text-yellow-600 rounded-lg group-hover:bg-yellow-100 transition-colors">
                 <Clock :size="18" />
               </div>
               <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">En attente</span>
             </div>
             <p class="text-3xl font-bold text-slate-800">{{ stats.pending || 0 }}</p>
           </div>

           <!-- Stat Card 2 -->
           <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow group">
             <div class="flex items-center gap-3 mb-2">
               <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg group-hover:bg-indigo-100 transition-colors">
                 <Calendar :size="18" />
               </div>
               <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Programmées</span>
             </div>
             <p class="text-3xl font-bold text-slate-800">{{ stats.accepted || 0 }}</p>
           </div>

           <!-- Stat Card 3 -->
           <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow group">
             <div class="flex items-center gap-3 mb-2">
               <div class="p-2 bg-purple-50 text-purple-600 rounded-lg group-hover:bg-purple-100 transition-colors">
                 <Activity :size="18" />
               </div>
               <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">En cours</span>
             </div>
             <p class="text-3xl font-bold text-slate-800">{{ stats.inProgress || 0 }}</p>
           </div>

           <!-- Stat Card 4 -->
           <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow group">
             <div class="flex items-center gap-3 mb-2">
               <div class="p-2 bg-green-50 text-green-600 rounded-lg group-hover:bg-green-100 transition-colors">
                 <CheckCircle :size="18" />
               </div>
               <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Terminées</span>
             </div>
             <p class="text-3xl font-bold text-slate-800">{{ stats.completed || 0 }}</p>
           </div>
         </div>
       </div>

       <!-- 4. Immersive Map Section -->
       <div class="bg-white rounded-3xl p-1 shadow-lg border border-gray-100 overflow-hidden">
         <div class="p-6 pb-2">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
              <MapPin :size="24" class="text-rose-500" />
              Intervenants à proximité
            </h2>
            <p class="text-slate-500 text-sm mt-1">Explorez la carte pour trouver les meilleurs professionnels disponibles autour de vous.</p>
         </div>
         
         <div class="rounded-[1.5rem] overflow-hidden m-2 border border-gray-100 relative h-[500px]">
           <l-map v-if="mapReady" ref="map" v-model:zoom="zoom" :center="center" :use-global-leaflet="false">
             <l-tile-layer
               url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png"
               layer-type="base"
               name="CartoDB Voyager"
             ></l-tile-layer>

             <!-- User Location Pulse -->
             <l-marker v-if="userLocation" :lat-lng="userLocation">
               <l-icon class-name="user-pulse-marker">
                 <div class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-lg relative z-10"></div>
                 <div class="absolute -inset-4 bg-blue-500/20 rounded-full animate-ping"></div>
               </l-icon>
             </l-marker>

             <!-- Intervenants Markers -->
             <l-marker 
               v-for="iv in mappedIntervenants" 
               :key="iv.id" 
               :lat-lng="iv.coords"
             >
               <l-icon class-name="custom-marker-icon">
                 <div class="marker-pin !bg-slate-900"></div>
                 <div class="avatar-container border-2 border-white drop-shadow-md">
                   <img :src="iv.avatar" class="avatar-img w-full h-full object-cover" />
                 </div>
               </l-icon>
               <l-tooltip :options="{ offset: [0, -20], direction: 'top', className: 'modern-tooltip' }">
                 <div class="p-3 min-w-[180px]">
                   <div class="flex items-center gap-2 mb-2">
                     <span class="w-2 h-2 rounded-full bg-green-500"></span>
                     <span class="text-xs font-bold uppercase text-green-600 tracking-wider">Disponible</span>
                   </div>
                   <div class="font-bold text-slate-800 text-lg leading-tight mb-1">{{ iv.name }}</div>
                   <div class="text-sm font-medium text-slate-500 mb-3">{{ iv.profession }}</div>
                   
                   <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                      <div class="flex items-center gap-1 bg-yellow-50 px-2 py-1 rounded-md">
                        <Star :size="14" class="fill-yellow-400 text-yellow-400" /> 
                        <span class="font-bold text-yellow-700 text-xs">{{ iv.rating }}</span>
                      </div>
                      <span class="text-xs text-gray-400 font-medium">{{ iv.reviews }} avis</span>
                   </div>
                 </div>
               </l-tooltip>
             </l-marker>
           </l-map>
           
           <!-- Map Overlay Gradient -->
           <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white/80 to-transparent pointer-events-none z-[400]"></div>
         </div>
       </div>

    </div>
    
  <!-- Footer Section -->
  <Footer @navigate-home="$emit('navigate-home')" class="mt-auto" />
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { 
  Clock, Calendar, Activity, CheckCircle, MapPin, Star, ArrowRight
} from 'lucide-vue-next';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LTooltip, LIcon } from "@vue-leaflet/vue-leaflet";
import intervenantService from '@/services/intervenantService';
import Footer from './Footer.vue';

const props = defineProps({
  currentUser: { type: Object, required: true },
  stats: { type: Object, default: () => ({ pending: 0, accepted: 0, inProgress: 0, completed: 0 }) }
})

defineEmits(['service-click', 'navigate-home'])

const map = ref(null)
const mapReady = ref(false)
const zoom = ref(13)
const center = ref([35.57845, -5.36837]) // Tanger par défaut
const userLocation = ref(null)
const intervenants = ref([])
const mappedIntervenants = ref([])

onMounted(async () => {
  console.log('ClientHomePage mounted')
  // Get user location
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        userLocation.value = [pos.coords.latitude, pos.coords.longitude]
        center.value = userLocation.value
      },
      (err) => console.warn("Geolocation error:", err)
    )
  }
  
  // Wait for next tick to init map
  setTimeout(() => {
    mapReady.value = true
  }, 100)
  
  // Fetch all intervenants (no distance filter)
  await fetchAllIntervenants()
})

const fetchAllIntervenants = async () => {
  try {
    const params = { per_page: 100, active: true } // Fetch many
    const res = await intervenantService.getAllIntervenants(params)
    intervenants.value = res.data || []
    generateMarkers()
  } catch (e) {
    console.error("Error fetching intervenants:", e)
  }
}

const generateMarkers = () => {
  mappedIntervenants.value = intervenants.value.map((iv) => {
    const seed = iv.id * 123.45; 
    const offsetLat = (Math.sin(seed) * 0.04) - 0.02; 
    const offsetLng = (Math.cos(seed) * 0.04) - 0.02;
    
    // Fallback coords
    const lat =  center.value[0] + offsetLat
    const lng =  center.value[1] + offsetLng

    return {
      ...iv,
      coords: [lat, lng],
      name: `${iv.utilisateur?.prenom || ''} ${iv.utilisateur?.nom || ''}`,
      profession: iv.taches?.[0]?.service?.nom_service || 'Professionnel',
      rating: iv.average_rating || 4.5,
      reviews: iv.review_count || Math.floor(Math.random() * 50),
      avatar: iv.utilisateur?.url || `https://ui-avatars.com/api/?name=${iv.utilisateur?.prenom}+${iv.utilisateur?.nom}&background=random`
    }
  })
}
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

/* Leaflet Custom overrides */
.avatar-container {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: hidden;
  background: white;
  position: absolute;
  top: -54px;
  left: -24px;
  z-index: 2;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.custom-marker-icon:hover .avatar-container {
  transform: scale(1.15) translateY(-8px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
  border-color: #0f172a; /* Slate 900 */
}
.marker-pin {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  position: absolute;
  top: 0;
  left: -5px;
  box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.15); /* Slate shadow */
  z-index: 1;
}

/* Tooltip Customization */
:deep(.leaflet-popup-content-wrapper),
:deep(.leaflet-tooltip) {
  border-radius: 12px;
  border: none;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  padding: 0;
}
:deep(.leaflet-tooltip-top:before) {
  border-top-color: white;
}
</style>
