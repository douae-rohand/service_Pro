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
                  @click="$emit('view-profile', intervenant.id)"
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
      intervenantsLoaded: false, // Indique si les intervenants ont été chargés au moins une fois
      serviceData: null,
      serviceTaches: [], // Les taches du service pour les filtres
      intervenantsFromApi: [],
      // Configuration des couleurs par nom de service (données UI uniquement)
      serviceColors: {
        'Jardinage': '#92B08B',
        'Ménage': '#4682B4',
      },
      // Mapping des IDs aux couleurs et noms pour un affichage immédiat
      serviceIdToColor: {
        1: '#92B08B', // Jardinage
        2: '#4682B4', // Ménage
      },
      serviceIdToName: {
        1: 'Jardiniers',
        2: 'Intervenants Ménage',
      },
    };
  },
  mounted() {
    // Charger les données en parallèle pour un chargement plus rapide
    // Ne pas utiliser await pour permettre l'affichage immédiat de la page
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
      this.loadIntervenants(true); // Afficher le loading lors du changement de service
    }
  },
  computed: {
    currentService() {
      // Utiliser les taches du service depuis l'API pour les types de service
      const serviceTypes = this.serviceTaches.length > 0
        ? this.serviceTaches.map(tache => tache.nom_tache).filter(Boolean)
        : [];
      
      // Si on a des données du service depuis l'API, les utiliser
      if (this.serviceData) {
        const serviceName = this.serviceData.nom_service;
        const color = this.serviceColors[serviceName] || '#6B7280';
        
        return {
          name: serviceName,
          color: color,
          serviceTypes: serviceTypes,
        };
      }
      
      // Fallback par défaut pendant le chargement - utiliser l'ID pour déterminer immédiatement nom et couleur
      let serviceName = 'Service';
      let color = '#6B7280';
      
      if (typeof this.service === 'number') {
        serviceName = this.serviceIdToName[this.service] || 'Service';
        color = this.serviceIdToColor[this.service] || '#6B7280';
      }
      
      return {
        name: serviceName,
        color: color, // Utiliser la couleur correcte dès le début
        serviceTypes: serviceTypes,
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
      // Toujours utiliser les intervenants de l'API (même si vide, pour éviter d'afficher les données statiques)
      const intervenants = this.intervenantsFromApi || [];
      
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
          // Un seul appel API - Laravel retourne le service avec ses taches incluses
          const response = await serviceService.getById(this.service);
          this.serviceData = response.data;
          
          // Les taches sont déjà incluses dans la réponse du backend Laravel
          this.serviceTaches = response.data.taches || [];
        } catch (error) {
          console.error('Erreur lors du chargement des informations du service:', error);
        }
      }
    },
    
    async loadIntervenants(showLoading = true) {
      try {
        // Afficher le loading seulement si explicitement demandé (pas au montage initial)
        if (showLoading) {
          this.loadingIntervenants = true;
        }
        
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
          
          return {
            id: intervenant.id,
            name: `${utilisateur.nom || ''} ${utilisateur.prenom || ''}`.trim() || 'Intervenant',
            rating: intervenant.average_rating || 0, // Note moyenne calculée depuis la base de données
            reviewCount: intervenant.review_count || 0, // Nombre d'avis calculé depuis la base de données
            hourlyRate: hourlyRate,
            location: intervenant.ville || utilisateur.address || 'Non spécifiée',
            image: intervenant.image_url || utilisateur.photo || 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=150&h=150&fit=crop', // Image depuis la base de données ou générique
            verified: intervenant.is_active !== false,
            specialties: specialties, // Toutes les spécialités
            taches: taches, // Garder les taches originales pour le filtrage
            experience: intervenant.bio || `${Math.floor(Math.random() * 10) + 1} ans d'expérience`,
            bringsMaterial: (intervenant.materiels && intervenant.materiels.length > 0) || false,
            ecoFriendly: Math.random() > 0.5, // Pour l'instant aléatoire
          };
        });
        
        // Marquer que les intervenants ont été chargés
        this.intervenantsLoaded = true;
        
      } catch (error) {
        console.error('Erreur lors du chargement des intervenants:', error);
        this.intervenantsFromApi = [];
        // Marquer comme chargé même en cas d'erreur
        this.intervenantsLoaded = true;
      } finally {
        if (showLoading) {
          this.loadingIntervenants = false;
        }
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