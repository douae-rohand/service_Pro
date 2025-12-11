<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="!currentService || loadingIntervenants" class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900"></div>
        <p class="mt-4 text-gray-600">{{ loadingIntervenants ? 'Chargement des intervenants...' : 'Chargement...' }}</p>
      </div>
    </div>

    <!-- Content -->
    <template v-else>
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

            <!-- Tarif horaire -->
            <div class="mb-6">
              <label class="block text-sm font-semibold mb-3">Tarif horaire</label>
              <input
                type="range"
                min="10"
                max="30"
                :value="priceRange[1]"
                @input="priceRange[1] = parseInt($event.target.value)"
                class="w-full"
                :style="{ accentColor: currentService.color }"
              />
              <div class="flex justify-between text-xs text-gray-600 mt-2">
                <span>{{ priceRange[0] }}DH/h</span>
                <span>{{ priceRange[1] }}DH/h</span>
              </div>
            </div>

            <!-- Note minimum -->
            <div class="mb-6">
              <label class="block text-sm font-semibold mb-3">Note</label>
              <div class="space-y-2">
                <label
                  v-for="rating in ['5+', '4.5+', '4+', '3.5+']"
                  :key="rating"
                  class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors"
                >
                  <input
                    type="radio"
                    name="rating"
                    :value="rating"
                    v-model="selectedRating"
                    class="w-4 h-4 cursor-pointer"
                    :style="{ accentColor: currentService.color }"
                  />
                  <span class="text-sm">{{ rating }}</span>
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
              <option value="rating">Note décroissante</option>
              <option value="price-asc">Prix croissant</option>
              <option value="price-desc">Prix décroissant</option>
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
                <div
                  v-if="intervenant.verified"
                  class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-medium text-white"
                  :style="{ backgroundColor: currentService.color }"
                >
                  ✓ Vérifié
                </div>
              </div>

              <!-- Content -->
              <div class="p-6">
                <div class="mb-3">
                  <h3 class="text-xl font-bold mb-1">{{ intervenant.name }}</h3>
                  <p class="text-sm text-gray-600">{{ intervenant.experience }}</p>
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

                <!-- Rating and price -->
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                  <div class="flex items-center gap-2">
                    <Star :size="16" class="fill-yellow-400 text-yellow-400" />
                    <span class="font-medium">{{ intervenant.rating }}</span>
                    <span class="text-gray-500 text-sm">({{ intervenant.reviewCount }} avis)</span>
                  </div>
                  <div class="flex items-center gap-1 font-bold" :style="{ color: currentService.color }">
                    <Coins :size="18" />
                    {{ intervenant.hourlyRate }}DH/h
                  </div>
                </div>

                <!-- Button -->
                <button
                  @click="$emit('view-profile', intervenant.id)"
                  class="w-full py-2.5 rounded-lg text-white transition-all hover:opacity-90"
                  :style="{ backgroundColor: currentService.color }"
                >
                  Voir le profil
                </button>
              </div>
            </div>
          </div>

          <!-- No results -->
          <div v-if="sortedIntervenants.length === 0" class="text-center py-16">
            <div class="text-6xl mb-4">🔍</div>
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
    </template>
  </div>
</template>

<script>
import { ArrowLeft, Star, Coins, MapPin, Search, Filter, SlidersHorizontal } from 'lucide-vue-next';
import intervenantService from '@/services/intervenantService';
import serviceService from '@/services/serviceService';

