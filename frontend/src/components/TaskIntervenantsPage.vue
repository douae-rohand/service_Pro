<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Content (affiché immédiatement) -->
    <div v-if="!error">
      <!-- Header -->
      <div class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
              <button
                @click="$emit('back')"
                @mouseenter="hoverBackButton = true"
                @mouseleave="hoverBackButton = false"
                class="flex items-center gap-2 text-gray-600 hover:text-white transition-all px-4 py-2 rounded-lg"
                :style="{ backgroundColor: hoverBackButton ? currentService.color : 'transparent' }"
              >
                <ArrowLeft :size="20" />
              </button>
              <div v-if="taskData">
                <h1 class="text-4xl font-bold" :style="{ color: currentService.color }">
                  {{ taskData.nom_tache }}
                </h1>
                <p class="text-gray-600 mt-2">
                  {{ sortedIntervenants.length }} intervenant{{ sortedIntervenants.length > 1 ? 's' : '' }} disponible{{ sortedIntervenants.length > 1 ? 's' : '' }} pour cette tâche
                </p>
              </div>
               <!-- Skeleton Loader pour le titre si pas encore chargé -->
              <div v-else class="animate-pulse w-full">
                <div class="h-8 bg-gray-200 rounded w-64 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-48"></div>
              </div>
            </div>

            <!-- Sorting Dropdown -->
            <div class="flex items-center gap-3 bg-gray-50 px-4 py-2 rounded-lg border border-gray-200">
              <span class="text-gray-600 font-medium">Trier par :</span>
              <select
                v-model="sortBy"
                class="bg-transparent border-none font-semibold focus:ring-0 cursor-pointer text-gray-800 outline-none"
              >
                <option value="pertinence">Pertinence</option>
                <option value="rating">Note (Décroissant)</option>
                <option value="price-asc">Prix (Croissant)</option>
                <option value="price-desc">Prix (Décroissant)</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Intervenants Grid -->
        <div class="grid md:grid-cols-2 gap-6">
          <div
            v-for="intervenant in paginatedIntervenants"
            :key="intervenant.id"
            class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all overflow-hidden"
          >
            <!-- Content -->
            <div class="p-6">
              <!-- Profile Header -->
              <div class="flex items-start gap-4 mb-4">
                <!-- Profile Picture -->
                <div class="relative flex-shrink-0">
                  <img
                    :src="intervenant.image"
                    :alt="intervenant.name"
                    class="w-20 h-20 rounded-full object-cover border-2"
                    :style="{ borderColor: currentService.color }"
                    @error="handleImageError"
                  />
                </div>

                <!-- Name and Info -->
                <div class="flex-1">
                  <h3 class="text-xl font-bold mb-1">{{ intervenant.name }}</h3>
                  <p class="text-sm text-gray-600 mb-2">{{ formatExperience(intervenant.experience) }} d'expérience</p>
                  
                  <!-- Rating -->
                  <div class="flex items-center gap-2 mb-2">
                    <Star :size="16" class="fill-yellow-400 text-yellow-400" />
                    <span class="font-semibold">{{ intervenant.rating }}</span>
                    <span class="text-gray-500 text-sm">({{ intervenant.reviewCount }} avis)</span>
                  </div>

                  <!-- Description -->
                  <p class="text-sm text-gray-700 mb-3">{{ intervenant.description || 'Professionnel expérimenté' }}</p>
                </div>
              </div>

              <!-- Location and Price -->
              <div class="space-y-2 mb-4">
                <div class="flex items-center gap-2 text-gray-700 text-sm">
                  <MapPin :size="16" :style="{ color: currentService.color }" />
                  <span>{{ intervenant.location }}</span>
                </div>
                <div class="flex items-center gap-2 text-gray-700 text-sm">
                  <Clock :size="16" :style="{ color: currentService.color }" />
                  <span v-if="intervenant.taskPrice">{{ intervenant.taskPrice }} DH/h</span>
                  <span v-else class="text-sm font-medium">Sur devis</span>
                </div>
              </div>

              <!-- Other Specialties -->
              <div class="mb-4">
                <p class="text-xs text-gray-500 mb-2 font-semibold">Autres spécialités :</p>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="(specialty, index) in (intervenant.otherSpecialties || []).slice(0, 2)"
                    :key="index"
                    class="text-xs px-3 py-1 rounded-full"
                    :style="{
                      backgroundColor: currentService.color + '20',
                      color: currentService.color
                    }"
                  >
                    {{ specialty }}
                  </span>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex gap-3 mt-6">
                <button
                  @click="viewProfile(intervenant)"
                  class="flex-1 py-2.5 rounded-lg border-2 transition-all hover:opacity-90 font-medium"
                  :style="{ 
                    borderColor: currentService.color,
                    color: currentService.color,
                    backgroundColor: 'white'
                  }"
                >
                  Voir le profil
                </button>
                <button
                  @click="handleReserve(intervenant)"
                  class="flex-1 py-2.5 rounded-lg text-white transition-all hover:opacity-90 font-medium flex items-center justify-center gap-2"
                  :style="{ backgroundColor: currentService.color }"
                >
                  <Calendar :size="18" />
                  Réserver
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
import { ArrowLeft, Star, MapPin, Clock, Calendar, CheckCircle } from 'lucide-vue-next';
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
    CheckCircle
  },
  props: {
    taskId: {
      type: [String, Number],
      required: true
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
      try {
        // Récupérer les tâches du service pour trouver celle qui correspond
        const response = await serviceService.getTaches(this.serviceId);
        const taches = response.data || [];
        const task = taches.find(t => t.id == this.taskId);
        
        if (task) {
          this.taskData = {
            id: task.id,
            nom_tache: task.nom_tache || task.nomTache || 'Sous-service',
            description: task.description || ''
          };
        } else {
          // Si la tâche n'est pas trouvée, utiliser des valeurs par défaut
          this.taskData = { 
            id: this.taskId,
            nom_tache: 'Sous-service', 
            description: '' 
          };
        }
      } catch (error) {
        console.error('Erreur lors du chargement de la tâche:', error);
        this.taskData = { 
          id: this.taskId,
          nom_tache: 'Sous-service', 
          description: '' 
        };
      }
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
            .map(t => t.nom_tache || t.name || '')
            .filter(Boolean);
          
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
          const realExperience = currentServiceInfo?.pivot?.experience || 'Expérience confirmée';

          return {
            id: intervenant.id,
            name: `${utilisateur.nom || ''} ${utilisateur.prenom || ''}`.trim() || 'Intervenant',
            rating: intervenant.average_rating || 0,
            reviewCount: intervenant.review_count || 0,
            hourlyRate: hourlyRate,
            taskPrice: taskPrice, // Prix spécifique pour cette tâche
            location: intervenant.ville || utilisateur.address || 'Non spécifiée',
            image: intervenant.image_url || utilisateur.photo || 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=150&h=150&fit=crop',
            verified: intervenant.is_active !== false,
            otherSpecialties: otherSpecialties,
            experience: realExperience,
            description: intervenant.bio || 'Professionnel expérimenté et fiable',
            // Données complètes pour le profil
            fullData: {
              ...intervenant,
              experience: realExperience // Passer l'expérience correcte au profil
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

