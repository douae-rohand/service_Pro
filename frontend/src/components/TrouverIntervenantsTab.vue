<template>
  <div class="min-h-screen bg-gray-50">
    <div v-if="currentView === 'list'">
    <!-- Top Search Bar -->
    <div class="bg-white shadow-lg sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-4xl font-bold mb-6" style="color: #2f4f4f">
          Trouver des intervenants
        </h1>

        <!-- Search inputs -->
        <div class="grid md:grid-cols-3 gap-4">
          <div class="relative">
            <Search class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" :size="20" />
            <input
              type="text"
              v-model="searchQuery"
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
              <option value="all">Tous les services</option>
              <option v-for="service in services" :key="service.id" :value="service.id">
                {{ service.nom_service }}
              </option>
            </select>
          </div>
          
          <button
            @click="applyFilters"
            class="py-3 rounded-lg text-white font-medium transition-all hover:opacity-90 flex items-center justify-center"
            style="background-color: #1a5fa3"
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
          <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-32">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-bold flex items-center gap-2" style="color: #2f4f4f">
                <SlidersHorizontal :size="20" />
                Filtres
              </h3>
              <button
                @click="resetFilters"
                class="text-sm px-3 py-1.5 rounded-lg hover:opacity-80 transition-all"
                style="color: #1a5fa3; background-color: #1a5fa320"
              >
                Réinitialiser
              </button>
            </div>

            <!-- Ville -->
            <div class="mb-6">
              <label class="block text-sm font-semibold mb-2">Ville</label>
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
              <label class="block text-sm font-semibold mb-3">Service</label>
              <div class="space-y-2 max-h-48 overflow-y-auto">
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
                    style="accent-color: #1a5fa3"
                  />
                  <span class="text-sm">{{ service.nom_service }}</span>
                </label>
              </div>
            </div>

          
            <!-- Disponibilité -->
            <div class="mb-6">
              <label class="block text-sm font-semibold mb-3">Disponibilité</label>
              <div class="space-y-2">
                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors">
                  <input
                    type="checkbox"
                    v-model="availableNow"
                    @change="applyFilters"
                    class="w-4 h-4 cursor-pointer"
                    style="accent-color: #1a5fa3"
                  />
                  <span class="text-sm">Disponible maintenant (actifs)</span>
                </label>
              </div>
            </div>

            <!-- Note minimum -->
            <div class="mb-6">
              <label class="block text-sm font-semibold mb-3">Note minimum</label>
              <div class="space-y-2">
                <label
                  v-for="rating in ratingOptions"
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
                    style="accent-color: #1a5fa3"
                  />
                  <span class="text-sm">{{ rating.label }}</span>
                </label>
              </div>
            </div>
          </div>
        </aside>

        <!-- Right Content - Results -->
        <main class="flex-1">
          <!-- Loading State -->
          <div v-if="loading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-4 text-gray-600">Chargement des intervenants...</p>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600 mb-6">
            <AlertCircle :size="20" class="inline mr-2" />
            {{ error }}
          </div>

          <div v-else>
            <!-- Results header -->
            <div class="flex items-center justify-between mb-6">
              <p class="text-gray-600">
                <span class="font-bold text-gray-900">{{ pagination.total }}</span>
                intervenant{{ pagination.total > 1 ? 's' : '' }} trouvé{{ pagination.total > 1 ? 's' : '' }}
                <span v-if="pagination.total > filteredIntervenants.length" class="text-sm text-gray-500">
                  (affichés: {{ filteredIntervenants.length }})
                </span>
              </p>
              <select
                v-model="sortBy"
                @change="sortIntervenants"
                class="px-4 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none cursor-pointer"
              >
                <option value="pertinence">Pertinence</option>
                <option value="rating">Note décroissante</option>
                <option value="price-asc">Prix croissant</option>
                <option value="price-desc">Prix décroissant</option>
                <option value="experience">Expérience</option>
              </select>
            </div>

            <!-- Intervenants Grid -->
            <div class="grid md:grid-cols-2 gap-6">
              <div
                v-for="intervenant in sortedIntervenants"
                :key="intervenant.id"
                class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all overflow-hidden cursor-pointer"
                @click="viewIntervenantProfile(intervenant.id)"
              >
                <!-- Image with badges -->
                <div class="relative h-48">
                  <img
                    :src="getIntervenantImage(intervenant)"
                    :alt="getIntervenantName(intervenant)"
                    class="w-full h-full object-cover"
                  />
                  <div
                    v-if="intervenant.is_active"
                    class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-medium text-white"
                    style="background-color: #1a5fa3"
                  >
                    ✓ Vérifié
                  </div>
                  <div
                    v-if="getIntervenantRating(intervenant) >= 4.5"
                    class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-medium text-white bg-yellow-500"
                  >
                    ⭐ Top
                  </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                  <div class="mb-3">
                    <h3 class="text-xl font-bold mb-1">
                      {{ getIntervenantName(intervenant) }}
                    </h3>
                    <p class="text-sm text-gray-600">{{ intervenant.bio || 'Aucune bio disponible' }}</p>
                  </div>

                  <!-- Specialties -->
                  <div class="mb-4">
                    <div class="flex flex-wrap gap-2">
                      <span
                        v-for="service in getIntervenantServices(intervenant)"
                        :key="service.id"
                        class="text-xs px-2 py-1 rounded-md"
                        style="background-color: #1a5fa320; color: #1a5fa3"
                      >
                        {{ service.nom_service }}
                      </span>
                    </div>
                  </div>

                  <!-- Icons -->
                  <div class="space-y-2 mb-4">
                    <div class="flex items-center gap-2 text-gray-700 text-sm">
                      <MapPin :size="16" style="color: #1a5fa3" />
                      <span>{{ intervenant.ville || 'Ville non spécifiée' }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-700 text-sm">
                      <Briefcase :size="16" style="color: #1a5fa3" />
                      <span>{{ getIntervenantExperience(intervenant) }}</span>
                    </div>
                    <div v-if="intervenant.utilisateur?.telephone" class="flex items-center gap-2 text-gray-700 text-sm">
                      <Phone :size="16" style="color: #1a5fa3" />
                      <span>{{ intervenant.utilisateur.telephone }}</span>
                    </div>
                  </div>

                  <!-- Rating and price -->
                  <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                      <Star :size="16" class="fill-yellow-400 text-yellow-400" />
                      <span class="font-medium">{{ getIntervenantRating(intervenant) }}</span>
                      <span class="text-gray-500 text-sm">({{ getReviewCount(intervenant) }} avis)</span>
                    </div>
                    
                  </div>

                  <!-- Buttons -->
                  <div class="flex gap-2">
                    <button
                      @click.stop="viewIntervenantProfile(intervenant.id)"
                      class="flex-1 py-2.5 rounded-lg text-white transition-all hover:opacity-90"
                      style="background-color: #1a5fa3"
                    >
                      Voir le profil
                    </button>
                    <button
                      @click.stop="addToFavorites(intervenant)"
                      class="px-4 py-2.5 rounded-lg border-2 transition-all hover:bg-red-50"
                      style="border-color: #1a5fa3; color: #1a5fa3"
                      title="Ajouter aux favoris"
                    >
                      <Heart :size="18" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- No results -->
            <div v-if="!loading && !error && filteredIntervenants.length === 0" class="text-center py-16">
              <div class="text-6xl mb-4">🔍</div>
              <h3 class="text-2xl font-bold mb-2">Aucun intervenant trouvé</h3>
              <p class="text-gray-600 mb-6">Essayez de modifier vos critères de recherche</p>
              <button
                @click="resetFilters"
                class="px-6 py-3 rounded-lg text-white transition-all hover:opacity-90"
                style="background-color: #1a5fa3"
              >
                Réinitialiser les filtres
              </button>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.total > 0 && pagination.last_page > 1" class="flex justify-center items-center gap-2 mt-8">
              <button
                @click="prevPage"
                :disabled="pagination.current_page === 1"
                class="px-4 py-2 rounded-lg border transition-all"
                :class="pagination.current_page === 1 
                  ? 'border-gray-300 text-gray-400 cursor-not-allowed' 
                  : 'border-gray-300 text-gray-700 hover:bg-gray-50'"
              >
                ← Précédent
              </button>
              
              <div class="flex items-center gap-1">
                <span 
                  v-for="page in visiblePages" 
                  :key="page"
                  @click="goToPage(page)"
                  class="px-3 py-1 rounded cursor-pointer transition-all"
                  :class="page === pagination.current_page 
                    ? 'bg-blue-500 text-white' 
                    : 'text-gray-700 hover:bg-gray-100'"
                >
                  {{ page }}
                </span>
                <span v-if="showEllipsis" class="px-2 text-gray-500">...</span>
              </div>
              
              <button
                @click="nextPage"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-4 py-2 rounded-lg border transition-all"
                :class="pagination.current_page === pagination.last_page 
                  ? 'border-gray-300 text-gray-400 cursor-not-allowed' 
                  : 'border-gray-300 text-gray-700 hover:bg-gray-50'"
              >
                Suivant →
              </button>
              
              <span class="text-sm text-gray-600 ml-4">
                Page {{ pagination.current_page }} sur {{ pagination.last_page }}
              </span>
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
  Coins,
  AlertCircle,
  Briefcase,
  Heart,
  Phone
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
    Coins,
    AlertCircle,
    Briefcase,
    Heart,
    Phone,
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
      minRating: 0,
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
      searchTimeout: null
    };
  },
  computed: {
    filteredIntervenants() {
      // Filtrage côté client pour les critères qui ne sont pas gérés par l'API
      return this.intervenants.filter(intervenant => {
        // Price filter (si l'API ne le gère pas)
        const price = this.getIntervenantPrice(intervenant);
        if (price < this.priceRange[0] || price > this.priceRange[1]) {
          return false;
        }
        
        // Rating filter (si l'API ne le gère pas)
        const rating = this.getIntervenantRating(intervenant);
        if (rating < this.minRating) {
          return false;
        }
        
        // Multiple services filter (si l'API ne gère qu'un seul service)
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
        case 'rating':
          return sorted.sort((a, b) => this.getIntervenantRating(b) - this.getIntervenantRating(a));
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
    
    ratingOptions() {
      return [
        { value: 0, label: 'Toutes les notes' },
        { value: 3, label: '3+ étoiles' },
        { value: 3.5, label: '3.5+ étoiles' },
        { value: 4, label: '4+ étoiles' },
        { value: 4.5, label: '4.5+ étoiles' },
        { value: 5, label: '5 étoiles' }
      ];
    },
    
    visiblePages() {
      const current = this.pagination.current_page;
      const last = this.pagination.last_page;
      const delta = 2;
      const range = [];
      const rangeWithDots = [];
      let l;
      
      for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
          range.push(i);
        }
      }
      
      range.forEach((i, index) => {
        if (index > 0 && i - range[index - 1] > 1) {
          rangeWithDots.push('...');
        }
        rangeWithDots.push(i);
      });
      
      return rangeWithDots.filter(p => p !== '...');
    },
    
    showEllipsis() {
      return this.pagination.last_page > 5 && this.pagination.current_page < this.pagination.last_page - 2;
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
      try {
        // Construire les paramètres pour l'API
        const params = {
        page: page,
        per_page: this.pagination.per_page
        };
        
        // Ajouter les filtres
         if (this.searchQuery) params.search = this.searchQuery;
        if (this.selectedCity !== 'all') params.ville = this.selectedCity;
        if (this.selectedServices.length > 0) params.service_id = this.selectedServices[0];
        if (this.availableNow) params.active = 'true';
        
         console.log('📤 API Params:', params);
        // Appeler le service
        const response = await intervenantService.getAllIntervenants(params);
        
         console.log('📦 API Response:', response);
        console.log('📦 Processing response:', response);

        // Structure de réponse paginée Laravel
        this.intervenants = response.data || [];

        this.pagination = {
        current_page: response.current_page || 1,
        last_page: response.last_page || 1,
        per_page: response.per_page || this.pagination.per_page,
        total: response.total || 0,
        from: response.from || 0,
        to: response.to || 0
        };
        
        // Extraire les villes uniques
        this.extractCities();
        
      } catch (error) {
        console.error('Error loading intervenants:', error);
        this.error = 'Erreur lors du chargement des intervenants.';
        this.intervenants = [];
        this.pagination.total = 0;
      } finally {
        this.loading = false;
      }
    },
    
    extractCities() {
      const citySet = new Set();
      this.intervenants.forEach(intervenant => {
        if (intervenant.ville) {
          citySet.add(intervenant.ville);
        }
      });
      this.cities = Array.from(citySet).sort();
    },
    
    getIntervenantImage(intervenant) {
      // Votre modèle Intervenant a une relation 'utilisateur'
      if (intervenant.utilisateur?.url) {
        return intervenant.utilisateur.url;
      }
      // Image par défaut basée sur le genre si disponible
      const gender = intervenant.utilisateur?.sexe || 'male';
      const defaultImages = {
        male: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&h=300&fit=crop',
        female: 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=400&h=300&fit=crop',
        other: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=400&h=300&fit=crop'
      };
      return defaultImages[gender] || defaultImages.male;
    },
    
    getIntervenantName(intervenant) {
      if (intervenant.utilisateur) {
        return `${intervenant.utilisateur.prenom || ''} ${intervenant.utilisateur.nom || ''}`.trim();
      }
      return 'Intervenant';
    },
    
    getIntervenantServices(intervenant) {
      // Votre API inclut 'taches.service' dans with()
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
    
    getIntervenantServiceIds(intervenant) {
      return this.getIntervenantServices(intervenant).map(service => service.id);
    },
    
    getIntervenantRating(intervenant) {
      // Utilisez average_rating si calculé par l'API
      if (intervenant.average_rating !== undefined) {
        return parseFloat(intervenant.average_rating).toFixed(1);
      }
      
      // Sinon, calculez depuis les interventions
      if (intervenant.interventions && Array.isArray(intervenant.interventions)) {
        const evaluations = intervenant.interventions
          .filter(intervention => intervention.evaluation && intervention.evaluation.note)
          .map(intervention => parseFloat(intervention.evaluation.note));
        
        if (evaluations.length > 0) {
          const sum = evaluations.reduce((acc, note) => acc + note, 0);
          return (sum / evaluations.length).toFixed(1);
        }
      }
      
      // Valeur par défaut pour les nouveaux intervenants
      return 4.0;
    },
    
    getReviewCount(intervenant) {
      // Utilisez review_count si fourni par l'API
      if (intervenant.review_count !== undefined) {
        return intervenant.review_count;
      }
      
      // Sinon, comptez les interventions avec évaluations
      if (intervenant.interventions && Array.isArray(intervenant.interventions)) {
        return intervenant.interventions.filter(intervention => 
          intervention.evaluation && intervention.evaluation.note
        ).length;
      }
      
      return 0;
    },
    
    getIntervenantPrice(intervenant) {
      // Votre relation taches a un pivot avec 'prix_tache'
      if (intervenant.taches && intervenant.taches.length > 0) {
        const prices = intervenant.taches
          .filter(tache => tache.pivot && tache.pivot.prix_tache)
          .map(tache => parseFloat(tache.pivot.prix_tache));
        
        if (prices.length > 0) {
          const avg = prices.reduce((a, b) => a + b, 0) / prices.length;
          return Math.round(avg);
        }
      }
      
      // Prix par défaut basé sur les services
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
      this.minRating = 0;
      this.availableNow = false;
      this.sortBy = 'pertinence';
      this.applyFilters();
    },
    
    async addToFavorites(intervenant) {
      try {
        // Déterminer le service ID (requis par le backend)
        let serviceId = null;
        if (this.selectedServices.length > 0) {
            serviceId = this.selectedServices[0];
        } else {
            const services = this.getIntervenantServices(intervenant);
            if (services.length > 0) serviceId = services[0].id;
        }

        if (!serviceId) {
             this.showToastMessage('Impossible d\'ajouter: aucun service détecté pour cet intervenant.', 'error');
             return;
        }

        await favoriteService.toggleFavorite(this.clientId, intervenant.id, serviceId);
        // Note: l'API retourne le nouveau statut. On pourrait mettre à jour l'UI ici si on stockait le statut.
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
      // Naviguer vers le profil de l'intervenant en interne
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

/* Custom scrollbar for services list */
.scrollbar-custom {
  scrollbar-width: thin;
  scrollbar-color: #1a5fa3 #f1f1f1;
}

.scrollbar-custom::-webkit-scrollbar {
  width: 6px;
}

.scrollbar-custom::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.scrollbar-custom::-webkit-scrollbar-thumb {
  background: #1a5fa3;
  border-radius: 3px;
}

.scrollbar-custom::-webkit-scrollbar-thumb:hover {
  background: #154a80;
}
</style>