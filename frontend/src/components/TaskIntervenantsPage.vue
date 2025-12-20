<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Content (affiché immédiatement) -->
    <div v-if="!error">
      <!-- Header (Non-sticky) -->
      <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <!-- Back button and Title row -->
          <div class="flex items-center justify-between gap-6 mb-4">
            <div class="flex items-center gap-4">
              <button
                @click="$emit('back')"
                @mouseenter="hoverBackButton = true"
                @mouseleave="hoverBackButton = false"
                class="flex items-center justify-center w-10 h-10 rounded-full transition-all duration-300 hover:scale-110 shadow-sm"
                :style="{ 
                  backgroundColor: hoverBackButton ? currentService.color : 'white',
                  color: hoverBackButton ? 'white' : '#4B5563',
                  border: `1px solid ${hoverBackButton ? currentService.color : '#E5E7EB'}`
                }"
              >
                <ArrowLeft :size="20" />
              </button>
              <div v-if="taskData">
                <h1 class="text-3xl md:text-4xl font-bold tracking-tight" :style="{ color: currentService.color }">
                  {{ taskData.nom_tache }}
                </h1>
              </div>
              <!-- Skeleton Loader -->
              <div v-else class="animate-pulse">
                <div class="h-8 bg-gray-200 rounded-lg w-64"></div>
              </div>
            </div>

            <!-- View Toggle & Controls -->
            <div class="flex items-center gap-3">
              <!-- View Toggle Buttons -->
              <div class="flex items-center gap-1 bg-white rounded-full border border-gray-200 shadow-sm p-1">
                <button
                  @click="viewMode = 'list'"
                  class="px-4 py-2 rounded-full text-sm font-semibold transition-all duration-300 flex items-center gap-2"
                  :style="viewMode === 'list' 
                    ? { backgroundColor: currentService.color, color: 'white' }
                    : { color: '#6B7280' }"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                  </svg>
                  Liste
                </button>
                <button
                  @click="viewMode = 'map'"
                  class="px-4 py-2 rounded-full text-sm font-semibold transition-all duration-300 flex items-center gap-2"
                  :style="viewMode === 'map' 
                    ? { backgroundColor: currentService.color, color: 'white' }
                    : { color: '#6B7280' }"
                >
                  <MapPin :size="16" />
                  Carte
                </button>
              </div>

              <!-- Near Me Button -->
              <button
                v-if="viewMode === 'map'"
                @click="sortByDistance"
                class="px-5 py-2.5 rounded-full text-sm font-bold text-white transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 flex items-center gap-2"
                :style="{ backgroundColor: currentService.color }"
              >
                <MapPin :size="16" />
                Près de moi
              </button>

              <!-- Sorting Dropdown -->
              <div class="group relative flex items-center gap-2 bg-white px-4 py-2 rounded-full border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                <span class="text-gray-400 font-medium text-xs uppercase tracking-wide">Trier par</span>
                <select
                  v-model="sortBy"
                  @change="handleSortChange"
                  class="bg-transparent border-none font-bold text-gray-700 text-sm focus:ring-0 cursor-pointer outline-none pl-0 pr-6 appearance-none"
                  style="background-image: none;"
                >
                  <option value="pertinence">Pertinence</option>
                  <option value="rating">Note (Meilleure)</option>
                  <option value="price-asc">Prix (Croissant)</option>
                  <option value="price-desc">Prix (Décroissant)</option>
                  <option value="distance" v-if="userLocation">Distance</option>
                </select>
                <div class="absolute right-3 pointer-events-none text-gray-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Counter row -->
          <div class="flex items-center gap-2 ml-14" v-if="taskData">
            <span class="inline-flex items-center justify-center bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-full">
              {{ viewMode === 'map' && userLocation ? nearbyIntervenants.length : sortedIntervenants.length }}
            </span>
            <p class="text-gray-500 font-medium text-sm">
              <template v-if="viewMode === 'map' && userLocation">
                intervenant{{ nearbyIntervenants.length > 1 ? 's' : '' }} à proximité (≤20 km)
              </template>
              <template v-else>
                intervenant{{ sortedIntervenants.length > 1 ? 's' : '' }} disponible{{ sortedIntervenants.length > 1 ? 's' : '' }}
              </template>
            </p>
          </div>
          <div v-else class="animate-pulse ml-14">
            <div class="h-4 bg-gray-200 rounded-lg w-48"></div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Map View -->
        <div v-if="viewMode === 'map'" class="mb-8">
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="h-[600px] relative">
              <!-- Message si aucun intervenant proche -->
              <div v-if="userLocation && mappedIntervenants.length === 0 && !loadingLocation && intervenantsLoaded" class="absolute inset-0 flex items-center justify-center z-50 bg-white/95">
                <div class="text-center p-8 bg-white rounded-2xl shadow-xl border-2 max-w-md mx-4" :style="{ borderColor: currentService.color }">
                  <MapPin :size="64" class="mx-auto mb-4" :style="{ color: currentService.color }" />
                  <h3 class="text-2xl font-bold mb-2" :style="{ color: currentService.color }">Aucun intervenant à proximité</h3>
                  <p class="text-gray-600 mb-4">Aucun intervenant n'a été trouvé dans un rayon de 20 km de votre position.</p>
                  <p class="text-sm text-gray-500 mb-6">Essayez de changer votre localisation ou consultez la liste complète des intervenants.</p>
                  <button
                    @click="viewMode = 'list'"
                    class="px-6 py-3 rounded-lg text-white font-semibold transition-all hover:shadow-lg hover:scale-105"
                    :style="{ backgroundColor: currentService.color }"
                  >
                    Voir tous les intervenants
                  </button>
                </div>
              </div>

              <l-map 
                v-if="mapReady" 
                ref="map" 
                v-model:zoom="zoom" 
                :center="mapCenter" 
                :use-global-leaflet="false"
                class="w-full h-full"
              >
                <l-tile-layer
                  url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                  layer-type="base"
                  name="OpenStreetMap"
                ></l-tile-layer>
                
                <!-- User Location Marker -->
                <l-marker v-if="userLocation" :lat-lng="userLocation">
                  <l-icon
                    :icon-url="'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png'"
                    :shadow-url="'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png'"
                    :icon-size="[25, 41]"
                    :icon-anchor="[12, 41]"
                    :popup-anchor="[1, -34]"
                    :shadow-size="[41, 41]"
                  />
                  <l-popup>
                    <div class="font-bold text-red-600">Votre position</div>
                  </l-popup>
                </l-marker>

                <!-- Intervenants Markers (seulement si localisation disponible et intervenants proches) -->
                <template v-if="userLocation">
                  <l-marker 
                    v-for="iv in mappedIntervenants" 
                    :key="iv.id" 
                    :lat-lng="iv.coords"
                  >
                  <l-icon class-name="custom-marker-icon">
                    <div class="marker-pin" :style="{ backgroundColor: currentService.color }"></div>
                    <div class="avatar-container">
                      <img :src="iv.image" class="avatar-img" />
                    </div>
                  </l-icon>
                  <l-tooltip :options="{ offset: [0, -20], direction: 'top' }" class="custom-tooltip">
                    <div class="p-3 min-w-[200px]">
                      <div class="font-bold text-gray-800 text-base mb-1">{{ iv.name }}</div>
                      <div class="text-xs font-medium uppercase tracking-wide mb-2" :style="{ color: currentService.color }">
                        {{ taskData?.nom_tache || 'Service' }}
                      </div>
                      <div class="flex items-center gap-3 text-sm border-t pt-2 mt-2">
                        <span class="flex items-center gap-1 font-bold text-yellow-500">
                          <Star :size="12" class="fill-current" /> {{ iv.rating }}
                        </span>
                        <span class="text-gray-400">({{ iv.reviews }} avis)</span>
                        <span v-if="iv.distance" class="text-gray-600 font-medium">
                          📍 {{ iv.distance }} km
                        </span>
                      </div>
                      <div class="mt-2 pt-2 border-t">
                        <span class="text-sm font-bold" :style="{ color: currentService.color }">
                          {{ iv.taskPrice ? iv.taskPrice + ' DH/h' : 'Sur devis' }}
                        </span>
                      </div>
                    </div>
                  </l-tooltip>
                  <l-popup>
                    <div class="p-2">
                      <div class="font-bold mb-1">{{ iv.name }}</div>
                      <button
                        @click="viewProfile(iv)"
                        class="mt-2 px-3 py-1 text-xs rounded text-white"
                        :style="{ backgroundColor: currentService.color }"
                      >
                        Voir profil
                      </button>
                    </div>
                  </l-popup>
                  </l-marker>
                </template>
              </l-map>
              
              <!-- Loading Overlay -->
              <div v-if="!mapReady || loadingLocation" class="absolute inset-0 bg-white/95 flex items-center justify-center z-50">
                <div class="text-center p-8 bg-white rounded-2xl shadow-lg border-2" :style="{ borderColor: currentService.color }">
                  <div class="animate-spin rounded-full h-12 w-12 border-b-2 mx-auto mb-4" :style="{ borderColor: currentService.color }"></div>
                  <p class="text-gray-600 font-medium mb-2">Chargement de la carte...</p>
                  <p v-if="loadingLocation && userLocation" class="text-sm text-gray-500">Géocodage des adresses en cours...</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- List View -->
        <div v-else>
          <!-- Intervenants Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 gap-8">
          <div
            v-for="intervenant in paginatedIntervenants"
            :key="intervenant.id"
            class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden flex flex-col"
          >
            <div class="p-8 flex-1 flex flex-col">
              <!-- Top Section: Image & Main Info -->
              <div class="flex gap-6 mb-6">
                <div class="relative">
                  <div class="w-24 h-24 rounded-2xl overflow-hidden shadow-lg border-2" :style="{ borderColor: currentService.color + '40' }">
                    <img
                      :src="intervenant.image"
                      :alt="intervenant.name"
                      class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-110"
                      @error="handleImageError"
                    />
                  </div>
                </div>

                  <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start mb-2">
                      <h3 class="text-xl font-bold text-gray-900 truncate pr-2">{{ intervenant.name }}</h3>
                      <div class="flex flex-col items-end gap-1">
                        <div class="flex items-center gap-1 bg-yellow-50 px-2 py-1 rounded-lg border border-yellow-100">
                           <Star :size="14" class="fill-yellow-400 text-yellow-400" />
                           <span class="font-bold text-gray-800 text-sm">{{ intervenant.rating }}</span>
                        </div>
                        <span class="text-gray-500 text-xs">{{ intervenant.reviewCount }} avis</span>
                      </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-x-4 gap-y-2 mb-3">
                      <div class="flex items-center gap-1.5 text-sm text-gray-500 font-medium">
                        <Briefcase :size="14" :style="{ color: currentService.color }" />
                        <span>{{ intervenant.interv_count || 0 }} missions</span>
                      </div>
                      <div class="flex items-center gap-1.5 text-sm text-gray-500 font-medium">
                        <MapPin :size="14" :style="{ color: currentService.color }" />
                        <span>{{ intervenant.location }}</span>
                      </div>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-100 mb-4">
                      {{ formatExperience(intervenant.experience) }} d'expérience
                    </div>
                  </div>
              </div>

              <!-- Badges & Availability -->
              <div class="mb-6 space-y-3">
              <p class="text-xs text-gray-500 mb-2"> Autres spécialités :</p>
                 <div class="flex flex-wrap gap-2">
                    <span 
                      v-for="(specialty, index) in (intervenant.otherSpecialties || []).slice(0, 5)"
                      :key="index"
                      class="px-2.5 py-1 rounded-md text-xs font-medium transition-colors"
                      :style="{ 
                        backgroundColor: (serviceIdToColor[specialty.serviceId] || currentService.color) + '10', 
                        color: serviceIdToColor[specialty.serviceId] || currentService.color 
                      }"
                    >
                      {{ specialty.name }}
                    </span>
                    <span v-if="(intervenant.otherSpecialties || []).length > 5" class="px-2 py-1 rounded-md text-xs font-medium bg-gray-50 text-gray-400">
                      +{{ (intervenant.otherSpecialties || []).length - 5 }}
                    </span>
                 </div>


              </div>
              
              <div class="h-px bg-gray-100 w-full mb-6"></div>

              <!-- Price & Actions -->
              <div class="flex items-end justify-between gap-4 mt-auto">
                <div>
                   <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider mb-1">Tarif horaire</p>
                   <div class="flex items-baseline gap-1">
                      <span class="text-2xl font-bold text-gray-900" v-if="intervenant.taskPrice">{{ intervenant.taskPrice }}</span>
                      <span class="text-2xl font-bold text-gray-900" v-else>--</span>
                      <span class="text-sm text-gray-500 font-medium" v-if="intervenant.taskPrice">DH/h</span>
                      <span class="text-sm text-gray-500 font-medium" v-else>Sur devis</span>
                   </div>
                </div>

                <div class="flex gap-2 w-1/2">
                   <button
                    @click="viewProfile(intervenant)"
                    class="flex-1 h-12 flex items-center justify-center rounded-xl border-2 transition-all duration-300 hover:bg-gray-50 font-bold text-sm"
                    :style="{ 
                      borderColor: currentService.color + '40',
                      color: currentService.color
                    }"
                  >
                    Profil
                  </button>
                  <button
                    @click="handleReserve(intervenant)"
                    class="flex-1 h-12 flex items-center justify-center rounded-xl text-white transition-all duration-300 shadow-lg shadow-gray-200 hover:shadow-xl hover:-translate-y-0.5 font-bold text-sm gap-2"
                    :style="{ backgroundColor: currentService.color }"
                  >
                    Réserver
                  </button>
                </div>
              </div>

            </div>
          </div>
        </div>



          <!-- Pagination Controls -->
          <div v-if="totalPages > 1" class="flex justify-center mt-12 gap-2">
            <button
              @click="currentPage > 1 && (currentPage--)"
              :disabled="currentPage === 1"
              class="px-4 py-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
            >
              Précédent
            </button>
            
            <button
              v-for="page in totalPages"
              :key="page"
              @click="currentPage = page"
              class="w-10 h-10 rounded-lg border transition-colors flex items-center justify-center font-medium"
              :style="{
                backgroundColor: currentPage === page ? currentService.color : 'transparent',
                borderColor: currentPage === page ? currentService.color : '#D1D5DB',
                color: currentPage === page ? 'white' : '#374151'
              }"
            >
              {{ page }}
            </button>
            
            <button
              @click="currentPage < totalPages && (currentPage++)"
              :disabled="currentPage === totalPages"
              class="px-4 py-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
            >
              Suivant
            </button>
          </div>

        <!-- No results -->
        <div v-if="intervenantsLoaded && !loading && sortedIntervenants.length === 0" class="text-center py-16">
          <div class="text-6xl mb-4">🔍</div>
          <h3 class="text-2xl font-bold mb-2">Aucun intervenant trouvé</h3>
          <p class="text-gray-600 mb-6">Aucun intervenant n'est disponible pour ce sous-service pour le moment.</p>
          <button
            @click="$emit('back')"
            class="px-6 py-3 rounded-lg text-white transition-all hover:opacity-90"
            :style="{ backgroundColor: currentService.color }"
          >
            Retour
          </button>
        </div>
        </div>
        <!-- End List View -->
      </div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="text-6xl mb-4">⚠️</div>
        <h3 class="text-2xl font-bold mb-2 text-red-600">Erreur</h3>
        <p class="text-gray-600 mb-6">{{ error }}</p>
        <button
          @click="loadData"
          class="px-6 py-3 rounded-lg text-white transition-all hover:opacity-90"
          style="background-color: #EF4444;"
        >
          Réessayer
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPopup, LTooltip, LIcon } from "@vue-leaflet/vue-leaflet";
import { ArrowLeft, Star, MapPin, Clock, Calendar, CheckCircle, Briefcase } from 'lucide-vue-next';
import intervenantService from '@/services/intervenantService';
import serviceService from '@/services/serviceService';
import { formatExperience } from '@/utils/experienceFormatter';
import authService from '@/services/authService';

