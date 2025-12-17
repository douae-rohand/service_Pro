<template>
  <div class="min-h-screen bg-gray-50">
    <div v-if="currentView === 'list'">
      <!-- Header -->
      <div class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <h1 class="text-3xl md:text-4xl font-bold tracking-tight mb-6" :style="{ color: currentServiceColor }">
            Trouver des intervenants
          </h1>

          <!-- Search inputs -->
          <div class="grid md:grid-cols-3 gap-4">
            <div class="relative">
              <Search class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" :size="20" />
              <input
                type="text"
                v-model="searchQuery"
                @keyup.enter="applyFilters"
                @input="onSearchInput"
                placeholder="Rechercher par nom, ville ou spécialité..."
                class="w-full pl-12 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:outline-none focus:border-gray-300"
              />
            </div>
            
            <div class="relative">
              <Filter class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" :size="20" />
              <select
                v-model="serviceFilter"
                @change="applyFilters"
                class="w-full pl-12 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:outline-none focus:border-gray-300 appearance-none cursor-pointer"
              >
                <option value="all">Type de service</option>
                <option v-for="service in services" :key="service.id" :value="service.id">
                  {{ service.nom_service }}
                </option>
              </select>
            </div>
            
            <button
              @click="applyFilters"
              class="py-3 rounded-lg text-white font-medium transition-all hover:opacity-90 flex items-center justify-center"
              :style="{ backgroundColor: currentServiceColor }"
              :disabled="loading"
            >
              <Search :size="20" class="inline mr-2" />
              {{ loading ? 'Chargement...' : 'Rechercher' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content with Sidebar -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
          <!-- Left Sidebar - Filters -->
          <aside class="lg:w-80 flex-shrink-0">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sticky top-32">
              <!-- Header -->
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold flex items-center gap-2">
                  <SlidersHorizontal :size="20" :style="{ color: currentServiceColor }" />
                  Filtres
                </h3>
                <button
                  @click="resetFilters"
                  class="text-sm px-3 py-1.5 rounded-lg hover:opacity-80 transition-all"
                  :style="{ color: currentServiceColor, backgroundColor: currentServiceColor + '20' }"
                >
                  Réinitialiser
                </button>
              </div>

              <!-- Ville -->
              <div class="mb-6">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Ville</label>
                <select
                  v-model="selectedCity"
                  @change="applyFilters"
                  class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none cursor-pointer"
                >
                  <option value="all">Toutes les villes</option>
                  <option v-for="city in cities" :key="city" :value="city">
                    {{ city }}
                  </option>
                </select>
              </div>

              <!-- Service -->
              <div class="mb-6">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Type de service</label>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                  <label
                    v-for="service in services"
                    :key="service.id"
                    class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors"
                  >
                    <input
                      type="checkbox"
                      :value="service.id"
                      v-model="selectedServices"
                      @change="applyFilters"
                      class="w-4 h-4 cursor-pointer"
                      :style="{ accentColor: currentServiceColor }"
                    />
                    <span class="text-sm">{{ service.nom_service }}</span>
                  </label>
                </div>
              </div>

              <!-- Disponibilité -->
              <div class="mb-6">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Disponibilité</label>
                <div class="space-y-2">
                  <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors">
                    <input
                      type="checkbox"
                      v-model="availableNow"
                      @change="applyFilters"
                      class="w-4 h-4 cursor-pointer"
                      :style="{ accentColor: currentServiceColor }"
                    />
                    <span class="text-sm">Disponible maintenant (actifs)</span>
                  </label>
                </div>
              </div>

              <!-- Note minimum -->
              <div class="mb-6">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Note minimum</label>
                <div class="space-y-2">
                  <label
                    v-for="rating in [
                      { value: 'all', label: 'Toutes les notes', stars: null },
                      { value: 5, label: '5 étoiles', stars: 5 },
                      { value: 4, label: '4 étoiles et plus', stars: 4 },
                      { value: 3, label: '3 étoiles et plus', stars: 3 },
                      { value: 2, label: '2 étoiles et plus', stars: 2 },
                      { value: 1, label: '1 étoile et plus', stars: 1 },
                      { value: 0, label: '0 étoile et plus', stars: 0 }
                    ]"
                    :key="rating.value"
                    class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors"
                  >
                    <input
                      type="radio"
                      name="rating"
                      :value="rating.value"
                      v-model="minRating"
                      @change="applyFilters"
                      class="w-4 h-4 cursor-pointer"
                      :style="{ accentColor: currentServiceColor }"
                    />
                    <span class="text-sm flex items-center gap-1">
                      {{ rating.label }}
                      <span v-if="rating.stars !== null" class="flex">
                        <Star 
                          v-for="i in 5" 
                          :key="i" 
                          :size="12" 
                          :class="i <= rating.stars ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'"
                        />
                      </span>
                    </span>
                  </label>
                </div>
              </div>
            </div>
          </aside>

          <!-- Right Content - Results -->
          <main class="flex-1">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-12">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 mx-auto" :style="{ borderColor: currentServiceColor }"></div>
              <p class="mt-4 text-gray-600">Chargement des intervenants...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 text-red-700 mb-6">
              <div class="flex items-start gap-3">
                <AlertCircle :size="24" class="text-red-500 flex-shrink-0 mt-0.5" />
                <div class="flex-1">
                  <h3 class="font-bold mb-1">Erreur de chargement</h3>
                  <p class="text-sm mb-3">{{ error }}</p>
                  <button
                    @click="loadIntervenants(1)"
                    class="px-4 py-2 rounded-lg text-white text-sm font-medium transition-all hover:opacity-90"
                    :style="{ backgroundColor: currentServiceColor }"
                  >
                    Réessayer
                  </button>
                </div>
              </div>
            </div>

            <div v-else>
              <!-- Results header with counter and sorting -->
              <div class="flex items-center justify-between mb-6">
                <!-- Counter -->
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center justify-center bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-full">
                    {{ pagination.total }}
                  </span>
                  <p class="text-gray-500 font-medium text-sm">
                    intervenant{{ pagination.total > 1 ? 's' : '' }} disponible{{ pagination.total > 1 ? 's' : '' }}
                  </p>
                </div>
                
                <!-- Sorting Dropdown -->
                <div class="group relative flex items-center gap-2 bg-white px-4 py-2 rounded-full border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                  <span class="text-gray-400 font-medium text-xs uppercase tracking-wide">Trier</span>
                  <select
                    v-model="sortBy"
                    @change="sortIntervenants"
                    class="bg-transparent border-none font-bold text-gray-700 text-sm focus:ring-0 cursor-pointer outline-none pl-0 pr-6 appearance-none"
                    style="background-image: none;"
                  >
                    <option value="pertinence">Pertinence</option>
                    <option value="rating-desc">Note (Meilleure)</option>
                    <option value="rating-asc">Note (Croissante)</option>
                    <option value="price-asc">Prix croissant</option>
                    <option value="price-desc">Prix décroissant</option>
                    <option value="experience">Expérience</option>
                  </select>
                  <div class="absolute right-3 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </div>
                </div>
              </div>

              <!-- Intervenants Grid -->
              <div class="grid md:grid-cols-2 gap-6">
                <div
                  v-for="intervenant in sortedIntervenants"
                  :key="intervenant.id"
                  class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden flex flex-col"
                >
                  <div class="p-8 flex-1 flex flex-col">
                    <!-- Top Section: Image & Main Info -->
                    <div class="flex gap-6 mb-6">
                      <div class="relative">
                        <div class="w-24 h-24 rounded-2xl overflow-hidden shadow-lg border-2" :style="{ borderColor: currentServiceColor + '40' }">
                          <img
                            :src="getIntervenantImage(intervenant)"
                            :alt="getIntervenantName(intervenant)"
                            class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-110"
                            @error="handleImageError"
                          />
                        </div>
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                          <h3 class="text-xl font-bold text-gray-900 truncate pr-2">{{ getIntervenantName(intervenant) }}</h3>
                          <div class="flex flex-col items-end gap-1">
                            <div class="flex items-center gap-1 bg-yellow-50 px-2 py-1 rounded-lg border border-yellow-100">
                              <Star :size="14" class="fill-yellow-400 text-yellow-400" />
                              <span class="font-bold text-gray-800 text-sm">{{ getIntervenantRating(intervenant) }}</span>
                            </div>
                            <span class="text-gray-500 text-xs">{{ getReviewCount(intervenant) }} avis</span>
                          </div>
                        </div>
                        
                        <p class="text-sm text-gray-500 font-medium mt-1 mb-3">{{ getIntervenantExperience(intervenant) }}</p>
                        
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-100">
                          <MapPin :size="12" />
                          {{ intervenant.ville || intervenant.utilisateur?.address || intervenant.utilisateur?.ville || 'Ville non spécifiée' }}
                        </div>
                      </div>
                    </div>

                    <!-- Specialties -->
                    <div class="mb-6 space-y-3">
                      <div class="flex flex-wrap gap-2">
                        <span 
                          v-for="(specialty, index) in getIntervenantSpecialties(intervenant).slice(0, 5)"
                          :key="index"
                          class="px-2.5 py-1 rounded-md text-xs font-medium transition-colors"
                          :style="{ 
                            backgroundColor: (serviceIdToColor[specialty.serviceId] || currentServiceColor) + '10', 
                            color: serviceIdToColor[specialty.serviceId] || currentServiceColor 
                          }"
                        >
                          {{ specialty.name }}
                        </span>
                        <span v-if="getIntervenantSpecialties(intervenant).length > 5" class="px-2 py-1 rounded-md text-xs font-medium bg-gray-50 text-gray-400">
                          +{{ getIntervenantSpecialties(intervenant).length - 5 }}
                        </span>
                      </div>
                    </div>
                    
                    <div class="h-px bg-gray-100 w-full mb-6"></div>
                    
                    <!-- Actions -->
                    <div class="mt-auto">
                      <div class="flex gap-2">
                        <button
                          @click.stop="viewIntervenantProfile(intervenant.id)"
                          class="flex-1 h-12 flex items-center justify-center rounded-xl transition-all duration-300 hover:opacity-90 font-bold text-sm text-white"
                          :style="{ backgroundColor: currentServiceColor }"
                        >
                          Voir le profil
                        </button>
                        <button
                          @click.stop="addToFavorites(intervenant)"
                          class="px-4 h-12 rounded-xl border-2 transition-all hover:bg-red-50 flex items-center justify-center"
                          :style="{ borderColor: currentServiceColor, color: currentServiceColor }"
                          title="Ajouter aux favoris"
                        >
                          <Heart :size="18" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- No results -->
              <div v-if="!loading && !error && sortedIntervenants.length === 0" class="text-center py-16">
                <h3 class="text-2xl font-bold mb-2">Aucun intervenant trouvé</h3>
                <p class="text-gray-600 mb-6">Essayez de modifier vos critères de recherche</p>
                <button
                  @click="resetFilters"
                  class="px-6 py-3 rounded-lg text-white transition-all hover:opacity-90"
                  :style="{ backgroundColor: currentServiceColor }"
                >
                  Réinitialiser les filtres
                </button>
              </div>

              <!-- Pagination Controls -->
              <div v-if="pagination.total > 0 && pagination.last_page > 1" class="flex justify-center mt-12 gap-2">
                <button
                  @click="prevPage"
                  :disabled="pagination.current_page === 1"
                  class="px-4 py-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
                >
                  Précédent
                </button>
                
                <button
                  v-for="page in visiblePages"
                  :key="page"
                  @click="goToPage(page)"
                  class="w-10 h-10 rounded-lg border transition-colors flex items-center justify-center font-medium"
                  :style="{
                    backgroundColor: pagination.current_page === page ? currentServiceColor : 'transparent',
                    borderColor: pagination.current_page === page ? currentServiceColor : '#D1D5DB',
                    color: pagination.current_page === page ? 'white' : '#374151'
                  }"
                >
                  {{ page }}
                </button>
                
                <button
                  @click="nextPage"
                  :disabled="pagination.current_page === pagination.last_page"
                  class="px-4 py-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
                >
                  Suivant
                </button>
              </div>
            </div>
          </main>
        </div>
      </div>
    </div>

    <!-- Profile View -->
    <div v-else-if="currentView === 'profile'" class="py-6">
      <IntervenantProfile 
        :intervenantId="selectedIntervenantId"
        :clientId="clientId"
        @back="backToList"
        @book="(iv) => {}" 
      />
    </div>

    <!-- Success Toast -->
    <div v-if="showToast" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in">
      {{ toastMessage }}
    </div>
  </div>
