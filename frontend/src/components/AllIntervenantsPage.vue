<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Content (affiché seulement quand les données sont chargées) -->
    <div v-if="serviceData && currentService">
      <!-- Top Search Bar -->
      <div class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center gap-4 mb-6">
            <button
              @click="$emit('back')"
              @mouseenter="hoverBackButton = true"
              @mouseleave="hoverBackButton = false"
              class="flex items-center gap-2 text-gray-600 hover:text-white transition-all px-4 py-2 rounded-lg"
              :style="{ backgroundColor: hoverBackButton ? currentService.color : 'transparent' }"
            >
              <ArrowLeft :size="20" />
            </button>
            <h1 class="text-4xl font-bold" :style="{ color: currentService.color }">
              Tous nos {{ currentService.name }}
            </h1>
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
          <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-32">
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
              <label class="block text-sm font-semibold mb-2">Ville</label>
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
              <label class="block text-sm font-semibold mb-3">Type de service</label>
              <div class="space-y-2 max-h-48 overflow-y-auto">
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
              <label class="block text-sm font-semibold mb-3">Note minimum</label>
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
          <!-- Results header -->
          <div class="flex items-center justify-between mb-6">
            <p class="text-gray-600">
              <span class="font-bold text-gray-900">{{ sortedIntervenants.length }}</span>
              intervenant{{ sortedIntervenants.length > 1 ? 's' : '' }} trouvé{{ sortedIntervenants.length > 1 ? 's' : '' }}
            </p>
            <select
              v-model="sortBy"
              class="px-4 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none cursor-pointer"
            >
              <option value="pertinence">Trier par: Pertinence</option>
              <option value="rating-desc">Note décroissante</option>
              <option value="rating-asc">Note croissante</option>
            </select>
          </div>

          <!-- Intervenants Grid -->
          <div class="grid md:grid-cols-2 gap-6">
            <div
              v-for="intervenant in sortedIntervenants"
              :key="intervenant.id"
              class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all overflow-hidden"
            >
              <!-- Image with badges -->
              <div class="relative h-48">
                <img
                  :src="intervenant.image"
                  :alt="intervenant.name"
                  class="w-full h-full object-cover"
                />
                
              </div>

              <!-- Content -->
              <div class="p-6">
                <div class="mb-3">
                  <h3 class="text-xl font-bold mb-1">{{ intervenant.name }}</h3>
                  <p class="text-sm text-gray-600">{{ intervenant.experience }} d'éxperience</p>
                </div>

                <!-- Specialties (Sous-services) -->
                <div class="mb-4">
                  <p class="text-xs text-gray-500 mb-2 font-semibold">Sous-services :</p>
                  <div class="flex flex-wrap gap-2">
                    <span
                      v-for="(specialty, index) in (intervenant.specialties || []).slice(0, 4)"
                      :key="index"
                      class="text-xs px-2 py-1 rounded-md"
                      :style="{
                        backgroundColor: currentService.color + '20',
                        color: currentService.color
                      }"
                    >
                      {{ specialty }}
                    </span>
                    <span
                      v-if="intervenant.specialties && intervenant.specialties.length > 4"
                      class="text-xs px-2 py-1 rounded-md text-gray-500"
                    >
                      +{{ intervenant.specialties.length - 4 }} autres
                    </span>
                  </div>
                </div>

                <!-- Icons -->
                <div class="space-y-2 mb-4">
                  <div class="flex items-center gap-2 text-gray-700 text-sm">
                    <MapPin :size="16" :style="{ color: currentService.color }" />
                    <span>{{ intervenant.location }}</span>
                  </div>
                </div>

                <!-- Rating -->
                <div class="flex items-center mb-4 pb-4 border-b border-gray-200">
                  <div class="flex items-center gap-2">
                    <Star :size="16" class="fill-yellow-400 text-yellow-400" />
                    <span class="font-medium">{{ intervenant.rating }}</span>
                    <span class="text-gray-500 text-sm">({{ intervenant.reviewCount }} avis)</span>
                  </div>
                </div>

                <!-- Button -->
                <button
                  @click="viewProfile(intervenant)"
                  class="w-full py-2.5 rounded-lg text-white transition-all hover:opacity-90"
                  :style="{ backgroundColor: currentService.color }"
                >
                  Voir le profil
                </button>
              </div>
            </div>
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
import { ArrowLeft, Star, MapPin, Search, Filter, SlidersHorizontal } from 'lucide-vue-next';
import intervenantService from '@/services/intervenantService';
import serviceService from '@/services/serviceService';

export default {
  name: 'AllIntervenantsPage',
  components: {
    ArrowLeft,
    Star,
    MapPin,
    Search,
    Filter,
    SlidersHorizontal
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
      priceRange: [10, 30],
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
      
      if (typeof this.service === 'number') {
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
        
        const matchesPrice = intervenant.hourlyRate >= this.priceRange[0] && 
                            intervenant.hourlyRate <= this.priceRange[1];
        
        let matchesRating = true;
        if (this.selectedRating !== 'all' && typeof this.selectedRating === 'number') {
          matchesRating = intervenant.rating >= this.selectedRating;
        }
        
        const matchesMaterial = !this.bringsMaterial || intervenant.bringsMaterial;
        const matchesEco = !this.ecoProducts || intervenant.ecoFriendly;
        
        return matchesSearch && matchesCity && matchesServiceTypeFilter && matchesServiceType && matchesPrice && matchesRating && matchesMaterial && matchesEco;
      });
    },
    sortedIntervenants() {
      const sorted = [...this.filteredIntervenants];
      if (this.sortBy === 'rating-desc' || this.sortBy === 'rating') return sorted.sort((a, b) => b.rating - a.rating);
      if (this.sortBy === 'rating-asc') return sorted.sort((a, b) => a.rating - b.rating);
      return sorted;
    }
  },
  methods: {
    async loadServiceInfo() {
      if (typeof this.service === 'number') {
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
        // Suppression du loading state visible
        // if (showLoading) {
        //   this.loadingIntervenants = true;
        // }
        
        const params = { active: 'true' };
        
        if (typeof this.service === 'number') {
          params.serviceId = this.service;
        }
        
        const response = await intervenantService.getAll(params);
        const intervenants = response.data || [];
        
        this.intervenantsFromApi = intervenants.map(intervenant => {
          const utilisateur = intervenant.utilisateur || {};
          const taches = intervenant.taches || [];
          
          const specialties = taches.map(tache => tache.nom_tache || tache.name || '').filter(Boolean);
          
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
          
          if (typeof this.service === 'number') {
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
            image: intervenant.image_url || utilisateur.photo || 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=150&h=150&fit=crop',
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
      this.priceRange = [10, 30];
      this.selectedRating = 'all';
      this.bringsMaterial = false;
      this.ecoProducts = false;
    }
  }
};
</script>