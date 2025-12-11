<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Error banner (si erreur et pas de données) -->
    <div v-if="error && !serviceData" class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
      <div class="flex">
        <div class="flex-1">
          <p class="text-red-700">{{ error }}</p>
        </div>
        <div>
          <button
            @click="loadServiceData"
            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm mr-2"
          >
            Réessayer
          </button>
        </div>
      </div>
    </div>

    <!-- Content (affiché seulement quand les données sont chargées) -->
    <div v-if="serviceData && currentService">
      <!-- Header -->
      <div class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center gap-4">
            <button
              @click="$emit('back')"
              @mouseenter="hoverBackButton = true"
              @mouseleave="hoverBackButton = false"
              class="flex items-center gap-2 text-gray-600 hover:text-white transition-all mb-4 px-4 py-2 rounded-lg"
              :style="{ backgroundColor: hoverBackButton ? currentService.color : 'transparent' }"
            >
              <ArrowLeft :size="20" />
            </button>
            <div>
              <h1 class="text-5xl" :style="{ color: currentService.color }">
                Service de {{ currentService.name }}
              </h1>
              <p class="text-gray-700 mt-2 text-lg">
                Découvrez toutes nos tâches et nos meilleurs intervenants
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Tasks Section -->
      <section class="mb-16">
        <div class="text-center mb-12">
          <h2 class="text-5xl mb-4" :style="{ color: currentService.color }">
            Sous-service disponibles
          </h2>
          <p class="text-gray-600 text-lg">
            Choisissez parmi notre large gamme de services professionnels
          </p>
        </div>
        <div v-if="currentService.tasks && currentService.tasks.length > 0" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="(task, index) in currentService.tasks"
            :key="task.id"
            class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all transform hover:scale-105 cursor-pointer group"
            :style="{ animation: `fadeInUp 0.6s ease-out ${index * 0.1}s both` }"
          >
            <!-- Image -->
            <div class="relative h-56 overflow-hidden">
              <img
                :src="task.image"
                :alt="task.name"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
            </div>
            
            <!-- Content -->
            <div class="p-6">
              <h3 class="text-xl mb-2" :style="{ color: currentService.color }">
                {{ task.name }}
              </h3>
              <p class="text-gray-600 text-sm mb-4">{{ task.description }}</p>
              
              <button 
                class="w-full py-3 rounded-lg text-white transition-all hover:opacity-90 flex items-center justify-center gap-2"
                :style="{ backgroundColor: currentService.color }"
                @click="$emit('task-click', task.name)"
              >
                <CheckCircle :size="18" />
                Réserver
              </button>
            </div>
          </div>
        </div>
        <div v-else-if="serviceData && (!currentService.tasks || currentService.tasks.length === 0)" class="text-center py-12">
          <p class="text-gray-600">Aucun sous-service disponible pour le moment.</p>
        </div>
      </section>

      <!-- Intervenants Section -->
      <section class="bg-white rounded-2xl p-8 shadow-lg">
        <div class="text-center mb-10">
          <h2 class="text-4xl mb-3" :style="{ color: currentService.color }">
            Nos meilleurs intervenants
          </h2>
          <p class="text-gray-600">
            Des professionnels vérifiés et expérimentés à votre service
          </p>
        </div>
        <div v-if="currentService.intervenants && currentService.intervenants.length > 0" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="intervenant in currentService.intervenants"
            :key="intervenant.id"
            class="bg-gray-50 rounded-xl shadow-md hover:shadow-lg transition-all overflow-hidden"
          >
            <div class="p-6">
              <div class="flex items-start gap-4 mb-4">
                <img
                  :src="intervenant.image"
                  :alt="intervenant.name"
                  class="w-16 h-16 rounded-full object-cover border-3"
                  :style="{ borderColor: currentService.color }"
                />
                <div class="flex-1">
                  <h3 class="text-lg">{{ intervenant.name }}</h3>
                  <span
                    v-if="intervenant.verified"
                    class="text-xs px-2 py-1 rounded-full inline-block mt-1"
                    :style="{
                      backgroundColor: currentService.color,
                      color: 'white',
                    }"
                  >
                    ✓ Vérifié
                  </span>
                  <div class="flex items-center gap-2 mt-2">
                    <Star :size="16" class="fill-yellow-400 text-yellow-400" />
                    <span>{{ intervenant.rating }}</span>
                    <span class="text-gray-500 text-sm">({{ intervenant.reviewCount }} avis)</span>
                  </div>
                </div>
              </div>

              <div class="space-y-2 mb-4">
                <div class="flex items-center gap-2 text-gray-700 text-sm">
                  <MapPin :size="16" :style="{ color: currentService.color }" />
                  <span>{{ intervenant.location }}</span>
                </div>
              </div>

              <div class="mb-4">
                <p class="text-xs text-gray-500 mb-2">Spécialités :</p>
                <div class="flex flex-wrap gap-1.5">
                  <span
                    v-for="(specialty, index) in intervenant.specialties"
                    :key="index"
                    class="text-xs px-2 py-1 rounded-md"
                    :style="{
                      backgroundColor: currentService.color + '20',
                      color: currentService.color
                    }"
                  >
                    {{ specialty }}
                  </span>
                </div>
              </div>

              <button
                @click="$emit('view-profile', intervenant.id)"
                class="w-full text-white py-2.5 rounded-lg transition-all hover:opacity-90"
                :style="{ backgroundColor: currentService.color }"
              >
                Voir le profil
              </button>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-12">
          <p class="text-gray-600">Aucun intervenant disponible pour le moment.</p>
        </div>
        
        <!-- Button to view all intervenants -->
        <div class="text-center mt-12">
          <button
            @click="$emit('view-all-intervenants')"
            @mouseenter="hoverViewAll = true"
            @mouseleave="hoverViewAll = false"
            class="px-10 py-4 rounded-xl text-white text-lg transition-all transform hover:scale-105 hover:shadow-xl inline-flex items-center gap-3"
            :style="{ 
              backgroundColor: currentService.color,
              boxShadow: hoverViewAll 
                ? `0 15px 35px ${currentService.color}60`
                : `0 10px 25px ${currentService.color}40`
            }"
          >
            {{ currentService.name === 'Ménage' ? 'Voir tous les intervenants ménage' : 'Voir tous les jardiniers' }}
            <ArrowLeft :size="20" class="rotate-180" />
          </button>
        </div>
      </section>
    </div>
    </div>
  </div>