</template>

<script>
import { 
  Search, 
  Filter, 
  SlidersHorizontal, 
  MapPin, 
  Star, 
  AlertCircle,
  Heart
} from 'lucide-vue-next';
import intervenantService from '@/services/intervenantService';
import serviceService from '@/services/serviceService';
import favoriteService from '@/services/favoriteService';
import IntervenantProfile from './IntervenantProfile.vue';

export default {
  name: 'TrouverIntervenantsTab',
  components: {
    Search,
    Filter,
    SlidersHorizontal,
    MapPin,
    Star,
    AlertCircle,
    Heart,
    IntervenantProfile
  },
  props: {
    clientId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      currentView: 'list', // 'list' | 'profile'
      selectedIntervenantId: null,
      loading: false,
      error: null,
      searchQuery: '',
      serviceFilter: 'all',
      selectedCity: 'all',
      selectedServices: [],
      priceRange: [0, 100],
      minRating: 'all',
      availableNow: false,
      sortBy: 'pertinence',
      
      // Data from API
      intervenants: [],
      services: [],
      cities: [],
      
      // Pagination
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 12,
        total: 0,
        from: 0,
        to: 0
      },
      
      // UI states
      showToast: false,
      toastMessage: '',
      searchTimeout: null,
      
      // Color mapping
      serviceColors: {
        'Jardinage': '#92B08B',
        'Ménage': '#4682B4',
      },
      serviceIdToColor: {
        1: '#92B08B', // Jardinage
        2: '#4682B4', // Ménage
      }
    };
  },
  computed: {
    currentServiceColor() {
      // If a specific service is selected, use its color
      if (this.serviceFilter !== 'all' && this.serviceFilter) {
        const selectedService = this.services.find(s => s.id == this.serviceFilter);
        if (selectedService) {
          return this.serviceColors[selectedService.nom_service] || this.serviceIdToColor[selectedService.id] || '#6B7280';
        }
        return this.serviceIdToColor[this.serviceFilter] || '#6B7280';
      }
      // Default color (can be changed based on your preference)
      return '#6B7280';
    },
    
    filteredIntervenants() {
      return this.intervenants.filter(intervenant => {
        // Price filter
        const price = this.getIntervenantPrice(intervenant);
        if (price < this.priceRange[0] || price > this.priceRange[1]) {
          return false;
        }
        
        // Rating filter
        if (this.minRating !== 'all' && typeof this.minRating === 'number') {
          const rating = parseFloat(this.getIntervenantRating(intervenant));
          if (rating < this.minRating) {
            return false;
          }
        }
        
        // Multiple services filter
        if (this.selectedServices.length > 0) {
          const intervenantServiceIds = this.getIntervenantServiceIds(intervenant);
          if (!this.selectedServices.some(serviceId => intervenantServiceIds.includes(serviceId))) {
            return false;
          }
        }
        
        return true;
      });
    },
    
    sortedIntervenants() {
      const sorted = [...this.filteredIntervenants];
      
      switch (this.sortBy) {
        case 'rating-desc':
        case 'rating':
          return sorted.sort((a, b) => parseFloat(this.getIntervenantRating(b)) - parseFloat(this.getIntervenantRating(a)));
        case 'rating-asc':
          return sorted.sort((a, b) => parseFloat(this.getIntervenantRating(a)) - parseFloat(this.getIntervenantRating(b)));
        case 'price-asc':
          return sorted.sort((a, b) => this.getIntervenantPrice(a) - this.getIntervenantPrice(b));
        case 'price-desc':
          return sorted.sort((a, b) => this.getIntervenantPrice(b) - this.getIntervenantPrice(a));
        case 'experience':
          return sorted.sort((a, b) => {
            const expA = this.getExperienceYears(a);
            const expB = this.getExperienceYears(b);
            return expB - expA;
          });
        default:
          return sorted;
      }
    },
    
    visiblePages() {
      const current = this.pagination.current_page;
      const last = this.pagination.last_page;
      const delta = 2;
      const range = [];
      
      for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
          range.push(i);
        }
      }
      
      return range;
    }
  },
  async mounted() {
    await this.loadInitialData();
  },
  methods: {
    backToList() {
      this.currentView = 'list';
      this.selectedIntervenantId = null;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    async loadInitialData() {
      this.loading = true;
      this.error = null;
      
      try {
        // Charger les services
        const servicesResponse = await serviceService.getAll();
        this.services = servicesResponse.data || [];
        
        // Charger les intervenants
        await this.loadIntervenants(1);
        
      } catch (error) {
        console.error('Error loading initial data:', error);
        this.error = 'Erreur lors du chargement des données. Veuillez réessayer.';
      } finally {
        this.loading = false;
      }
    },
    
    async loadIntervenants(page = 1) {
      this.loading = true;
      this.error = null;
      try {
        const params = {
          page: page,
          per_page: this.pagination.per_page
        };
        
        // Ajouter les filtres selon l'API backend
        if (this.searchQuery && this.searchQuery.trim()) {
          params.search = this.searchQuery.trim();
        }
        
        if (this.selectedCity !== 'all' && this.selectedCity) {
          params.ville = this.selectedCity;
        }
        
        // Service filter: prioritize serviceFilter over selectedServices
        if (this.serviceFilter !== 'all' && this.serviceFilter) {
          params.service_id = this.serviceFilter;
        } else if (this.selectedServices.length > 0) {
          params.service_id = this.selectedServices[0];
        }
        
        if (this.availableNow) {
          params.active = 'true';
        }
        
        console.log('📤 Loading intervenants with params:', params);
        
        const response = await intervenantService.getAllIntervenants(params);
        
        console.log('📦 API Response:', {
          hasData: !!response.data,
          dataLength: response.data?.length || 0,
          pagination: {
            current_page: response.current_page,
            last_page: response.last_page,
            total: response.total
          }
        });
        
        // Handle Laravel pagination response
        this.intervenants = response.data || [];
        
        // Update pagination info
        this.pagination = {
          current_page: response.current_page || 1,
          last_page: response.last_page || 1,
          per_page: response.per_page || this.pagination.per_page,
          total: response.total || 0,
          from: response.from || 0,
          to: response.to || 0
        };
        
        // Extraire les villes uniques depuis les données chargées
        this.extractCities();
        
        // Log for debugging
        if (this.intervenants.length > 0) {
          console.log('✅ Loaded intervenants:', this.intervenants.length);
          console.log('Sample intervenant:', {
            id: this.intervenants[0].id,
            name: this.getIntervenantName(this.intervenants[0]),
            rating: this.getIntervenantRating(this.intervenants[0]),
            tachesCount: this.intervenants[0].taches?.length || 0
          });
        }
        
      } catch (error) {
        console.error('❌ Error loading intervenants:', error);
        console.error('Error details:', {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status,
          url: error.config?.url,
          params: error.config?.params
        });
        
        // More detailed error message
        let errorMessage = 'Erreur lors du chargement des intervenants.';
        
        if (error.response) {
          // Server responded with error
          if (error.response.status === 401) {
            errorMessage = 'Vous devez être connecté pour voir les intervenants.';
          } else if (error.response.status === 403) {
            errorMessage = 'Vous n\'avez pas la permission d\'accéder à cette ressource.';
          } else if (error.response.status === 404) {
            errorMessage = 'La route API n\'a pas été trouvée. Vérifiez la configuration.';
          } else if (error.response.status === 500) {
            errorMessage = 'Erreur serveur: ' + (error.response.data?.message || 'Veuillez réessayer plus tard.');
          } else if (error.response.data?.message) {
            errorMessage = error.response.data.message;
          } else if (error.response.data?.error) {
            errorMessage = error.response.data.error;
          }
        } else if (error.request) {
          // Request was made but no response received
          errorMessage = 'Impossible de se connecter au serveur. Vérifiez votre connexion internet et que le serveur est démarré.';
        } else {
          // Error in request setup
          errorMessage = error.message || errorMessage;
        }
        
        this.error = errorMessage;
        this.intervenants = [];
        this.pagination.total = 0;
      } finally {
        this.loading = false;
      }
    },
    
    extractCities() {
      const citySet = new Set(['Toutes les villes']);
      this.intervenants.forEach(intervenant => {
        // Get city from intervenant.ville or utilisateur.address
        const city = intervenant.ville || 
                    intervenant.utilisateur?.address || 
                    intervenant.utilisateur?.ville;
        if (city && city.trim() && city !== 'Non spécifiée') {
          citySet.add(city.trim());
        }
      });
      this.cities = Array.from(citySet).sort();
    },
    
    getIntervenantImage(intervenant) {
      // Try multiple possible image paths from database
      if (intervenant.utilisateur?.url) {
        return intervenant.utilisateur.url;
      }
      if (intervenant.utilisateur?.photo) {
        return intervenant.utilisateur.photo;
      }
      if (intervenant.image_url) {
        return intervenant.image_url;
      }
      
      // Fallback to default based on gender
      const gender = intervenant.utilisateur?.sexe || 'male';
      const defaultImages = {
        male: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop',
        female: 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop',
        other: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&h=150&fit=crop'
      };
      return defaultImages[gender] || defaultImages.male;
    },
    
    handleImageError(event) {
      event.target.src = 'https://via.placeholder.com/150?text=Image+non+disponible';
    },
    
    getIntervenantName(intervenant) {
      if (intervenant.utilisateur) {
        return `${intervenant.utilisateur.prenom || ''} ${intervenant.utilisateur.nom || ''}`.trim();
      }
      return 'Intervenant';
    },
    
    getIntervenantServices(intervenant) {
      if (intervenant.taches && Array.isArray(intervenant.taches)) {
        const services = new Map();
        intervenant.taches.forEach(tache => {
          if (tache.service && !services.has(tache.service.id)) {
            services.set(tache.service.id, tache.service);
          }
        });
        return Array.from(services.values());
      }
      return [];
    },
    
    getIntervenantSpecialties(intervenant) {
      // Get specialties from taches (like in AllIntervenantsPage)
      if (intervenant.taches && Array.isArray(intervenant.taches)) {
        return intervenant.taches.map(tache => ({
          name: tache.nom_tache || tache.name || '',
          serviceId: tache.service_id || (tache.service ? tache.service.id : null)
        })).filter(s => s.name);
      }
      return [];
    },
    
    // Get all taches for an intervenant (for specialties display)
    getIntervenantTaches(intervenant) {
      if (intervenant.taches && Array.isArray(intervenant.taches)) {
        return intervenant.taches;
      }
      return [];
    },
    
    getIntervenantServiceIds(intervenant) {
      return this.getIntervenantServices(intervenant).map(service => service.id);
    },
    
    getIntervenantRating(intervenant) {
      // Use average_rating from API (calculated by backend)
      if (intervenant.average_rating !== undefined && intervenant.average_rating !== null) {
        return parseFloat(intervenant.average_rating).toFixed(1);
      }
      
      // Fallback: calculate from interventions if available
      if (intervenant.interventions && Array.isArray(intervenant.interventions)) {
        const evaluations = intervenant.interventions
          .filter(intervention => intervention.evaluation && intervention.evaluation.note)
          .map(intervention => parseFloat(intervention.evaluation.note));
        
        if (evaluations.length > 0) {
          const sum = evaluations.reduce((acc, note) => acc + note, 0);
          return (sum / evaluations.length).toFixed(1);
        }
      }
      
      // Default rating for new intervenants
      return '0.0';
    },
    
    getReviewCount(intervenant) {
      if (intervenant.review_count !== undefined) {
        return intervenant.review_count;
      }
      
      if (intervenant.interventions && Array.isArray(intervenant.interventions)) {
        return intervenant.interventions.filter(intervention => 
          intervention.evaluation && intervention.evaluation.note
        ).length;
      }
      
      return 0;
    },
    
    getIntervenantPrice(intervenant) {
      // Get price from taches pivot table (prix_tache)
      if (intervenant.taches && intervenant.taches.length > 0) {
        const prices = intervenant.taches
          .filter(tache => {
            // Check both pivot structure and direct prix_tache
            return (tache.pivot && tache.pivot.prix_tache) || tache.prix_tache;
          })
          .map(tache => {
            // Get price from pivot or direct property
            return parseFloat(tache.pivot?.prix_tache || tache.prix_tache || 0);
          })
          .filter(price => price > 0); // Only valid prices
        
        if (prices.length > 0) {
          const avg = prices.reduce((a, b) => a + b, 0) / prices.length;
          return Math.round(avg);
        }
      }
      
      // Default price if no prices found
      const services = this.getIntervenantServices(intervenant);
      const basePrice = services.length > 0 ? 25 : 20;
      return basePrice;
    },
    
    getExperienceYears(intervenant) {
      if (intervenant.utilisateur?.created_at) {
        const startDate = new Date(intervenant.utilisateur.created_at);
        const years = new Date().getFullYear() - startDate.getFullYear();
        return years;
      }
      return 0;
    },
    
    getIntervenantExperience(intervenant) {
      // Try to get experience from services pivot first
      if (intervenant.services && Array.isArray(intervenant.services)) {
        const serviceWithExperience = intervenant.services.find(s => s.pivot?.experience);
        if (serviceWithExperience && serviceWithExperience.pivot.experience) {
          const exp = serviceWithExperience.pivot.experience;
          if (exp && exp !== 'Pas') {
            return exp;
          }
        }
      }
      
      // Fallback: calculate from created_at
      const years = this.getExperienceYears(intervenant);
      if (years > 0) {
        return `${years} an${years > 1 ? 's' : ''} d'expérience`;
      }
      return 'Nouvel intervenant';
    },
    
    async applyFilters() {
      await this.loadIntervenants(1);
    },
    
    onSearchInput() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.applyFilters();
      }, 500);
    },
    
    sortIntervenants() {
      // Le tri est géré par la propriété computed sortedIntervenants
    },
    
    resetFilters() {
      this.searchQuery = '';
      this.serviceFilter = 'all';
      this.selectedCity = 'all';
      this.selectedServices = [];
      this.priceRange = [0, 100];
      this.minRating = 'all';
      this.availableNow = false;
      this.sortBy = 'pertinence';
      this.applyFilters();
    },
    
    async addToFavorites(intervenant) {
      try {
        let serviceId = null;
        if (this.selectedServices.length > 0) {
          serviceId = this.selectedServices[0];
        } else if (this.serviceFilter !== 'all') {
          serviceId = this.serviceFilter;
        } else {
          const services = this.getIntervenantServices(intervenant);
          if (services.length > 0) serviceId = services[0].id;
        }

        if (!serviceId) {
          this.showToastMessage('Impossible d\'ajouter: aucun service détecté pour cet intervenant.', 'error');
          return;
        }

        await favoriteService.toggleFavorite(this.clientId, intervenant.id, serviceId);
        this.showToastMessage(`Favoris mis à jour pour ${this.getIntervenantName(intervenant)} !`);
      } catch (error) {
        console.error('Error adding to favorites:', error);
        this.showToastMessage('Erreur lors de la mise à jour des favoris', 'error');
      }
    },
    
    async prevPage() {
      if (this.pagination.current_page > 1) {
        await this.loadIntervenants(this.pagination.current_page - 1);
      }
    },
    
    async nextPage() {
      if (this.pagination.current_page < this.pagination.last_page) {
        await this.loadIntervenants(this.pagination.current_page + 1);
      }
    },
    
    async goToPage(page) {
      if (page !== this.pagination.current_page) {
        await this.loadIntervenants(page);
      }
    },
    
    viewIntervenantProfile(intervenantId) {
      this.selectedIntervenantId = intervenantId;
      this.currentView = 'profile';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    
    showToastMessage(message, type = 'success') {
      this.toastMessage = message;
      this.showToast = true;
      
      setTimeout(() => {
        this.showToast = false;
      }, 3000);
    }
  }
};
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
