<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Content (affiché immédiatement) -->
    <div v-if="!error">
      <!-- Header -->
      <div class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50 transition-all duration-300">
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

            <!-- Sorting Dropdown - aligned to the right -->
            <div class="group relative flex items-center gap-2 bg-white px-4 py-2 rounded-full border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
              <span class="text-gray-400 font-medium text-xs uppercase tracking-wide">Trier par</span>
              <select
                v-model="sortBy"
                class="bg-transparent border-none font-bold text-gray-700 text-sm focus:ring-0 cursor-pointer outline-none pl-0 pr-6 appearance-none"
                style="background-image: none;"
              >
                <option value="pertinence">Pertinence</option>
                <option value="rating">Note (Meilleure)</option>
                <option value="price-asc">Prix (Croissant)</option>
                <option value="price-desc">Prix (Décroissant)</option>
              </select>
              <div class="absolute right-3 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
              </div>
            </div>
          </div>

          <!-- Counter row -->
          <div class="flex items-center gap-2 ml-14" v-if="taskData">
            <span class="inline-flex items-center justify-center bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-full">
              {{ sortedIntervenants.length }}
            </span>
            <p class="text-gray-500 font-medium text-sm">
              intervenant{{ sortedIntervenants.length > 1 ? 's' : '' }} disponible{{ sortedIntervenants.length > 1 ? 's' : '' }}
            </p>
          </div>
          <div v-else class="animate-pulse ml-14">
            <div class="h-4 bg-gray-200 rounded-lg w-48"></div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
import { ArrowLeft, Star, MapPin, Clock, Calendar, CheckCircle, Briefcase } from 'lucide-vue-next';
import intervenantService from '@/services/intervenantService';
import serviceService from '@/services/serviceService';
import { formatExperience } from '@/utils/experienceFormatter';

export default {
  name: 'TaskIntervenantsPage',
  components: {
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
  emits: ['back', 'view-profile'],
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
    };
  },
  mounted() {
    this.loadData();
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

          return {
            id: intervenant.id,
            name: `${utilisateur.nom || ''} ${utilisateur.prenom || ''}`.trim() || 'Intervenant',
            rating: intervenant.average_rating || 0,
            reviewCount: intervenant.review_count || 0,
            interv_count: intervenant.interv_count || 0,
            hourlyRate: hourlyRate,
            taskPrice: taskPrice, // Prix spécifique pour cette tâche
            location: intervenant.ville || utilisateur.address || 'Non spécifiée',
            image: intervenant.image_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(utilisateur.prenom + ' ' + utilisateur.nom)}&background=random&color=fff`,
            verified: intervenant.is_active !== false,
            otherSpecialties: otherSpecialties,
            experience: realExperience,
            description: intervenant.bio || 'Professionnel expérimenté et fiable',
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
      // Émettre un événement pour la réservation
      // Vous pouvez ajouter une logique de réservation ici
      console.log('Réserver avec:', intervenant);
      // Pour l'instant, rediriger vers le profil
      this.viewProfile(intervenant);
    },
    formatExperience
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
</style>