</template>

<script>
import { ArrowLeft, Star, MapPin, CheckCircle } from 'lucide-vue-next';
import serviceService from '@/services/serviceService';
import intervenantService from '@/services/intervenantService';

export default {
  name: 'ServiceDetailPage',
  components: {
    ArrowLeft,
    Star,
    MapPin,
    CheckCircle
  },
  props: {
    service: {
      type: [String, Number],
      required: true
    }
  },
  emits: ['back', 'view-all-intervenants', 'view-profile', 'task-click'],
  data() {
    return {
      hoverBackButton: false,
      hoverViewAll: false,
      loading: false,
      error: null,
      serviceData: null,
      taches: [],
      // Configuration des couleurs par nom de service (données UI uniquement)
      serviceColors: {
        'Jardinage': '#92B08B',
        'Ménage': '#4682B4',
      },
      // Mapping des IDs aux couleurs pour un affichage immédiat
      serviceIdToColor: {
        1: '#92B08B', // Jardinage
        2: '#4682B4', // Ménage
      },
      serviceIdToName: {
        1: 'Jardinage',
        2: 'Ménage',
      },
      intervenants: [],
    };
  },
  mounted() {
    // Charger les données immédiatement en arrière-plan sans bloquer l'affichage
    // Utiliser nextTick pour s'assurer que le DOM est rendu avant de charger
    this.$nextTick(() => {
      this.loadServiceData(false);
    });
  },
  watch: {
    service() {
      this.loadServiceData(true); // Afficher le loading lors du changement de service
    }
  },
  computed: {
    currentService() {
      // Fallback par défaut pendant le chargement - la structure s'affiche immédiatement avec la bonne couleur
      if (!this.serviceData) {
        // Utiliser l'ID pour déterminer immédiatement le nom et la couleur
        let serviceName = 'Service';
        let color = '#6B7280';
        
        if (typeof this.service === 'number') {
          serviceName = this.serviceIdToName[this.service] || 'Service';
          color = this.serviceIdToColor[this.service] || '#6B7280';
        }
        
        return {
          name: serviceName,
          color: color, // Utiliser la couleur correcte dès le début
          tasks: [],
          intervenants: [],
        };
      }
      
      const serviceName = this.serviceData.nom_service;
      const color = this.serviceColors[serviceName] || '#6B7280';
      
      // Image par défaut générique si aucune image n'est fournie par la base de données
      const defaultImage = 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=1080';
      
      return {
        id: this.serviceData.id,
        name: serviceName,
        description: this.serviceData.description || '',
        color: color,
        tasks: this.taches.map(tache => ({
          id: tache.id,
          name: tache.nom_tache,
          description: tache.description || '',
          image: tache.image_url || defaultImage,
        })),
        intervenants: this.intervenants,
      };
    }
  },
  methods: {
    async loadServiceData(showLoading = true) {
      try {
        // Afficher le loading seulement si explicitement demandé (pas au montage initial)
        if (showLoading) {
          this.loading = true;
        }
        this.error = null;
        
        // Récupérer le service (le backend Laravel retourne déjà les taches incluses)
        const serviceId = this.service;
        
        // Un seul appel API - Laravel retourne le service avec ses taches
        const serviceResponse = await serviceService.getById(serviceId);
        this.serviceData = serviceResponse.data;
        
        // Les taches sont déjà incluses dans la réponse du backend Laravel
        this.taches = serviceResponse.data.taches || [];
        
        // Charger les intervenants pour ce service (limiter à 3 pour l'affichage)
        await this.loadIntervenants(serviceId);
        
      } catch (err) {
        console.error('Erreur lors du chargement du service:', err);
        this.error = err.message || 'Impossible de charger les données du service. Veuillez réessayer.';
      } finally {
        if (showLoading) {
          this.loading = false;
        }
      }
    },
    
    async loadIntervenants(serviceId) {
      try {
        // Récupérer les intervenants filtrés par serviceId
        const params = { active: 'true', serviceId: serviceId };
        const response = await intervenantService.getAll(params);
        const intervenants = response.data || [];
        
        // Mapper les données de l'API vers le format attendu
        const mappedIntervenants = intervenants.map(intervenant => {
          const utilisateur = intervenant.utilisateur || {};
          const taches = intervenant.taches || [];
          
          // Extraire les spécialités depuis les taches
          const specialties = taches.map(tache => tache.nom_tache || tache.name || '').filter(Boolean);
          
          // Calculer le tarif moyen depuis les pivots
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
            image: intervenant.image_url || utilisateur.photo || 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=150&h=150&fit=crop',
            verified: intervenant.is_active !== false,
            specialties: specialties,
          };
        });
        
        // Filtrer les intervenants : exclure ceux avec 0 avis ou note de 0
        // Trier par note décroissante, puis prendre les 3 meilleurs
        this.intervenants = mappedIntervenants
          .filter(intervenant => intervenant.reviewCount > 0 && intervenant.rating > 0)
          .sort((a, b) => {
            // Trier par note décroissante, puis par nombre d'avis décroissant
            if (b.rating !== a.rating) {
              return b.rating - a.rating;
            }
            return b.reviewCount - a.reviewCount;
          })
          .slice(0, 3); // Limiter à 3 intervenants
      } catch (error) {
        console.error('Erreur lors du chargement des intervenants:', error);
        this.intervenants = [];
      }
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
</style>