export default {
  name: 'TaskIntervenantsPage',
  components: {
    LMap,
    LTileLayer,
    LMarker,
    LPopup,
    LTooltip,
    LIcon,
    ArrowLeft,
    Star,
    MapPin,
    Clock,
    Calendar,
    CheckCircle,
    Briefcase
  },
  props: {
    taskId: {
      type: [String, Number],
      required: true
    },
    taskName: {
      type: String,
      default: 'Sous-service'
    },
    serviceId: {
      type: [String, Number],
      required: true
    }
  },
    emits: ['back', 'view-profile', 'login-required', 'navigate-booking'], // Added login-required and navigate-booking
  data() {
    return {
      citySearch: '',
      sortBy: 'pertinence',
      hoverBackButton: false,
      loading: true,
      error: null,
      taskData: null,
      serviceData: null,
      intervenantsFromApi: [],
      intervenantsLoaded: false,
      serviceColors: {
        'Jardinage': '#92B08B',
        'Ménage': '#4682B4',
      },
      serviceIdToColor: {
        1: '#92B08B',
        2: '#4682B4',
      },
      serviceIdToName: {
        1: 'Jardinage',
        2: 'Ménage',
      },
      currentPage: 1,
      itemsPerPage: 4,
      // Map & Location
      viewMode: 'list', // 'list' or 'map'
      mapReady: false,
      zoom: 13,
      mapCenter: [35.57845, -5.36837], // Default center (Tanger, Morocco)
      userLocation: null,
      loadingLocation: false,
      mappedIntervenants: [],
    };
  },
  mounted() {
    this.loadData();
    this.initializeMap();
    this.getUserLocation();
  },
  watch: {
    taskId() {
      this.loadData();
    },
    serviceId() {
      this.loadData();
    }
  },
  computed: {
    currentService() {
      if (this.serviceData) {
        const serviceName = this.serviceData.nom_service;
        const color = this.serviceColors[serviceName] || '#6B7280';
        return {
          name: serviceName,
          color: color,
        };
      }
      
      let serviceName = 'Service';
      let color = '#6B7280';
      
      if (typeof this.serviceId === 'number') {
        serviceName = this.serviceIdToName[this.serviceId] || 'Service';
        color = this.serviceIdToColor[this.serviceId] || '#6B7280';
      }
      
      return {
        name: serviceName,
        color: color,
      };
    },
    taskPrice() {
      if (!this.intervenantsFromApi || this.intervenantsFromApi.length === 0) return null;
      const prices = this.intervenantsFromApi
        .map(i => i.taskPrice)
        .filter(p => p && p > 0);
      if (prices.length === 0) return null;
      const avg = prices.reduce((a, b) => a + b, 0) / prices.length;
      return Math.round(avg);
    },
    filteredIntervenants() {
      const intervenants = this.intervenantsFromApi || [];
      
      if (!intervenants || intervenants.length === 0) {
        return [];
      }
      
      return intervenants.filter((intervenant) => {
        const searchText = this.citySearch.trim().toLowerCase();
        let matchesSearch = true;
        if (searchText) {
          const intervenantName = (intervenant.name || '').toLowerCase();
          const intervenantLocation = (intervenant.location || '').toLowerCase();
          matchesSearch = intervenantName.includes(searchText) || 
                         intervenantLocation.includes(searchText);
        }
        
        return matchesSearch;
      });
    },
    sortedIntervenants() {
      const sorted = [...this.filteredIntervenants];
      if (this.sortBy === 'rating') return sorted.sort((a, b) => b.rating - a.rating);
      if (this.sortBy === 'price-asc') return sorted.sort((a, b) => (a.taskPrice || 0) - (b.taskPrice || 0));
      if (this.sortBy === 'price-desc') return sorted.sort((a, b) => (b.taskPrice || 0) - (a.taskPrice || 0));
      if (this.sortBy === 'distance' && this.userLocation) {
        return sorted.sort((a, b) => (a.distance || Infinity) - (b.distance || Infinity));
      }
      return sorted;
    },
    // Filter intervenants within 20km when in map view
    nearbyIntervenants() {
      if (this.viewMode !== 'map' || !this.userLocation) {
        return this.sortedIntervenants;
      }
      return this.sortedIntervenants.filter(intervenant => {
        return intervenant.distance !== undefined && intervenant.distance <= 20;
      });
    },
    totalPages() {
      return Math.ceil(this.sortedIntervenants.length / this.itemsPerPage);
    },
    paginatedIntervenants() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.sortedIntervenants.slice(start, end);
    }
  },
  methods: {
    async loadData() {
      this.loading = true;
      this.error = null;
      this.intervenantsLoaded = false;
      
      try {
        // Charger les données du service et de la tâche en parallèle
        await Promise.all([
          this.loadServiceInfo(),
          this.loadTaskInfo(),
          this.loadIntervenants()
        ]);
      } catch (err) {
        console.error('Erreur lors du chargement des données:', err);
        this.error = err.message || 'Impossible de charger les données. Veuillez réessayer.';
      } finally {
        this.loading = false;
        this.intervenantsLoaded = true;
      }
    },
    
    async loadServiceInfo() {
      try {
        const response = await serviceService.getById(this.serviceId);
        this.serviceData = response.data;
      } catch (error) {
        console.error('Erreur lors du chargement du service:', error);
        // Ne pas bloquer si le service ne charge pas
      }
    },
    
    async loadTaskInfo() {
      // Utiliser le nom de la tâche passé en prop
      console.log('TaskIntervenantsPage - taskName prop:', this.taskName);
      console.log('TaskIntervenantsPage - taskId prop:', this.taskId);
      this.taskData = {
        id: this.taskId,
        nom_tache: this.taskName || 'Sous-service',
        description: ''
      };
    },
    
    async loadIntervenants() {
      try {
        // Récupérer les intervenants filtrés par tacheId
        const params = { 
          active: 'true',
          tacheId: this.taskId
        };
        
        const response = await intervenantService.getAll(params);
        const intervenants = response.data || [];
        
        this.intervenantsFromApi = intervenants.map(intervenant => {
          const utilisateur = intervenant.utilisateur || {};
          const taches = intervenant.taches || [];
          
          // Trouver la tâche spécifique et son prix
          const currentTask = taches.find(t => t.id == this.taskId);
          // Récupérer le prix depuis la table pivot (prix_tache)
          // Si pas de prix défini en base, on laisse null pour afficher "Sur devis"
          const taskPrice = currentTask?.pivot?.prix_tache || currentTask?.pivot?.prixTache;
          
          // Autres spécialités (exclure la tâche actuelle)
          const otherSpecialties = taches
            .filter(t => t.id != this.taskId)
            .map(t => ({
              name: t.nom_tache || t.name || '',
              serviceId: t.service_id // Assumes service_id is available on the task object
            }))
            .filter(s => s.name);
          
          // Tarif horaire moyen pour les autres tâches
          let hourlyRate = 25;
          if (taches.length > 0) {
            const rates = taches
              .map(t => t.pivot?.prix_tache || t.pivot?.prixTache)
              .filter(Boolean);
            if (rates.length > 0) {
              hourlyRate = Math.round(rates.reduce((a, b) => a + b, 0) / rates.length);
            }
          }
          
          // Trouver l'expérience pour ce service spécifique
          const userServices = intervenant.services || [];
          const currentServiceInfo = userServices.find(s => s.id == this.serviceId);
          const realExperience = currentServiceInfo?.pivot?.experience || 'Pas';

          // Récupérer l'adresse complète de l'intervenant depuis la BD
          const address = intervenant.address || '';
          const ville = intervenant.ville || '';
          // Construire l'adresse complète pour le géocodage
          let fullAddress = '';
          if (address && ville) {
            fullAddress = `${address}, ${ville}, Morocco`;
          } else if (ville) {
            fullAddress = `${ville}, Morocco`;
          } else if (address) {
            fullAddress = `${address}, Morocco`;
          }
          
          return {
            id: intervenant.id,
            name: `${utilisateur.nom || ''} ${utilisateur.prenom || ''}`.trim() || 'Intervenant',
            rating: intervenant.average_rating || 0,
            reviewCount: intervenant.review_count || 0,
            interv_count: intervenant.interv_count || 0,
            hourlyRate: hourlyRate,
            taskPrice: taskPrice, // Prix spécifique pour cette tâche
            location: ville || address || 'Non spécifiée',
            address: fullAddress, // Adresse complète pour géocodage
            addressParts: {
              address: address,
              ville: ville
            },
            image: intervenant.image_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(utilisateur.prenom + ' ' + utilisateur.nom)}&background=random&color=fff`,
            verified: intervenant.is_active !== false,
            otherSpecialties: otherSpecialties,
            experience: realExperience,
            description: intervenant.bio || 'Professionnel expérimenté et fiable',
            coords: null, // Sera rempli par géocodage
            distance: null, // Sera calculé après géocodage
            fullData: {
              ...intervenant,
              experience: realExperience 
            }
          };
        });
        
      } catch (error) {
        console.error('Erreur lors du chargement des intervenants:', error);
        this.intervenantsFromApi = [];
      }
    },
    
    viewProfile(intervenant) {
      // Émettre l'événement avec toutes les données de l'intervenant
      this.$emit('view-profile', {
        id: intervenant.id,
        data: intervenant
      });
    },
    
    applySearch() {
      // La recherche est gérée par le computed filteredIntervenants
      // Cette méthode peut être utilisée pour des actions supplémentaires si nécessaire
    },
    
    handleImageError(event) {
      event.target.src = 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=150&h=150&fit=crop';
    },
    
      handleReserve(intervenant) {
        // Vérifier si l'utilisateur est connecté
        if (!authService.isAuthenticated()) {
          // Stocker les paramètres pour après connexion
          localStorage.setItem('booking_intervenantId', intervenant.id);
          localStorage.setItem('booking_serviceId', this.serviceId);
          localStorage.setItem('booking_taskId', this.taskId);
          localStorage.setItem('redirect_after_login', 'booking');
          this.$emit('login-required');
          return;
        }
        
        // Si connecté, rediriger vers la page de réservation
        this.$emit('navigate-booking', {
          intervenantId: intervenant.id,
          serviceId: this.serviceId,
          taskId: this.taskId
        });
      },
    
    // Map & Location Methods
    initializeMap() {
      this.$nextTick(() => {
        this.mapReady = true;
        // Si on est en mode carte, régénérer les marqueurs
        if (this.viewMode === 'map') {
          this.generateMapMarkers();
        }
      });
    },
    
    getUserLocation() {
      this.loadingLocation = true;
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          async (pos) => {
            this.userLocation = [pos.coords.latitude, pos.coords.longitude];
            this.mapCenter = this.userLocation;
            // Calculer les distances et générer les marqueurs (async)
            await this.calculateDistances();
          },
          (err) => {
            console.warn("Location error:", err);
            // Pas de localisation disponible - ne pas afficher de marqueurs
            this.userLocation = null;
            this.mapCenter = [35.57845, -5.36837];
            this.generateMapMarkers(); // Générera un tableau vide
            this.loadingLocation = false;
          }
        );
      } else {
        this.userLocation = null;
        this.loadingLocation = false;
        this.generateMapMarkers(); // Générera un tableau vide
      }
    },
    
    // Mapping des villes marocaines principales avec leurs coordonnées
    getCityCoordinates(ville) {
      if (!ville) return null;
      
      const cityMap = {
        'Casablanca': [33.5731, -7.5898],
        'Rabat': [34.0209, -6.8416],
        'Fes': [34.0331, -5.0003],
        'Fès': [34.0331, -5.0003],
        'Marrakech': [31.6295, -7.9811],
        'Tanger': [35.7595, -5.8340],
        'Agadir': [30.4278, -9.5981],
        'Meknes': [33.8950, -5.5547],
        'Oujda': [34.6810, -1.9086],
        'Kenitra': [34.2611, -6.5802],
        'Tetouan': [35.5711, -5.3724],
        'Safi': [32.2994, -9.2372],
        'Mohammedia': [33.6833, -7.3833],
        'Khouribga': [32.8810, -6.9063],
        'El Jadida': [33.2543, -8.5078],
        'Beni Mellal': [32.3373, -6.3498],
        'Nador': [35.1681, -2.9333],
        'Taza': [34.2144, -4.0088],
        'Settat': [33.0010, -7.6167],
        'Larache': [35.1911, -6.1547]
      };
      
      const normalizedVille = ville.trim();
      return cityMap[normalizedVille] || null;
    },
    
    async geocodeAddress(addressParts, delay = 0) {
      const { address, ville } = addressParts;
      
      if (!ville && !address) {
        return null;
      }
      
      // D'abord, essayer avec le mapping des villes principales
      if (ville) {
        const cityCoords = this.getCityCoordinates(ville);
        if (cityCoords) {
          console.log(`Coordonnées de ville trouvées pour "${ville}":`, cityCoords);
          return cityCoords;
        }
      }
      
      // Délai pour respecter les limites de l'API Nominatim (1 requête/seconde)
      if (delay > 0) {
        await new Promise(resolve => setTimeout(resolve, delay));
      }
      
      // Essayer d'abord avec l'adresse complète, puis avec la ville seule
      const addressesToTry = [];
      
      if (address && ville) {
        addressesToTry.push(`${address}, ${ville}, Morocco`);
        addressesToTry.push(`${ville}, Morocco`);
      } else if (ville) {
        addressesToTry.push(`${ville}, Morocco`);
      } else if (address) {
        addressesToTry.push(`${address}, Morocco`);
      }
      
      for (const addr of addressesToTry) {
        try {
          // Utiliser Nominatim (OpenStreetMap) pour géocoder l'adresse
          const response = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(addr)}&limit=1&countrycodes=ma`,
            {
              headers: {
                'User-Agent': 'ServicePro App' // Requis par Nominatim
              }
            }
          );
          
          if (!response.ok) {
            console.warn('Erreur HTTP lors du géocodage:', response.status);
            continue;
          }
          
          const data = await response.json();
          
          if (data && data.length > 0) {
            const coords = [
              parseFloat(data[0].lat),
              parseFloat(data[0].lon)
            ];
            console.log(`Géocodage réussi pour "${addr}":`, coords);
            return coords;
          }
        } catch (error) {
          console.error('Erreur de géocodage pour:', addr, error);
          continue;
        }
      }
      
      console.warn('Géocodage échoué pour:', addressParts);
      return null;
    },
    
    async calculateDistances() {
      if (!this.userLocation) {
        // Si pas de localisation, réinitialiser les distances
        this.intervenantsFromApi = this.intervenantsFromApi.map(intervenant => ({
          ...intervenant,
          distance: null,
          coords: null
        }));
        this.generateMapMarkers();
        return;
      }
      
      // Géocoder toutes les adresses et calculer les distances
      this.loadingLocation = true;
      
      // Géocoder séquentiellement pour respecter les limites de l'API (1 req/seconde)
      const results = [];
      for (let i = 0; i < this.intervenantsFromApi.length; i++) {
        const intervenant = this.intervenantsFromApi[i];
        
        // Si les coordonnées sont déjà calculées, les réutiliser
        if (intervenant.coords && intervenant.coords.length === 2) {
          const distance = this.haversineDistance(
            this.userLocation[0],
            this.userLocation[1],
            intervenant.coords[0],
            intervenant.coords[1]
          );
          results.push({
            ...intervenant,
            distance: Math.round(distance * 10) / 10
          });
          continue;
        }
        
        // Sinon, géocoder l'adresse (avec délai de 1 seconde entre chaque requête)
        const delay = i > 0 ? 1000 : 0; // Délai de 1 seconde sauf pour le premier
        const coords = await this.geocodeAddress(intervenant.addressParts || { address: intervenant.address, ville: intervenant.location }, delay);
        
        if (coords) {
          // Calculer la distance
          const distance = this.haversineDistance(
            this.userLocation[0],
            this.userLocation[1],
            coords[0],
            coords[1]
          );
          
          results.push({
            ...intervenant,
            coords: coords,
            distance: Math.round(distance * 10) / 10
          });
        } else {
          // Si le géocodage échoue, marquer comme non disponible pour la carte
          results.push({
            ...intervenant,
            coords: null,
            distance: null
          });
        }
      }
      
      // Mettre à jour les intervenants avec les coordonnées et distances calculées
      this.intervenantsFromApi = results;
      
      this.loadingLocation = false;
      
      // Regenerate map markers after calculating distances to apply 20km filter
      if (this.viewMode === 'map') {
        this.generateMapMarkers();
      }
    },
    
    haversineDistance(lat1, lon1, lat2, lon2) {
      const R = 6371; // Radius of the Earth in km
      const dLat = (lat2 - lat1) * Math.PI / 180;
      const dLon = (lon2 - lon1) * Math.PI / 180;
      const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon/2) * Math.sin(dLon/2);
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
      return R * c;
    },
    
    generateMapMarkers() {
      // Si la localisation du client n'est pas disponible, ne pas afficher de marqueurs
      if (!this.userLocation || this.viewMode !== 'map') {
        this.mappedIntervenants = [];
        return;
      }
      
      // Filtrer uniquement les intervenants avec distance calculée et ≤ 20km
      const nearbyIntervenants = this.intervenantsFromApi.filter(iv => {
        // Vérifier que la distance est calculée, que les coordonnées existent, et ≤ 20km
        if (iv.distance !== undefined && iv.distance !== null && 
            iv.coords && iv.coords.length === 2 && 
            Array.isArray(iv.coords) && 
            !isNaN(iv.coords[0]) && !isNaN(iv.coords[1])) {
          return iv.distance <= 20;
        }
        return false;
      });
      
      console.log(`Génération de ${nearbyIntervenants.length} marqueurs sur ${this.intervenantsFromApi.length} intervenants`);
      
      // Générer les marqueurs uniquement pour les intervenants proches
      this.mappedIntervenants = nearbyIntervenants.map((iv) => {
        // Utiliser les coordonnées déjà calculées
        const coords = iv.coords;
        
        return {
          ...iv,
          coords: coords,
          name: iv.name,
          rating: iv.rating || 0,
          reviews: iv.reviewCount || 0,
          image: iv.image,
          taskPrice: iv.taskPrice,
          distance: iv.distance
        };
      });
      
      console.log('Marqueurs générés:', this.mappedIntervenants.length);
    },
    
    async sortByDistance() {
      if (!this.userLocation) {
        this.getUserLocation();
        return;
      }
      this.sortBy = 'distance';
      await this.calculateDistances();
      this.generateMapMarkers();
    },
    
    async handleSortChange() {
      if (this.sortBy === 'distance' && !this.userLocation) {
        this.getUserLocation();
      }
      if (this.sortBy === 'distance' && this.userLocation) {
        await this.calculateDistances();
      }
    },
    
    formatExperience
  },
  watch: {
    viewMode: {
      async handler(newMode) {
        if (newMode === 'map' && this.mapReady) {
          this.$nextTick(async () => {
            // Si on passe en mode carte et qu'on a la localisation, calculer les distances
            if (this.userLocation) {
              await this.calculateDistances();
            } else {
              // Sinon, demander la localisation
              this.getUserLocation();
            }
          });
        } else if (newMode === 'list') {
          // En mode liste, réinitialiser les distances pour ne pas filtrer
          this.mappedIntervenants = [];
        }
      }
    },
    userLocation: {
      async handler(newLocation) {
        // Quand la localisation change, recalculer les distances
        if (newLocation && this.viewMode === 'map') {
          await this.calculateDistances();
        }
      }
    },
    intervenantsFromApi: {
      async handler() {
        if (this.viewMode === 'map' && this.userLocation) {
          // Recalculer les distances si on a de nouveaux intervenants
          await this.calculateDistances();
        }
      },
      deep: true
    }
  }
};
</script>

<style scoped>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Leaflet Custom Marker Styles */
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
}

.marker-pin {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  position: absolute;
  top: 0;
  left: -6px;
  box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.1);
  z-index: 1;
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.custom-tooltip {
  font-family: inherit;
}
</style>

