<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Content (affiché seulement quand les données sont chargées) -->
    <div v-if="serviceData && currentService">
      <!-- Header -->
      <div class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center gap-4 mb-6">
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
            <div>
              <h1 class="text-3xl md:text-4xl font-bold tracking-tight" :style="{ color: currentService.color }">
                Tous nos {{ currentService.name }}
              </h1>
            </div>
          </div>

        <!-- Search inputs -->
        <div class="grid md:grid-cols-3 gap-4">
          <div class="relative">
            <Search class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" :size="20" />
            <input
              type="text"
              v-model="citySearch"
              @keyup.enter="applySearch"
              placeholder="Rechercher par nom, ville ou spécialité..."
              class="w-full pl-12 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:outline-none focus:border-gray-300"
            />
          </div>
          <div class="relative">
            <Filter class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" :size="20" />
            <select
              v-model="serviceTypeFilter"
              class="w-full pl-12 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:outline-none focus:border-gray-300 appearance-none cursor-pointer"
            >
              <option value="all">Type de service</option>
              <option v-for="type in (currentService.serviceTypes || [])" :key="type" :value="type">
                {{ type }}
              </option>
            </select>
          </div>
          <button
            @click="applySearch"
            class="py-3 rounded-lg text-white font-medium transition-all hover:opacity-90"
            :style="{ backgroundColor: currentService.color }"
          >
            <Search :size="20" class="inline mr-2" />
            Rechercher
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
                <SlidersHorizontal :size="20" :style="{ color: currentService.color }" />
                Filtres
              </h3>
              <button
                @click="resetFilters"
                class="text-sm px-3 py-1.5 rounded-lg hover:opacity-80 transition-all"
                :style="{ color: currentService.color, backgroundColor: currentService.color + '20' }"
              >
                Réinitialiser
              </button>
            </div>

            <!-- Ville -->
            <div class="mb-6">
              <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Ville</label>
              <select
                v-model="selectedCity"
                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none cursor-pointer"
              >
                <option v-for="city in cities" :key="city" :value="city === 'Toutes les villes' ? 'all' : city">
                  {{ city }}
                </option>
              </select>
            </div>

            <!-- Type de service -->
            <div class="mb-6">
              <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Type de service</label>
              <div class="space-y-2 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                <label
                  v-for="type in (currentService.serviceTypes || [])"
                  :key="type"
                  class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors"
                >
                  <input
                    type="checkbox"
                    :checked="selectedServiceTypes.includes(type)"
                    @change="toggleServiceType(type)"
                    class="w-4 h-4 cursor-pointer"
                    :style="{ accentColor: currentService.color }"
                  />
                  <span class="text-sm">{{ type }}</span>
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
                    { value: 4, label: 'Entre 4 et 5 étoiles', stars: 4 },
                    { value: 3, label: 'Entre 3 et 4 étoiles', stars: 3 },
                    { value: 2, label: 'Entre 2 et 3 étoiles', stars: 2 },
                    { value: 1, label: 'Entre 1 et 2 étoiles', stars: 1 },
                    { value: 0, label: 'Entre 0 et 1 étoile', stars: 0 }
                  ]"
                  :key="rating.value"
                  class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors"
                >
                  <input
                    type="radio"
                    name="rating"
                    :value="rating.value"
                    v-model="selectedRating"
                    class="w-4 h-4 cursor-pointer"
                    :style="{ accentColor: currentService.color }"
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

            <!-- Apply button -->
            <button
              class="w-full py-3 rounded-lg text-white font-medium transition-all hover:opacity-90"
              :style="{ backgroundColor: currentService.color }"
            >
              Appliquer les filtres
            </button>
          </div>
        </aside>

        <!-- Right Content - Results -->
        <main class="flex-1">
          <!-- Results header with counter and sorting -->
          <div class="flex items-center justify-between mb-6">
            <!-- Counter -->
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center justify-center bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-full">
                {{ sortedIntervenants.length }}
              </span>
              <p class="text-gray-500 font-medium text-sm">
                intervenant{{ sortedIntervenants.length > 1 ? 's' : '' }} disponible{{ sortedIntervenants.length > 1 ? 's' : '' }}
              </p>
            </div>
            
            <!-- Sorting Dropdown -->
            <div class="group relative flex items-center gap-2 bg-white px-4 py-2 rounded-full border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
              <span class="text-gray-400 font-medium text-xs uppercase tracking-wide">Trier</span>
              <select
                v-model="sortBy"
                class="bg-transparent border-none font-bold text-gray-700 text-sm focus:ring-0 cursor-pointer outline-none pl-0 pr-6 appearance-none"
                style="background-image: none;"
              >
                <option value="pertinence">Pertinence</option>
                <option value="rating-desc">Note (Meilleure)</option>
                <option value="rating-asc">Note (Croissante)</option>
              </select>
              <div class="absolute right-3 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
              </div>
            </div>
          </div>

          <!-- Intervenants Grid -->
          <div class="grid md:grid-cols-2 gap-6">
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
                    <div class="flex justify-between items-start">
                      <h3 class="text-xl font-bold text-gray-900 truncate pr-2">{{ intervenant.name }}</h3>
                      <div class="flex flex-col items-end gap-1">
                        <div class="flex items-center gap-1 bg-yellow-50 px-2 py-1 rounded-lg border border-yellow-100">
                           <Star :size="14" class="fill-yellow-400 text-yellow-400" />
                           <span class="font-bold text-gray-800 text-sm">{{ intervenant.rating }}</span>
                        </div>
                        <span class="text-gray-500 text-xs">{{ intervenant.reviewCount }} avis</span>
                      </div>
                    </div>
                    
                    <p class="text-sm text-gray-500 font-medium mt-1 mb-3">{{ formatExperience(intervenant.experience) }} d'expérience</p>
                    
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-100">
                      <MapPin :size="12" />
                      {{ intervenant.location }}
                    </div>
                  </div>
                </div>

                <!-- Specialties -->
                <div class="mb-6 space-y-3">
                   <div class="flex flex-wrap gap-2">
                      <span 
                        v-for="(specialty, index) in (intervenant.specialties || []).slice(0, 5)"
                        :key="index"
                        class="px-2.5 py-1 rounded-md text-xs font-medium transition-colors"
                        :style="{ 
                          backgroundColor: (serviceIdToColor[specialty.serviceId] || currentService.color) + '10', 
                          color: serviceIdToColor[specialty.serviceId] || currentService.color 
                        }"
                      >
                        {{ specialty.name }}
                      </span>
                      <span v-if="(intervenant.specialties || []).length > 5" class="px-2 py-1 rounded-md text-xs font-medium bg-gray-50 text-gray-400">
                        +{{ (intervenant.specialties || []).length - 5 }}
                      </span>
                   </div>
                </div>
                
                <div class="h-px bg-gray-100 w-full mb-6"></div>

                <!-- Actions -->
                <div class="mt-auto">
                  <button
                    @click="viewProfile(intervenant)"
                    class="w-full h-12 flex items-center justify-center rounded-xl transition-all duration-300 hover:opacity-90 font-bold text-sm text-white"
                    :style="{ 
                      backgroundColor: currentService.color
                    }"
                  >
                    Voir le profil
                  </button>
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

          <!-- No results (seulement après le chargement complet des données) -->
          <div v-if="intervenantsLoaded && !loadingIntervenants && sortedIntervenants.length === 0" class="text-center py-16">
            <h3 class="text-2xl font-bold mb-2">Aucun intervenant trouvé</h3>
            <p class="text-gray-600 mb-6">Essayez de modifier vos critères de recherche</p>
            <button
              @click="resetFilters"
              class="px-6 py-3 rounded-lg text-white transition-all hover:opacity-90"
              :style="{ backgroundColor: currentService.color }"
            >
              Réinitialiser les filtres
            </button>
          </div>
        </main>
      </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ArrowLeft, Star, MapPin, Search, Filter, SlidersHorizontal, CheckCircle } from 'lucide-vue-next';
