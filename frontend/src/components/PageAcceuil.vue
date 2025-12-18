```
<template>
  <div class="space-y-8 animate-fade-in">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
      <div>
        <h2 class="text-3xl font-bold" style="color: #2f4f4f">
          Bonjour, <span style="color: #1a5fa3">{{ currentUser.name }}</span> 👋
        </h2>
        <p class="text-gray-500 mt-2">
          Voici ce qui se passe avec vos services aujourd'hui.
        </p>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white p-5 rounded-2xl shadow-sm border-2 hover:shadow-md transition-shadow group" style="border-color: #1a5fa3">
        <div class="flex justify-between items-start mb-2">
          <div class="p-2 rounded-lg transition-colors" style="background-color: #e8f0fe; color: #1a5fa3">
            <Clock :size="20" />
          </div>
          <span class="text-xs font-semibold px-2 py-1 rounded-full" style="background-color: #e8f0fe; color: #1a5fa3">+2 this week</span>
        </div>
        <div class="text-3xl font-bold text-gray-800">{{ stats.pending }}</div>
        <div class="text-sm font-medium" style="color: #6c757d">En attente</div>
      </div>
      
      <div class="bg-white p-5 rounded-2xl shadow-sm border-2 hover:shadow-md transition-shadow group" style="border-color: #92b08b">
        <div class="flex justify-between items-start mb-2">
          <div class="p-2 rounded-lg transition-colors" style="background-color: #f0f7ef; color: #92b08b">
            <Calendar :size="20" />
          </div>
        </div>
        <div class="text-3xl font-bold text-gray-800">{{ stats.accepted }}</div>
        <div class="text-sm font-medium" style="color: #6c757d">Programmées</div>
      </div>

      <div class="bg-white p-5 rounded-2xl shadow-sm border-2 hover:shadow-md transition-shadow group" style="border-color: #b19dd8">
        <div class="flex justify-between items-start mb-2">
          <div class="p-2 rounded-lg transition-colors" style="background-color: #f7f4fc; color: #8e7cc3">
            <Activity :size="20" />
          </div>
        </div>
        <div class="text-3xl font-bold text-gray-800">{{ stats.inProgress }}</div>
        <div class="text-sm font-medium" style="color: #6c757d">En cours</div>
      </div>

      <div class="bg-white p-5 rounded-2xl shadow-sm border-2 hover:shadow-md transition-shadow group" style="border-color: #e8793f">
        <div class="flex justify-between items-start mb-2">
          <div class="p-2 rounded-lg transition-colors" style="background-color: #fff4e5; color: #e8793f">
            <CheckCircle :size="20" />
          </div>
        </div>
        <div class="text-3xl font-bold text-gray-800">{{ stats.completed }}</div>
        <div class="text-sm font-medium" style="color: #6c757d">Terminées</div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!-- Main Column -->
      <div class="lg:col-span-2 space-y-8">
        
        <!-- Upcoming Appointment Card -->
        <div 
          v-if="upcomingIntervention" 
          class="rounded-2xl p-6 text-white shadow-lg relative overflow-hidden"
          style="background: linear-gradient(135deg, #1a5fa3 0%, #144f8c 100%)"
        >
          <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-16 -mt-16 pointer-events-none"></div>
          
          <div class="flex justify-between items-start relative z-10">
            <div>
              <div class="uppercase tracking-wider text-xs font-semibold opacity-80 mb-1">Prochain Rendez-vous</div>
              <h3 class="text-2xl font-bold mb-4">{{ upcomingIntervention.taskName }}</h3>
              
              <div class="flex items-center gap-6 text-sm">
                <div class="flex items-center gap-2 bg-white/20 px-3 py-1.5 rounded-lg backdrop-blur-sm">
                  <Calendar :size="16" />
                  <span>{{ formatDate(upcomingIntervention.date) }}</span>
                </div>
                <div class="flex items-center gap-2 bg-white/20 px-3 py-1.5 rounded-lg backdrop-blur-sm">
                  <Clock :size="16" />
                  <span>{{ upcomingIntervention.time || '09:00' }}</span>
                </div>
              </div>
            </div>
            
            <div class="bg-white/10 p-4 rounded-xl backdrop-blur-md flex flex-col items-center min-w-[100px]">
               <div class="w-12 h-12 rounded-full bg-white p-0.5 mb-2 overflow-hidden shadow-inner">
                 <img :src="upcomingIntervention.intervenantAvatar" class="w-full h-full object-cover rounded-full" />
               </div>
               <span class="font-bold text-sm text-center block">{{ upcomingIntervention.intervenantName }}</span>
               <span class="text-xs opacity-75">{{ upcomingIntervention.serviceName }}</span>
            </div>
          </div>
        </div>

        <!-- Map Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
            <div>
              <h3 class="text-xl font-bold flex items-center gap-2" style="color: #2f4f4f">
                <MapPin :size="20" style="color: #1a5fa3" />
                Intervenants à proximité
              </h3>
              <p class="text-sm text-gray-500 mt-1">Trouvez rapidement un pro près de chez vous</p>
            </div>
            
            <select 
              v-model="selectedServiceId" 
              @change="onFilterChange"
              class="px-4 py-2 bg-gray-50 border-transparent rounded-xl text-sm font-medium transition-all cursor-pointer hover:bg-gray-100 focus:ring-2 focus:ring-blue-200"
            >
              <option value="">Tous les services</option>
              <option v-for="s in services" :key="s.id" :value="s.id">
                {{ s.nom_service }}
              </option>
            </select>
          </div>

          <div class="h-[400px] rounded-xl overflow-hidden shadow-inner border border-gray-100 relative z-0">
            <l-map v-if="mapReady" ref="map" v-model:zoom="zoom" :center="center" :use-global-leaflet="false">
              <l-tile-layer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                layer-type="base"
                name="OpenStreetMap"
              ></l-tile-layer>
              
              <!-- User -->
              <l-marker :lat-lng="userLocation">
                <l-icon
                   :icon-url="'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png'"
                   :shadow-url="'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png'"
                   :icon-size="[25, 41]"
                   :icon-anchor="[12, 41]"
                   :popup-anchor="[1, -34]"
                   :shadow-size="[41, 41]"
                />
              </l-marker>

              <!-- Intervenants -->
              <l-marker 
                v-for="iv in mappedIntervenants" 
                :key="iv.id" 
                :lat-lng="iv.coords"
              >
                <l-icon class-name="custom-marker-icon">
                  <div class="marker-pin" style="background-color: #1a5fa3"></div>
                  <div class="avatar-container">
                    <img :src="iv.avatar" class="avatar-img" />
                  </div>
                </l-icon>
                <l-tooltip :options="{ offset: [0, -20], direction: 'top' }" class="custom-tooltip">
                  <div class="p-2 min-w-[150px]">
                    <div class="font-bold text-gray-800 text-base mb-1">{{ iv.name }}</div>
                    <div class="text-xs font-medium uppercase tracking-wide mb-2" style="color: #1a5fa3">{{ iv.profession }}</div>
                    <div class="flex items-center gap-3 text-sm border-t pt-2 mt-1">
                       <span class="flex items-center gap-1 font-bold text-yellow-500">
                         <Star :size="12" class="fill-current" /> {{ iv.rating }}
                       </span>
                       <span class="text-gray-400">({{ iv.reviews }} avis)</span>
                    </div>
                  </div>
                </l-tooltip>
              </l-marker>
            </l-map>
          </div>
        </div>

      </div>

      <!-- Sidebar Column -->
      <div class="space-y-8">
        
        <!-- Recent Activity Feed -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h3 class="font-bold mb-6 flex items-center gap-2" style="color: #2f4f4f">
            <Bell :size="20" style="color: #e8793f" />
            Activités Récentes
          </h3>
          
          <div class="space-y-6 relative">
            <!-- Vertical Line -->
            <div class="absolute left-[19px] top-2 bottom-4 w-0.5 bg-gray-100"></div>

            <div v-if="loadingActivity" class="flex justify-center py-4">
               <div class="animate-spin rounded-full h-6 w-6 border-b-2" style="border-color: #1a5fa3"></div>
            </div>

            <div v-else-if="activities.length === 0" class="text-sm text-gray-400 pl-4">
               Aucune activité récente.
            </div>

            <div 
              v-for="(activity, index) in activities" 
              :key="index"
              class="relative flex gap-4 group"
            >
              <div 
                class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 border-4 border-white z-10 shadow-sm"
                :style="getActivityStyle(activity.type)"
              >
                <component :is="getActivityIcon(activity.type)" :size="16" class="text-white" />
              </div>
              <div class="pt-1">
                <p class="text-sm font-semibold text-gray-800">{{ activity.title }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ activity.date }} • {{ activity.time }}</p>
                <p class="text-xs text-gray-600 mt-2 bg-gray-50 p-2 rounded-lg" v-if="activity.details">
                  {{ activity.details }}
                </p>
              </div>
            </div>
            
            <button class="w-full mt-4 py-2 text-sm font-medium transition-colors hover:underline" style="color: #1a5fa3">
              Voir tout l'historique
            </button>
          </div>
        </div>

        <!-- Promo / Featured Service -->
        <div class="rounded-2xl p-6 text-white text-center shadow-lg relative overflow-hidden" 
             style="background: linear-gradient(135deg, #2f4f4f 0%, #1a2f2f 100%)">
           <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
           <div class="relative z-10">
             <div class="inline-block p-3 bg-white/10 rounded-full mb-4 backdrop-blur-sm">
               <Sparkles :size="24" style="color: #fee347" />
             </div>
             <h3 class="font-bold text-xl mb-2">Service Premium</h3>
             <p class="text-gray-300 text-sm mb-6">Obtenez 10% de réduction sur votre premier nettoyage complet ce mois-ci.</p>
             <button class="w-full py-2.5 bg-white font-bold rounded-xl hover:bg-gray-100 transition-colors" style="color: #2f4f4f">
               En profiter
             </button>
           </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import "leaflet/dist/leaflet.css";
console.log('Component loading...');
import { LMap, LTileLayer, LMarker, LPopup, LTooltip, LIcon } from "@vue-leaflet/vue-leaflet";
import intervenantService from '@/services/intervenantService';
import serviceService from '@/services/serviceService';
import { demandService } from '@/services/demandService';
import { 
  Clock, Calendar, Activity, CheckCircle, MapPin, Star, Bell, 
  Sparkles, FileText, Check, AlertCircle 
} from 'lucide-vue-next';

export default {
  name: 'PageAcceuil',
  components: {
    LMap, LTileLayer, LMarker, LPopup, LTooltip, LIcon,
    Clock, Calendar, Activity, CheckCircle, MapPin, Star, Bell, Sparkles, FileText, Check, AlertCircle
  },
  props: {
    currentUser: { type: Object, required: true },
    stats: { type: Object, required: true }
  },
  data() {
    return {
      // Map State
      zoom: 13,
      center: [35.57845, -5.36837],
      userLocation: [35.57845, -5.36837],
      
      // Data
      services: [],
      intervenants: [],
      mappedIntervenants: [],
      activities: [],
      upcomingIntervention: null,
      
      // UI State
      loading: false,
      loadingActivity: false,
      selectedServiceId: '',
      mapReady: false,
    };
  },
  async mounted() {
    console.log('PageAcceuil mounted');
    // Ensure DOM is ready before initializing map
    this.$nextTick(() => {
      this.mapReady = true;
    });
    this.getUserLocation();
    await Promise.all([
      this.fetchServices(),
      this.fetchIntervenants(),
      this.fetchClientActivity()
    ]);
  },
  methods: {
    getUserLocation() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          (pos) => {
            this.userLocation = [pos.coords.latitude, pos.coords.longitude];
            this.center = this.userLocation;
            this.generateMarkers();
          },
          (err) => console.warn("Loc err:", err)
        );
      }
    },
    
    async fetchServices() {
      try {
        const res = await serviceService.getAll();
        this.services = res.data || [];
      } catch (e) { console.error(e); }
    },
    
    async fetchIntervenants() {
      try {
        const params = { per_page: 50, active: true };
        if (this.selectedServiceId) params.service_id = this.selectedServiceId;
        const res = await intervenantService.getAllIntervenants(params);
        this.intervenants = res.data || [];
        this.generateMarkers();
      } catch (e) { console.error(e); }
    },
    
    async fetchClientActivity() {
      this.loadingActivity = true;
      try {
        const demands = await demandService.getClientDemands(this.currentUser.id);
        
        // 1. Process Upcoming
        const upcoming = demands
          .filter(d => ['acceptée', 'en cours'].includes(d.parametre_status?.label?.toLowerCase() || d.status?.toLowerCase()))
          .sort((a, b) => new Date(a.date) - new Date(b.date))
          .find(d => new Date(d.date) >= new Date().setHours(0,0,0,0));
            
        if (upcoming) {
          this.upcomingIntervention = {
             taskName: upcoming.title || 'Service',
             date: upcoming.date,
             time: upcoming.time || '14:00',
             serviceName: upcoming.service || 'Service à domicile',
             intervenantName: upcoming.intervenant?.name || 'Intervenant assigné',
             intervenantAvatar: upcoming.intervenant?.image || `https://ui-avatars.com/api/?name=${upcoming.intervenant?.name || 'I'}&background=random`
          };
        }

        // 2. Process Recent Activity
        this.activities = demands.slice(0, 5).map(d => {
          let type = 'info';
          let title = 'Mise à jour';
          const status = (d.parametre_status?.label || d.status || '').toLowerCase();
          
          if (status === 'en attente') { type = 'created'; title = 'Nouvelle demande envoyée'; }
          else if (status === 'acceptée') { type = 'success'; title = 'Demande acceptée'; }
          else if (status === 'terminée') { type = 'completed'; title = 'Intervention terminée'; }
          else if (status === 'annulée') { type = 'warning'; title = 'Demande annulée'; }
          
          return {
            type,
            title,
            date: new Date(d.date).toLocaleDateString(),
            time: d.time || '',
            details: d.title
          };
        });
        
      } catch (e) {
        console.error("Error fetching activity", e);
      } finally {
        this.loadingActivity = false;
      }
    },
    
    generateMarkers() {
      this.mappedIntervenants = this.intervenants.map((iv) => {
        const seed = iv.id * 123.45; 
        const offsetLat = (Math.sin(seed) * 0.02);
        const offsetLng = (Math.cos(seed) * 0.02);
        return {
          ...iv,
          coords: [this.center[0] + offsetLat, this.center[1] + offsetLng],
          name: `${iv.utilisateur?.prenom || ''} ${iv.utilisateur?.nom || ''}`,
          profession: iv.taches?.[0]?.service?.nom_service || 'Pro',
          rating: iv.average_rating || 4.5,
          reviews: iv.review_count || 12,
          avatar: iv.utilisateur?.url || `https://ui-avatars.com/api/?name=${iv.utilisateur?.prenom}+${iv.utilisateur?.nom}&background=random&color=fff`
        };
      });
    },
    
    onFilterChange() { this.fetchIntervenants(); },
    
    getActivityStyle(type) {
      const map = {
        created: 'background-color: #1a5fa3',
        success: 'background-color: #92b08b',
        completed: 'background-color: #e8793f',
        warning: 'background-color: #dc3545',
        info: 'background-color: #6c757d'
      };
      return map[type] || map.info;
    },
    
    getActivityIcon(type) {
      const map = {
        created: 'FileText',
        success: 'Check',
        completed: 'Sparkles',
        warning: 'AlertCircle',
        info: 'Activity'
      };
      return map[type] || map.info;
    },
    
    formatDate(d) {
      return new Date(d).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
    }
  }
};
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.6s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

/* Leaflet Custom overrides */
.avatar-container {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: 4px solid white;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  overflow: hidden;
  background: white;
  position: absolute;
  top: -48px;
  left: -22px;
  z-index: 2;
  transition: transform 0.2s ease;
}
.custom-marker-icon:hover .avatar-container {
  transform: scale(1.1) translateY(-5px);
  border-color: #1a5fa3;
}
.marker-pin {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  position: absolute;
  top: 0;
  left: -6px;
  box-shadow: 0 0 0 4px rgba(26, 95, 163, 0.2);
  z-index: 1;
}
</style>
```