export default {
  name: 'AllIntervenantsPage',
  components: {
    ArrowLeft,
    Star,
    Coins,
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
      serviceData: null,
      serviceTaches: [], // Les taches du service pour les filtres
      intervenantsFromApi: [],
      // Mapping des IDs de service aux configurations
      serviceIdMapping: {
        1: 'jardinage',
        2: 'menage',
      },
      serviceInfo: {
        jardinage: {
          name: 'Jardiniers',
          color: '#92B08B',
          serviceTypes: ['Tonte de pelouse', 'Taille de haies', 'Plantation de fleurs', 'Élagage', 'Entretien potager'],
          intervenants: [
            {
              id: 1,
              name: 'Youssef Benali',
              rating: 4.9,
              reviewCount: 127,
              hourlyRate: 25,
              location: 'Tetouan Centre',
              image: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Tonte de pelouse', 'Taille de haies', 'Plantation de fleurs'],
              experience: '10 ans d\'expérience',
              bringsMaterial: true,
              ecoFriendly: true,
            },
            {
              id: 2,
              name: 'Hassan Alami',
              rating: 4.8,
              reviewCount: 156,
              hourlyRate: 28,
              location: 'Tetouan Medina',
              image: 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Élagage', 'Entretien potager'],
              experience: '8 ans d\'expérience',
              bringsMaterial: true,
              ecoFriendly: false,
            },
            {
              id: 3,
              name: 'Omar El Amrani',
              rating: 4.7,
              reviewCount: 94,
              hourlyRate: 26,
              location: 'Tetouan Saniat Rmel',
              image: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Tonte de pelouse', 'Taille de haies', 'Plantation de fleurs'],
              experience: '6 ans d\'expérience',
              bringsMaterial: false,
              ecoFriendly: true,
            },
            {
              id: 4,
              name: 'Karim Berrada',
              rating: 5.0,
              reviewCount: 203,
              hourlyRate: 30,
              location: 'Tetouan Martil',
              image: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Tonte de pelouse', 'Élagage', 'Plantation de fleurs'],
              experience: '15 ans d\'expérience',
              bringsMaterial: true,
              ecoFriendly: true,
            },
            {
              id: 5,
              name: 'Mehdi Fahmi',
              rating: 4.6,
              reviewCount: 78,
              hourlyRate: 24,
              location: 'Tetouan M\'diq',
              image: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop',
              verified: false,
              specialties: ['Entretien potager', 'Plantation de fleurs'],
              experience: '4 ans d\'expérience',
              bringsMaterial: false,
              ecoFriendly: true,
            },
            {
              id: 6,
              name: 'Amine Idrissi',
              rating: 4.9,
              reviewCount: 165,
              hourlyRate: 27,
              location: 'Tetouan Fnideq',
              image: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Taille de haies', 'Élagage', 'Plantation de fleurs'],
              experience: '9 ans d\'expérience',
              bringsMaterial: true,
              ecoFriendly: false,
            },
          ],
        },
        menage: {
          name: 'Intervenants Ménage',
          color: '#4682B4',
          serviceTypes: ['Ménage résidentiel & régulier', 'Nettoyage en profondeur (Deep Cleaning)', 'Nettoyage spécial : déménagement & post-travaux', 'Lavage vitres & surfaces spécialisées', 'Nettoyage mobilier & textiles', 'Nettoyage professionnel (bureaux & commerces)'],
          intervenants: [
            {
              id: 1,
              name: 'Fatima Tazi',
              rating: 5.0,
              reviewCount: 89,
              hourlyRate: 22,
              location: 'Tetouan M\'diq',
              image: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Ménage résidentiel & régulier', 'Nettoyage mobilier & textiles'],
              experience: '5 ans d\'expérience',
              bringsMaterial: true,
              ecoFriendly: true,
            },
            {
              id: 2,
              name: 'Amina Chakir',
              rating: 4.9,
              reviewCount: 203,
              hourlyRate: 24,
              location: 'Tetouan Centre',
              image: 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Nettoyage en profondeur (Deep Cleaning)', 'Nettoyage professionnel (bureaux & commerces)'],
              experience: '12 ans d\'expérience',
              bringsMaterial: true,
              ecoFriendly: false,
            },
            {
              id: 3,
              name: 'Salma Moussaoui',
              rating: 5.0,
              reviewCount: 145,
              hourlyRate: 23,
              location: 'Tetouan Medina',
              image: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Nettoyage spécial : déménagement & post-travaux', 'Lavage vitres & surfaces spécialisées'],
              experience: '6 ans d\'expérience',
              bringsMaterial: false,
              ecoFriendly: true,
            },
            {
              id: 4,
              name: 'Nadia Hassani',
              rating: 4.8,
              reviewCount: 112,
              hourlyRate: 21,
              location: 'Tetouan Martil',
              image: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Ménage résidentiel & régulier', 'Nettoyage professionnel (bureaux & commerces)'],
              experience: '4 ans d\'expérience',
              bringsMaterial: true,
              ecoFriendly: false,
            },
            {
              id: 5,
              name: 'Khadija Benali',
              rating: 4.7,
              reviewCount: 95,
              hourlyRate: 22,
              location: 'Tetouan Fnideq',
              image: 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Nettoyage mobilier & textiles', 'Lavage vitres & surfaces spécialisées'],
              experience: '3 ans d\'expérience',
              bringsMaterial: false,
              ecoFriendly: false,
            },
            {
              id: 6,
              name: 'Leila Idrissi',
              rating: 5.0,
              reviewCount: 178,
              hourlyRate: 25,
              location: 'Tetouan Saniat Rmel',
              image: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Nettoyage en profondeur (Deep Cleaning)', 'Nettoyage spécial : déménagement & post-travaux'],
              experience: '10 ans d\'expérience',
              bringsMaterial: true,
              ecoFriendly: true,
            },
          ],
        },
      },
      // Les villes seront extraites dynamiquement depuis les intervenants
    };
  },
  async mounted() {
    await this.loadServiceInfo();
    await this.loadIntervenants();
  },
  watch: {
    service() {
      this.loadServiceInfo();
      this.loadIntervenants();
    }
  },
  computed: {
    currentService() {
      // Si service est un nombre, utiliser le mapping, sinon utiliser directement
      const serviceKey = typeof this.service === 'number' 
        ? this.serviceIdMapping[this.service] 
        : this.service;
      
      // Utiliser les taches du service depuis l'API pour les types de service
      const serviceTypes = this.serviceTaches.length > 0
        ? this.serviceTaches.map(tache => tache.nom_tache).filter(Boolean)
        : [];
      
      // Si on a des données du service depuis l'API, les utiliser
      if (this.serviceData) {
        const serviceName = this.serviceData.nom_service;
        const config = this.serviceInfo[serviceKey] || {
          name: serviceName,
          color: '#6B7280',
          serviceTypes: [],
        };
        
        return {
          ...config,
          name: config.name || serviceName,
          serviceTypes: serviceTypes.length > 0 ? serviceTypes : (config.serviceTypes || []),
        };
      }
      
      // Sinon utiliser les données statiques
      return this.serviceInfo[serviceKey] || {
        name: 'Service',
        color: '#6B7280',
        serviceTypes: serviceTypes,
        intervenants: [],
      };
    },
    
    // Extraire les villes depuis les intervenants chargés
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
      // Utiliser les intervenants de l'API si disponibles, sinon utiliser les données statiques
      const intervenants = this.intervenantsFromApi.length > 0 
        ? this.intervenantsFromApi 
        : (this.currentService?.intervenants || []);
      
      if (!intervenants || intervenants.length === 0) {
        return [];
      }
      
      return intervenants.filter((intervenant) => {
        // Recherche par texte (nom ou ville)
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
        
        // Filtre par ville (select de la sidebar)
        const matchesCity = this.selectedCity === 'all' || 
                          this.selectedCity === 'Toutes les villes' || 
                          intervenant.location === this.selectedCity;
        
        // Filtre par type de service depuis la barre de recherche (si utilisé)
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
        
        // Filtre par type de service depuis la sidebar (checkboxes)
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
        
        // Filtre par prix
        const matchesPrice = intervenant.hourlyRate >= this.priceRange[0] && 
                            intervenant.hourlyRate <= this.priceRange[1];
        
        // Filtre par note
        let matchesRating = true;
        if (this.selectedRating === '5+') matchesRating = intervenant.rating >= 5.0;
        else if (this.selectedRating === '4.5+') matchesRating = intervenant.rating >= 4.5;
        else if (this.selectedRating === '4+') matchesRating = intervenant.rating >= 4.0;
        else if (this.selectedRating === '3.5+') matchesRating = intervenant.rating >= 3.5;
        
        // Filtres supplémentaires
        const matchesMaterial = !this.bringsMaterial || intervenant.bringsMaterial;
        const matchesEco = !this.ecoProducts || intervenant.ecoFriendly;
        
        return matchesSearch && matchesCity && matchesServiceTypeFilter && matchesServiceType && matchesPrice && matchesRating && matchesMaterial && matchesEco;
      });
    },
    sortedIntervenants() {
      const sorted = [...this.filteredIntervenants];
      if (this.sortBy === 'rating') return sorted.sort((a, b) => b.rating - a.rating);
      if (this.sortBy === 'price-asc') return sorted.sort((a, b) => a.hourlyRate - b.hourlyRate);
      if (this.sortBy === 'price-desc') return sorted.sort((a, b) => b.hourlyRate - a.hourlyRate);
      return sorted;
    }
  },
  methods: {
    async loadServiceInfo() {
      // Charger les données du service depuis l'API si c'est un ID
      if (typeof this.service === 'number') {
        try {
          const response = await serviceService.getById(this.service);
          this.serviceData = response.data;
          
          // Charger les taches du service pour les filtres
          const tachesResponse = await serviceService.getTaches(this.service);
          this.serviceTaches = tachesResponse.data || [];
        } catch (error) {
          console.error('Erreur lors du chargement des informations du service:', error);
        }
      }
    },
    
    async loadIntervenants() {
      try {
        this.loadingIntervenants = true;
        
        // Préparer les paramètres de requête
        const params = { active: 'true' };
        
        // Si un service est spécifié, filtrer par serviceId côté backend
        if (typeof this.service === 'number') {
          params.serviceId = this.service;
        }
        
        // Récupérer les intervenants depuis l'API
        const response = await intervenantService.getAll(params);
        const intervenants = response.data || [];
        
        // Mapper les données de l'API vers le format attendu par le template
        this.intervenantsFromApi = intervenants.map(intervenant => {
          const utilisateur = intervenant.utilisateur || {};
          const taches = intervenant.taches || [];
          
          // Extraire les spécialités depuis les taches
          const specialties = taches.map(tache => tache.nom_tache || tache.name || '').filter(Boolean);
          
          // Calculer le tarif moyen depuis les pivots ou utiliser une valeur par défaut
          let hourlyRate = 25; // Par défaut
          if (taches.length > 0) {
            const rates = taches
              .map(t => t.pivot?.prix_tache || t.pivot?.prixTache)
              .filter(Boolean);
            if (rates.length > 0) {
              hourlyRate = Math.round(rates.reduce((a, b) => a + b, 0) / rates.length);
            }
          }
          
          // Calculer la note moyenne (pour l'instant valeur par défaut, peut être calculée depuis les évaluations)
          const rating = 4.5; // Valeur par défaut
          const reviewCount = 0; // Peut être calculé depuis les évaluations
          
          return {
            id: intervenant.id,
            name: `${utilisateur.nom || ''} ${utilisateur.prenom || ''}`.trim() || 'Intervenant',
            rating: rating,
            reviewCount: reviewCount,
            hourlyRate: hourlyRate,
            location: intervenant.ville || utilisateur.address || 'Non spécifiée',
            image: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=150&h=150&fit=crop', // Image par défaut
            verified: intervenant.is_active !== false,
            specialties: specialties, // Toutes les spécialités
            taches: taches, // Garder les taches originales pour le filtrage
            experience: intervenant.bio || `${Math.floor(Math.random() * 10) + 1} ans d'expérience`,
            bringsMaterial: (intervenant.materiels && intervenant.materiels.length > 0) || false,
            ecoFriendly: Math.random() > 0.5, // Pour l'instant aléatoire
          };
        });
        
      } catch (error) {
        console.error('Erreur lors du chargement des intervenants:', error);
        this.intervenantsFromApi = [];
      } finally {
        this.loadingIntervenants = false;
      }
    },
    toggleServiceType(type) {
      if (this.selectedServiceTypes.includes(type)) {
        this.selectedServiceTypes = this.selectedServiceTypes.filter(t => t !== type);
      } else {
        this.selectedServiceTypes.push(type);
      }
    },
    applySearch() {
      // Appliquer les filtres de la barre de recherche
      // Si citySearch contient quelque chose, mettre à jour selectedCity si correspond
      if (this.citySearch.trim()) {
        const searchText = this.citySearch.trim().toLowerCase();
        const matchingCity = this.cities.find(city => 
          city !== 'Toutes les villes' && city.toLowerCase().includes(searchText)
        );
        if (matchingCity) {
          this.selectedCity = matchingCity;
        }
      }
      
      // Si serviceTypeFilter est sélectionné, l'ajouter aux selectedServiceTypes
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