import intervenantService from '@/services/intervenantService';
import serviceService from '@/services/serviceService';
import { formatExperience } from '@/utils/experienceFormatter';

export default {
  name: 'AllIntervenantsPage',
  components: {
    ArrowLeft,
    Star,
    MapPin,
    Search,
    Filter,
    SlidersHorizontal,
    CheckCircle
  },
  props: {
    service: {
      type: [String, Number],
      required: true
    }
  },
  emits: ['back', 'view-profile'],
  data() {
    return {
      citySearch: '',
      serviceTypeFilter: 'all',
      selectedCity: 'all',
      selectedServiceTypes: [],
      selectedRating: 'all',
      bringsMaterial: false,
      ecoProducts: false,
      sortBy: 'pertinence',
      hoverBackButton: false,
      loading: false,
      loadingIntervenants: false,
      intervenantsLoaded: false,
      serviceData: null,
      serviceTaches: [],
      intervenantsFromApi: [],
      serviceColors: {
        'Jardinage': '#92B08B',
        'Ménage': '#4682B4',
      },
      serviceIdToColor: {
        1: '#92B08B',
        2: '#4682B4',
      },
      serviceIdToName: {
        1: 'Jardiniers',
        2: 'Intervenants Ménage',
      },
      currentPage: 1,
      itemsPerPage: 4,
    };
  },
  mounted() {
    Promise.all([
      this.loadServiceInfo(),
      this.loadIntervenants(false)
    ]).catch(error => {
      console.error('Erreur lors du chargement des données:', error);
    });
  },
  watch: {
    service() {
      this.loadServiceInfo();
      this.loadIntervenants(true);
    }
  },
  computed: {
    currentService() {
      const serviceTypes = this.serviceTaches.length > 0
        ? this.serviceTaches.map(tache => tache.nom_tache).filter(Boolean)
        : [];
      
      if (this.serviceData) {
        const serviceName = this.serviceData.nom_service;
        const color = this.serviceColors[serviceName] || '#6B7280';
        
        return {
          name: serviceName,
          color: color,
          serviceTypes: serviceTypes,
        };
      }
      
      let serviceName = 'Service';
      let color = '#6B7280';
      
      if (this.service) {
        serviceName = this.serviceIdToName[this.service] || 'Service';
        color = this.serviceIdToColor[this.service] || '#6B7280';
      }
      
      return {
        name: serviceName,
        color: color,
        serviceTypes: serviceTypes,
      };
    },
    
    cities() {
      const allCities = ['Toutes les villes'];
      const uniqueCities = new Set();
      
      this.intervenantsFromApi.forEach(intervenant => {
        if (intervenant.location && intervenant.location !== 'Non spécifiée') {
          uniqueCities.add(intervenant.location);
        }
      });
      
      return [...allCities, ...Array.from(uniqueCities).sort()];
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
          const intervenantSpecialties = (intervenant.specialties || []).join(' ').toLowerCase();
          matchesSearch = intervenantName.includes(searchText) || 
                         intervenantLocation.includes(searchText) ||
                         intervenantSpecialties.includes(searchText);
        }
        
        const matchesCity = this.selectedCity === 'all' || 
                          this.selectedCity === 'Toutes les villes' || 
                          intervenant.location === this.selectedCity;
        
        let matchesServiceTypeFilter = true;
        if (this.serviceTypeFilter && this.serviceTypeFilter !== 'all') {
          const intervenantTacheNames = (intervenant.taches || []).map(t => 
            (t.nom_tache || t.name || '').toLowerCase().trim()
          );
          const filterTypeLower = this.serviceTypeFilter.toLowerCase().trim();
          matchesServiceTypeFilter = intervenantTacheNames.some(tacheName => 
            tacheName === filterTypeLower || 
            tacheName.includes(filterTypeLower) ||
            filterTypeLower.includes(tacheName)
          );
        }
        
        let matchesServiceType = true;
        if (this.selectedServiceTypes.length > 0) {
          const intervenantTacheNames = (intervenant.taches || []).map(t => 
            (t.nom_tache || t.name || '').toLowerCase().trim()
          );
          matchesServiceType = this.selectedServiceTypes.some(selectedType => {
            const selectedTypeLower = selectedType.toLowerCase().trim();
            return intervenantTacheNames.some(tacheName => 
              tacheName === selectedTypeLower || 
              tacheName.includes(selectedTypeLower) ||
              selectedTypeLower.includes(tacheName)
            );
          });
        }
        
        let matchesRating = true;
        if (this.selectedRating !== 'all' && typeof this.selectedRating === 'number') {
          const rating = Number(intervenant.rating);
          if (this.selectedRating === 5) {
            matchesRating = rating === 5;
          } else {
            matchesRating = rating >= this.selectedRating && rating < (this.selectedRating + 1);
          }
        }
        
        const matchesMaterial = !this.bringsMaterial || intervenant.bringsMaterial;
        const matchesEco = !this.ecoProducts || intervenant.ecoFriendly;
        
        return matchesSearch && matchesCity && matchesServiceTypeFilter && matchesServiceType && matchesRating && matchesMaterial && matchesEco;
      });
    },
    sortedIntervenants() {
      const sorted = [...this.filteredIntervenants];
      if (this.sortBy === 'rating-desc' || this.sortBy === 'rating') return sorted.sort((a, b) => b.rating - a.rating);
      if (this.sortBy === 'rating-asc') return sorted.sort((a, b) => a.rating - b.rating);
      return sorted;
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
    async loadServiceInfo() {
      if (this.service) { // Check souple (string ou number)
        try {
          const response = await serviceService.getById(this.service);
          this.serviceData = response.data;
          this.serviceTaches = response.data.taches || [];
        } catch (error) {
          console.error('Erreur lors du chargement des informations du service:', error);
        }
      }
    },
    
    async loadIntervenants(showLoading = false) {
      try {
        const params = { active: 'true' };
        
        if (this.service) { // Check souple
          params.serviceId = this.service;
        }
        
        const response = await intervenantService.getAll(params);
        const intervenants = response.data || [];
        
        this.intervenantsFromApi = intervenants.map(intervenant => {
          const utilisateur = intervenant.utilisateur || {};
          const taches = intervenant.taches || [];
          
          const specialties = taches.map(tache => ({
            name: tache.nom_tache || tache.name || '',
            serviceId: tache.service_id
          })).filter(s => s.name);
          
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
          let realExperience = 'Pas';
          
          if (this.service) { // Check souple
            const currentServiceInfo = userServices.find(s => s.id == this.service);
            realExperience = currentServiceInfo?.pivot?.experience || realExperience;
          }
          
          return {
            id: intervenant.id,
            name: `${utilisateur.nom || ''} ${utilisateur.prenom || ''}`.trim() || 'Intervenant',
            rating: intervenant.average_rating || 0,
            reviewCount: intervenant.review_count || 0,
            hourlyRate: hourlyRate,
            location: intervenant.ville || utilisateur.address || 'Non spécifiée',
            image: intervenant.image_url || utilisateur.profile_photo || utilisateur.url || 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=150&h=150&fit=crop',
            verified: intervenant.is_active !== false,
            specialties: specialties,
            taches: taches,
            experience: realExperience,
            bringsMaterial: (intervenant.materiels && intervenant.materiels.length > 0) || false,
            ecoFriendly: Math.random() > 0.5,
            // Données complètes pour le profil
            fullData: intervenant
          };
        });
        
        this.intervenantsLoaded = true;
        
      } catch (error) {
        console.error('Erreur lors du chargement des intervenants:', error);
        this.intervenantsFromApi = [];
        this.intervenantsLoaded = true;
      } finally {
        if (showLoading) {
          this.loadingIntervenants = false;
        }
      }
    },
    
    // NOUVELLE MÉTHODE pour gérer le clic sur "Voir le profil"
    viewProfile(intervenant) {
      // Émettre l'événement avec toutes les données de l'intervenant
      this.$emit('view-profile', {
        id: intervenant.id,
        data: intervenant
      });
    },
    
    toggleServiceType(type) {
      if (this.selectedServiceTypes.includes(type)) {
        this.selectedServiceTypes = this.selectedServiceTypes.filter(t => t !== type);
      } else {
        this.selectedServiceTypes.push(type);
      }
    },
    applySearch() {
      if (this.citySearch.trim()) {
        const searchText = this.citySearch.trim().toLowerCase();
        const matchingCity = this.cities.find(city => 
          city !== 'Toutes les villes' && city.toLowerCase().includes(searchText)
        );
        if (matchingCity) {
          this.selectedCity = matchingCity;
        }
      }
      
      if (this.serviceTypeFilter && this.serviceTypeFilter !== 'all') {
        if (!this.selectedServiceTypes.includes(this.serviceTypeFilter)) {
          this.selectedServiceTypes.push(this.serviceTypeFilter);
        }
      }
    },
    resetFilters() {
      this.selectedCity = 'all';
      this.selectedServiceTypes = [];
      this.serviceTypeFilter = 'all';
      this.citySearch = '';
      this.selectedRating = 'all';
      this.bringsMaterial = false;
      this.ecoProducts = false;
      this.currentPage = 1; // Reset to first page
    },
    handleImageError(event) {
      event.target.src = 'https://via.placeholder.com/150?text=Image+non+disponible';
    },
    formatExperience
  }
};
</script>

<style scoped>
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